<?php
/**
 * Plugin Name: RFQ Supplier Order Manager
 * Description: A WordPress plugin to manage RFQ Supplier Orders.
 * Version: 1.9
 * Author: Your Name
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/**
 * Database connection function for TNT_Db
 */
function get_tnt_order_manager_db_connection() {
    $db_host = 'localhost';
    $db_name = 'TNT_Db';
    $db_user = 'Tom1977';
    $db_pass = 'TNT2024@!';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        error_log('Database Connection Error: ' . $conn->connect_error);
        return false;
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Enqueue Scripts and Styles
 */
function rfq_order_manager_enqueue_assets() {
    // Enqueue Bootstrap CSS (Optional)
    wp_enqueue_style(
        'bootstrap-css',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
        array(),
        '4.5.2'
    );

    // Enqueue DataTables CSS
    wp_enqueue_style(
        'datatables-css',
        'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css',
        array(),
        '1.10.24'
    );

    // Enqueue Custom CSS
    wp_enqueue_style(
        'unique-order-manager-style',
        plugin_dir_url(__FILE__) . 'css/unique-order-manager.css',
        array('bootstrap-css', 'datatables-css'),
        '1.0'
    );

    // Enqueue jQuery (Already enqueued by WordPress)
    // Enqueue Bootstrap JS (Optional)
    wp_enqueue_script(
        'bootstrap-js',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js',
        array('jquery'),
        '4.5.2',
        true
    );

    // Enqueue DataTables JS
    wp_enqueue_script(
        'datatables-js',
        'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js',
        array('jquery'),
        '1.10.24',
        true
    );

    // Enqueue Custom JS
    wp_enqueue_script(
        'unique-order-manager-script',
        plugin_dir_url(__FILE__) . 'js/unique-order-manager.js',
        array('jquery', 'bootstrap-js', 'datatables-js'),
        '1.0',
        true
    );

    wp_localize_script(
        'unique-order-manager-script',
        'uniqueOrderManager',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('unique_order_manager_nonce'),
        )
    );
}
add_action('wp_enqueue_scripts', 'rfq_order_manager_enqueue_assets');

/**
 * Shortcode to Display RFQ Supplier Order Form and Table
 */
/**
 * Shortcode to Display RFQ Supplier Order Form and Table
 */
