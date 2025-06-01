<?php
/*
Plugin Name: Editable Clients Table
Description: Provides a shortcode [editable_clients] to show an editable, fixed-header, searchable table of clients, plus a “New Client” row. Uses explicit mysqli connection.
Version:     1.2
Author:      You
License:     GPL2
*/

defined('ABSPATH') or exit;

/**
 * Return a mysqli connection to TNT_Db
 */
if ( ! function_exists( 'get_tnt_client_db_connection' ) ) {
    function get_tnt_client_db_connection() {
        $conn = new mysqli('localhost', 'Tom1977', 'TNT2024@!', 'TNT_Db');
        if ( $conn->connect_error ) {
            error_log( 'DB Connection Error: ' . $conn->connect_error );
            return false;
        }
        $conn->set_charset( 'utf8mb4' );
        return $conn;
    }
}

// 1) Enqueue DataTables & our script
add_action('wp_enqueue_scripts', function(){
    // Core DataTables
    wp_enqueue_style(
      'ect-datatables-css',
      'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css'
    );
    wp_enqueue_script(
      'ect-datatables-js',
      'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js',
      ['jquery'],
      null,
      true
    );

    // FixedHeader extension (for sticky headers)
    wp_enqueue_style(
      'ect-fixedheader-css',
      'https://cdn.datatables.net/fixedheader/4.0.2/css/fixedHeader.dataTables.min.css',
      ['ect-datatables-css'],
      '4.0.2'
    );
    wp_enqueue_script(
      'ect-fixedheader-js',
      'https://cdn.datatables.net/fixedheader/4.0.2/js/dataTables.fixedHeader.min.js',
      ['ect-datatables-js'],
      '4.0.2',
      true
    );

    // Your custom script
    wp_enqueue_script(
      'ect-script',
      plugin_dir_url(__FILE__).'js/ect-script.js',
      ['jquery','ect-datatables-js','ect-fixedheader-js'],
      null,
      true
    );
    wp_localize_script('ect-script','ect_ajax',[
      'url'   => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('ect_nonce')
    ]);
});

// 2) Shortcode: render table + new-row
add_shortcode('editable_clients', function(){
    $conn = get_tnt_client_db_connection();
    if (!$conn) return '<p>Database connection failed.</p>';
    ob_start(); ?>
    <style>
      /* visible borders on table */
      #ect-table { border-collapse: collapse; width: 100%; }
      #ect-table, #ect-table th, #ect-table td { border: 1px solid #666; }
    </style>
    <table id="ect-table" class="display" style="width:100%">
      <thead>
        <tr>
          <th>ID</th><th>Client Name</th><th>Address</th><th>VAT Number</th>
          <th>Main Contact</th><th>Contact Number</th><th>Source</th>
          <th>Notes</th><th>Bank Details</th><th>Account Number</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $res = $conn->query("SELECT * FROM client ORDER BY client_id");
      while($r = $res->fetch_assoc()):
      ?>
        <tr data-id="<?= intval($r['client_id']) ?>">
          <td><?= intval($r['client_id']) ?></td>
          <td contenteditable="true" data-field="client_name"><?= esc_html($r['client_name']) ?></td>
          <td contenteditable="true" data-field="address"><?= esc_html($r['address']) ?></td>
          <td contenteditable="true" data-field="vat_number"><?= esc_html($r['vat_number']) ?></td>
          <td contenteditable="true" data-field="main_contact"><?= esc_html($r['main_contact']) ?></td>
          <td contenteditable="true" data-field="main_contact_number"><?= esc_html($r['main_contact_number']) ?></td>
          <td contenteditable="true" data-field="source"><?= esc_html($r['source']) ?></td>
          <td contenteditable="true" data-field="notes"><?= esc_html($r['notes']) ?></td>
          <td contenteditable="true" data-field="bank_details"><?= esc_html($r['bank_details']) ?></td>
          <td contenteditable="true" data-field="account_number"><?= esc_html($r['account_number']) ?></td>
          <td><button class="ect-save-row button">Save</button></td>
        </tr>
      <?php endwhile; ?>

      <!-- New client row -->
      <tr id="ect-new-row">
        <td>New</td>
        <td contenteditable="true" id="new_client_name"></td>
        <td contenteditable="true" id="new_address"></td>
        <td contenteditable="true" id="new_vat_number"></td>
        <td contenteditable="true" id="new_main_contact"></td>
        <td contenteditable="true" id="new_main_contact_number"></td>
        <td contenteditable="true" id="new_source"></td>
        <td contenteditable="true" id="new_notes"></td>
        <td contenteditable="true" id="new_bank_details"></td>
        <td contenteditable="true" id="new_account_number"></td>
        <td><button id="ect-add-row" class="button button-primary">Add</button></td>
      </tr>
      </tbody>
    </table>
    <?php
    $conn->close();
    return ob_get_clean();
});

// AJAX: update existing client (logged-in)
add_action('wp_ajax_ect_update_client', 'ect_update_client_cb');
// AJAX: update existing client (not logged-in)
add_action('wp_ajax_nopriv_ect_update_client', 'ect_update_client_cb');
function ect_update_client_cb(){
    check_ajax_referer('ect_nonce','nonce');
    $conn = get_tnt_client_db_connection() or wp_send_json_error('DB error');
    $id    = intval($_POST['id']);
    $field = preg_replace('/[^a-z_]/','',$_POST['field']);
    $value = $conn->real_escape_string(sanitize_text_field($_POST['value']));
    $allowed = ['client_name','address','vat_number','main_contact','main_contact_number','source','notes','bank_details','account_number'];
    if(!in_array($field,$allowed,true) || $id<1){
      $conn->close();
      wp_send_json_error('Invalid');
    }
    $sql = "UPDATE client SET {$field}='{$value}' WHERE client_id={$id}";
    if($conn->query($sql)){
      $conn->close();
      wp_send_json_success();
    } else {
      $err=$conn->error;
      $conn->close();
      wp_send_json_error($err);
    }
}

// AJAX: add new client (logged-in)
add_action('wp_ajax_ect_add_client', 'ect_add_client_cb');
// AJAX: add new client (not logged-in)
add_action('wp_ajax_nopriv_ect_add_client', 'ect_add_client_cb');
function ect_add_client_cb(){
    check_ajax_referer('ect_nonce','nonce');
    $conn = get_tnt_client_db_connection() or wp_send_json_error('DB error');
    $fields = ['client_name','address','vat_number','main_contact','main_contact_number','source','notes','bank_details','account_number'];
    $vals = [];
    foreach($fields as $f){
      $vals[$f] = $conn->real_escape_string(sanitize_text_field($_POST[$f] ?? ''));
    }
    $cols = implode(',', array_keys($vals));
    $esc  = implode("','", array_values($vals));
    $sql  = "INSERT INTO client ({$cols}) VALUES ('{$esc}')";
    if($conn->query($sql)){
      $new_id = $conn->insert_id;
      $conn->close();
      wp_send_json_success(['id'=>$new_id]);
    } else {
      $err=$conn->error;
      $conn->close();
      wp_send_json_error($err);
    }
}
