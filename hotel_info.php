<?
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);
require_once "hotel.inc";
$con = connect();

require_once "library/main_menu.inc";

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Update')) {
  check($_REQUEST['name'], 'Please enter Hotel Name', 'hotel_info.php');
  $sql = gen_update_sql('hotel_info', $_REQUEST['id'], array(),$con);
  mysql_query($sql, $con) or die(mysql_error());
}

$result = mysql_query("SELECT * FROM hotel_info where id=1", $con);
$row = mysql_fetch_array($result);
$skip = array("id");
generate_form('Edit Only','hotel_info.php',1,'hotel_info', $row, $skip, "", "", $con);   
main_footer();
?>