function rfq_order_manager_display() {
    ob_start(); ?>
    <div id="unique-order-manager" class="container">
        <h2 class="text-center mb-4">RFQ Supplier Order Management</h2>

        <!-- Debug Output -->
        <div id="debug-info" style="display:none;">
            <pre><?php echo date('Y-m-d H:i:s'); ?></pre>
            <pre>User: <?php echo esc_html(wp_get_current_user()->user_login); ?></pre>
        </div>

        <!-- Order Form -->
        <form id="unique-order-form" class="rfq-form">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="price">Price:</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="quantity">Quantity:</label>
                    <input type="number" step="1" min="0" id="quantity" name="quantity" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="tax">Tax:</label>
                    <input type="number" step="0.01" id="tax" name="tax" class="form-control">
                </div>
                <div class="form-group col-md-4">
                    <label for="vat">VAT:</label>
                    <input type="number" step="0.01" id="vat" name="vat" class="form-control">
                </div>
                <div class="form-group col-md-4">
                    <label for="total">Total:</label>
                    <input type="number" step="0.01" id="total" name="total" class="form-control" required>
                </div>
            </div>
            <!-- RFQ Supplier ID Input -->
            <div class="form-group">
                <label for="rfq_supplier_id">RFQ Supplier ID</label>
                <input type="text" id="rfq_supplier_id" class="form-control" autocomplete="off" placeholder="Enter Supplier Name" style="border: 2px solid #007bff;">
                <div id="rfq_supplier_suggestions" style="border: 2px dashed #28a745; min-height: 20px; margin-top: 5px;">
                    <ul class="suggestion-list active" style="margin: 0; padding: 0;">

                    </ul>
                </div>
            </div>

            <!-- RFQ Client ID Input -->
            <div class="form-group">
                <label for="rfq_client_id">RFQ Client ID</label>
                <input type="text" id="rfq_client_id" class="form-control" autocomplete="off" placeholder="Enter Client Name" style="border: 2px solid #007bff;">
                <div id="rfq_client_suggestions" style="border: 2px dashed #28a745; min-height: 20px; margin-top: 5px;">
                    <ul class="suggestion-list" style="margin: 0; padding: 0;"></ul>
                </div>
            </div>

            <!-- Toast Notification -->
            <div aria-live="polite" aria-atomic="true" style="position: relative;">
                <div id="order-toast" class="toast" style="position: absolute; top: 0; right: 0;" data-delay="5000">
                    <div class="toast-header">
                        <strong class="mr-auto">Notification</strong>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        <!-- Message will be injected here -->
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit Order</button>
        </form>


        <!-- Order Table -->
        <h3 class="mt-5">Past Orders</h3>
        <button id="create-order-document" class="btn btn-primary mb-3">Create an Order Document</button>
        <table id="unique-order-table" class="display table table-striped table-bordered" style="width:100%">

            <thead>
                <tr>
                    <th>Select</th>
                    <th>Order ID</th>
                    <th>Price</th>
                    <th>Tax</th>
                    <th>VAT</th>
                    <th>Total</th>
                    <th>Quantity</th>
                    <th>Supplier Name</th>
                    <th>Client Name</th>
                    <th>Product Name</th>
                    <th>Order Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populated by AJAX -->
            </tbody>
        </table>
    </div>
    <?php 
    return ob_get_clean();
}
add_shortcode('unique_order_manager_form', 'rfq_order_manager_display');


/**
 * Create Order Document and assign selected supplier_order lines to it.
 */
/**
 * Create Order Document and assign selected supplier_order lines to it.
 */
function create_order_document() {
    check_ajax_referer('unique_order_manager_nonce', 'nonce');
    $selected_order_ids = isset($_POST['selected_order_ids']) ? $_POST['selected_order_ids'] : [];
    if(empty($selected_order_ids)) {
        wp_send_json_error(['message' => 'No order lines selected.']);
    }
    $conn = get_tnt_order_manager_db_connection();
    if(!$conn) {
        wp_send_json_error(['message' => 'Database connection failed.']);
    }
    
    // Insert new order_document row using a column-less insert
    $insert_sql = "INSERT INTO order_document () VALUES ()";
    if(!$conn->query($insert_sql)) {
        wp_send_json_error(['message' => 'Failed to create order document.', 'error' => $conn->error]);
    }
    $document_id = $conn->insert_id;

    // Update supplier_order rows
    $order_ids_placeholder = implode(',', array_fill(0, count($selected_order_ids), '?'));
    $update_sql = "UPDATE supplier_order SET order_document_id = ? WHERE order_id IN ($order_ids_placeholder)";
    $stmt = $conn->prepare($update_sql);
    if(!$stmt) {
        wp_send_json_error(['message' => 'Failed to prepare update query.', 'error' => $conn->error]);
    }
    $types = str_repeat('i', count($selected_order_ids) + 1);
    $params = array_merge([$document_id], array_map('intval', $selected_order_ids));
    $stmt->bind_param($types, ...$params);
    if(!$stmt->execute()) {
        wp_send_json_error(['message' => 'Failed to update supplier orders.', 'error' => $stmt->error]);
    }
    $stmt->close();
    wp_send_json_success(['message' => 'Order document created successfully!', 'document_id' => $document_id]);
}
add_action('wp_ajax_create_order_document', 'create_order_document');
add_action('wp_ajax_nopriv_create_order_document', 'create_order_document');


/**
 * Handle fetching past orders via AJAX
 */
