jQuery(function($){
  var selectedRFQProduct  = null;
  var assignedSupplierIds = [];
  var rfqSection          = $('#rfq-product-section');
  var supplierSection     = $('#supplier-section');
  var $rfqTable           = $('#rfq-product-table');
  var $supplierTable      = $('#supplier-table');

  // 0) Init / re-init DataTable
  function initDataTable(selector) {
    if ( $.fn.DataTable.isDataTable(selector) ) {
      $(selector).DataTable().destroy();
    }
    $(selector).DataTable({
      paging:    false,
      info:      false,
      searching: true,
      ordering:  true,
      autoWidth: false,
      order:     [[0,'asc']]
    });
  }

  // 1) Fetch RFQs
  function fetchRFQs() {
    $.post(rfqSupplierAjax.ajax_url, {
      action: 'fetch_rfq_products',
      nonce:  rfqSupplierAjax.nonce
    }, function(res){
      if (!res.success) {
        $rfqTable.find('tbody')
          .html('<tr><td colspan="9">Error: '+res.data.message+'</td></tr>');
      } else {
        renderRFQTable(res.data.products);
      }
    }, 'json');
  }

  // 2) Render RFQ table
  function renderRFQTable(items) {
    var $body = $rfqTable.find('tbody').empty();
    items.forEach(function(p){
      $body.append(
        '<tr>' +
          '<td>'+ p.rfq_product_id        +'</td>' +
          '<td>'+ p.rfq_client_id         +'</td>' +
          '<td>'+ p.product_family_name   +'</td>' +
          '<td>'+ p.product_name          +'</td>' +
          '<td>'+ p.quantity              +'</td>' +
          '<td>'+ p.specifications       +'</td>' +
          '<td>'+ p.notes                 +'</td>' +
          '<td>'+ p.client_name           +'</td>' +
          '<td>' +
            '<button class="select-rfq-product" '+
                    'data-id="'+p.rfq_product_id+'" '+
                    'data-client-id="'+p.rfq_client_id+'" '+
                    'data-family="'+p.product_family_name+'" '+
                    'data-name="'+p.product_name+'" '+
                    'data-qty="'+p.quantity+'" '+
                    'data-client="'+p.client_name+'">Select</button>' +
          '</td>' +
        '</tr>'
      );
    });
    initDataTable('#rfq-product-table');
  }
  

// 3) On RFQ “Select” click
$('#rfq-product-table').on('click', '.select-rfq-product', function() {
  // grab ID and bounce to same page with query string
  var id   = $(this).data('id'),
      base = window.location.href.split('?')[0];
  window.location.href = base + '?rfq_product_id=' + id;
});

// 3b) If there’s already an rfq_product_id in the URL, show supplier panel
$(function(){
  // parse the rfq_product_id param
  var params = new URLSearchParams(window.location.search),
      rp     = params.get('rfq_product_id');
  if (!rp) return;            // no RFQ selected yet

  // hide RFQ list, show supplier UI
  $('#rfq-product-section').hide();
  $('#supplier-section').show();

  // you’ll want to set selectedRFQProduct here if you need its details,
  // e.g. via a data-fetch or stash them in PHP into a JS var.

  // fetch already-assigned suppliers, then all suppliers
  $.post(rfqSupplierAjax.ajax_url, {
    action:         'fetch_assigned_suppliers',
    nonce:          rfqSupplierAjax.nonce,
    rfq_product_id: rp
  }, function(res) {
    assignedSupplierIds = res.success ? res.data.supplier_ids : [];
    fetchSuppliers();  // now load the full supplier list
  }, 'json');
});


  // 4) Fetch all suppliers
function fetchSuppliers() {
  $.post(rfqSupplierAjax.ajax_url, {
    action: 'fetch_suppliers',
    nonce:  rfqSupplierAjax.nonce
  }, function(res){
    if(!res.success){
      $('#supplier-table tbody')
        .html('<tr><td colspan="6">Error loading suppliers</td></tr>');
    } else {
      renderSupplierTable(res.data.suppliers);
    }
  }, 'json');
}

  // 5) Render supplier table, marking assigned rows
  function renderSupplierTable(list) {
    var $body = $supplierTable.find('tbody').empty();
    list.forEach(function(s){
      // make sure we compare integers
      var id  = parseInt(s.supplier_id,10),
          cls = assignedSupplierIds.indexOf(id) !== -1 ? 'assigned-row' : '';
      $body.append(
        '<tr class="'+cls+'">'+
          '<td>'+ s.supplier_id      +'</td>'+
          '<td>'+ s.supplier_name    +'</td>'+
          '<td>'+ s.notes            +'</td>'+
          '<td>'+ s.primary_product  +'</td>'+
          '<td>'+ s.database_ranking +'</td>'+
          '<td><button class="select-supplier" data-id="'+id+'">Select</button></td>'+
        '</tr>'
      );
    });
    initDataTable('#supplier-table');
  }

  // 6) On supplier “Select” → submit immediately
  $supplierTable.on('click','.select-supplier',function(){
    var supplierId = $(this).data('id');
    if ( assignedSupplierIds.indexOf(supplierId) !== -1 ) {
      return alert('This supplier is already assigned.');
    }
    $.post(rfqSupplierAjax.ajax_url, {
      action:         'submit_rfq_supplier',
      nonce:          rfqSupplierAjax.nonce,
      rfq_product_id: selectedRFQProduct.rfq_product_id,
      supplier_id:    supplierId,
      notes:          ''
    }, function(res){
      if (res.success) {
        window.location.href = 'https://tntbearings.com/supplier-rfq-product-table/';
      } else {
        alert('Error: '+res.data.message);
      }
    }, 'json');
  });

  // 7) Kick things off
  fetchRFQs();
});
