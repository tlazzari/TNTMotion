<?php
/**
 * Plugin Name: RFQ Supplier Order Manager
 * Description: Manage RFQ Supplier Orders with status editing for client and supplier in the table.
 * Version: 3.1
 * Author: Your Name
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/**
 * DB Connection Helper
 */
function get_tnt_order_table_manager_db_connection() {
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
 * Enqueue CSS/JS
 */
function rfq_table_manager_enqueue_assets() {
    $conn = get_tnt_order_table_manager_db_connection();

    // Enqueue some CSS
    wp_enqueue_style(
        'editable-rfq-table-style',
        plugin_dir_url(__FILE__) . 'css/editable-rfq-table.css',
        array(),
        '1.0'
    );

    // Enqueue main JS
    wp_enqueue_script(
        'editable-rfq-table-script',
        plugin_dir_url(__FILE__) . 'js/editable-rfq-table.js',
        array('jquery'),
        '3.1',
        true
    );

    // Build array of currencies (if you have them):
    $currencies = [];
    if ($conn) {
        $curRes = $conn->query("SELECT currency_id, currency_description FROM currency ORDER BY currency_description ASC");
        if ($curRes) {
            while ($row = $curRes->fetch_assoc()) {
                $currencies[] = [
                    'currency_id'          => (int)$row['currency_id'],
                    'currency_description' => $row['currency_description'],
                ];
            }
        }
    }
    wp_localize_script('editable-rfq-table-script', 'availableCurrencies', $currencies);

    // Build array of statuses from `status` table
    $statuses = [];
    if ($conn) {
        $statRes = $conn->query("SELECT status_id, status_name FROM status ORDER BY status_name ASC");
        if ($statRes) {
            while ($stRow = $statRes->fetch_assoc()) {
                $statuses[] = [
                    'status_id'   => (int)$stRow['status_id'],
                    'status_name' => $stRow['status_name'],
                ];
            }
        }
    }
    // localize statuses so we can build dropdown
    wp_localize_script('editable-rfq-table-script', 'availableStatuses', $statuses);

    // localize general AJAX data
    wp_localize_script(
        'editable-rfq-table-script',
        'editableRFQTable',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('editable_rfq_table_nonce'),
        )
    );
}
add_action('wp_enqueue_scripts', 'rfq_table_manager_enqueue_assets');

/**
 * Shortcode to Display the Table
 */
function rfq_table_display() {
    ob_start();
    ?>
    <style>
    /* Example styling for smaller font table, row hover, etc. */
    #editable-rfq-table,
    #editable-rfq-table th,
    #editable-rfq-table td {
        font-size: 12px;
        font-family: Arial, sans-serif;
        border-collapse: collapse;
    }
    #editable-rfq-table thead tr {
        background: #f2f2f2;
    }
    #editable-rfq-table th, #editable-rfq-table td {
        padding: 6px;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    #editable-rfq-table tbody tr:hover {
        background: #f9f9f9;
    }
    .save-button,
    .create-order-button,
    .close-line-button,
    #download-excel-button,
    #create-rfq-button {
        font-size: 12px; 
        padding: 4px 6px; 
        background: #4caf50; 
        color: #fff; 
        border: none; 
        border-radius: 3px;
        cursor: pointer;
        margin-right: 2px;
    }
    .save-button:hover,
    .create-order-button:hover,
    .close-line-button:hover,
    #download-excel-button:hover,
    #create-rfq-button:hover {
        background: #45a049;
    }
    </style>

    <div style="max-width: 1400px; margin:20px auto; padding:20px; background:#f7f7f7; border-radius:5px;">
 
        <button id="download-excel-button" class="download-excel-button">Download as Excel</button>

        <div class="table-wrapper">
        <table id="editable-rfq-table">
            <thead>
                <tr>
                    <th style="width:120px;">Close / Make Order</th>
                    <th data-orderby="client.client_name">Client Name</th> <!-- Updated from Client ID -->
                    <th data-orderby="rfq_supplier.rfq_supplier_id">RFQ Supplier ID</th>
                    <th data-orderby="rfq_product.rfq_product_id">RFQ Product ID</th>
                    <th data-orderby="suppliers.supplier_name">Supplier Name</th>
                    <th data-orderby="products.product_name">Product Name</th>
                    <th data-orderby="rfq_product.quantity">Q.ty</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Supplier Notes</th>
                    <th>Product Notes</th>
                    <th>Specifications</th>
                    <th>Measurements</th>
                    <th>Promised Delivery</th>
                    <th>Client Status</th>      <!-- we will turn this into a dropdown -->
                    <th>RFQ Supplier Status</th><!-- also a dropdown -->
                </tr>
            </thead>
            <tbody><!-- populated by JS --></tbody>
        </table>
        </div>
    </div>

    <!-- Hidden by default. We'll show it if the order already exists. -->
    <div id="override-modal" style="display:none; position:fixed; top:30%; left:50%; transform:translate(-50%,-30%); background:#fff; border:1px solid #ccc; padding:20px; z-index:9999; width:300px;">
        <h3>Order Already Exists</h3>
        <p>That order already exists in the system. Do you want to override and create a new order anyway?</p>
        <button id="override-button" style="margin-right:10px;">OVERRIDE</button>
        <button id="quit-button">QUIT</button>
    </div>
    <!-- A simple backdrop if you want -->
    <div id="override-backdrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.3); z-index:9998;"></div>

    <?php
    return ob_get_clean();
}
add_shortcode('editable_rfq_table', 'rfq_table_display');