function unique_fetch_past_orders() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unique_order_manager_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
    }

    // Get database connection
    $conn = get_tnt_order_manager_db_connection();
    if (!$conn) {
        wp_send_json_error(array('message' => 'Database connection failed.'));
    }

    // Fetch orders with necessary joins
    $query = "
        SELECT 
            so.order_id, 
            so.price, 
            so.tax, 
            so.vat, 
            so.total, 
            so.quantity,
            s.supplier_name,
            c.client_name,
            p.product_name
        FROM supplier_order so
        LEFT JOIN rfq_supplier rs ON so.rfq_supplier_id = rs.rfq_supplier_id
        LEFT JOIN suppliers s ON rs.supplier_id = s.supplier_id
        LEFT JOIN rfq_client rc ON so.rfq_client_id = rc.rfq_client_id
        LEFT JOIN client c ON rc.client_id = c.client_id
        LEFT JOIN rfq_product rp ON rs.rfq_product_id = rp.rfq_product_id
        LEFT JOIN products p ON rp.product_id = p.product_id
        ORDER BY so.order_id DESC;
    ";
    $result = $conn->query($query);

    if (!$result) {
        wp_send_json_error(array('message' => 'Database query failed.', 'error' => $conn->error));
    }

    $orders = array();
    while ($row = $result->fetch_assoc()) {
        $orders[] = array(
            'order_id' => $row['order_id'],
            'price' => $row['price'],
            'tax' => $row['tax'],
            'vat' => $row['vat'],
            'total' => $row['total'],
            'quantity' => $row['quantity'],
            'supplier_name' => $row['supplier_name'],
            'client_name' => $row['client_name'],
            'product_name' => $row['product_name'],
        );
    }

    // Ensure 'orders' is an array, even if empty
    if (empty($orders)) {
        $orders = array();
    }

    wp_send_json_success(array('orders' => $orders));

    // Close connection
    $result->free();
    $conn->close();
}
add_action('wp_ajax_unique_fetch_past_orders', 'unique_fetch_past_orders');
add_action('wp_ajax_nopriv_unique_fetch_past_orders', 'unique_fetch_past_orders');

/**
 * Handle fetching RFQ suggestions via AJAX
 */
/**
 * Handle AJAX request to fetch RFQ suggestions.
 */
/**
 * Handle AJAX request to fetch RFQ suggestions.
 */
