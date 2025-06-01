jQuery(document).ready(function ($) {
  let selectedRFQProduct = null;
  let selectedSupplier   = null;

  // ───────────────────────────────────────────────────────
  // 0) Helper: initialize or re-initialize a DataTable
  // ───────────────────────────────────────────────────────
  function makeStickySearchable(selector) {
    // If already a DataTable, destroy it so we can re-init
    if ($.fn.DataTable.isDataTable(selector)) {
      $(selector).DataTable().destroy();
    }
    // Re-initialize with desired features
    $(selector).DataTable({
      paging:       false,
      info:         false,
      searching:    true,
      ordering:     true,
      autoWidth:    false,
      responsive:   true,
      stickyHeader: true,        // requires DataTables StickyHeader extension
      order:       [[0, 'asc']], // default sort on first column
    });
  }

  // ───────────────────────────────────────────────────────
  // 1) Fetch RFQ Products from the server
  // ───────────────────────────────────────────────────────
  function fetchRFQProducts() {
    $.post(rfqSupplierAjax.ajax_url, {
      action: 'fetch_rfq_products',
      nonce:  rfqSupplierAjax.nonce
    }, function (res) {
      if (res.success) {
        populateRFQProductTable(res.data.products);
      } else {
        $('#rfq-product-table tbody')
          .html('<tr><td colspan="7">' + res.data.message + '</td></tr>');
      }
    });
  }

  // ───────────────────────────────────────────────────────
  // 2) Populate RFQ-Product table + DataTable it
  // ───────────────────────────────────────────────────────
  function populateRFQProductTable(products) {
    const $tbody = $('#rfq-product-table tbody').empty();

    products.forEach(p => {
      $tbody.append(`
        <tr>
          <td>${p.rfq_product_id}</td>
          <td>${p.product_name}</td>
          <td>${p.quantity}</td>
          <td>${p.specifications}</td>
          <td>${p.notes}</td>
          <td>${p.client_name}</td>
          <td>
            <button class="select-rfq-product"
                    data-rfq-product-id="${p.rfq_product_id}"
                    data-product-name="${p.product_name}"
                    data-quantity="${p.quantity}">
              Select
            </button>
          </td>
        </tr>
      `);
    });

    // Make this table sticky, searchable, sortable
    makeStickySearchable('#rfq-product-table');

    // Re-bind “Select” button clicks
    $('#rfq-product-table .select-rfq-product')
      .off('click')
      .on('click', function () {
        selectedRFQProduct = {
          rfq_product_id: $(this).data('rfq-product-id'),
          product_name:   $(this).data('product-name'),
          quantity:       $(this).data('quantity')
        };
        $('#rfq-product-section').hide();
        $('#supplier-section').show();
        updateSelectedRecords();
        fetchSuppliers();
      });
  }

  // ───────────────────────────────────────────────────────
  // 3) Fetch all Suppliers (fallback)
  // ───────────────────────────────────────────────────────
  function fetchSuppliers() {
    $.post(rfqSupplierAjax.ajax_url, {
      action: 'fetch_suppliers',
      nonce:  rfqSupplierAjax.nonce
    }, function (res) {
      if (res.success) {
        populateSupplierTable(res.data.suppliers);
      } else {
        $('#supplier-table tbody')
          .html('<tr><td colspan="6">' + res.data.message + '</td></tr>');
      }
    });
  }

  // ───────────────────────────────────────────────────────
  // 4) Populate Supplier table + DataTable it
  // ───────────────────────────────────────────────────────
  function populateSupplierTable(suppliers) {
    const $tbody = $('#supplier-table tbody').empty();

    suppliers.forEach(s => {
      $tbody.append(`
        <tr>
          <td>${s.supplier_id}</td>
          <td>${s.supplier_name}</td>
          <td>${s.notes}</td>
          <td>${s.primary_product}</td>
          <td>${s.database_ranking}</td>
          <td>
            <button class="select-supplier"
                    data-supplier-id="${s.supplier_id}"
                    data-supplier-name="${s.supplier_name}">
              Select
            </button>
          </td>
        </tr>
      `);
    });

    // Make supplier table sticky, searchable, sortable
    makeStickySearchable('#supplier-table');

    // Re-bind supplier “Select” clicks
    $('#supplier-table .select-supplier')
      .off('click')
      .on('click', function () {
        selectedSupplier = {
          supplier_id:   $(this).data('supplier-id'),
          supplier_name: $(this).data('supplier-name')
        };
        updateSelectedRecords();
        $('#selected-supplier-id').val(selectedSupplier.supplier_id);
        $('#selected-rfq-product-id').val(selectedRFQProduct.rfq_product_id);
        $('#supplier-section').hide();
        $('#rfq-supplier-form-section').show();
      });
  }

  // ───────────────────────────────────────────────────────
  // 5) Update the “Selected Records” display
  // ───────────────────────────────────────────────────────
  function updateSelectedRecords() {
    const rpHtml = selectedRFQProduct
      ? `<strong>RFQ Product:</strong> ${selectedRFQProduct.product_name} (Qty: ${selectedRFQProduct.quantity})`
      : 'No RFQ Product selected.';
    const spHtml = selectedSupplier
      ? `<strong>Supplier:</strong> ${selectedSupplier.supplier_name}`
      : 'No Supplier selected.';

    $('#selected-rfq-product, #rfq-supplier-form-section #selected-rfq-product').html(rpHtml);
    $('#selected-supplier,     #rfq-supplier-form-section #selected-supplier').html(spHtml);
  }

  // ───────────────────────────────────────────────────────
  // 6a) Supplier search form
  // ───────────────────────────────────────────────────────
  $('#supplier-search-btn')
    .off('click')
    .on('click', function () {
      const name = $('#search-supplier-name').val();
      const prod = $('#search-supplier-product').val();
      $.post(rfqSupplierAjax.ajax_url, {
        action: 'search_suppliers',
        nonce:  rfqSupplierAjax.nonce,
        name:   name,
        product: prod
      }, function (res) {
        if (res.success) {
          populateSupplierTable(res.data.suppliers);
        } else {
          $('#supplier-table tbody')
            .html('<tr><td colspan="6">' + res.data.message + '</td></tr>');
        }
      });
    });

  // ───────────────────────────────────────────────────────
  // 6b) Show Add-New-Supplier form
  // ───────────────────────────────────────────────────────
  $('#add-new-supplier-btn')
    .off('click')
    .on('click', function () {
      $('#supplier-section').hide();
      $('#new-supplier-section').show();
    });

  // ───────────────────────────────────────────────────────
  // 6c) Cancel Add-New-Supplier
  // ───────────────────────────────────────────────────────
  $('#cancel-add-supplier')
    .off('click')
    .on('click', function () {
      $('#new-supplier-section').hide();
      $('#supplier-section').show();
    });

  // ───────────────────────────────────────────────────────
  // 6d) Submit New-Supplier form
  // ───────────────────────────────────────────────────────
  $('#new-supplier-form')
    .off('submit')
    .on('submit', function (e) {
      e.preventDefault();
      let data = $(this).serialize();
      data += '&action=create_supplier&nonce=' + rfqSupplierAjax.nonce;
      $.post(rfqSupplierAjax.ajax_url, data, function (res) {
        if (res.success) {
          selectedSupplier = {
            supplier_id:   res.data.supplier_id,
            supplier_name: res.data.supplier_name
          };
          updateSelectedRecords();
          $('#new-supplier-section').hide();
          $('#rfq-supplier-form-section').show();
          $('#selected-supplier-id').val(res.data.supplier_id);
        } else {
          alert('Error: ' + res.data.message);
        }
      });
    });

  // ───────────────────────────────────────────────────────
  // 6e) Submit RFQ-Supplier assignment form
  // ───────────────────────────────────────────────────────
  $('#rfq-supplier-form')
    .off('submit')
    .on('submit', function (e) {
      e.preventDefault();
      $.post(rfqSupplierAjax.ajax_url, {
        action:         'submit_rfq_supplier',
        nonce:          rfqSupplierAjax.nonce,
        rfq_product_id: selectedRFQProduct.rfq_product_id,
        supplier_id:    selectedSupplier.supplier_id,
        notes:          $('#notes').val()
      }, function (res) {
        if (res.success) {
          // Redirect on success
          window.location.href = 'https://tntbearings.com/supplier-rfq-product-table/';
        } else {
          alert('Error: ' + res.data.message);
        }
      });
    });

  // ───────────────────────────────────────────────────────
  // 7) Kick things off
  // ───────────────────────────────────────────────────────
  fetchRFQProducts();
});