/**
 * fetch_rfq_table_data()
 */
/**
 * fetch_rfq_table_data()
 */
function fetch_rfq_table_data() {
    check_ajax_referer('editable_rfq_table_nonce', 'nonce');

    $orderby = isset($_POST['orderby']) ? $_POST['orderby'] : 'rfq_supplier.rfq_supplier_id';
    $allowed_columns = [
        'client.client_name', 
        'rfq_supplier.rfq_supplier_id',
        'rfq_product.rfq_product_id',
        'suppliers.supplier_name',
        'products.product_name',
        'rfq_product.quantity',
        'rfq_supplier.promised_delivery_date'
    ];
    if (!in_array($orderby, $allowed_columns)) {
        $orderby = 'rfq_supplier.rfq_supplier_id';
    }

    $conn = get_tnt_order_table_manager_db_connection();
    if (!$conn) {
        wp_send_json_error(['message' => 'Database connection failed.']);
    }

    // Updated SQL Query without duplicate ORDER BY
    $query = "
        SELECT 
            client.client_name,  -- Selecting client_name from the client table
            rfq_supplier.rfq_supplier_id,
            rfq_product.rfq_product_id,
            suppliers.supplier_id,
            suppliers.supplier_name,
            products.product_name,
            rfq_product.quantity,
            rfq_product.notes AS product_notes,
            rfq_product.specifications,
            rfq_product.measurements,
            
            MAX(quotation.price)              AS price,
            MIN(quotation.promised_delivery_date) AS promised_delivery_date,


            -- Client status details
            rfq_client.status AS client_status_id,
            client_status.status_name AS client_status_name,

            -- Supplier status details
            rfq_supplier.status_rfq_supplier AS supplier_status_id,
            supplier_status.status_name AS rfq_supplier_status_name,

            -- Currency details
            currency.currency_id,
            currency.currency_description,

            -- Aggregating supplier notes
            GROUP_CONCAT(DISTINCT rfq_supplier.notes ORDER BY rfq_supplier.notes ASC SEPARATOR ', ') AS supplier_notes
        FROM rfq_supplier
        -- Joining rfq_product to rfq_supplier
        LEFT JOIN rfq_product 
            ON rfq_supplier.rfq_product_id = rfq_product.rfq_product_id

        -- Joining quotation to rfq_product and rfq_supplier
        LEFT JOIN quotation 
            ON rfq_product.rfq_product_id = quotation.rfq_product_id
            AND rfq_supplier.rfq_supplier_id = quotation.rfq_supplier_id

        -- Joining currency to quotation
        LEFT JOIN currency 
            ON quotation.currency_id = currency.currency_id

        -- Joining suppliers to rfq_supplier
        LEFT JOIN suppliers 
            ON rfq_supplier.supplier_id = suppliers.supplier_id

        -- Joining products to rfq_product
        LEFT JOIN products 
            ON rfq_product.product_id = products.product_id

        -- Joining rfq_client to rfq_product
        LEFT JOIN rfq_client 
            ON rfq_client.rfq_client_id = rfq_product.rfq_client_id

        -- Joining client to rfq_client
        LEFT JOIN client 
            ON rfq_client.client_id = client.client_id

        -- Joining status for clients
        LEFT JOIN status AS client_status 
            ON rfq_client.status = client_status.status_id

        -- Joining status for suppliers
        LEFT JOIN status AS supplier_status 
            ON rfq_supplier.status_rfq_supplier = supplier_status.status_id

        -- Grouping to prevent duplicate rfq_supplier entries
        GROUP BY 
            rfq_supplier.rfq_supplier_id,
            client.client_name,
            rfq_product.rfq_product_id,
            suppliers.supplier_id,
            suppliers.supplier_name,
            products.product_name,
            rfq_product.quantity,
            rfq_product.notes,
            rfq_product.specifications,
            rfq_product.measurements,
            rfq_client.status,
            client_status.status_name,
            rfq_supplier.status_rfq_supplier,
            supplier_status.status_name,
            currency.currency_id,
            currency.currency_description

        -- Ordering the results dynamically based on user selection
        ORDER BY 
            $orderby ASC
    ";

    $res = $conn->query($query);
    if (!$res) {
        wp_send_json_error(['message' => 'Query failed.', 'sql_error' => $conn->error]);
    }

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }

    wp_send_json_success(['rows' => $rows]);
}
add_action('wp_ajax_fetch_rfq_table_data', 'fetch_rfq_table_data');
add_action('wp_ajax_nopriv_fetch_rfq_table_data', 'fetch_rfq_table_data');

