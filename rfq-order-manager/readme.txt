rfq_supplier
	1 	supplier_id 	int(215) 			No 	None 			
	2 	rfq_product_id 	int(215) 			No 	None 			
	3 	final_quotation 	int(100) 			Yes 	NULL 			
	4 	first_quotation 	int(100) 			Yes 	NULL 			
	5 	promised_delivery_date 	date 			Yes 	NULL 	
	6 	notes 	varchar(215) 	utf8mb4_unicode_ci 		Yes 	NULL 			
	7 	first_rfq_date 	date 			No 	current_timestamp() 			
	8 	actual_delivery_date 	date 			No 	current_timestamp() 		
	9 	specification 	varchar(250) 	utf8mb4_unicode_ci 		Yes 	NULL 	
	10 	measurements 	varchar(250) 	utf8mb4_unicode_ci 		Yes 	NULL


supplier_order
 	1	order_id Primary 	int(250) 			No 	None 		AUTO_INCREMENT 	
	2 	price 	double 			No 	None 	
	3 	tax 	double 			Yes 	NULL 	
	4 	vat 	double 			Yes 	NULL  	
	5 	rfq_client_id 	int(250) 			No 	None 
	6 	notes 	text 	utf8mb4_unicode_ci 		Yes 
	7 	supplier_id 	int(250) 			No 	None 
	8 	rfq_supplier_id 	int(250) 			No 	None 
	9 	order_date 	date 			No 	current_timestamp() 
	10 	delivery_date 	date 			Yes 
	11 	status 	int(250) 			Yes 	NULL

rfq_client
 	1 rfq_client_id Primary 	int(10) 	No 	None 	AUTO_INCREMENT 	
	2 	client_id 	int(10) 			No 	None 			
	3 	notes 	varchar(300) 	utf8mb4_unicode_ci 		Yes 	NULL 
	4 	last_update 	date 			No 	current_timestamp() 	
	5 	rfq_date 	date 			No 	current_timestamp() 
	6 	status 	varchar(300) 	utf8mb4_unicode_ci 		No 	None

client
 	1 	product_id Primary 	int(11) 			No 	None 		AUTO_INCREMENT 	
	2 	product_name 	varchar(300) 	utf8mb4_unicode_ci 		No 	None 	
	3 	drawing 	varchar(100) 	utf8mb4_unicode_ci 		Yes 	NULL 
	4 	specifications 	varchar(300) 	utf8mb4_unicode_ci 		Yes 	NULL  	
	5 	notes 	varchar(300) 	utf8mb4_unicode_ci 		Yes 	NULL 
	6 	product_family 	varchar(100) 	utf8mb4_unicode_ci 		Yes 	NULL 
	7 	last_update 	date 			No 	current_timestamp() 	

rfq_prodcut
	1 	rfq_product_id Primary 	int(10) 			No 	None 		AUTO_INCREMENT
	2 	product_id 	int(10) 			No 	None 			Change Change 
	3 	quantity 	varchar(100) 	utf8mb4_unicode_ci 		Yes 	NULL 
	4 	specifications 	varchar(300) 	utf8mb4_unicode_ci 		Yes 	NULL
	5 	notes 	varchar(300) 	utf8mb4_unicode_ci 		Yes 	NULL 
	6 	drawing_number 	varchar(20) 	utf8mb4_unicode_ci 		Yes 	NULL
	7 	target_price 	varchar(10) 	utf8mb4_unicode_ci 		Yes 	NULL 
	8 	best_quotation 	varchar(100) 	utf8mb4_unicode_ci 		Yes 	NULL
	9 	backup_quotation 	varchar(100) 	utf8mb4_unicode_ci 		Yes 
	10 	winning_supplier_id 	int(10) 			Yes 	NULL 
	11 	backup_supplier_id 	int(10) 			Yes 	NULL 
	12 	last_update 	date 			No 	current_timestamp() 
	13 	rfq_client_id 	int(11) 			Yes 	NULL 
	14 	product_family_id 	int(11) 			No 	None 
	15 	rfq_date 	date 			No 	current_timestamp() 	


join rfq_client and client on rfq_client.client_id=client_client_id
join rfq_product and rfq_client on rfq_product.rfq_client_id=rfq_client.rfq_client_id
join rfq_supplier and rfq_product rfq_supplier.rfq_product_id=rfq_product.rfq_product_id
join rfq_supplier and suppliers on rfq_supplier.supplier_id=supplier.supplier_id


this is my rfq.supplier table, please create a wp plugin that calls explicitly using msqli to my TNT_Db database
do not use prefix for tables and call the db names explicitly using mysqli
use function function get_tnt_supplier_db_connection() to avoid conflicts and directly call database
function get_tnt_supplier_order_db_connection() {
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

Create a form that allows me to fill supplier_order table with the following fields:
-price
-tax
-vat
-total
-other_costs
-rfq_supplier_id: when i type in the rfq_supplier_id field it gives me suggestions based on supplier_name or client_name ( query that JOIN client and rfq_client on client_id, rfq_client and rfq_product on rfq_client_id, rfq_product and rfq_supplier on supplier_id, rfq_supplier and supplier on supplier_id) showing me the fields: supplier_name, supplier_rfq_id, client_name, client_rfq_id, product_name (from products table join on product_id), product_family_name (from product_family table join on product_family_id)

-rfq_client_id: when i type in the rfq_client_id field it gives me suggestions based on supplier_name and client_name ( query that JOIN client and rfq_client on client_id, rfq_client and rfq_product on rfq_client_id, rfq_product and rfq_supplier on supplier_id, rfq_supplier and supplier on supplier_id) showing me the fields: supplier_name, supplier_rfq_id, client_name, client_rfq_id, product_name (from products table join on product_id), product_family_name (from product_family table join on product_family_id)

-notes

-measurements
-specifications
-delivery date
-status

below in the page please show me the table with all the past orders printing the following:
price,tax, vat, total, other_costs, supplier_name, client_name, notes, status and on the site the link to a page with all the order fields

join supplier_order and suppliers on supplier_order.supplier_id=suppliers.supplier_id
join supplier_order and status on status.status_id=supplier_order.status_id
join supplier_order and rfq_supplier on supplier_order.rfq_supplier_id=rfq_supplier.rfq_supplier_id
join rfq_supplier and rfq_product on rfq_supplier.rfq_product_id=rfq_product.rfq_product_it
join rfq_client and supplier_order on rfq_client.rfq_client_id=supplier_order.rfq_client_id
join rfq_client and client on rfq_client.client_id=client.client_id