function unique_fetch_rfq_suggestions() {
    // Log that the function has been triggered
    error_log("unique_fetch_rfq_suggestions() triggered.");

    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unique_order_manager_nonce')) {
        error_log("Nonce verification failed.");
        wp_send_json_error(['message' => 'Nonce verification failed.']);
    }

    // Retrieve and sanitize the search term
    $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
    error_log("Search Term: " . $search_term);

    if (empty($search_term)) {
        error_log("Empty search term received.");
        wp_send_json_error(['message' => 'Search term cannot be empty.']);
    }

    // Get database connection
    $conn = get_tnt_order_manager_db_connection();
    if (!$conn) {
        error_log("Database connection failed.");
        wp_send_json_error(['message' => 'Database connection failed.']);
    }

    // Prepare SQL statement
    // Adjust the table names and column names based on your database schema
    $sql = "
        SELECT 
            products.product_name, 
            suppliers.supplier_name, 
            client.client_name, 
            rfq_client.rfq_client_id, 
            rfq_supplier.rfq_supplier_id 
        FROM rfq_client 
        JOIN client ON rfq_client.client_id = client.client_id 
        JOIN rfq_product ON rfq_product.rfq_client_id = rfq_client.rfq_client_id 
        JOIN products ON rfq_product.product_id = products.product_id 
        JOIN rfq_supplier ON rfq_supplier.rfq_product_id = rfq_product.rfq_product_id 
        JOIN suppliers ON rfq_supplier.supplier_id = suppliers.supplier_id 

            WHERE products.product_name LIKE CONCAT('%', ?, '%') 
            OR suppliers.supplier_name LIKE CONCAT('%', ?, '%') 
            OR client.client_name LIKE CONCAT('%', ?, '%')
        LIMIT 5
    ";

    error_log("Preparing SQL statement: " . $sql);

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Failed to prepare SQL statement: " . $conn->error);
        wp_send_json_error(['message' => 'Failed to prepare SQL statement.']);
    }

    // Bind parameters (assuming all are strings)
    if (!$stmt->bind_param("sss", $search_term, $search_term, $search_term)) {
        error_log("Failed to bind parameters: " . $stmt->error);
        wp_send_json_error(['message' => 'Failed to bind parameters.']);
    }

    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Failed to execute SQL statement: " . $stmt->error);
        wp_send_json_error(['message' => 'Failed to execute SQL statement.']);
    }

    // Fetch results
    $result = $stmt->get_result();
    $suggestions = [];

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'rfq_supplier_id' => intval($row['rfq_supplier_id']),
            'rfq_client_id' => intval($row['rfq_client_id']),
            'supplier_name' => $row['supplier_name'],
            'client_name' => $row['client_name'],
            'product_name' => $row['product_name']
        ];
    }

    // Log the number of suggestions found
    error_log("Number of suggestions found: " . count($suggestions));

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Return the suggestions as a successful JSON response
    wp_send_json_success(['suggestions' => $suggestions]);
}
add_action('wp_ajax_unique_fetch_rfq_suggestions', 'unique_fetch_rfq_suggestions');
add_action('wp_ajax_nopriv_unique_fetch_rfq_suggestions', 'unique_fetch_rfq_suggestions');

/**
 * Handle inserting a new order via AJAX
 */
