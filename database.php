<?php

// $serverName = "S160-153-245-11\SQLEXPRESS";
// $username = "hworld";
// $password = "123987";
// $databaseName = "biconatrade";
// $connectionInfo = array( "UID"=>$username,                            
//                          "PWD"=>$password,                            
//                          "Database"=>$databaseName); 
// $conn = sqlsrv_connect( $serverName, $connectionInfo);
// if ($conn) {
// } else {
//     echo "Not Connected.<br />";
// }
$serverName = "ADMIN-PC";
$username = "";
$password = "";
$databaseName = "DusolnN";
$connectionInfo = array( "UID"=>$username,                            
                         "PWD"=>$password,                            
                         "Database"=>$databaseName); 
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if ($conn) {
    // print("nsdk");
} else {
    echo "Not Connected.<br />";
}
?>