<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "smartspot";

// $servername = "153.92.15.11";
// $servername = "srv1319.hstgr.io";
// $username = "u136659995_wilfred27";
// $password = "Wilfred.10121912816";
// $database = "u136659995_smartpot";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    // echo "No Database Connection";
}
//  else {
//     echo "Connected successfully";
// }


// $conn->close();

?>

