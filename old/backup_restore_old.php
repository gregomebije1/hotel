<?php 
error_reporting(E_ALL);
require_once "hotel.inc";
require_once "util.inc";

$con = connect();
/* You must be sure that there is a UID that is 1*/
main_menu(1, '', 1, '', $con);

if (isset($_REQUEST['action']) && (($_REQUEST['action'] == 'Backup') || ($_REQUEST['action'] == 'Restore'))) {
  if (empty($_REQUEST['file'])) {
    echo msg_box('Please enter the file to backup or restore from', 'backup_restore.php', 'Back To Backup/Restore');
	exit;
  }
  if ($_REQUEST['action'] == 'Restore') {
    if (!file_exists($_REQUEST['file'])) {
	  echo msg_box('No such file exists!', 'backup_restore.php', 'Backup To Backup/Restore');
	  exit;
	} 
    $lines = file($_REQUEST['file']);
	$i = 0;
	foreach ($lines  as $line) {
	  if ($i == 10) 
	    exit;
	 //mysql_query(htmlspecialchars($line), $con);
	 echo "$line <br>";
	 //mysql_query(htmlspecialchars($line, ENT_QUOTES), $con) or die ("Cannot execute sql " . mysql_error());
	 mysql_query($line, $con) or die ("Cannot execute sql " . mysql_error());
	 //echo htmlspecialchars($line) . "<br />Executed<br />";
	 $i++;
	}
	echo "<h3>Database has been restored from {$_REQUEST['file']}</h3>";
  } else if ($_REQUEST['action'] == 'Backup') {
    if (file_exists($_REQUEST['file'])) {
	  unlink($_REQUEST['file']);
	}
    $fp = fopen($_REQUEST['file'], "w");
    $sql = "";
    $result = mysql_list_tables("hotel", $con);
    for ($i = 0; $i < mysql_num_rows($result); $i++) {
	  $table_name = mysql_tablename($result, $i);
	  $result2 = mysql_query("select * from $table_name", $con);
	  if (!$result2) 
        die('Query failed: ' . mysql_error());
	  $sql ="truncate table $table_name;\n";
	  $x = mysql_num_fields($result2);
	  $num_rows = mysql_num_rows($result2);
	  if ($num_rows == 0) 
	    continue;
	  else 
	    $sql .="INSERT INTO $table_name(";
	  for($j = 0; $j < $x; $j++) {
	    if ($j == ($x - 1)) 
	      $sql .= mysql_field_name($result2, $j);
	    else
	      $sql .= mysql_field_name($result2, $j) . ", ";
	  }
	  $sql .= ") values ";
	  while($row = mysql_fetch_row($result2)) {
        $sql .= "(";
		for($k = 0; $k < $x; $k++) {
		  if ($k == ($x - 1))
		    if (mysql_field_type($result2, $k) == 'int') 
			  $sql .= htmlspecialchars($row[$k], ENT_QUOTES);
			else 
			  $sql .= "'" . htmlspecialchars($row[$k], ENT_QUOTES) . "'";
		  else {
			if (mysql_field_type($result2, $k) == 'int') 
			  $sql .= htmlspecialchars($row[$k], ENT_QUOTES) . ", ";
			else 
			  $sql .= " '" . htmlspecialchars($row[$k], ENT_QUOTES) . "', ";
		  }
		}
		$sql .= "), ";
	  }
	  $sql = substr_replace($sql, ";", -2); //Replace the last comma with a semi-colon
	  fwrite($fp, "$sql\n");
          echo "<br>$sql";
	}
	mysql_free_result($result);
	fclose($fp);
	echo "<h3>Database has been backedup to {$_REQUEST['file']}</h3>";
  }
  exit;
}
 $result2 = mysql_query("select * from sync_settings", $con);
 $row = mysql_fetch_array($result2);
?>
 <table>
   <tr class='class1'>
    <td colspan="4">
     <h3>Backup/Restore the databse</h3>
     <form action="backup_restore.php" method="post">
    </td>
   </tr>
   <tr>
    <td>Folder</td>
	<td><input type='text' name='file' value='backup.sql'></td>
   <tr>
    <td><input type="submit" name="action" value="Backup"></td>
	<td><input type="submit" name="action" value="Restore"></td>
   </tr>
   </form>
  </table>
<?
  main_footer();
?>