function unique_insert_order() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unique_order_manager_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
    }

    // Get and sanitize POST data
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
    $tax = isset($_POST['tax']) ? floatval($_POST['tax']) : 0.0;
    $vat = isset($_POST['vat']) ? floatval($_POST['vat']) : 0.0;
    $total = isset($_POST['total']) ? floatval($_POST['total']) : 0.0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $rfq_supplier_id = isset($_POST['rfq_supplier_id']) ? intval($_POST['rfq_supplier_id']) : 0;
    $rfq_client_id = isset($_POST['rfq_client_id']) ? intval($_POST['rfq_client_id']) : 0;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // Validate required fields
    $missing_fields = array();
    if ($price <= 0) $missing_fields[] = 'Price';
    if ($quantity < 0) $missing_fields[] = 'Quantity';
    if ($total < 0) $missing_fields[] = 'Total';
    if ($rfq_supplier_id <= 0) $missing_fields[] = 'RFQ Supplier ID';
    if ($rfq_client_id <= 0) $missing_fields[] = 'RFQ Client ID';

    if (!empty($missing_fields)) {
        wp_send_json_error(array('message' => 'Missing or invalid fields: ' . implode(', ', $missing_fields) . '.'));
    }

    // Get database connection
    $conn = get_tnt_order_manager_db_connection();
    if (!$conn) {
        wp_send_json_error(array('message' => 'Database connection failed.'));
    }

    // Prepare the INSERT statement
    $query = "
        INSERT INTO supplier_order (price, tax, vat, total, quantity, rfq_supplier_id, rfq_client_id, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        wp_send_json_error(array('message' => 'Prepare failed.', 'error' => $conn->error));
    }

    // Bind parameters
    $stmt->bind_param(
        'ddddiiis', // Types: double, double, double, double, int, int, int, string
        $price,
        $tax,
        $vat,
        $total,
        $quantity,
        $rfq_supplier_id,
        $rfq_client_id,
        $notes
    );

    // Execute the statement
    if ($stmt->execute()) {
        wp_send_json_success(array('message' => 'Order inserted successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to insert order.', 'error' => $stmt->error));
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
add_action('wp_ajax_unique_insert_order', 'unique_insert_order');
add_action('wp_ajax_nopriv_unique_insert_order', 'unique_insert_order');

/**
 * Handle updating multiple fields of an order via AJAX
 */
function unique_update_order_fields() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unique_order_manager_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
    }

    // Get and sanitize POST data
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $updates = isset($_POST['updates']) ? $_POST['updates'] : array();

    // Define allowed fields for updating
    $allowed_fields = array('price', 'tax', 'vat', 'total', 'quantity');

    // Validate input
    if ($order_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid Order ID.'));
    }

    if (!is_array($updates) || empty($updates)) {
        wp_send_json_error(array('message' => 'No fields to update.'));
    }

    // Get database connection
    $conn = get_tnt_order_manager_db_connection();
    if (!$conn) {
        wp_send_json_error(array('message' => 'Database connection failed.'));
    }

    // Initialize success flag and messages
    $success = true;
    $messages = array();

    foreach ($updates as $field => $new_value) {
        if (!in_array($field, $allowed_fields)) {
            $success = false;
            $messages[] = "Invalid field: $field.";
            continue;
        }

        // Sanitize and validate based on field type
        switch ($field) {
            case 'price':
            case 'tax':
            case 'vat':
            case 'total':
                $sanitized_value = floatval($new_value);
                if ($sanitized_value < 0) {
                    $success = false;
                    $messages[] = ucfirst($field) . " cannot be negative.";
                    continue 2; // Skip to next field
                }
                break;
            case 'quantity':
                $sanitized_value = intval($new_value);
                if ($sanitized_value < 0) {
                    $success = false;
                    $messages[] = "Quantity cannot be negative.";
                    continue 2; // Skip to next field
                }
                break;
        }

        // Prepare the UPDATE statement
        $query = "UPDATE supplier_order SET $field = ? WHERE order_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $success = false;
            $messages[] = "Prepare failed for $field: " . $conn->error;
            continue;
        }

        // Bind parameters
        if (in_array($field, array('price', 'tax', 'vat', 'total'))) {
            $stmt->bind_param('di', $sanitized_value, $order_id);
        } elseif ($field === 'quantity') {
            $stmt->bind_param('ii', $sanitized_value, $order_id);
        }

        // Execute
        if (!$stmt->execute()) {
            $success = false;
            $messages[] = "Failed to update $field: " . $stmt->error;
        }

        $stmt->close();
    }

    // Close connection
    $conn->close();

    if ($success) {
        wp_send_json_success(array('message' => 'Fields updated successfully.'));
    } else {
        wp_send_json_error(array('message' => implode(' ', $messages)));
    }
}
add_action('wp_ajax_unique_update_order_fields', 'unique_update_order_fields');
add_action('wp_ajax_nopriv_unique_update_order_fields', 'unique_update_order_fields');




/**
 * Shortcode to Display Order Details with Chinese Translations
 */
/**
 * Shortcode to Display Order Details with Chinese Translations, QR Code, and Order Date
 */
