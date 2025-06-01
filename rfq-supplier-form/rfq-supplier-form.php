<?php
/**
 * Plugin Name: RFQ Supplier Manager
 * Description: A WordPress plugin to manage RFQ Supplier interactions.
 * Version: 1.1
 * Author: Your Name
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get a mysqli connection to TNT_Db
 */
function get_tnt_supplier_db_connection() {
    $conn = new mysqli('localhost', 'Tom1977', 'TNT2024@!', 'TNT_Db');
    if ($conn->connect_error) {
        error_log('DB Connection Error: ' . $conn->connect_error);
        return false;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

/**
 * Enqueue CSS/JS assets
 */
function rfq_supplier_enqueue_assets() {
    wp_enqueue_style(
      'datatables-css',
      'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css',
      [],
      '1.10.24'
    );
    wp_enqueue_script(
      'datatables-js',
      'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js',
      ['jquery'],
      '1.10.24',
      true
    );
    wp_enqueue_style(
        'rfq-supplier-style',
        plugin_dir_url(__FILE__) . 'css/rfq-supplier-style.css',
        [],
        '1.1'
    );
    wp_enqueue_script(
        'rfq-supplier-script',
        plugin_dir_url(__FILE__) . 'js/rfq-supplier-script.js',
        ['jquery','datatables-js'],  // <- add datatables-js as a dependency
        '1.2',                       // <- bump your version so browsers pull the new file
        true
    );
    wp_localize_script(
        'rfq-supplier-script',
        'rfqSupplierAjax',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rfq_supplier_nonce')
        ]
    );
}
add_action('wp_enqueue_scripts', 'rfq_supplier_enqueue_assets');



/**
 * Shortcode: display RFQ Supplier Manager UI
 */
function rfq_supplier_display() {
    // 0) Read the incoming rfq_product_id (or zero if none)
    $rp = isset($_GET['rfq_product_id']) ? intval($_GET['rfq_product_id']) : 0;

    ob_start();
    ?>
    <div id="rfq-supplier-manager">
      <?php if ( $rp === 0 ): ?>
        <h2>RFQ Supplier Management</h2>
        <div id="rfq-product-section">
          <h3>Select RFQ Product</h3>
          <table id="rfq-product-table">
            <thead>
              <tr>
                <th>RFQ Product ID</th>
                <th>RFQ Client ID</th>
                <th>Family</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Specs</th>
                <th>Notes</th>
                <th>Client</th>
                <th>Select</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      <?php else: ?>
        <h2>Assign Supplier to RFQ #<?php echo $rp; ?></h2>
        <p><a href="<?php echo esc_url( remove_query_arg('rfq_product_id') ); ?>">&larr; Back to RFQ list</a></p>

        <div id="supplier-section">

          <table id="supplier-table">
            <thead>
              <tr>
                <th>ID</th><th>Name</th><th>Notes</th><th>Primary Product</th><th>Ranking</th><th>Select</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

         <div id="new-supplier-section" style="display:none;">
            <h3>Add New Supplier</h3>
            <form id="new-supplier-form">
                <div><label>Name: <input type="text" name="supplier_name" required></label></div>
                <div><label>Address: <input type="text" name="supplier_address"></label></div>
                <div><label>Main Contact: <input type="text" name="supplier_main_contact"></label></div>
                <div><label>Source: <input type="text" name="supplier_source"></label></div>
                <button type="submit">Create Supplier</button>
                <button type="button" id="cancel-add-supplier">Cancel</button>
            </form>
        </div>

        <div id="rfq-supplier-form-section" style="display:none;">
            <h3>Submit RFQ Supplier</h3>
            <form id="rfq-supplier-form">
                <input type="hidden" id="selected-rfq-product-id" name="rfq_product_id">
                <input type="hidden" id="selected-supplier-id"     name="supplier_id">
                <div><label for="notes">Notes:</label><textarea id="notes" name="notes" required></textarea></div>
                <button type="submit">Submit RFQ Supplier</button>
            </form>
        </div>

        <div id="response-message" style="display:none; margin-top:20px;"></div>
    </div>

        <script>
        jQuery(function($){
          var selectedRFQ = <?php echo $rp; ?>;
          // 1) get already assigned
          $.post(rfqSupplierAjax.ajax_url, {
            action: 'fetch_assigned_suppliers',
            nonce:  rfqSupplierAjax.nonce,
            rfq_product_id: selectedRFQ
          }, function(res){
            window.assignedSupplierIds = res.data.supplier_ids || [];
            fetchSuppliers();
          }, 'json');

          // 2) fetchSuppliers() (reuse your existing function)
          function fetchSuppliers() {
            $.post(rfqSupplierAjax.ajax_url, {
              action: 'fetch_suppliers',
              nonce:  rfqSupplierAjax.nonce
            }, function(res){
              var $tb = $('#supplier-table tbody').empty();
              if(!res.success){
                $tb.html('<tr><td colspan="6">Error loading</td></tr>');
                return;
              }
              res.data.suppliers.forEach(function(s){
                var cls = assignedSupplierIds.includes(+s.supplier_id)
                          ? 'assigned-row' : '';
                $tb.append(
                  '<tr class="'+cls+'">'+
                    '<td>'+s.supplier_id+'</td>'+
                    '<td>'+s.supplier_name+'</td>'+
                    '<td>'+s.notes+'</td>'+
                    '<td>'+s.primary_product+'</td>'+
                    '<td>'+s.database_ranking+'</td>'+
                    '<td><button class="select-supplier" data-id="'+s.supplier_id+'">Select</button></td>'+
                  '</tr>'
                );
              });
              $('#supplier-table').DataTable({ destroy:true, paging:false, searching:true });
            }, 'json');
          }

          // 3) on select
          $('#supplier-table').on('click','.select-supplier',function(){
            var sid = +$(this).data('id');
            if(window.assignedSupplierIds.includes(sid)){
              return alert('Already assigned');
            }
            $.post(rfqSupplierAjax.ajax_url, {
              action: 'submit_rfq_supplier',
              nonce:  rfqSupplierAjax.nonce,
              rfq_product_id: selectedRFQ,
              supplier_id:    sid,
              notes:          ''
            }, function(res){
              if(res.success){
                location.href = location.href.split('?')[0];
              } else {
                alert('Error: '+res.data.message);
              }
            }, 'json');
          });
        });
        </script>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('rfq_supplier_form','rfq_supplier_display');

/**
 * AJAX: fetch RFQ products
 */
// in your plugin file
function fetch_rfq_products() {
    check_ajax_referer('rfq_supplier_nonce', 'nonce');
    $conn = get_tnt_supplier_db_connection();
    if ( ! $conn ) wp_send_json_error([ 'message' => 'Database connection failed.' ]);

    $sql = "
        SELECT
          rp.rfq_product_id,
          rp.rfq_client_id,
          pf.product_family_name,
          p.product_name,
          rp.quantity,
          rp.specifications,
          rp.notes,
          c.client_name
        FROM rfq_product rp
        JOIN product_family pf 
          ON pf.product_family_id = rp.product_family_id
        JOIN products p  
          ON p.product_id = rp.product_id
        LEFT JOIN rfq_client rc 
          ON rc.rfq_client_id = rp.rfq_client_id
        JOIN client c  
          ON c.client_id = rc.client_id
        WHERE (
            rc.status NOT IN (7,8,19)
            OR rc.status IS NULL
        )
        ORDER BY rp.rfq_date DESC
    ";
    $res = $conn->query($sql);
    $products = [];
    while ( $row = $res->fetch_assoc() ) {
        $products[] = $row;
    }
    wp_send_json_success([ 'products' => $products ]);
}


add_action('wp_ajax_fetch_rfq_products', 'fetch_rfq_products');
add_action('wp_ajax_nopriv_fetch_rfq_products', 'fetch_rfq_products');

/**
 * AJAX: fetch all suppliers (fallback)
 */
function fetch_suppliers() {
    check_ajax_referer('rfq_supplier_nonce', 'nonce');
    $conn = get_tnt_supplier_db_connection();
    if (!$conn) wp_send_json_error(['message' => 'Database connection failed.']);
    $sql = "SELECT supplier_id, supplier_name, notes, primary_product, database_ranking FROM suppliers ORDER BY database_ranking ASC";
    $res = $conn->query($sql);
    $suppliers = [];
    while ($row = $res->fetch_assoc()) {
        $suppliers[] = $row;
    }
    wp_send_json_success(['suppliers' => $suppliers]);
}
add_action('wp_ajax_fetch_suppliers', 'fetch_suppliers');
add_action('wp_ajax_nopriv_fetch_suppliers', 'fetch_suppliers');

/**
 * AJAX: search existing suppliers by name or product
 */
function search_suppliers() {
    check_ajax_referer('rfq_supplier_nonce', 'nonce');
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $prod = isset($_POST['product']) ? sanitize_text_field($_POST['product']) : '';
    $conn = get_tnt_supplier_db_connection();
    if (!$conn) wp_send_json_error(['message' => 'Database connection failed.']);
    $clauses = [];
    if ($name !== '') {
        $clauses[] = "supplier_name LIKE '%" . $conn->real_escape_string($name) . "%'";
    }
    if ($prod !== '') {
        $clauses[] = "primary_product LIKE '%" . $conn->real_escape_string($prod) . "%'";
    }
    $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
    $sql = "SELECT supplier_id, supplier_name, notes, primary_product, database_ranking
            FROM suppliers $where ORDER BY database_ranking ASC";
    $res = $conn->query($sql);
    $suppliers = [];
    while ($row = $res->fetch_assoc()) {
        $suppliers[] = $row;
    }
    wp_send_json_success(['suppliers' => $suppliers]);
}
add_action('wp_ajax_search_suppliers', 'search_suppliers');
add_action('wp_ajax_nopriv_search_suppliers', 'search_suppliers');



//Fetch & highlight already‐selected suppliers for this RFQ’s client

add_action('wp_ajax_fetch_assigned_suppliers', 'fetch_assigned_suppliers');
add_action('wp_ajax_nopriv_fetch_assigned_suppliers', 'fetch_assigned_suppliers');

function fetch_assigned_suppliers(){
  check_ajax_referer('rfq_supplier_nonce','nonce');
  $rp = intval($_POST['rfq_product_id']);
  $conn = get_tnt_supplier_db_connection();
  // get rfq_client_id
  $stmt = $conn->prepare("SELECT rfq_client_id FROM rfq_product WHERE rfq_product_id=?");
  $stmt->bind_param('i',$rp);
  $stmt->execute();
  $stmt->bind_result($client_id);
  $stmt->fetch();
  $stmt->close();
  // now find suppliers already linked to that client
  $stmt = $conn->prepare("
    SELECT DISTINCT rs.supplier_id
      FROM rfq_supplier rs
      JOIN rfq_product rp ON rp.rfq_product_id=rs.rfq_product_id
     WHERE rp.rfq_client_id=?
  ");
  $stmt->bind_param('i',$client_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $ids = [];
  while($r=$res->fetch_assoc()) $ids[] = (int)$r['supplier_id'];
  wp_send_json_success(['supplier_ids'=>$ids]);
}



/**
 * AJAX: create a new supplier
 */
function create_supplier() {
    check_ajax_referer('rfq_supplier_nonce', 'nonce');
    $name    = sanitize_text_field($_POST['supplier_name'] ?? '');
    $addr    = sanitize_text_field($_POST['supplier_address'] ?? '');
    $contact = sanitize_text_field($_POST['supplier_main_contact'] ?? '');
    $source  = sanitize_text_field($_POST['supplier_source'] ?? '');
    if (!$name) {
        wp_send_json_error(['message' => 'Supplier name is required.']);
    }
    $conn = get_tnt_supplier_db_connection();
    if (!$conn) wp_send_json_error(['message' => 'Database connection failed.']);
    $notes = json_encode(['address' => $addr, 'contact' => $contact, 'source' => $source]);
    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, notes, primary_product, database_ranking) VALUES (?, ?, ?, 0)");
    $stmt->bind_param('sss', $name, $notes, $source);
    if ($stmt->execute()) {
        wp_send_json_success(['supplier_id' => $stmt->insert_id, 'supplier_name' => $name]);
    }
    wp_send_json_error(['message' => $stmt->error]);
}
add_action('wp_ajax_create_supplier', 'create_supplier');
add_action('wp_ajax_nopriv_create_supplier', 'create_supplier');

/**
 * AJAX: submit RFQ supplier assignment
 */
function submit_rfq_supplier() {
    check_ajax_referer('rfq_supplier_nonce', 'nonce');
    $rp = intval($_POST['rfq_product_id']);
    $sp = intval($_POST['supplier_id']);
    $notes = sanitize_textarea_field($_POST['notes']);
    $conn = get_tnt_supplier_db_connection();
    if (!$conn) wp_send_json_error(['message' => 'Database connection failed.']);
    $stmt = $conn->prepare("INSERT INTO rfq_supplier (supplier_id, rfq_product_id, notes) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $sp, $rp, $notes);
    if ($stmt->execute()) {
        wp_send_json_success(['message' => 'RFQ Supplier submitted successfully.']);
    }
    wp_send_json_error(['message' => $stmt->error]);
}
add_action('wp_ajax_submit_rfq_supplier', 'submit_rfq_supplier');
add_action('wp_ajax_nopriv_submit_rfq_supplier', 'submit_rfq_supplier');
