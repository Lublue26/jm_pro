<?
	//
	// Magento CSV Report Script -  Unprocessed Sales Orders
	//
	
	// DATABASE CONNECTION
	
	
	$hostname_MySQLCon="localhost";
	$database_MySQLCon = "MY_DATABASE_NAME";
	$username_MySQLCon = "MY_DATABASE_USER";
	$password_MySQLCon = "MY_DATABASE_PASS";

	$MySQLCon = mysql_pconnect($hostname_MySQLCon, $username_MySQLCon, $password_MySQLCon);
	$MySQLDb = mysql_select_db($database_MySQLCon, $MySQLCon);
	
	// FUNCTION AREA
	
	function get_DB_Row($query){
		$result = @mysql_query($query);
		$numresults = @mysql_num_rows($result);
		
		for ($i = 0; $i < $numresults; $i++) 
		{
			$row = @mysql_fetch_array($result);
		}
		
		return $row;
	}
	
	// Using this temporary function until I find correct way to pull country names from the database according to two digit code
	// Put in your own until a database polled solution is uncovered
	
	function getCountryName($code){
		switch($code) {
			case "GB" 	:	$country = "United Kingdom";
							break;
			case "IE" 	:	$country = "Ireland";
							break;
		}
		
		return $country;
	}
	
	// INITILISATION AREA
	
	$sitename = "mysite";  				// update this to customise your filename
	$datestring = date('j-m-y');
	$filename = $sitename."-".$datestring;
	$output = "";
	
	$fieldlist = array("orderid","firstname","lastname","address","city","region","country",
	"postcode","telephone","email","itemcode","itemname","quantity","shipping_instructions");
	
	$checklist = array("country", "quantity");
	
	// FIELDLIST INSTRUCTIONS 
	// The order of the CSV fields are set here as well as the field titles
	// The field titles must correspond to a number of SQL Variables being set with the AS operator below
	// If you change the default titles be sure to also update the SQL command accordingly
	// Please note that 'country' and 'quantity' values must be processed but should still be put in your prefered sequence
	
	$numfields = sizeof($fieldlist);
	
	// *********************   NOW START BUILDING THE CSV
	
	// Create the column headers
	
	for($k =0; $k < $numfields;  $k++) { 
		$output .= $fieldlist[$k];
		if ($k < ($numfields-1)) $output .= ", ";
	}
	$output .= "\n";
	
	

	$orderquery = "SELECT * FROM sales_order";  	// Query to find unprocessed orders
	$orderresult = mysql_query($orderquery);
	$numorders = @mysql_num_rows($orderresult);
	
	for($i =0; $i < $numorders; $i++) {
		$order = @mysql_fetch_array($orderresult);	// Place each order into an array for proccessing
		$orderid = $order['entity_id'];   			// Grab the orderid for use in the main SQL command
		
		// The following SQL command will find all the individual items associated with this order
		
		$itemquery = 	"SELECT sales_flat_order_item.order_id AS orderid,
						sales_flat_order_item.product_type,
						sales_flat_order_item.quote_item_id,
						sales_flat_order_item.parent_item_id AS parentid,
						sales_flat_order_item.sku AS itemcode,
						sales_flat_order_item.name AS itemname,
						sales_flat_order_item.qty_ordered AS qty_ordered,
						sales_flat_order_item.qty_shipped AS qty_shipped,
						sales_flat_quote_address.email AS email,
						sales_flat_quote_address.prefix AS title,
						sales_flat_quote_address.firstname AS firstname,
						sales_flat_quote_address.lastname AS lastname,
						sales_flat_quote_address.street AS address,
						sales_flat_quote_address.city AS city,
						sales_flat_quote_address.region AS region,
						sales_flat_quote_address.country_id AS country,
						sales_flat_quote_address.postcode AS postcode,
						sales_flat_quote_address.telephone AS telephone,
						sales_flat_quote_address.shipping_description AS shipping_instructions
						FROM sales_flat_order_item 
						INNER JOIN sales_flat_quote_item
						ON sales_flat_order_item.quote_item_id = sales_flat_quote_item.item_id
						INNER JOIN sales_flat_quote_address
						ON sales_flat_quote_item.quote_id = sales_flat_quote_address.quote_id
						WHERE sales_flat_order_item.order_id = $orderid
						AND sales_flat_quote_address.address_type = 'shipping' 
						AND sales_flat_order_item.product_type <> 'configurable'
						AND sales_flat_order_item.qty_shipped < sales_flat_order_item.qty_ordered";
						
		$itemresult = mysql_query($itemquery);		// Run the query
		$numitems = @mysql_num_rows($itemresult);   // Check the number of items in the order
		
		for($j =0; $j < $numitems; $j++) {
			$item = @mysql_fetch_array($itemresult); // Place the item in an array for processing
			
			// Output the order item values in the same sequence set in the fieldlist to match headers
			
			for($m =0; $m < sizeof($fieldlist); $m++) { 
				
				$fieldvalue = $fieldlist[$m];
				
				if(in_array($fieldvalue, $checklist)) {    				// check if on special processing list
				
					if($fieldvalue == "country") $output .= getCountryName($item[$fieldvalue]);
					
					if($fieldvalue == "quantity") {
						$parentid = $item['parentid'];
						if($parentid == 'NULL') {  						// simple product sold on its own (no parent)
							$qty_ordered = $item["qty_ordered"];
							$qty_shipped = $item["qty_shipped"];
						} else { 										// simple product was part of bundle or configurable group
																		// If so, use the parent quanities to calculate
							$parentitem = get_DB_row("SELECT * FROM sales_flat_order_item WHERE item_id = $parentid");
							$qty_ordered = $parentitem["qty_ordered"];
							$qty_shipped = $parentitem["qty_shipped"];
						}
						$output .= ($qty_ordered - $qty_shipped);
					}
					
				} else {
					$output .= $item[$fieldvalue];
				}
				
				if ($m < ($numfields-1)) $output .= ", ";
			}
			
			$output .= "\n";
		}
		
		
	}
	
	// Send the CSV file to the browser for download
	
	header("Content-type: text/x-csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	echo $output;
	exit;
	
?>
