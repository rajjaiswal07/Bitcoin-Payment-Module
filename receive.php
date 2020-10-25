<?php

include 'include.php';
include 'database.php';
$invoice_id = (isset($_GET['invoice_id']) ? $_GET['invoice_id'] : '');
$response_address = (isset($_GET['response_address']) ? $_GET['response_address'] : '');

$query1 = "SELECT * FROM [biconatrade].[dbo].[invoices] WHERE invoice_id='$invoice_id'";
$result1 = sqlsrv_query($conn, $query1);
$row = sqlsrv_fetch_array($result1);
$price_in_btc = $row['price_in_btc'];

$address = $row['address'];
// $address = '16Agm26UJwXAaRARwrMdcq9uA19dTASoCN';
$balanace_root = 'https://blockchain.info/q/getreceivedbyaddress/'.$address;
$ch  = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $balanace_root);
$resp = curl_exec($ch);
$balance = json_decode($resp);

function digits($num){
    return (int) (log($num, 10) + 1);
  }
$len = digits($balance);

$new_rece = $str = substr($balance, -$len);
$new_bal = substr($price_in_btc, -$len);
if($new_rece == $new_bal)
{
    $status = 'Yes';
}
else
{
    $status = 'No';
}

$query1 = "UPDATE [biconatrade].[dbo].[invoices] SET balance = '$balance', status = '$status' WHERE invoice_id='$invoice_id'";
$result1 = sqlsrv_query($conn, $query1);
if($result1)
{
    $msg = 1;
    header("Location: ../topupfinal.asp?msg=".$msg);
    exit();
}
else 
{
    $msg = 0;
    header("Location: ../topupfinal.asp?msg=".$msg);
    exit();
}

?>