function unique_order_details_shortcode() {
    // Get 'order_id' from query variables
    $order_id = get_query_var('order_id', false);

    if (!$order_id) {
        return '<p>No order ID provided.</p>';
    }

    // Get current page URL
    $current_url = home_url(add_query_arg(null, null));
    $qr_code_src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($current_url);

    // Get database connection
    $conn = get_tnt_order_manager_db_connection();
    if (!$conn) {
        return '<p>Database connection failed.</p>';
    }

    // Query to fetch order details
    $query = "
        SELECT
            so.order_id,
            so.order_date,
            client.client_id,
            rfq_supplier.supplier_id,
            suppliers.supplier_address,
            products.product_name,
            product_family.product_family_name,
            rfq_product.specifications,
            so.notes
        FROM supplier_order so
        LEFT JOIN rfq_supplier ON so.rfq_supplier_id = rfq_supplier.rfq_supplier_id
        LEFT JOIN rfq_product ON rfq_product.rfq_product_id = rfq_supplier.rfq_product_id
        LEFT JOIN products ON rfq_product.product_id = products.product_id
        JOIN product_family ON product_family.product_family_id = rfq_product.product_family_id
        LEFT JOIN suppliers ON rfq_supplier.supplier_id = suppliers.supplier_id
        LEFT JOIN rfq_client ON so.rfq_client_id = rfq_client.rfq_client_id
        LEFT JOIN client ON rfq_client.client_id = client.client_id
        WHERE so.order_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return '<p>Prepare failed: ' . esc_html($conn->error) . '</p>';
    }

    $stmt->bind_param('i', $order_id);

    if (!$stmt->execute()) {
        return '<p>Execute failed: ' . esc_html($stmt->error) . '</p>';
    }

    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $output = '<div style="display: flex; align-items: center; justify-content: space-between;">';
        $output .= '<h3>Order Details</h3>';
        $output .= '<img src="' . esc_url($qr_code_src) . '" alt="QR Code" style="width: 150px; height: 150px;">';
        $output .= '</div>';
        $output .= '<table class="table table-bordered">';
        $output .= '<tbody>';
        $output .= "<tr><td><strong>Order Date (订单日期):</strong></td><td>" . esc_html($row['order_date']) . "</td></tr>";
        $output .= "<tr><td><strong>Order ID (订单号码):</strong></td><td>" . esc_html($row['order_id']) . "</td></tr>";
        $output .= "<tr><td><strong>Client ID (客户号码):</strong></td><td>" . esc_html($row['client_id']) . " - " . esc_html($row['client_name']) . "</td></tr>";
        $output .= "<tr><td><strong>Supplier ID (供应商):</strong></td><td>" . esc_html($row['supplier_id']) . " - " . esc_html($row['supplier_name']) . "</td></tr>";
        $output .= "<tr><td><strong>Supplier Address (供应商 地址):</strong></td><td>" . esc_html($row['supplier_address']) . "</td></tr>";
        $output .= "<tr><td><strong>Product Name (产品):</strong></td><td>" . esc_html($row['product_name']) . "</td></tr>";
        $output .= "<tr><td><strong>Product Family Name (产品种类):</strong></td><td>" . esc_html($row['product_family_name']) . "</td></tr>";
        $output .= "<tr><td><strong>Specifications (规格说明):</strong></td><td>" . esc_html($row['specifications']) . "</td></tr>";
        $output .= "<tr><td><strong>Notes (笔记):</strong></td><td>" . esc_html($row['notes']) . "</td></tr>";
        $output .= '</tbody>';
        $output .= '</table>';
    } else {
        $output = '<p>Order not found.</p>';
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    return $output;
}
add_shortcode('unique_order_details', 'unique_order_details_shortcode');


/**
 * Register Custom Query Variable
 */
function unique_register_query_vars($vars) {
    $vars[] = 'order_id'; // Add 'order_id' as a query variable
    return $vars;
}
add_filter('query_vars', 'unique_register_query_vars');

/**
 * Add Rewrite Rules
 */
function unique_add_rewrite_rules() {
    add_rewrite_rule(
        '^order-details/?$',
        'index.php?pagename=order-details',
        'top'
    );
}
add_action('init', 'unique_add_rewrite_rules');

/**
 * Flush Rewrite Rules on Activation
 */
function unique_flush_rewrite_rules() {
    unique_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'unique_flush_rewrite_rules');

/**
 * Flush Rewrite Rules on Deactivation
 */
function unique_deactivate_flush_rewrite_rules() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'unique_deactivate_flush_rewrite_rules');

/**
 * Display Rewrite Rules for Debugging
 */
add_action('wp_loaded', function () {
    global $wp_rewrite;
    error_log('Rewrite Rules: ' . print_r($wp_rewrite->rules, true));
});
?>
