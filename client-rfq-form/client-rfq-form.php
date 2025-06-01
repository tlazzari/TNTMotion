<?php
/**
 * Plugin Name: Client RFQ Form
 * Description: A WordPress plugin to manage Request for Quotations (RFQs) with new/existing clients, including an editable table. Status options are fetched from the DB, stored in `rfq_product.status_rfq_product`, and displayed as a dropdown.
 * Version: 2.4
 * Author: Tom Il Bello
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Return false if any product row is missing family or product.
 */
function rfq_validate_products() {
    $families = $_POST['product_family_id'] ?? [];
    $products = $_POST['product_id']        ?? [];

    if (! is_array($families) || ! is_array($products) || count($families) === 0) {
        return false;
    }

    foreach ($families as $i => $fam_id) {
        // if either family _or_ product is blank/zero, bail out
        if ( intval($fam_id) <= 0 || intval( $products[ $i ] ?? 0 ) <= 0 ) {
            return false;
        }
    }

    return true;
}

/** 
 * 1) Database Connection
 */
function rfq_get_db_connection() {
    global $wpdb;
    // Database credentials - **Note:** Storing credentials in plugin code is not recommended for security reasons.
    $db_host   = 'localhost';
    $db_name   = 'TNT_Db';
    $db_user   = 'Tom1977';
    $db_pass   = 'TNT2024@!';

    // Initialize wpdb
    $rfq_db = new wpdb($db_user, $db_pass, $db_name, $db_host);
    $rfq_db->prefix = '';

    // Check for connection errors
    if (!empty($rfq_db->error)) {
        error_log('RFQ_DB Connection Error: ' . $rfq_db->error);
        return false;
    }

    // Set character set
    $rfq_db->query("SET NAMES 'utf8mb4'");

    return $rfq_db;
}

/** 2) Enqueue Scripts & Styles **/
function rfq_enqueue_scripts() {
    // Core DataTables 1.13.4
    wp_enqueue_style(
        'datatables-css',
        'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css',
        [],
        '1.13.4'
    );
    wp_enqueue_script(
        'datatables-js',
        'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js',
        ['jquery'],
        '1.13.4',
        true
    );
      // **NEW**: Your RFQ styles
  wp_enqueue_style(
    'rfq-style',
    plugin_dir_url(__FILE__) . 'css/rfq-style.css',
    ['datatables-css'],   // if you want it after Datatables
    '2.4'
  );


    // SheetJS for XLSX export
    wp_enqueue_script(
        'sheetjs',
        'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js',
        [],
        '0.18.5',
        true
    );

    // Your main RFQ script
 wp_enqueue_script(
   'rfq-script',
   plugin_dir_url(__FILE__) . 'js/rfq-script.js',
   ['jquery','datatables-js','sheetjs'],
   '2.4',
   true
 );

    // Localize for admin-ajax
    wp_localize_script('rfq-script','rfq_ajax_object',[
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce('rfq_nonce'),
      'currencies'=> rfq_get_currencies(),
      'statuses'  => rfq_get_statuses(),
    ]);

    // Localize for REST
    wp_localize_script('rfq-script','rfq_rest',[
      'endpoint' => esc_url_raw(rest_url('rfq/v1/details')),
      'nonce'    => wp_create_nonce('wp_rest'),
    ]);
}
add_action('wp_enqueue_scripts','rfq_enqueue_scripts');

///save changes in the new page table
add_action('template_redirect','rfq_details_form_handler');
function rfq_details_form_handler(){
  if (
  ! empty($_POST['nonce'])
  && wp_verify_nonce($_POST['nonce'], 'rfq_nonce')
  && ! empty($_POST['rfq_product_id'])
  && ! empty($_POST['rfq_supplier_id'])
  ) {
    // 1) get your DB connection
    $rfq_db = rfq_get_db_connection();
    if ( ! $rfq_db ) {
      wp_die('DB connection failed.');
    }

    // 2) pull & sanitize all your inputs
    $rfq_product_id  = intval( $_POST['rfq_product_id']   );
    $rfq_supplier_id = intval( $_POST['rfq_supplier_id']  );
    $orig_price_db   = floatval( $_POST['original_price']  );
    $pdd             = sanitize_text_field( $_POST['promised_delivery_date'] ?? '' );
    $to_cli          = floatval( $_POST['price_to_client'] );

    // 3) look up existing quotation row by product+supplier
    $existing_qid = $rfq_db->get_var( $rfq_db->prepare(
      "SELECT quotation_id 
         FROM quotation 
        WHERE rfq_product_id  = %d
          AND rfq_supplier_id = %d
        LIMIT 1",
      $rfq_product_id,
      $rfq_supplier_id
    ) );

    if ( $existing_qid ) {
      // 4a) UPDATE
      $rfq_db->update(
        'quotation',
        [
          'price'                  => $orig_price_db,
          'promised_delivery_date' => $pdd,
        ],
        [ 'quotation_id' => $existing_qid ],
        [ '%f', '%s' ],
        [ '%d' ]
      );
    } else {
      // 4b) INSERT
      $rfq_db->insert(
        'quotation',
        [
          'rfq_product_id'         => $rfq_product_id,
          'rfq_supplier_id'        => $rfq_supplier_id,
          'price'                  => $orig_price_db,
          'promised_delivery_date' => $pdd,
        ],
        [ '%d','%d','%f','%s' ]
      );
    }

    // 5) now update your rfq_product as before
    $rfq_db->update(
      'rfq_product',
      [ 'price_to_client' => $to_cli ],
      [ 'rfq_product_id'  => $rfq_product_id ],
      [ '%f' ],
      [ '%d' ]
    );

    // 6) redirect back
    wp_safe_redirect( remove_query_arg([], $_SERVER['REQUEST_URI']) );
    exit;
  }
}





/**
 * Helper function to fetch statuses from DB for localization
 */
function rfq_get_statuses() {
    $rfq_db = rfq_get_db_connection();
    $statuses = array();
    if ($rfq_db) {
        $status_rows = $rfq_db->get_results("SELECT status_id, status_name FROM status ORDER BY status_name ASC");
        if ($status_rows) {
            foreach ($status_rows as $status) {
                $statuses[] = array(
                    'id'   => intval($status->status_id),
                    'name' => esc_html($status->status_name)
                );
            }
        }
    }
    return $statuses;
}

