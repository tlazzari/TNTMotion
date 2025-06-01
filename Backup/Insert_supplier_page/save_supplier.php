<?php
// Explicitly define database credentials for TNT_Db
$host = 'localhost';
$dbname = 'TNT_Db';
$user = 'Tom1977';
$password = 'TNT2024@!';
$port = 3306;

// Set the content type to JSON
header('Content-Type: application/json');

// Get the incoming data
$data = json_decode(file_get_contents('php://input'), true);
error_log("Received data: " . print_r($data, true)); // Debug incoming data

// Create a direct MySQL connection to TNT_Db
$mysqli = new mysqli($host, $user, $password, $dbname, $port);

// Check connection
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

// Set character set to utf8mb4
if (!$mysqli->set_charset("utf8mb4")) {
    echo json_encode(['success' => false, 'message' => "Error loading character set utf8mb4: " . $mysqli->error]);
    exit;
}

// Handle update_supplier action
if ($data['action'] === 'update_supplier') {
    $id = intval($data['id']);
    $columnIndex = intval($data['columnIndex']);
    $updatedValue = $mysqli->real_escape_string($data['updatedValue']);

    // Map columnIndex to table column names
    $columns = [
        1 => 'supplier_name',
        2 => 'supplier_address',
        3 => 'last_audited',
        4 => 'supplier_main_contact',
        5 => 'last_update',
        6 => 'notes',
        7 => 'brand',
        8 => 'primary_product',
        9 => 'factory_size',
        10 => 'clients',
        11 => 'equipment',
        12 => 'website',
        13 => 'source',
        14 => 'database_ranking'
    ];

    if (!isset($columns[$columnIndex])) {
        echo json_encode(['success' => false, 'message' => 'Invalid column index']);
        exit;
    }

    $columnName = $columns[$columnIndex];

    // Update query
    $query = "UPDATE suppliers SET $columnName = '$updatedValue' WHERE supplier_id = $id";
    $result = $mysqli->query($query);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Supplier updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => "Failed to update supplier: " . $mysqli->error]);
    }
    exit;
}

// Handle add_supplier action
if ($data['action'] === 'add_supplier') {
    $supplierData = $data['data'];

    // Build the INSERT query
    $query = "INSERT INTO suppliers (
                supplier_name, supplier_address, last_audited, supplier_main_contact, last_update, 
                notes, brand, primary_product, factory_size, clients, equipment, website, source, database_ranking
              ) VALUES (
                '" . $mysqli->real_escape_string($supplierData[0]) . "',
                '" . $mysqli->real_escape_string($supplierData[1]) . "',
                '" . $mysqli->real_escape_string($supplierData[2]) . "',
                '" . $mysqli->real_escape_string($supplierData[3]) . "',
                '" . $mysqli->real_escape_string($supplierData[4]) . "',
                '" . $mysqli->real_escape_string($supplierData[5]) . "',
                '" . $mysqli->real_escape_string($supplierData[6]) . "',
                '" . $mysqli->real_escape_string($supplierData[7]) . "',
                '" . $mysqli->real_escape_string($supplierData[8]) . "',
                '" . $mysqli->real_escape_string($supplierData[9]) . "',
                '" . $mysqli->real_escape_string($supplierData[10]) . "',
                '" . $mysqli->real_escape_string($supplierData[11]) . "',
                '" . $mysqli->real_escape_string($supplierData[12]) . "',
                '" . $mysqli->real_escape_string($supplierData[13]) . "'
              )";

    $result = $mysqli->query($query);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Supplier added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => "Failed to add supplier: " . $mysqli->error]);
    }
    exit;
}

// Handle invalid actions
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;
