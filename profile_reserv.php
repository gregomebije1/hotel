<?php


session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "hotel.inc";
require_once "util.inc";

$con = connect();
header('Content-type: text/xml');

$result = mysql_query("select title, firstname, lastname, phone, sex, address
  from guest_checked_out where id=".$_REQUEST['id'], $con);
	
echo "<guest>";
if (mysql_num_rows($result) == 0) {
  echo "<found>False</found>";
} else {
  echo "<found>True</found>";
  while($row = mysql_fetch_array($result)) {
    echo "<title>";
    echo empty($row['title']) ? "." : $row['title'];
    echo "</title>";

    echo "<firstname>";
    echo empty($row['firstname']) ? "." : $row['firstname'];
    echo "</firstname>";

    echo "<lastname>";
    echo empty($row['lastname']) ? "." : $row['lastname'];
    echo "</lastname>";

    echo "<phone>";
    echo empty($row['phone']) ? "." : $row['phone'];
    echo "</phone>"; 

    echo "<sex>";
    echo empty($row['sex']) ? "Male" : $row['sex'];
    echo "</sex>"; 

    echo "<address>";
    echo empty($row['address']) ? "." : $row['address'];
    echo "</address>"; 

  }
}
echo "</guest>";

function say($a) {
  if(empty($a)) {
    return "nil";
  } else {
    return $a;
  }
}
