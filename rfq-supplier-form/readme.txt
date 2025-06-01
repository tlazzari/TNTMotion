1 	supplier_id 	int(215) 			No 	None 			
	2 	rfq_products 	int(215) 			No 	None 			
	3 	final_quotation 	int(100) 			Yes 	NULL 			
	4 	first_quotation 	int(100) 			Yes 	NULL 			
	5 	promised_delivery_date 	date 			Yes 	NULL 	
	6 	notes 	varchar(215) 	utf8mb4_unicode_ci 		Yes 	NULL 			
	7 	first_rfq_date 	date 			No 	current_timestamp() 			
	8 	actual_delivery_date 	date 			No 	current_timestamp() 			
	9 	specification 	varchar(250) 	utf8mb4_unicode_ci 		Yes 	NULL 	
	10 	measurements 	varchar(250) 	utf8mb4_unicode_ci 		Yes 	NULL


this is my rfq.supplier table, please create a wp plugin that calls explicitly using msqli to my TNT_Db database and on the top of the page it makes me create an rfq_supplier by doing the following:

makes me select the rfq_product_id from a table of the last 50 rfq_product table lines sorted by rfq_date from last to first but instead of product_id I want to see the product_name from the table products,then i want to see, quantity specifications, notes, instead of client_id i want to see the client_name from table client.
once the product is selected, only the selected line remains and i want to see the supplier table and be able to select a supplier and sort it as in the tntbearing.com/suppliers where i can select a supplier.
after i select a supplier only the selected supplier line remains and i have a form with pre-populated supplier_id, rfq_product_id already selected based on my selection above and i can add notes and finally click SUBMIT RFQ SUPPLIER.

do not use prefix for tables and call the db names explicitly using mysqli
use function function get_tnt_supplier_db_connection() to avoid conflicts and directly call database
function get_tnt_supplier_db_connection() {
    $db_host = 'localhost'; // Replace with your database host
    $db_name = 'TNT_Db';    // Replace with your database name
    $db_user = 'Tom1977';  // Replace with your database username
    $db_pass = 'TNT2024@!';  // Replace with your database password

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        error_log('Database Connection Error: ' . $conn->connect_error);
        return false;
    }

    return $conn;
}

shortcode name is:
add_shortcode('rfq_supplier_form', 'rfq_supplier_display');

first query is:
    $query = "
        SELECT 
            rp.rfq_product_id, 
            p.product_name, 
            rp.quantity, 
            rp.specifications, 
            rp.notes, 
            c.client_name
        FROM rfq_product rp
        JOIN products p ON rp.product_id = p.product_id
        JOIN rfq_client rc ON rp.rfq_client_id = rc.rfq_client_id
        JOIN client c ON rc.client_id = c.client_id
        ORDER BY rp.rfq_date DESC
        LIMIT 50;
    ";

