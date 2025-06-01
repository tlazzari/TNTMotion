jQuery(document).ready(function($){

  /*** 1) Inline-edit document-level fields ***/
  $('body').on('click','span.editable-doc-field,textarea.editable-doc-field',function(e){
    e.stopPropagation();
    const $s     = $(this),
          docId  = $s.data('doc-id'),
          field  = $s.data('field'),
          type   = $s.data('field-type') || 'text',
          oldVal = $s.is('textarea') ? $s.val().trim() : $s.text().trim();

    if ($s.find('input,select,textarea').length) return;

    let $input;
    if (type === 'select') {
      $input = $('<select class="inline-doc-select" style="width:120px;"></select>');
      let action = field==='currency_id' ? 'otp_get_currencies'
                 : field==='entity_id'   ? 'otp_get_entities'
                 : field==='supplier_id' ? 'otp_get_suppliers'
                 : '';
      if (!action) return;
      $.post(otpData.ajax_url,{ action, nonce: otpData.nonce }, resp => {
        if (!resp.success) return alert('Err: '+resp.data);
        resp.data.forEach(x=>{
          const id   = field==='entity_id'   ? x.entity_id
                     : field==='supplier_id' ? x.supplier_id
                     :                          x.currency_id;
          const text = field==='entity_id'   ? x.entity_name
                     : field==='supplier_id' ? x.supplier_name
                     :                          x.currency_description;
          const sel  = (id==$s.data('current-id')) ? ' selected' : '';
          $input.append(`<option value="${id}"${sel}>${text}</option>`);
        });
      },'json');
    }
    else if ($s.is('textarea')) {
      $input = $('<textarea class="inline-doc-textarea" style="width:100%;resize:vertical;"></textarea>')
               .val(oldVal);
    }
    else {
      $input = $('<input type="text" class="inline-doc-input" style="width:90%;" />')
               .val(oldVal);
    }

    $s.empty().append($input);
    $input.focus().on('blur keypress', evt => {
      if (evt.type==='blur' || evt.which===13) {
        const newVal = $input.val().trim();
        $s.text(newVal);
        $.post(otpData.ajax_url,{
          action: 'otp_update_doc_field',
          nonce:  otpData.nonce,
          doc_id: docId,
          field,
          value: newVal
        }, r => {
          if (!r.success) {
            alert('Save err: '+r.data);
            $s.text(oldVal);
          }
        },'json');
      }
    });
  });

  //1.1  // supplier autocomplete
  $('#supplier-autocomplete').autocomplete({
    source(request, response) {
      $.post(otpData.ajax_url, {
        action: 'otp_search_suppliers',
        nonce:  otpData.nonce,
        term:   request.term
      }, resp => {
        if (!resp.success) return;
        // map to jQuery UI format: { label, value }
        const list = resp.data.map(s => ({
          label: s.supplier_name,
          value: s.supplier_id
        }));
        response(list);
      }, 'json');
    },
    minLength: 2,
    select(event, ui) {
      // fill both the visible text and hidden ID
      $('#supplier-autocomplete').val(ui.item.label);
      $('#supplier-id').val(ui.item.value);
      return false; // don't replace the text again
    }
  });



  /*** 2) Inline-edit supplier_order lines ***/
  $('body').on('click','span.editable-line-field',function(e){
    e.stopPropagation();
    const $cell   = $(this),
          orderId = $cell.data('order-id'),
          field   = $cell.data('field'),
          oldVal  = $cell.text().trim(),
          type    = field==='currency_id'
                    ? 'select'
                    : (field==='specifications'||field==='measurements')
                      ? 'textarea'
                      : 'text';

    if ($cell.find('input,select,textarea').length) return;

    let $input;
    if (type==='select') {
      $input = $('<select class="inline-edit"></select>');
      $.post(otpData.ajax_url,{ action:'otp_get_currencies', nonce:otpData.nonce }, resp => {
        if (!resp.success) return alert('Err:'+resp.data);
        resp.data.forEach(c=>{
          const sel = (c.currency_id==$cell.data('current-id')) ? ' selected' : '';
          $input.append(`<option value="${c.currency_id}"${sel}>${c.currency_description}</option>`);
        });
      },'json');
    }
    else if (type==='textarea') {
      $input = $('<textarea class="inline-edit" style="width:100%;resize:vertical;"></textarea>').val(oldVal);
    }
    else {
      $input = $('<input type="text" class="inline-edit" style="width:90%;" />').val(oldVal);
    }

    $cell.empty().append($input);
    $input.focus().on('blur keypress', evt => {
      if (evt.type==='blur' || evt.which===13) {
        const newVal = $input.val().trim();
        $cell.text(newVal);
        $.post(otpData.ajax_url,{
          action:   'otp_update_supplier_order_field',
          nonce:    otpData.nonce,
          order_id: orderId,
          field,
          value:    newVal
        }, r => {
          if (!r.success) {
            alert('Save failed: '+r.data);
            $cell.text(oldVal);
          }
        },'json');
      }
    });
  });


  /*** 3) Inline-edit order_item fields ***/
  $('body').on('click','span.editable-item-field',function(e){
    e.stopPropagation();
    const $cell  = $(this),
          itemId = $cell.data('item-id'),
          field  = $cell.data('field'),
          oldVal = $cell.text().trim(),
          type   = field==='currency_id'
                   ? 'select'
                   : (field==='description')
                     ? 'textarea'
                     : 'text';

    if ($cell.find('input,select,textarea').length) return;

    let $input;
    if (type==='select') {
      $input = $('<select class="inline-edit"></select>');
      $.post(otpData.ajax_url,{ action:'otp_get_currencies', nonce:otpData.nonce }, resp => {
        if (!resp.success) return alert('Err:'+resp.data);
        resp.data.forEach(c=>{
          const sel = (c.currency_id==$cell.data('current-id')) ? ' selected' : '';
          $input.append(`<option value="${c.currency_id}"${sel}>${c.currency_description}</option>`);
        });
      },'json');
    }
    else if (type==='textarea') {
      $input = $('<textarea class="inline-edit" style="width:100%;resize:vertical;"></textarea>').val(oldVal);
    }
    else {
      $input = $('<input type="text" class="inline-edit" style="width:90%;" />').val(oldVal);
    }

    $cell.empty().append($input);
    $input.focus().on('blur keypress', evt => {
      if (evt.type==='blur' || evt.which===13) {
        const newVal = $input.val().trim();
        $cell.text(newVal);
        $.post(otpData.ajax_url,{
          action:  'otp_update_order_item_field',
          nonce:   otpData.nonce,
          item_id: itemId,
          field,
          value:   newVal
        }, r => {
          if (!r.success) {
            alert('Save failed: '+r.data);
            $cell.text(oldVal);
          }
        },'json');
      }
    });
  });

  //Tax update

$('#tax-rate-input').on('change blur', function(){
  const rate = parseFloat($(this).val());
  if (isNaN(rate)) return;

  console.log('Bulk Tax Rate:', rate);

  // 1) Update each line and fire its own AJAX
  $('#existing-lines-table tbody tr').each(function(){
    const $row    = $(this);
    const orderId = $row
      .find('span.editable-line-field[data-field="price"]')
      .data('order-id');
    const price   = parseFloat(
      $row.find('span.editable-line-field[data-field="price"]').text()
    ) || 0;
    const newTax  = (price * rate / 100).toFixed(2);

    // update UI
    $row.find('span.editable-line-field[data-field="tax"]').text(newTax);

    // persist line-level tax
    $.post(otpData.ajax_url, {
      action:   'otp_update_supplier_order_field',
      nonce:    otpData.nonce,
      order_id: orderId,
      field:    'tax',
      value:    newTax
    }, resp => {
      if (!resp.success) console.error('Line save failed', orderId, resp.data);
    }, 'json');
  });

  // 2) Now recalc the document’s totals *once*
  if (window.currentOrderDocId) {
    $.post(otpData.ajax_url, {
      action:               'otp_calculate_totals',
      nonce:                otpData.nonce,
      order_document_id:    window.currentOrderDocId
    }, function(resp){
      if (resp.success) {
        console.log('Document totals updated:', resp.data);
        // If you also have the order-document table on this same page,
        // update its UI spans here. For example:
        $('span.editable-doc-field[data-field="tax"]').text(resp.data.tax);
        $('span.editable-doc-field[data-field="total"]').text(resp.data.total);
        $('span.editable-doc-field[data-field="grand_total"]').text(resp.data.grand_total);
      } else {
        console.error('Doc recalc failed:', resp.data);
      }
    }, 'json');
  }
});





  /*** 4) Navigation buttons ***/
  $('body')
    .on('click','.add-order-line',function(){
      const id = $(this).data('doc-id');
      window.location.href = otpData.base_url+'?view=order_lines&order_document_id='+id;
    })
    .on('click','.add-items',function(){
      const id = $(this).data('doc-id');
      window.location.href = otpData.base_url+'?view=order_items&order_document_id='+id;
    })
    .on('click','.order-document-link',function(){
      const id = $(this).data('doc-id');
      $.post(otpData.ajax_url,{
        action: 'otp_order_document_link',
        nonce:  otpData.nonce,
        order_document_id: id
      }, resp => {
        if (resp.success) window.location.href = resp.data;
        else alert('Err: '+resp.data);
      },'json');
    });
// terms and condition button
    $('body').on('click', '.edit-terms-btn', function(){
    const docId = $(this).data('doc-id');
    window.location.href = otpData.base_url
                        + '?view=edit_terms&order_document_id='
                        + docId;
  });


  /*** 5) “Add New Document” form ***/
  $('body').on('submit','#otp-add-doc-form',function(e){
    e.preventDefault();
    const data = $(this).serializeArray();
    data.push({name:'action',value:'otp_add_order_document'});
    data.push({name:'nonce',value:otpData.nonce});
    $.post(otpData.ajax_url,data, resp => {
      if (resp.success) location.reload();
      else alert('Err: '+resp.data);
    },'json');
  });


  /*** 6) Populate currency dropdowns ***/
  $('body').on('focus','select[name="currency_id"]',function(){
    const $sel = $(this);
    if ($sel.find('option').length>1) return;
    $sel.empty().append('<option value="">--Select--</option>');
    $.post(otpData.ajax_url,{
      action:  'otp_get_currencies',
      nonce:   otpData.nonce
    }, resp => {
      if (!resp.success) return alert('Err: '+resp.data);
      resp.data.forEach(c=>{
        $sel.append('<option value="'+c.currency_id+'">'+c.currency_description+'</option>');
      });
    },'json');
  });


  /*** 7) Save the “Add New Item” form ***/
  $('body').on('submit','#otp-add-item-form',function(e){
    e.preventDefault();
    const $f = $(this);
    const data = $f.serializeArray();
    data.push({ name: 'action', value: 'otp_add_order_item' });
    data.push({ name: 'nonce',  value: otpData.nonce });
    $.post(otpData.ajax_url, data, resp => {
      if (resp.success) location.reload();
      else alert('Error saving item: '+resp.data);
    },'json');
  });


  /*** 8) Assign selected lines ***/
  $('body').on('click','#otp-assign-lines',function(){
    const docId = $(this).data('doc-id'),
          ids   = $('.select-line-checkbox:checked').map(function(){
                    return $(this).data('order-id');
                  }).get();
    if (!ids.length) return alert('Select at least one.');
    if (!confirm('Assign?')) return;
    $.post(otpData.ajax_url,{
      action:             'otp_assign_order_lines',
      nonce:              otpData.nonce,
      order_document_id:  docId,
      selected_order_ids: ids
    }, resp => {
      if (resp.success) location.reload();
      else alert('Err: '+resp.data);
    },'json');
  });


  /*** 9) Print PDF ***/
  $('body').on('click','#print-document', function(){
    window.print();
  });



// 10) Calculate button
$('body').on('click', '.calculate-totals', function(){
  const docId = $(this).data('doc-id');
  $.post(otpData.ajax_url, {
    action:               'otp_calculate_totals',
    nonce:                otpData.nonce,
    order_document_id:    docId
  }, function(resp){
    if (!resp.success) {
      return alert('Error: ' + resp.data);
    }
    const { total, tax, grand_total } = resp.data;
    const $row = $('tr[data-doc-id="'+docId+'"]');
    // Update UI
    $row.find('span.editable-doc-field[data-field="total"]').text(total);
    $row.find('span.editable-doc-field[data-field="tax"]').text(tax);
    $row.find('span.editable-doc-field[data-field="grand_total"]').text(grand_total);
  }, 'json');
  });
});


