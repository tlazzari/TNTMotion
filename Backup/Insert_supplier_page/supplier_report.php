<?php
/**
 * Plugin Name: Supplier Report
 * Description: A plugin to display supplier reports based on supplier IDs.
 * Version: 1.1
 * Author: Tom Il Bello
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register the Supplier Report shortcode
add_shortcode('Supplier_Report', function() {
    if (!isset($_GET['supplier_id'])) {
        return '<p>No supplier ID provided.</p>';
    }

    $supplier_id = intval($_GET['supplier_id']);

    // Database connection
    $host = 'localhost';
    $dbname = 'TNT_Db';
    $user = 'Tom1977';
    $password = 'TNT2024@!';
    $port = 3306;

    $mysqli = new mysqli($host, $user, $password, $dbname, $port);

    if ($mysqli->connect_error) {
        return "<p>Database connection failed: {$mysqli->connect_error}</p>";
    }

    // Set character set to utf8mb4
    if (!$mysqli->set_charset("utf8mb4")) {
        return "<p>Error loading character set utf8mb4: {$mysqli->error}</p>";
    }

    $query = "SELECT 
                suppliers.supplier_id, 
                suppliers.supplier_name, 
                suppliers.supplier_address, 
                rfq_supplier.first_rfq_date, 
                rfq_client_1.rfq_client_id AS supplier_rfq_client_id,
                client.client_name, 
                rfq_product.rfq_product_id, 
                products.product_name, 
                quotation.price, 
                currency.currency_description
            FROM suppliers
            LEFT JOIN rfq_supplier ON suppliers.supplier_id = rfq_supplier.supplier_id
            LEFT JOIN rfq_product ON rfq_supplier.rfq_supplier_id = rfq_product.rfq_client_id
            LEFT JOIN rfq_client AS rfq_client_1 ON rfq_client_1.rfq_client_id = rfq_product.rfq_client_id
            LEFT JOIN products ON products.product_id = rfq_product.product_id
            LEFT JOIN client ON rfq_client_1.client_id = client.client_id
            LEFT JOIN quotation ON rfq_product.rfq_product_id = quotation.rfq_product_id
            LEFT JOIN currency ON currency.currency_id = quotation.currency_id
            WHERE suppliers.supplier_id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return '<p>No records found for the selected supplier.</p>';
    }

    // Build the HTML table for the supplier report
    $output = '<table border="1" style="width: 100%;">';
    $output .= '<tr>
        <th>Supplier ID</th>
        <th>Supplier Name</th>
        <th>Address</th>
        <th>First RFQ Date</th>
        <th>RFQ Client ID</th>
        <th>RFQ Product ID</th>
        <th>Client Name</th>
        <th>Product Name</th>
        <th>Price</th>
        <th>Currency</th>
    </tr>';

    while ($row = $result->fetch_assoc()) {
        $output .= '<tr>';
        foreach ($row as $value) {
            $output .= '<td>' . htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8') . '</td>';
        }
        $output .= '</tr>';
    }

    $output .= '</table>';

    $stmt->close();
    $mysqli->close();

    return $output;
});
