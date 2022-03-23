<?php


session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "hotel.inc";
require_once "library/util.inc";

$con = connect();
header('Content-type: text/plain');

$result = mysql_query("select c.rate from room r join room_category c on 
 r.room_category_id = c.id where r.id=" . $_REQUEST['rn'] . " and r.occupancy = 'Vacant'
 and r.occupancy != 'Confirmed Booking'", $con);

$row = mysql_fetch_array($result);

echo $row['rate'];
?>  