/** 
 * 3) Shortcode: Display RFQ Form and Table
 */
function rfq_display_form() {

    if ( isset($_GET['rfq_product_id']) && intval($_GET['rfq_product_id']) > 0 ) {
        $id = intval($_GET['rfq_product_id']);
        $rfq_db = rfq_get_db_connection();
        $sql = "
          SELECT DISTINCT
            c.client_name,
            pf.product_family_name,
            rp.rfq_product_id,
            p.product_name,
            COALESCE(s.supplier_name,'-') AS supplier_name,
            COALESCE(rs.rfq_supplier_id, 0) AS rfq_supplier_id,
            COALESCE(s.supplier_id, 0)       AS supplier_id,            
            COALESCE(ROUND(q.price,3),'-') AS original_price,
            COALESCE(ROUND(rp.price_to_client,3),'-') AS price_to_client,
            /* currency conversion logic */
            CASE WHEN rp.currency_id=1 AND q.currency_id=2 THEN q.price*8
                 WHEN rp.currency_id=2 AND q.currency_id=1 THEN q.price/8
                 ELSE q.price END AS converted_price,
            ROUND((
              (rp.price_to_client - 
                CASE WHEN rp.currency_id=1 AND q.currency_id=2 THEN q.price*8
                     WHEN rp.currency_id=2 AND q.currency_id=1 THEN q.price/8
                     ELSE q.price END
              ) /
              CASE WHEN rp.currency_id=1 AND q.currency_id=2 THEN q.price*8
                   WHEN rp.currency_id=2 AND q.currency_id=1 THEN q.price/8
                   ELSE q.price END)*100
            ,2) AS margin
          FROM rfq_product rp
          JOIN rfq_client  rc ON rc.rfq_client_id=rp.rfq_client_id
          JOIN client      c  ON c.client_id=rc.client_id
          JOIN product_family pf ON pf.product_family_id=rp.product_family_id
          JOIN products    p  ON p.product_id=rp.product_id
          LEFT JOIN rfq_supplier rs ON rs.rfq_product_id=rp.rfq_product_id
          LEFT JOIN suppliers   s  ON s.supplier_id=rs.supplier_id
          LEFT JOIN quotation   q  ON q.rfq_product_id=rp.rfq_product_id
          WHERE rp.rfq_product_id = %d
        ";
        $prepared = $rfq_db->prepare($sql,$id);
        $lines = $rfq_db->get_results($prepared, ARRAY_A);
        if ( ! $lines ) {
            echo '<p>No details found.</p>';
            return ob_get_clean();
        }
        // Output a styled table
        ?>
        <h2>RFQ Product ID#<?php echo esc_html($id); ?> Details</h2>
        <table id="rfq-details-table" class="display" style="width:100%">
          <thead>
            <tr>
              <th>Client</th><th>Family</th><th>Product</th>
              <th>Supplier</th><th>Orig. Price</th><th>Price To Client</th>
              <th>Converted</th><th>Margin %</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($lines as $l): ?>
            <tr>
              <td><?php echo nl2br(esc_html($l['client_name'])); ?></td>
              <td><?php echo esc_html($l['product_family_name']); ?></td>
              <td><?php echo esc_html($l['product_name']); ?></td>
              <td><?php echo esc_html($l['supplier_name']); ?></td>
              <td>
                <input
                  type="number"
                  class="js-edit-original"
                    data-rfq-product-id="<?php echo intval( $l['rfq_product_id'] ); ?>"
                    data-rfq-supplier-id="<?php echo intval( $l['rfq_supplier_id'] ); ?>"
                  step="0.01"
                  value="<?php echo esc_attr($l['original_price']); ?>"
                >
              </td>
              <td>
                <input
                  type="number"
                  class="js-edit-client-price"
                  data-rfq-product-id="<?php echo intval( $l['rfq_product_id'] ); ?>"
                  step="0.01"
                  value="<?php echo esc_attr( $l['price_to_client'] ); ?>"
                >
            </td>
              <td><?php echo esc_html($l['converted_price']); ?></td>
              <td><?php echo esc_html($l['margin']); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <script>
        jQuery(document).ready(function($){
          $('#rfq-details-table').DataTable({
            paging: false,
            searching: false,
            info: false,
            autoWidth: true
          });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    ob_start();

    // Display messages based on URL parameters
    if ( isset($_GET['rfq_created']) && intval($_GET['rfq_created']) > 0 ) {
        echo '<div class="rfq-message rfq-success">RFQ created successfully. RFQ CLIENT ID: ' . intval($_GET['rfq_created']) . '</div>';
    }
    if ( isset($_GET['rfq_new_created']) && intval($_GET['rfq_new_created']) > 0 ) {
        echo '<div class="rfq-message rfq-success">RFQ added successfully with ID: ' . intval($_GET['rfq_new_created']) . '</div>';
    }
    if ( isset($_GET['rfq_error']) ) {
        echo '<div class="rfq-message rfq-error">' . esc_html($_GET['rfq_error']) . '</div>';
    }
    if ( isset($_GET['product_error']) ) {
        echo '<div class="rfq-message rfq-error">' . esc_html($_GET['product_error']) . '</div>';
    }

    // Fetch status options from DB
    $rfq_db = rfq_get_db_connection();
    $status_options = array();
    if ($rfq_db) {
        $status_rows = $rfq_db->get_results("SELECT status_id, status_name FROM status ORDER BY status_name ASC");
        if ($status_rows) {
            $status_options = $status_rows;
        }
    }

    ?>
   <!-- RFQ Form Container -->
    <div class="rfq-form-container">
        <form method="POST" id="rfq_form" novalidate>
            <?php wp_nonce_field('rfq_nonce', 'nonce'); ?>
            
            <div class="rfq-form-grid">
                <!-- Left Column: Client Section -->
                <div class="rfq-form-column">
                    <div class="rfq-section">
                        <label>Select RFQ Client Option:</label>
                        <div class="rfq-options">
                            <label><input type="radio" name="rfq_option" value="new" checked> Add to New RFQ Client</label>
                            <label><input type="radio" name="rfq_option" value="existing"> Add to Existing RFQ Client</label>
                        </div>
                    </div>

                    <!-- New Client Section -->
                    <div class="rfq-section" id="new_client_section">
                        <h4>New RFQ Client Details</h4>
                        <div class="rfq-field">
                            <label for="new_client_name">Client Name</label>
                            <input type="text" id="new_client_name" name="new_client_name" autocomplete="off" required>
                            <input type="hidden" name="new_client_id" id="new_client_id">
                            <div id="new_client_suggestions" class="suggestions-container"></div>
                        </div>
                        <div class="rfq-field">
                            <label for="new_notes">Notes</label>
                            <textarea id="new_notes" name="new_notes" rows="3"></textarea>
                        </div>
                        <div class="rfq-field">
                            <label for="new_status">Status</label>
                            <select id="new_status" name="new_status" required>
                              <option value="">-- Select Status --</option>
                              <?php foreach( $status_options as $s ): ?>
                                <option value="<?= intval($s->status_id) ?>">
                                  <?= esc_html($s->status_name) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Existing Client Section -->
                    <div class="rfq-section" id="existing_client_section" style="display:none;">
                        <h4>Select Existing RFQ Client</h4>
                        <div class="rfq-field">
                            <label for="existing_client_name">Client Name / RFQ Notes</label>
                            <input type="text" id="existing_client_name" name="existing_client_name" autocomplete="off">
                            <input type="hidden" name="existing_client_id" id="existing_client_id">
                            <div id="existing_client_suggestions" class="suggestions-container"></div>
                            <button type="button" id="cancel_existing_client" class="cancel-button">Cancel</button>
                        </div>
                    </div>

                    <!-- Existing RFQs Table -->
                    <div id="existing_rfqs_table_container" class="rfq-section" style="display:none;">
                        <h4>Existing RFQs for Selected Client</h4>
                        <table id="existing_rfqs_table">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>RFQ Client ID</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column: Product Section -->
                <div class="rfq-form-column">
                    <div id="products_container">
                        <div class="product-entry">
                            <h4>Product Details</h4>
                            <div class="rfq-field">
                                <label for="product_family_name_1">Product Family:</label>
                                <input type="text" id="product_family_name_1" name="product_family_name[]" autocomplete="off" required>
                                <input type="hidden" id="product_family_id_1" name="product_family_id[]">
                                <div class="suggestions-container product_family_suggestions"></div>
                            </div>

                            <div class="rfq-field">
                                <label for="product_name_1">Product Name:</label>
                                <input type="text" id="product_name_1" name="product_name[]" autocomplete="off" required>
                                <input type="hidden" id="product_id_1" name="product_id[]">
                                <div class="suggestions-container product_suggestions"></div>
                            </div>

                            <div class="rfq-field">
                                <label for="quantity_1">Quantity</label>
                                <input type="number" id="quantity_1" name="quantity[]" min="1" required>
                            </div>
                            <div class="rfq-field">
                                <label for="specifications_1">Specifications</label>
                                <input type="text" id="specifications_1" name="specifications[]">
                            </div>
                            <div class="rfq-field">
                                <label for="product_notes_1">Product Notes</label>
                                <textarea id="product_notes_1" name="product_notes[]" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add_another_product_btn">
                    Add Another Product
                    </button>
                </div>
            </div>

            <input type="hidden" name="selected_rfq_client_id" id="selected_rfq_client_id" value="">
            <input type="submit" name="submit_rfq" value="Submit RFQ" class="rfq-submit">
        </form>
    </div>

    <!-- Editable RFQ Table Container -->
<div class="rfq-table-section">
        <div class="rfq-table-header">
            <h3>Editable RFQ Table</h3>
            <button id="download_xlsx_btn" class="rfq-button">Download XLSX</button>
        </div>
        <div class="rfq-table-wrapper">
        <table id="single_rfq_table">
          <thead>
            <tr>
              <th>Select</th>
              <th>RFQ Client ID</th>
              <th>Client Name</th>
              <th>Product Name</th>
              <th>Quantity</th>
              <th>Suppliers</th>         <!-- new -->
              <th>Valid Quotes</th>      <!-- new -->
              <th>Target Price (Bf VAT)</th>
              <th>Currency</th>
              <th>Paid %</th>
              <th>Price to Client (Bf VAT)</th>
              <th>Notes</th>
              <th>Status</th>            
            </tr>
          </thead>
          <tbody></tbody>
        </table>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('client_rfq_form', 'rfq_display_form');


/**
 * 4) Handle Form Submission
 */
function rfq_handle_form_submission() {
    if (isset($_POST['submit_rfq'])) {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rfq_nonce')) {
            wp_redirect(add_query_arg('rfq_error', 'Security check failed.', get_permalink()));
            exit;
        }

        // Get DB connection
        $rfq_db = rfq_get_db_connection();
        if (!$rfq_db) {
            wp_redirect(add_query_arg('rfq_error', 'Database connection failed.', get_permalink()));
            exit;
        }


        // 2) **new**: validate _all_ product rows BEFORE inserting client
        if ( ! rfq_validate_products() ) {
            wp_redirect( add_query_arg(
                'rfq_error',
                'Form incomplete: please fill in every productFamily→product row.',
                get_permalink()
            ) );
            exit;
        }

        // Determine RFQ option
        $rfq_option = isset($_POST['rfq_option']) ? sanitize_text_field($_POST['rfq_option']) : 'new';

        if ($rfq_option === 'new') {
            // Handle new RFQ client
            $new_client_id = isset($_POST['new_client_id']) ? intval($_POST['new_client_id']) : 0;
            if ($new_client_id <= 0) {
                wp_redirect(add_query_arg('rfq_error', 'Invalid client selected for new RFQ.', get_permalink()));
                exit;
            }

            // Verify client exists
            $client_exists = $rfq_db->get_var($rfq_db->prepare(
                "SELECT COUNT(*) FROM client WHERE client_id = %d",
                $new_client_id
            ));
            if ($client_exists <= 0) {
                wp_redirect(add_query_arg('rfq_error', 'Selected client does not exist.', get_permalink()));
                exit;
            }

            $new_notes     = isset($_POST['new_notes']) ? sanitize_textarea_field($_POST['new_notes']) : '';
            $new_status_id = isset($_POST['new_status']) ? intval($_POST['new_status']) : 0;

            // Insert into rfq_client
            $inserted = $rfq_db->insert('rfq_client', array(
                'client_id' => $new_client_id,
                'notes'     => $new_notes,
            ), array(
                '%d',
                '%s',
            ));

            // Log the raw SQL that wpdb just ran
            error_log( "🔍 RFQ_CLIENT INSERT: " . $rfq_db->last_query );
            if ( false === $inserted ) {
              error_log( "❌ RFQ_CLIENT ERROR: " . $rfq_db->last_error );
              // …
            }

            $rfq_client_id   = intval( $rfq_db->insert_id );
            rfq_insert_products( $rfq_db, $rfq_client_id, $new_status_id );




            if ( false === $inserted ) {
            // write the raw error and the last query to your PHP error log
            error_log( "❌ RFQ_CLIENT INSERT FAILED: " . $rfq_db->last_error );
            error_log( "🔍 SQL was: " . $rfq_db->last_query );
            wp_redirect( add_query_arg('rfq_error','Failed to create RFQ Client.',get_permalink()) );
            exit;
        }

            wp_redirect(add_query_arg(['rfq_new_created' => $rfq_client_id], get_permalink()));
            exit;

        } elseif ($rfq_option === 'existing') {
            // Handle existing RFQ client
            $selected_rfq_client_id = isset($_POST['selected_rfq_client_id']) ? intval($_POST['selected_rfq_client_id']) : 0;
            if ($selected_rfq_client_id <= 0) {
                wp_redirect(add_query_arg('rfq_error', 'No RFQ Client selected.', get_permalink()));
                exit;
            }

            // Verify RFQ client exists
            $exists = $rfq_db->get_var($rfq_db->prepare(
                "SELECT COUNT(*) FROM rfq_client WHERE rfq_client_id = %d",
                $selected_rfq_client_id
            ));
            if ($exists <= 0) {
                wp_redirect(add_query_arg('rfq_error', 'Selected RFQ Client does not exist.', get_permalink()));
                exit;
            }

            // Insert products
            rfq_insert_products($rfq_db, $selected_rfq_client_id);

            wp_redirect(add_query_arg(['rfq_created' => $selected_rfq_client_id], get_permalink()));
            exit;

        } else {
            // Invalid RFQ option
            wp_redirect(add_query_arg('rfq_error', 'Invalid RFQ option selected.', get_permalink()));
            exit;
        }
    }
}
add_action('template_redirect', 'rfq_handle_form_submission');

/**
 * 5) Insert Products into rfq_product Table
 * @param wpdb $rfq_db - Database connection
 * @param int $rfq_client_id - The RFQ Client ID
 */
function rfq_insert_products( $rfq_db, $rfq_client_id, $status_to_set = 0) {
    // Retrieve and sanitize input data
    $product_family_ids = isset($_POST['product_family_id']) && is_array($_POST['product_family_id']) ? array_map('intval', $_POST['product_family_id']) : array();
    $product_ids        = isset($_POST['product_id']) && is_array($_POST['product_id']) ? array_map('intval', $_POST['product_id']) : array();
    $product_names      = isset($_POST['product_name']) && is_array($_POST['product_name']) ? array_map('sanitize_text_field', $_POST['product_name']) : array();
    $quantities         = isset($_POST['quantity']) && is_array($_POST['quantity']) ? array_map('intval', $_POST['quantity']) : array();
    $specs_list         = isset($_POST['specifications']) && is_array($_POST['specifications']) ? array_map('sanitize_text_field', $_POST['specifications']) : array();
    $notes_list         = isset($_POST['product_notes']) && is_array($_POST['product_notes']) ? array_map('sanitize_textarea_field', $_POST['product_notes']) : array();

    // Optional: Check that all arrays have the same length
    $count = count($product_names);
    if (
        count($product_family_ids) !== $count ||
        count($product_ids) !== $count ||
        count($quantities) !== $count ||
        count($specs_list) !== $count ||
        count($notes_list) !== $count
    ) {
        wp_redirect(add_query_arg('product_error', 'Mismatched product data.', get_permalink()));
        exit;
    }

    // Iterate through products and insert into database
    for ($i = 0; $i < $count; $i++) {
        $fam_id  = $product_family_ids[$i] ?? 0;
        $prod_id = $product_ids[$i] ?? 0;
        $qty     = $quantities[$i] ?? 0;
        $spec    = $specs_list[$i] ?? '';
        $pnotes  = $notes_list[$i] ?? '';

        // Validate product family and product IDs
        if ($fam_id <= 0 || $prod_id <= 0) {
            wp_redirect(add_query_arg('product_error', 'Invalid product family or product selection.', get_permalink()));
            exit;
        }

        // Verify product family exists
        $family_exists = $rfq_db->get_var($rfq_db->prepare(
            "SELECT COUNT(*) FROM product_family WHERE product_family_id = %d",
            $fam_id
        ));
        if ($family_exists <= 0) {
            wp_redirect(add_query_arg('product_error', 'Selected product family does not exist.', get_permalink()));
            exit;
        }

        // Verify product exists
        $product_exists = $rfq_db->get_var($rfq_db->prepare(
            "SELECT COUNT(*) FROM products WHERE product_id = %d",
            $prod_id
        ));
        if ($product_exists <= 0) {
            wp_redirect(add_query_arg('product_error', 'Selected product does not exist.', get_permalink()));
            exit;
        }

        // Insert into rfq_product
        $ins = $rfq_db->insert('rfq_product', array(
            'rfq_client_id'     => $rfq_client_id,
            'product_family_id' => $fam_id,
            'product_id'        => $prod_id,
            'quantity'          => $qty,
            'specifications'    => $spec,
            'notes'             => $pnotes,
            'status_rfq_product'   => $status_to_set,
        ), array(
            '%d', // rfq_client_id
            '%d', // product_family_id
            '%d', // product_id
            '%d', // quantity
            '%s', // specifications
            '%s', // notes
            '%d', // status_rfq_product
        ));

        error_log( "🔍 RFQ_PRODUCT INSERT: " . $rfq_db->last_query );
        error_log( "    → status_to_set = " . var_export( $status_to_set, true ) );

        if ( ! $ins ) {
          error_log( "❌ RFQ_PRODUCT ERROR: " . $rfq_db->last_error );
      }

        if (!$ins) {
            wp_redirect(add_query_arg('product_error', 'Failed to add product.', get_permalink()));
            exit;
        }
    }
}





/**
 * 6) AJAX Handlers for Suggestions and Creation
 */

/** 
 * rfq_get_new_client_suggestions (for new client suggestions)
 */
function rfq_get_new_client_suggestions() {
    check_ajax_referer('rfq_nonce','nonce');

    $search_term = isset($_POST['client_name']) ? sanitize_text_field($_POST['client_name']) : '';
    $rfq_db = rfq_get_db_connection();
    if (!$rfq_db) {
        wp_send_json_error(['message'=>'Database connection failed.']);
    }

    $results = [];
    if (!empty($search_term)) {
        // Fetch clients matching the search term
        $sql = "
            SELECT client_id, client_name 
            FROM client 
            WHERE client_name LIKE %s 
            ORDER BY client_name ASC 
            LIMIT 10
        ";
        $like = '%' . $rfq_db->esc_like($search_term) . '%';
        $rows = $rfq_db->get_results($rfq_db->prepare($sql, $like), ARRAY_A);
        if ($rows) {
            foreach ($rows as $r) {
                $results[] = [
                    'client_id'   => intval($r['client_id']),
                    'client_name' => esc_html($r['client_name']),
                ];
            }
        }
    }
    wp_send_json_success(['results'=>$results]);
}
add_action('wp_ajax_rfq_get_new_client_suggestions','rfq_get_new_client_suggestions');
add_action('wp_ajax_nopriv_rfq_get_new_client_suggestions','rfq_get_new_client_suggestions');

/** 
 * rfq_get_existing_client_suggestions (for existing client suggestions)
 */
function rfq_get_existing_client_suggestions() {
    check_ajax_referer('rfq_nonce','nonce');

    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
    $rfq_db = rfq_get_db_connection();
    if (!$rfq_db) {
        wp_send_json_error(['message'=>'Database connection failed.']);
    }

    $results = [];
    if (!empty($search_term)) {
        // Fetch clients matching the search term
        $sql = "
            SELECT DISTINCT c.client_id, c.client_name 
            FROM client c
            LEFT JOIN rfq_client rc ON c.client_id = rc.client_id
            WHERE c.client_name LIKE %s OR rc.notes LIKE %s
            ORDER BY c.client_name ASC 
            LIMIT 10
        ";
        $like = '%' . $rfq_db->esc_like($search_term) . '%';
        $rows = $rfq_db->get_results($rfq_db->prepare($sql, $like, $like), ARRAY_A);
        if ($rows) {
            foreach ($rows as $r) {
                // Fetch RFQs associated with this client
                $rfq_rows = $rfq_db->get_results(
                    $rfq_db->prepare(
                        "SELECT rfq_client_id FROM rfq_client WHERE client_id = %d LIMIT 10",
                        $r['client_id']
                    )
                );
                $rfq_ids = [];
                if ($rfq_rows) {
                    foreach ($rfq_rows as $rfq){
                        $rfq_ids[] = intval($rfq->rfq_client_id);
                    }
                }
                $results[] = [
                    'client_id'   => intval($r['client_id']),
                    'client_name' => esc_html($r['client_name']),
                    'rfq_ids'     => $rfq_ids
                ];
            }
        }
    }
    wp_send_json_success(['results'=>$results]);
}
add_action('wp_ajax_rfq_get_existing_client_suggestions','rfq_get_existing_client_suggestions');
add_action('wp_ajax_nopriv_rfq_get_existing_client_suggestions','rfq_get_existing_client_suggestions');

/** 
 * rfq_get_rfqs_for_client_v2 (Unique Name)
 */
function rfq_get_rfqs_for_client_v2() {
    check_ajax_referer('rfq_nonce','nonce');
    $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
    $rfq_db = rfq_get_db_connection();
    $rfqs = [];

    if ($client_id > 0 && $rfq_db) {
        $rfq_rows = $rfq_db->get_results(
            $rfq_db->prepare(
                "SELECT * FROM rfq_client WHERE client_id = %d LIMIT 10",
                $client_id
            )
        );
        if ($rfq_rows) {
            foreach ($rfq_rows as $rc) {
                $pRows = $rfq_db->get_results(
                    $rfq_db->prepare(
                        "SELECT pf.product_family_name, p.product_name, rp.quantity 
                         FROM rfq_product rp
                         JOIN product_family pf ON rp.product_family_id = pf.product_family_id
                         JOIN products p ON rp.product_id = p.product_id
                         WHERE rp.rfq_client_id = %d",
                        $rc->rfq_client_id
                    )
                );

                $pNames = [];
                $qList  = [];
                if ($pRows) {
                    foreach ($pRows as $pp) {
                        $pNames[] = $pp->product_name;
                        $qList[]  = $pp->quantity;
                    }
                }

                // Fetch status name
                $status_name = '';
                if ($rc->status > 0) {
                    $status = $rfq_db->get_row($rfq_db->prepare(
                        "SELECT status_name FROM status WHERE status_id = %d LIMIT 1",
                        $rc->status
                    ));
                    if ($status) {
                        $status_name = esc_html($status->status_name);
                    }
                }

                $rfqs[] = array(
                    'rfq_client_id'     => intval($rc->rfq_client_id),
                    'notes'             => esc_html($rc->notes),
                    'status'            => $status_name,
                    'products'          => $pNames,
                    'quantities'        => $qList
                );
            }
        }
    }

    wp_send_json_success(['rfqs' => $rfqs]);
}
add_action('wp_ajax_rfq_get_rfqs_for_client_v2', 'rfq_get_rfqs_for_client_v2');
add_action('wp_ajax_nopriv_rfq_get_rfqs_for_client_v2', 'rfq_get_rfqs_for_client_v2');

/** 
 * rfq_create_new_product_family
 */
function rfq_create_new_product_family() {
    check_ajax_referer('rfq_nonce','nonce');
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    if (empty($name)) {
        wp_send_json_error(['message'=>'No name provided.']);
    }
    $rfq_db = rfq_get_db_connection();
    if ($rfq_db) {
        $ins = $rfq_db->insert('product_family', ['product_family_name' => $name], ['%s']);
        if (!$ins) {
            wp_send_json_error(['message'=>'Failed to create product family.']);
        }
        $id = intval($rfq_db->insert_id);
        wp_send_json_success(['product_family_id' => $id, 'product_family_name' => $name]);
    }
    wp_send_json_error(['message'=>'Database connection failed.']);
}
add_action('wp_ajax_rfq_create_new_product_family','rfq_create_new_product_family');
add_action('wp_ajax_nopriv_rfq_create_new_product_family','rfq_create_new_product_family');

/** 
 * rfq_create_new_product
 */
function rfq_create_new_product() {
    // Log incoming request
    error_log('AJAX Request Received: ' . print_r($_POST, true));

    check_ajax_referer('rfq_nonce', 'nonce');

    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    if (empty($name)) {
        error_log('Error: Product name is missing.');
        wp_send_json_error(['message' => 'No name provided.']);
    }

    $rfq_db = rfq_get_db_connection();
    if (!$rfq_db) {
        error_log('Error: Database connection failed.');
        wp_send_json_error(['message' => 'Database connection failed.']);
    }

    // Insert the product into the database
    $inserted = $rfq_db->insert('products', ['product_name' => $name], ['%s']);
    if (!$inserted) {
        error_log('Database Error: ' . $rfq_db->last_error);
        wp_send_json_error(['message' => 'Failed to create product.', 'db_error' => $rfq_db->last_error]);
    }

    $product_id = intval($rfq_db->insert_id);
    wp_send_json_success(['product_id' => $product_id, 'product_name' => $name]);
}
add_action('wp_ajax_rfq_create_new_product', 'rfq_create_new_product');
add_action('wp_ajax_nopriv_rfq_create_new_product', 'rfq_create_new_product');

/** 
 * rfq_get_product_families (Autocomplete)
 */
function rfq_get_product_families() {
    check_ajax_referer('rfq_nonce','nonce');
    $term   = sanitize_text_field($_POST['product_family_name'] ?? '');
    $rfq_db = rfq_get_db_connection();
    $results = [];

    if ($rfq_db && $term !== '') {
        $rows = $rfq_db->get_results(
            $rfq_db->prepare(
                "SELECT DISTINCT product_family_id, product_family_name
                   FROM product_family
                  WHERE product_family_name LIKE %s
                  ORDER BY product_family_name ASC
                  LIMIT 10",
                '%' . $rfq_db->esc_like($term) . '%'
            )
        );
        foreach ($rows as $rw) {
            $results[] = [
                'id'   => intval(  $rw->product_family_id   ),
                'name' => esc_html($rw->product_family_name),
            ];
        }
    }

    wp_send_json_success(['results' => $results]);
}

add_action('wp_ajax_rfq_get_product_families','rfq_get_product_families');
add_action('wp_ajax_nopriv_rfq_get_product_families','rfq_get_product_families');

/** 
 * rfq_get_products (Autocomplete)
 */
/**
 * rfq_get_products (Autocomplete)
 */
function rfq_get_products() {
    check_ajax_referer('rfq_nonce','nonce');
    $term   = sanitize_text_field($_POST['product_name'] ?? '');
    $rfq_db = rfq_get_db_connection();
    $results = [];

    if ($rfq_db && $term !== '') {
        $rows = $rfq_db->get_results(
            $rfq_db->prepare(
                "SELECT DISTINCT product_id, product_name
                   FROM products
                  WHERE product_name LIKE %s
                  ORDER BY product_name ASC
                  LIMIT 10",
                '%' . $rfq_db->esc_like($term) . '%'
            )
        );
        foreach ($rows as $rw) {
            $results[] = [
                'id'   => intval(  $rw->product_id  ),
                'name' => esc_html($rw->product_name),
            ];
        }
    }

    wp_send_json_success(['results' => $results]);
}

add_action('wp_ajax_rfq_get_products','rfq_get_products');
add_action('wp_ajax_nopriv_rfq_get_products','rfq_get_products');


/**
 * 7) AJAX Handlers for Suppliers and Quotation
 */

/**
 * srfq_fetch_suppliers
 */
function srfq_fetch_suppliers() {
    check_ajax_referer('rfq_nonce','nonce');
    $rfq_db = rfq_get_db_connection();
    if (!$rfq_db) {
        wp_send_json_error(['message'=>'Database connection failed.']);
    }

    $sql = "SELECT supplier_id, supplier_name, primary_product FROM suppliers ORDER BY supplier_name ASC";
    $rows = $rfq_db->get_results($sql);
    if ($rows === null) {
        wp_send_json_error(['message'=>'Query error: '.$rfq_db->last_error]);
    }
    $suppliers = [];
    foreach ($rows as $r) {
        $suppliers[] = [
            'supplier_id'    => intval($r->supplier_id),
            'supplier_name'  => esc_html($r->supplier_name),
            'primary_product'=> esc_html($r->primary_product)
        ];
    }
    wp_send_json_success(['suppliers' => $suppliers]);
}
add_action('wp_ajax_srfq_fetch_suppliers','srfq_fetch_suppliers');
add_action('wp_ajax_nopriv_srfq_fetch_suppliers','srfq_fetch_suppliers');

/**
 * srfq_save_supplier
 */
function srfq_save_supplier() {
    check_ajax_referer('rfq_nonce','nonce');
    $rfq_db = rfq_get_db_connection();
    if (!$rfq_db) {
        wp_send_json_error(['message'=>'Database connection failed.']);
    }

    $rfq_product_id  = isset($_POST['rfq_product_id']) ? intval($_POST['rfq_product_id']) : 0;
    $rfq_supplier_id = isset($_POST['rfq_supplier_id']) ? intval($_POST['rfq_supplier_id']) : 0;
    $supplier_id     = isset($_POST['supplier_id']) ? intval($_POST['supplier_id']) : 0;
    $is_new          = (isset($_POST['is_new_supplier']) && $_POST['is_new_supplier'] === '1');

    if ($is_new) {
        $newName = isset($_POST['new_supplier_name']) ? sanitize_text_field($_POST['new_supplier_name']) : '';
        $newPrim = isset($_POST['new_supplier_main']) ? sanitize_text_field($_POST['new_supplier_main']) : '';
        if (empty($newName)) {
            wp_send_json_error(['message'=>'New supplier name is required.']);
        }
        $ins = $rfq_db->insert('suppliers', [
            'supplier_name'   => $newName,
            'primary_product' => $newPrim
        ], ['%s', '%s']);
        if (!$ins) {
            wp_send_json_error(['message'=>'Failed to create new supplier.']);
        }
        $supplier_id = intval($rfq_db->insert_id);
    }

    if ($rfq_supplier_id <= 0) {
        // Insert new rfq_supplier
        $ins = $rfq_db->insert('rfq_supplier', [
            'rfq_product_id' => $rfq_product_id,
            'supplier_id'    => $supplier_id
        ], ['%d', '%d']);
        if (!$ins) {
            wp_send_json_error(['message'=>'Failed to insert rfq_supplier row.']);
        }
    } else {
        // Update existing rfq_supplier
        $upd = $rfq_db->update('rfq_supplier', [
            'supplier_id' => $supplier_id
        ], ['rfq_supplier_id' => $rfq_supplier_id], ['%d'], ['%d']);
        if ($upd === false) {
            wp_send_json_error(['message'=>'Failed updating rfq_supplier row.']);
        }
    }
    wp_send_json_success(['message'=>'Supplier updated/inserted successfully.']);
}
add_action('wp_ajax_srfq_save_supplier','srfq_save_supplier');
add_action('wp_ajax_nopriv_srfq_save_supplier','srfq_save_supplier');

/**
 * srfq_save_quotation
 */
/**
 * AJAX handler: save editable RFQ row
 */
/**
 * AJAX handler: save editable RFQ row
 */
function srfq_save_quotation() {
    check_ajax_referer('rfq_nonce','nonce');
    $rfq_db = rfq_get_db_connection();
    if ( ! $rfq_db ) {
        wp_send_json_error([ 'message' => 'DB connection failed.' ]);
    }

    $rfq_product_id      = intval( $_POST['rfq_product_id'] ?? 0 );
    $rfq_supplier_id     = intval( $_POST['rfq_supplier_id'] ?? 0 ); // <-- capture this
    if ( $rfq_product_id <= 0 || $rfq_supplier_id <= 0 ) {
        wp_send_json_error([ 'message' => 'Invalid RFQ product or supplier ID.' ]);
    }

    // build up our update arrays
    $update     = [];
    $update_fmt = [];

    if ( isset($_POST['quantity']) ) {
        $update['quantity']      = intval( $_POST['quantity'] );
        $update_fmt[]            = '%d';
    }
    if ( isset($_POST['target_price']) ) {
        $update['target_price']  = floatval( $_POST['target_price'] );
        $update_fmt[]            = '%f';
    }
    if ( isset($_POST['price_to_client']) ) {
        $update['price_to_client'] = floatval( $_POST['price_to_client'] );
        $update_fmt[]              = '%f';
    }
    if ( isset($_POST['notes']) ) {
        $update['notes']          = sanitize_text_field( $_POST['notes'] );
        $update_fmt[]             = '%s';
    }

    //  ——— quotation upsert ———
    // sanitize once
    $orig_price_db = floatval( $_POST['price_to_client'] ?? 0 );
    $pdd           = sanitize_text_field( $_POST['promised_delivery_date'] ?? '' );

    // Always upsert the quotation row:
    $rfq_db->query( $rfq_db->prepare(
        "
        INSERT INTO quotation
          (rfq_product_id, rfq_supplier_id, price, promised_delivery_date)
        VALUES
          (%d, %d, %f, %s)
        ON DUPLICATE KEY UPDATE
          price                  = VALUES(price),
          promised_delivery_date = VALUES(promised_delivery_date)
        ",
        $rfq_product_id,
        $rfq_supplier_id,
        $orig_price_db,
        $pdd
    ) );


    if ( isset($_POST['paid_percentage']) ) {
        $update['percentage_paid'] = intval( $_POST['paid_percentage'] );
        $update_fmt[]              = '%d';
    }
    if ( isset($_POST['currency_id']) ) {
        $update['currency_id']     = intval( $_POST['currency_id'] );
        $update_fmt[]              = '%d';
    }

    if ( empty( $update ) ) {
        wp_send_json_error([ 'message' => 'Nothing to update.' ]);
    }

    $where     = [ 'rfq_product_id' => $rfq_product_id ];
    $where_fmt = [ '%d' ];

    $res = $rfq_db->update(
        'rfq_product',
        $update,
        $where,
        $update_fmt,
        $where_fmt
    );

    if ( $res === false ) {
        error_log( 'srfq_save_quotation error: ' . $rfq_db->last_error );
        wp_send_json_error([ 'message' => 'DB error: ' . $rfq_db->last_error ]);
    }

    wp_send_json_success([ 'message' => 'Saved successfully.' ]);
}

add_action('wp_ajax_srfq_save_quotation','srfq_save_quotation');
add_action('wp_ajax_nopriv_srfq_save_quotation','srfq_save_quotation');

//save changes in the new page
add_action('wp_ajax_srfq_save_live_prices','srfq_save_live_prices');
add_action('wp_ajax_nopriv_srfq_save_live_prices','srfq_save_live_prices');
function srfq_save_live_prices(){
  check_ajax_referer('rfq_nonce','nonce');
  $rfq_product_id   = intval(  $_POST['rfq_product_id']   ?? 0);
  $rfq_supplier_id  = intval(  $_POST['rfq_supplier_id']  ?? 0);
  $orig_price       = floatval($_POST['original_price']  ?? 0);
  $client_price     = floatval($_POST['price_to_client'] ?? 0);
  if (!$rfq_product_id || !$rfq_supplier_id) {
    wp_send_json_error('Invalid IDs');
  }

  $db = rfq_get_db_connection();
  if (!$db) {
    wp_send_json_error('DB fail');
  }

  // 1) Upsert the quotation row:
  $db->query($db->prepare(
    "
    INSERT INTO quotation
      (rfq_product_id, rfq_supplier_id, price, promised_delivery_date)
    VALUES
      (%d, %d, %f, '')
    ON DUPLICATE KEY UPDATE
      price = VALUES(price)
    ",
    $rfq_product_id,
    $rfq_supplier_id,
    $orig_price
  ));

  // 2) Update the client‐price on rfq_product
  $db->update(
    'rfq_product',
    [ 'price_to_client' => $client_price ],
    [ 'rfq_product_id'  => $rfq_product_id ],
    [ '%f' ],
    [ '%d' ]
  );

  wp_send_json_success();
}


/** 
 * srfq_fetch_statuses (Return all statuses from DB)
 */
function srfq_fetch_statuses() {
    check_ajax_referer('rfq_nonce','nonce');
    $rfq_db = rfq_get_db_connection();
    if (!$rfq_db) {
        wp_send_json_error(['message'=>'Database connection failed.']);
    }

    $rows = $rfq_db->get_results("SELECT status_id, status_name FROM status ORDER BY status_name ASC");
    if ($rows === null) {
        wp_send_json_error(['message'=>'Query error: '.$rfq_db->last_error]);
    }
    $statuses = [];
    foreach ($rows as $r) {
        $statuses[] = [
            'id'   => intval($r->status_id),
            'name' => esc_html($r->status_name)
        ];
    }
    wp_send_json_success(['statuses' => $statuses]);
}
add_action('wp_ajax_srfq_fetch_statuses','srfq_fetch_statuses');
add_action('wp_ajax_nopriv_srfq_fetch_statuses','srfq_fetch_statuses');

function rfq_get_currencies() {
    $rfq_db = rfq_get_db_connection();
    $out = [];
    if ($rfq_db) {
        $rows = $rfq_db->get_results(
            "SELECT currency_id, currency_description 
             FROM currency
             ORDER BY currency_description ASC"
        );
        foreach ($rows as $r) {
            $out[] = [
                'id'   => intval($r->currency_id),
                'name' => esc_html($r->currency_description),
            ];
        }
    }
    return $out;
}

/**
 * srfq_save_rfq_status
 *
 * AJAX handler: saves the new status_id into rfq_product.status_rfq_product
 * Must return clean JSON—no stray whitespace or HTML.
 */
function srfq_save_rfq_status() {
    // 1) Verify the nonce
    check_ajax_referer( 'rfq_nonce', 'nonce' );

    // 2) Connect to the RFQ database
    $rfq_db = rfq_get_db_connection();
    if ( ! $rfq_db ) {
        wp_send_json_error([ 'message' => 'Database connection failed.' ]);
        // wp_send_json_error() always dies() immediately
    }

    // 3) Sanitize inputs
    $rfq_product_id = isset($_POST['rfq_product_id']) ? intval($_POST['rfq_product_id']) : 0;
    $new_status_id  = isset($_POST['status_id'])      ? intval($_POST['status_id'])      : 0;

    if ( $rfq_product_id <= 0 || $new_status_id <= 0 ) {
        wp_send_json_error([ 'message' => 'Invalid RFQ product or status ID.' ]);
    }

    // 4) Update rfq_product.status_rfq_product
    $updated = $rfq_db->update(
        'rfq_product',
        [ 'status_rfq_product' => $new_status_id ],
        [ 'rfq_product_id'     => $rfq_product_id ],
        [ '%d' ],
        [ '%d' ]
    );

    if ( false === $updated ) {
        // If update() returns false, something went wrong
        wp_send_json_error([ 'message' => 'Failed updating status: ' . $rfq_db->last_error ]);
    }

    // 5) Success
    wp_send_json_success([ 'message' => 'Status updated successfully.' ]);
}
// Hook it into both logged‐in and nonlogged‐in AJAX
add_action( 'wp_ajax_rfq_save_rfq_status',    'srfq_save_rfq_status' );
add_action( 'wp_ajax_nopriv_rfq_save_rfq_status', 'srfq_save_rfq_status' );




/**
 * 7) AJAX Handlers for Editable RFQ Table
 */

/**
 * srfq_fetch_lines => Fetch data for the editable RFQ table
 */
/**
 * AJAX handler: fetch data for editable RFQ table
 */
add_action('wp_ajax_srfq_fetch_lines','srfq_fetch_lines');
add_action('wp_ajax_nopriv_srfq_fetch_lines','srfq_fetch_lines');
function srfq_fetch_lines(){
    check_ajax_referer('rfq_nonce','nonce');
    $rfq_db = rfq_get_db_connection();
    if(!$rfq_db) wp_send_json_error(['message'=>'DB error']);

    $sql = "
      SELECT DISTINCT
        rc.rfq_client_id,
        COALESCE(c.client_name,'No Client')    AS client_name,
        p.product_name,
        rp.quantity,
        COALESCE(ROUND(rp.target_price,3),0)    AS target_price,
        rp.currency_id,
        cur.currency_description,
        COALESCE(ROUND(rp.price_to_client,3),0) AS price_to_client,
        rp.notes                               AS notes,
        rp.percentage_paid                   AS paid_percentage,
        rp.rfq_product_id,
        rp.status_rfq_product                 AS status_id,
        COALESCE(st.status_name,'')            AS status_name,

        -- how many suppliers linked
        COALESCE(sup.supplier_count,0)         AS suppliers_count,
        -- how many valid quotations
        COALESCE(qt.quote_count,0)             AS quotations_count

      FROM rfq_client        rc
      JOIN rfq_product      rp  ON rc.rfq_client_id      = rp.rfq_client_id
      LEFT JOIN client      c   ON rc.client_id          = c.client_id
      LEFT JOIN products    p   ON rp.product_id         = p.product_id
      LEFT JOIN currency    cur ON cur.currency_id      = rp.currency_id
      LEFT JOIN status      st  ON rp.status_rfq_product = st.status_id

      LEFT JOIN (
        SELECT rfq_product_id, COUNT(*) AS supplier_count
        FROM rfq_supplier
        GROUP BY rfq_product_id
      ) sup ON sup.rfq_product_id = rp.rfq_product_id

      LEFT JOIN (
        SELECT rfq_product_id, COUNT(*) AS quote_count
        FROM quotation
        WHERE price IS NOT NULL AND price <> 0
        GROUP BY rfq_product_id
      ) qt  ON qt.rfq_product_id  = rp.rfq_product_id

      ORDER BY qt.quote_count ASC    -- ← sort ascending
    ";

    $rows = $rfq_db->get_results($sql);
    if ($rows === null) wp_send_json_error(['message'=>$rfq_db->last_error]);

    $data = array_map(function($r){
      return [
        'rfq_client_id'     => (int)$r->rfq_client_id,
        'client_name'       => $r->client_name,
        'product_name'      => $r->product_name,
        'quantity'          => (int)$r->quantity,
        'target_price'      => $r->target_price,
        'currency_id'       => (int)$r->currency_id,
        'currency_description' => $r->currency_description,
        'price_to_client'   => $r->price_to_client,
        'notes'             => $r->notes,
        'paid_percentage'   => $r->paid_percentage,
        'rfq_product_id'    => (int)$r->rfq_product_id,
        'status_rfq_product'=> (int)$r->status_id,
        'status_name'       => $r->status_name,
        'suppliers_count'   => (int)$r->suppliers_count,
        'quotations_count'  => (int)$r->quotations_count,
      ];
    }, $rows);

    wp_send_json_success(['rows'=>$data]);
}



