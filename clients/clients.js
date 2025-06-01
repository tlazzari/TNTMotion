jQuery(document).ready(function($){

  // Initialize DataTable
  $('#ect-table').DataTable({
    paging:       false,
    info:         false,
    searching:    true,
    ordering:     true,
    autoWidth:    false,
    responsive:   true,
    stickyHeader: true,
    order: [[0,'asc']]
  });

  // 1) Save existing row
  $('#ect-table').on('click','.ect-save-row', function(){
    const $tr = $(this).closest('tr'),
          id  = $tr.data('id');

    $tr.find('td[contenteditable][data-field]').each(function(){
      const field = $(this).data('field'),
            value = $(this).text().trim();
      $.post(ect_ajax.url, {
        action:'ect_update_client',
        nonce: ect_ajax.nonce,
        id:     id,
        field:  field,
        value:  value
      }, function(res){
        if(!res.success){
          alert('Update failed: '+res.data);
        }
      });
    });
  });

  // 2) Add new client
  $('#ect-add-row').on('click', function(){
    const data = {
      action:'ect_add_client',
      nonce: ect_ajax.nonce
    };
    $('#ect-new-row td[id^="new_"]').each(function(){
      const id  = $(this).attr('id').replace('new_',''),
            txt = $(this).text().trim();
      data[id] = txt;
    });

    $.post(ect_ajax.url, data, function(res){
      if(res.success){
        const nid = res.data.id;
        // build new row markup
        let cols = '';
        $('#ect-new-row td[id^="new_"]').each(function(){
          cols += `<td contenteditable="true">${$(this).text().trim()}</td>`;
        });
        const $row = $(
          `<tr data-id="${nid}">
             <td>${nid}</td>${cols}
             <td><button class="ect-save-row button">Save</button></td>
           </tr>`
        );
        // insert above new-row
        $('#ect-new-row').before($row);
        // clear new-row
        $('#ect-new-row td[id^="new_"]').text('');
        // re-draw DataTable
        $('#ect-table').DataTable().row.add($row).draw(false);
      } else {
        alert('Insert failed: '+res.data);
      }
    });
  });

});
