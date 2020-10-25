<?php

include 'database.php';
include 'include.php';

$invoice_id = $_GET['invoice_id'];
$transaction_hash = $_GET['transaction_hash'];
$value_in_btc = $_GET['value'] / 100000000;

$query = "SELECT address FROM [biconatrade].[dbo].[invoices] WHERE invoice_id='{$invoice_id}'";
$result = sqlsrv_query($conn, $query); 
if($result === false){
    die( print_r( sqlsrv_errors(), true));
}

// $result = $stmt->get_result();
while($row = sqlsrv_fetch_array($result)) {
  $my_address = $row['address'];
}
if ($_GET['address'] != $my_address) {
    echo 'Incorrect Receiving Address';
  return;
}

if ($_GET['secret'] != $secret) {
  echo 'Invalid Secret';
  return;
}

if ($_GET['confirmations'] >= 4) {
  //Add the invoice to the database
  $query1 = "INSERT INTO [biconatrade].[dbo].[invoice_payments] (invoice_id, transaction_hash, value) VALUES ('$invoice_id', '$transaction_hash', '$value_in_btc')";
  $result1 = sqlsrv_query($conn, $query1);

  //Delete from pending
  $query2 = "Delete from [biconatrade].[dbo].[pending_invoice_payments] WHERE invoice_id='{$invoice_id}'";
  $result2 = sqlsrv_query($conn, $query2);

  // if($result) {
	//    echo "*ok*";
  // }
} else {
  //Waiting for confirmations
  //create a pending payment entry
  $query4 = "INSERT INTO [biconatrade].[dbo].[pending_invoice_payments] (invoice_id, transaction_hash, value) VALUES ('$invoice_id', '$transaction_hash', '$value_in_btc')";
  $result4 = sqlsrv_query($conn, $query4);

   echo "Waiting for confirmations";
}

?>