/**
 * update_rfq_table_data()
 */
/**
 * update_rfq_table_data()
 */
/**
 * AJAX handler: update a single RFQ line, including upserting a quotation row.
 */
function update_rfq_table_data() {
    check_ajax_referer('editable_rfq_table_nonce', 'nonce');

    // 1) Sanitize + collect inputs
    $rfq_supplier_id        = intval( $_POST['rfq_supplier_id']        );
    $quantity               = sanitize_text_field( $_POST['quantity'] );
    $price                  = floatval( $_POST['price']             );
    $currency_id            = intval( $_POST['currency_id']         );
    $supplier_notes         = sanitize_text_field( $_POST['supplier_notes'] );
    $product_notes          = sanitize_text_field( $_POST['product_notes']  );
    $specifications         = sanitize_text_field( $_POST['specifications'] );
    $measurements           = sanitize_text_field( $_POST['measurements']   );
    $promised_delivery_date = sanitize_text_field( $_POST['promised_delivery_date'] );
    $client_status_id       = intval( $_POST['client_status_id']       );
    $rfq_supplier_status_id = intval( $_POST['rfq_supplier_status_id'] );

    // 2) Connect
    $conn = get_tnt_order_table_manager_db_connection();
    if ( ! $conn ) {
        wp_send_json_error([ 'message' => 'Database connection failed.' ]);
    }

    // 3) Start transaction
    $conn->begin_transaction();

    try {
        //
        // 4) UPDATE rfq_product (joined via rfq_supplier_id)
        //
        $sql = "
            UPDATE rfq_product rp
            JOIN rfq_supplier rs ON rp.rfq_product_id = rs.rfq_product_id
            SET
              rp.quantity       = ?,
              rp.notes          = ?,
              rp.specifications = ?,
              rp.measurements   = ?
            WHERE rs.rfq_supplier_id = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssi',
            $quantity,
            $product_notes,
            $specifications,
            $measurements,
            $rfq_supplier_id
        );
        $stmt->execute();
        $stmt->close();

        //
        // 5) UPDATE rfq_supplier
        //
        $sql = "
            UPDATE rfq_supplier
            SET
              notes                = ?,
              status_rfq_supplier  = ?
            WHERE rfq_supplier_id = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sii',
            $supplier_notes,
            $rfq_supplier_status_id,
            $rfq_supplier_id
        );
        $stmt->execute();
        $stmt->close();

        //
        // 6) UPDATE rfq_client (via the same join chain)
        //
        $sql = "
            UPDATE rfq_client rc
            JOIN rfq_product rp ON rc.rfq_client_id = rp.rfq_client_id
            JOIN rfq_supplier rs ON rp.rfq_product_id = rs.rfq_product_id
            SET rc.status = ?
            WHERE rs.rfq_supplier_id = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ii',
            $client_status_id,
            $rfq_supplier_id
        );
        $stmt->execute();
        $stmt->close();

        //
        // 7) Figure out rfq_product_id for the quotation upsert
        //
        $sql = "SELECT rfq_product_id FROM rfq_supplier WHERE rfq_supplier_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $rfq_supplier_id);
        $stmt->execute();
        $stmt->bind_result($rfq_product_id);
        $stmt->fetch();
        $stmt->close();

        if (empty($rfq_product_id)) {
            throw new Exception("Could not determine rfq_product_id for supplier #{$rfq_supplier_id}");
        }

        //
        // 8) UPSERT into quotation
        //    (requires a UNIQUE KEY on (rfq_product_id, rfq_supplier_id))
        //
        $sql = "
            INSERT INTO quotation
              (rfq_product_id, rfq_supplier_id, currency_id, price, promised_delivery_date)
            VALUES
              (?,              ?,               ?,           ?,     ?)
            ON DUPLICATE KEY UPDATE
              currency_id           = VALUES(currency_id),
              price                 = VALUES(price),
              promised_delivery_date = VALUES(promised_delivery_date)
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'iiids',
            $rfq_product_id,
            $rfq_supplier_id,
            $currency_id,
            $price,
            $promised_delivery_date
        );
        $stmt->execute();
        $stmt->close();

        // 9) Commit
        $conn->commit();

        wp_send_json_success([ 'message' => 'Data saved successfully.' ]);

    } catch ( Exception $e ) {
        // Rollback on ANY error
        $conn->rollback();
        wp_send_json_error([
            'message' => 'Transaction failed: ' . $e->getMessage()
        ]);
    }
}
add_action('wp_ajax_update_rfq_table_data',    'update_rfq_table_data');
add_action('wp_ajax_nopriv_update_rfq_table_data','update_rfq_table_data');


