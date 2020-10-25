<?php

//Proxy to the api/receive method in order to not reveal the callback URL

include 'include.php';
include 'database.php';

$gap_limit = 300;
// $invoice_id = $_SESSION['invoice_id'];
$invoice_id = (isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '');
// print($invoice_id); exit;
// $price_in_btc = $_GET['price_in_btc'];
// print($price_in_btc);
$callback_url = $mysite_root . "callback.php?invoice_id=" . $invoice_id . "&secret=" . $secret;
$url = $blockchain_receive_root . "v2/receive?xpub=" . $my_xpub . "&callback=" . urlencode($callback_url) . "&key=" . $my_api_key . "&gap_limit=" . $gap_limit;
// $resp = file_get_contents($blockchain_receive_root . "v2/receive?key=" . $my_api_key . "&callback=" . urlencode($callback_url) . "&xpub=" . $my_xpub);
$ch  = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$resp = curl_exec($ch);
$response = json_decode($resp);

// print_r($response); exit;


//Add the invoice to the database
// $stmt = $db->prepare("UPDATE invoices SET address = ? WHERE invoice_id = ?");
// $stmt->bind_param("si", $response->address, $invoice_id);
// $result = $stmt->execute();
$query1 = "UPDATE [biconatrade].[dbo].[invoices] SET address = '$response->address' WHERE invoice_id='$invoice_id'";
$result1 = sqlsrv_query($conn, $query1);

// if (!$result) {
//     die(__LINE__ . ' Invalid query: ' . mysqli_error($db));
// }
// json_encode(array('price_btc' => $price_in_btc ));
print json_encode(array('input_address' => $response->address ));

?>