/**
 * create_supplier_order
 */
function create_supplier_order() {
    check_ajax_referer('editable_rfq_table_nonce', 'nonce');

    // Gather posted data with default values
    $rfq_supplier_id    = isset($_POST['rfq_supplier_id']) ? intval($_POST['rfq_supplier_id']) : 0;
    $rfq_product_id     = isset($_POST['rfq_product_id']) ? intval($_POST['rfq_product_id']) : 0;
    $supplier_id        = isset($_POST['supplier_id']) ? intval($_POST['supplier_id']) : 0;

    $price              = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
    $currency_id        = isset($_POST['currency_id']) ? intval($_POST['currency_id']) : 1;
    $force              = isset($_POST['force']) ? intval($_POST['force']) : 0;

    // New fields from front end
    $measurements       = isset($_POST['measurements']) ? trim($_POST['measurements']) : '';
    $specifications     = isset($_POST['specifications']) ? trim($_POST['specifications']) : '';
    $delivery_date      = isset($_POST['promised_delivery_day']) ? trim($_POST['promised_delivery_day']) : '';

    // Initialize $rfq_client_id
    $rfq_client_id     = 0;

    // Log the received data
    error_log("DEBUG create_supplier_order: rfq_supplier_id=$rfq_supplier_id, rfq_product_id=$rfq_product_id, supplier_id=$supplier_id, price=$price, currency_id=$currency_id, measurements='$measurements', specifications='$specifications', delivery_date='$delivery_date', force=$force");

    $conn = get_tnt_order_table_manager_db_connection();
    if (!$conn) {
        wp_send_json_error(['message' => 'Database connection failed.']);
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        /**
         * 1) Look up rfq_client_id
         */
        $lookup_sql = "
            SELECT rp.rfq_client_id
            FROM rfq_supplier rs
            JOIN rfq_product rp ON rs.rfq_product_id = rp.rfq_product_id
            WHERE rs.rfq_supplier_id = ?
              AND rs.rfq_product_id   = ?
            LIMIT 1
        ";
        $stmtC = $conn->prepare($lookup_sql);
        if (!$stmtC) {
            throw new Exception('Failed to prepare rfq_client_id lookup: ' . $conn->error);
        }
        $stmtC->bind_param('ii', $rfq_supplier_id, $rfq_product_id);
        if (!$stmtC->execute()) {
            throw new Exception('Failed to execute rfq_client_id lookup: ' . $stmtC->error);
        }
        $stmtC->bind_result($rfq_client_id);
        if (!$stmtC->fetch()) {
            throw new Exception('Could not find rfq_client_id for this supplier line.');
        }
        $stmtC->close();

        // Log the fetched rfq_client_id
        error_log("DEBUG: Fetched rfq_client_id = $rfq_client_id");

        /**
         * 2) (Optional) check if same order exists
         */
        if (!$force) {
            $check_sql = "
                SELECT COUNT(*) as cnt
                FROM supplier_order
                WHERE rfq_supplier_id = ?
                  AND rfq_product_id  = ?
                  AND rfq_client_id   = ?
                  AND supplier_id     = ?
                  AND price           = ?
                  AND currency_id     = ?
                  AND measurements    = ?
                  AND specifications  = ?
                  AND delivery_date   = ?
            ";
            $stmtCheck = $conn->prepare($check_sql);
            if (!$stmtCheck) {
                throw new Exception('Failed to prepare existence check: ' . $conn->error);
            }
            // Bind parameters with correct types
            $stmtCheck->bind_param(
                'iiiidisss',
                $rfq_supplier_id,
                $rfq_product_id,
                $rfq_client_id,
                $supplier_id,
                $price,
                $currency_id,
                $measurements,
                $specifications,
                $delivery_date
            );
            if (!$stmtCheck->execute()) {
                throw new Exception('Failed to execute existence check: ' . $stmtCheck->error);
            }

            // Fetch the count
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            // Log the count
            error_log("DEBUG - existing order count = $count");

            if ($count > 0) {
                // Rollback transaction
                $conn->rollback();
                // Already exists => return error
                wp_send_json_error([
                    'message'        => 'This order already exists!',
                    'already_exists' => true
                ]);
            }
        }

        /**
         * 3) Insert
         */
        // get next order_number
        $orderNumQuery = "SELECT MAX(order_document_id) AS last_order FROM supplier_order";
        $res = $conn->query($orderNumQuery);
        if (!$res) {
            throw new Exception('Could not get last order number: ' . $conn->error);
        }
        $row = $res->fetch_assoc();
        $lastOrderNum = !empty($row['last_order']) ? intval($row['last_order']) : 0;
        $newOrderNum  = $lastOrderNum + 1;

        // Prepare Insert Statement
        $insertSql = "
            INSERT INTO supplier_order (
                rfq_supplier_id,
                rfq_product_id,
                rfq_client_id,
                supplier_id,
                price,
                currency_id,
                order_document_id,
                measurements,
                specifications,
                delivery_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, COALESCE(NULLIF(?, ''), NULL))
        ";
        $stmt = $conn->prepare($insertSql);
        if (!$stmt) {
            throw new Exception('Failed to prepare insert for supplier_order: ' . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param(
            'iiiidiisss',
            $rfq_supplier_id,
            $rfq_product_id,
            $rfq_client_id,
            $supplier_id,
            $price,
            $currency_id,
            $newOrderNum,
            $measurements,
            $specifications,
            $delivery_date
        );

        // Log the insert parameters
        $full_insert_query = sprintf(
            "INSERT INTO supplier_order (rfq_supplier_id, rfq_product_id, rfq_client_id, supplier_id, price, currency_id, order_document_id, measurements, specifications, delivery_date) VALUES (%d, %d, %d, %d, %.2f, %d, %d, '%s', '%s', '%s')",
            $rfq_supplier_id,
            $rfq_product_id,
            $rfq_client_id,
            $supplier_id,
            $price,
            $currency_id,
            $newOrderNum,
            $conn->real_escape_string($measurements),
            $conn->real_escape_string($specifications),
            $conn->real_escape_string($delivery_date)
        );
        error_log("DEBUG: Insert Query = $full_insert_query");

        // Execute the insert
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert into supplier_order: ' . $stmt->error);
        }

        // Log the inserted ID
        $last_id = $stmt->insert_id;
        error_log("DEBUG: Last Insert ID = $last_id");

        // Verify insertion by selecting all fields
        $select_sql = "SELECT * FROM supplier_order WHERE order_id = ?";
        $stmt_select = $conn->prepare($select_sql);
        if ($stmt_select) {
            $stmt_select->bind_param('i', $last_id);
            if (!$stmt_select->execute()) {
                throw new Exception("Failed to execute select statement after insert: " . $stmt_select->error);
            } else {
                $result = $stmt_select->get_result();
                if ($result) {
                    $inserted_row = $result->fetch_assoc();
                    // Log all fields
                    $log_message = "DEBUG: Inserted Row - " . json_encode($inserted_row);
                    error_log($log_message);
                } else {
                    throw new Exception("Failed to fetch inserted row data.");
                }
            }
            $stmt_select->close();
        } else {
            throw new Exception("Failed to prepare select statement after insert: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();

        wp_send_json_success(['message' => 'Order created successfully!']);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        error_log("Transaction Error: " . $e->getMessage());
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

add_action('wp_ajax_create_supplier_order', 'create_supplier_order');
add_action('wp_ajax_nopriv_create_supplier_order', 'create_supplier_order');

/**
 * create_new_line_entry
 */
function create_new_line_entry() {
    check_ajax_referer('editable_rfq_table_nonce', 'nonce');

    $conn = get_tnt_order_table_manager_db_connection();
    if (!$conn) {
        wp_send_json_error(['message' => 'Database connection failed.']);
    }

    // Are we creating a brand-new client or using an existing client?
    $client_mode = $_POST['client_mode']; // "new_client" or "existing_client"

    // For the RFQ Product dropdown: "new" means create a new product, otherwise we have an existing rfq_product_id
    $existing_rfq_product = $_POST['existing_rfq_product'];

    // Supplier info
    $supplier_id    = intval($_POST['supplier-id']);
    $supplier_notes = $_POST['supplier-notes'];

    // Product fields
    $measurements       = $_POST['new-measurements'];
    $status_rfq_product = intval($_POST['new-product-status']);
    $target_price       = floatval($_POST['new-target-price']); // if float

    /**
     * 1) Insert or determine the rfq_client_id
     */
    if ($client_mode === 'new_client') {
        // Insert a new row in rfq_client
        $client_id         = intval($_POST['new-client-id']);
        $new_rfq_notes     = $_POST['new-rfq-notes'];
        $new_client_status = intval($_POST['new-client-status']);
        $new_rfq_date      = $_POST['new-rfq-date'];

        $client_q = "
            INSERT INTO rfq_client (client_id, notes, status, rfq_date)
            VALUES (?, ?, ?, ?)
        ";
        $stmt_c = $conn->prepare($client_q);
        if (!$stmt_c) {
            wp_send_json_error(['message' => 'Failed to prepare INSERT into rfq_client.', 'error' => $conn->error]);
        }
        $stmt_c->bind_param('isis', $client_id, $new_rfq_notes, $new_client_status, $new_rfq_date);
        if (!$stmt_c->execute()) {
            wp_send_json_error(['message' => 'Failed to insert new rfq_client.', 'error' => $stmt_c->error]);
        }
        $rfq_client_id = $stmt_c->insert_id;
        $stmt_c->close();

        // 2) If user wants a brand new product
        if ($existing_rfq_product === 'new') {
            $product_id        = intval($_POST['new-product-id']);
            $quantity          = $_POST['new-quantity'];
            $product_notes     = $_POST['new-product-notes'];
            $specifications    = $_POST['new-specifications'];
            $product_family_id = intval($_POST['new-product-family-id']);

            $p_q = "
                INSERT INTO rfq_product (
                    rfq_client_id,
                    product_id,
                    quantity,
                    notes,
                    specifications,
                    target_price,
                    product_family_id,
                    measurements,
                    status_rfq_product
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_p = $conn->prepare($p_q);
            if (!$stmt_p) {
                wp_send_json_error(['message' => 'Failed to prepare INSERT into rfq_product.', 'error' => $conn->error]);
            }
            $stmt_p->bind_param(
                'iisssdisi',
                $rfq_client_id,
                $product_id,
                $quantity,
                $product_notes,
                $specifications,
                $target_price, 
                $product_family_id,
                $measurements,
                $status_rfq_product
            );
            if (!$stmt_p->execute()) {
                wp_send_json_error(['message' => 'Failed to insert new rfq_product.', 'error' => $stmt_p->error]);
            }
            $rfq_product_id = $stmt_p->insert_id;
            $stmt_p->close();
        } else {
            // Using an existing product
            $rfq_product_id = intval($existing_rfq_product);
        }

    } else {
        // existing_client mode
        $existing_rfq_client_id = intval($_POST['existing_rfq_client_id']);

        // Possibly create a brand-new product
        if ($existing_rfq_product === 'new') {
            $product_id        = intval($_POST['new-product-id']);
            $quantity          = $_POST['new-quantity'];
            $product_notes     = $_POST['new-product-notes'];
            $specifications    = $_POST['new-specifications'];
            $product_family_id = intval($_POST['new-product-family-id']);

            $p_q = "
                INSERT INTO rfq_product (
                    rfq_client_id,
                    product_id,
                    quantity,
                    notes,
                    specifications,
                    target_price,
                    product_family_id,
                    measurements,
                    status_rfq_product
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt_p = $conn->prepare($p_q);
            if (!$stmt_p) {
                wp_send_json_error(['message' => 'Failed to prepare INSERT into rfq_product.', 'error' => $conn->error]);
            }
            $stmt_p->bind_param(
                'iisssdisi',
                $existing_rfq_client_id,
                $product_id,
                $quantity,
                $product_notes,
                $specifications,
                $target_price,
                $product_family_id,
                $measurements,
                $status_rfq_product
            );
            if (!$stmt_p->execute()) {
                wp_send_json_error(['message' => 'Failed to insert new rfq_product.', 'error' => $stmt_p->error]);
            }
            $rfq_product_id = $stmt_p->insert_id;
            $stmt_p->close();
        } else {
            $rfq_product_id = intval($existing_rfq_product);
        }
    }

    /**
     * 3) Insert into rfq_supplier
     */
    $insert_supplier = "
        INSERT INTO rfq_supplier (rfq_product_id, supplier_id, notes)
        VALUES (?, ?, ?)
    ";
    $stmt_s = $conn->prepare($insert_supplier);
    if (!$stmt_s) {
        wp_send_json_error(['message' => 'Failed to prepare INSERT into rfq_supplier.', 'error' => $conn->error]);
    }
    $stmt_s->bind_param('iis', $rfq_product_id, $supplier_id, $supplier_notes);
    if (!$stmt_s->execute()) {
        wp_send_json_error(['message' => 'Failed to insert into rfq_supplier.', 'error' => $stmt_s->error]);
    }
    $rfq_supplier_id = $stmt_s->insert_id;
    $stmt_s->close();

    /**
     * 4) (Optional) Insert a row into `quotation` or do more logic
     * If you want to create a `quotation` row with promised_delivery_date, price, etc., do it here:
     *
     *   $quotation_sql = "
     *       INSERT INTO quotation (rfq_product_id, rfq_supplier_id, currency_id, closed, price, promised_delivery_date)
     *       VALUES (?, ?, ?, ?, ?, ?)
     *   ";
     *   // and so on...
     */

    wp_send_json_success(['message' => 'New entry created successfully!']);
}
add_action('wp_ajax_create_new_line_entry', 'create_new_line_entry');
add_action('wp_ajax_nopriv_create_new_line_entry', 'create_new_line_entry');

/**
 * close_quotation_line
 * This function handles the AJAX action for closing a quotation line.
 */
function close_quotation_line() {
    check_ajax_referer('editable_rfq_table_nonce', 'nonce');

    $rfq_supplier_id = isset($_POST['rfq_supplier_id']) ? intval($_POST['rfq_supplier_id']) : 0;
    $rfq_product_id  = isset($_POST['rfq_product_id']) ? intval($_POST['rfq_product_id']) : 0;

    if ($rfq_supplier_id === 0 || $rfq_product_id === 0) {
        wp_send_json_error(['message' => 'Invalid RFQ Supplier ID or RFQ Product ID.']);
    }

    $conn = get_tnt_order_table_manager_db_connection();
    if (!$conn) {
        wp_send_json_error(['message' => 'Database connection failed.']);
    }

    // Example logic to "close" a quotation line. Adjust according to your schema.
    $update_sql = "
        UPDATE quotation
        SET closed = 1
        WHERE rfq_supplier_id = ?
          AND rfq_product_id = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        wp_send_json_error(['message' => 'Failed to prepare update query.', 'error' => $conn->error]);
    }

    $stmt->bind_param('ii', $rfq_supplier_id, $rfq_product_id);
    if (!$stmt->execute()) {
        wp_send_json_error(['message' => 'Failed to close quotation line.', 'error' => $stmt->error]);
    }

    wp_send_json_success(['message' => 'Quotation line closed successfully!']);
}
add_action('wp_ajax_close_quotation_line', 'close_quotation_line');
add_action('wp_ajax_nopriv_close_quotation_line', 'close_quotation_line');

?>
