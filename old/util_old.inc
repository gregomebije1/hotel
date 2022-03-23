<?php

include_once('config.inc');

function run_backup_restore($host, $url) {
  //Setup a basic HTTP Connection to run Restore on the foreign server
  $fp = fsockopen($host, 80, $errno, $errstr, 30);
  if (!$fp) {
    echo "$errstr ($errno)<br />\n";
  } else {	
    echo "Connected to HTTP Connection<br>";
    echo "Running backup program on $host: $url<br>";
	
	$out = "GET  $url HTTP/1.1\r\n";
    $out .= "Host: $host\r\n";
    $out .= "Connection: Close\r\n\r\n";
	
    fwrite($fp, $out);
	 while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
  }
}

function upload_download($command, $conn_id) {
  if ($command == 'download') {
  
    if (file_exists('backup')) 
	  unlink('backup');
	  
    //f (ftp_get($conn_id, "backup", "backup", FTP_BINARY)) {
	if (ftp_get($conn_id, "backup", "backup", FTP_ASCII)) {
      echo "Successfully downloaded backup<br>";
    } else {
      echo "There was a problem: Please re-run schrnoziation again<br>
	   Or Contact Technical Support";
    }
  } 
  if ($command == 'upload') {
    // try to delete $file
    if (ftp_delete($conn_id, 'backup')) {
     echo "backup deleted successful<br>";
    } else {
     echo "could not delete backup <br>";
    }
	
	$fp = fopen('backup', 'r');
 
    // upload the file
    $upload = ftp_fput($conn_id, 'backup', $fp, FTP_BINARY); 

    // check upload status
    if (!$upload) { 
     echo "FTP upload has failed!<br>";
    } else {
     echo "Uploaded backup file <br>";
    }
  }
}

function closeSale()
	{
		//deletes sessions vars 
		
		unset($_SESSION['current_sale_customer_id']);
		unset($_SESSION['items_in_sale']);
		unset($_SESSION['current_item_search']);
		unset($_SESSION['current_customer_search']);
	}

function isValidData($data_to_check)
	{
		//checks data for errors
	
		for($k=0;$k<count($data_to_check);$k++)
		{
			if(ereg('\"',$data_to_check[$k]) or ereg('<',$data_to_check[$k]) or ereg('>',$data_to_check[$k]) )
			{
				return false;
			}
		}
		
		return true;
	
	}

	function getLastSaleID()
	{
		//gets the last sale ID entered
		
		$table='sales';
	$result=mysql_query("SELECT id FROM $table ORDER BY id desc");
		
		$row=mysql_fetch_assoc($result);
		
		return $row['id'];
	}
	


function update($field_names,$field_data,$tablename,$id,$output)
	{
		//pre: $field_names and $field_data are pararell arrays and tablename and id are strings.
		//post: creates a query then executes it limites based on id.
		
		if($id=='')
		{
			echo 'You did not enter an ID';
			exit();
		}
		if(!(isValidData($field_data)))
		{
			echo 'You have entered an invalid character in one or more of the fields,
			please hit back and try again';
			exit();
		}
			$query="UPDATE $tablename SET $field_names[0]=\"$field_data[0]\"";
			
		for($k=1;$k< count($field_names);$k++)
		{
			$query.=', '."$field_names[$k]=\"$field_data[$k]\"";
		
		}
			if($output)
			{
				$query.=" WHERE id=\"$id\"";
			}
			else
			{
				$query.=" WHERE sale_id=\"$id\"";
			}
			mysql_query($query);
	
	
		if($output)
		{
			echo "<center><b>You have succesfully updated this in table $tablename</b></center><br>";
				
				echo "<center><table width=350 cellspacing=$this->cellspacing cellpadding=$this->cellpadding bgcolor=$this->table_bgcolor style=\"border: $this->border_style $this->border_color $this->border_width px\">
				<tr bgcolor=$this->header_rowcolor>
				<th align='left'><font color='$this->header_text_color' face='$this->headerfont_face' size='$this->headerfont_size'>Field</th></font>
				<th align='left'><font color='$this->header_text_color' face='$this->headerfont_face' size='$this->headerfont_size'>Data</th></font>
				</tr>";
				for($k=0;$k<count($field_names);$k++)
				{
					//certain fields I do not want displayed.
					if($field_names[$k]!="password")
					{
						echo "<tr bgcolor=$this->rowcolor><td width='120'><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>$field_names[$k]". '</font></td>'."<td><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>$field_data[$k]</font></td></tr>\n";
					}
					else
					{
						echo "<tr bgcolor=$this->rowcolor><td width='120'><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>$field_names[$k]". '</font></td>'."<td><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>*******</font></td></tr>\n";
					
					}
				}
				echo '</table></center>';
	
		}
	}	
	

	function insert($field_names,$field_data,$tablename,$output)
	{
		//pre: $field_names and $field_data are pararell arrays and $tablename is a string.
		//post: creates a query then executes it.
		
		if(!(isValidData($field_data)))
		{
			echo 'You have entered an invalid character in one or more of the fields,
			please hit back and try again';
			exit();
		}
		
		$query = "INSERT INTO $tablename ($field_names[0]";
			
		for($k=1;$k< count($field_names);$k++)
		{
			$query.=', '."$field_names[$k]";
		
		}
		
		$query.=") VALUES (\"$field_data[0]\"";
		
		for($k=1;$k< count($field_data);$k++)
		{
			$query.=', '."\"$field_data[$k]\"";
		
		}
			$query.=')';
			//echo $query . "<br>";
			mysql_query($query);
			
			
			
			if($output)
			{
				echo "<center><b>You have succesfully added this in table $tablename</b></center><br>";
				
				echo "<center><table width=350 cellspacing=$this->cellspacing cellpadding=$this->cellpadding bgcolor=$this->table_bgcolor style=\"border: $this->border_style $this->border_color $this->border_width px\">
				<tr bgcolor=$this->header_rowcolor>
				<th align='left'><font color='$this->header_text_color' face='$this->headerfont_face' size='$this->headerfont_size'>Field</th></font>
				<th align='left'><font color='$this->header_text_color' face='$this->headerfont_face' size='$this->headerfont_size'>Data</th></font>
				</tr>";
				for($k=0;$k<count($field_names);$k++)
				{
					//certain fields I do not want displayed.
					if($field_names[$k]!="password")
					{
						echo "<tr bgcolor=$this->rowcolor><td width='120'><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>$field_names[$k]". '</font></td>'."<td><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>$field_data[$k]</font></td></tr>\n";
					}
					else
					{
						echo "<tr bgcolor=$this->rowcolor><td width='120'><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>$field_names[$k]". '</font></td>'."<td><font color='$this->rowcolor_text' face='$this->rowfont_face' size='$this->rowfont_size'>*******</font></td></tr>\n";
					
					}
				}
				echo '</table></center>';
	
			}
		}
	


function go2Back() {
	echo "<script>  history.back();   </script>";
} 
function go2BackWithError( $errmsg ) {
	echo "<script> alert(\"$errmsg\");  history.back();   </script>";
} 
function go2Page( $page ) {
	echo "<script> location.href =\"$page\";   </script>";
} 
function is_leap_year($y) {
  if (($y % 400) == 0) {
    return 1;
  } elseif (($y % 100) == 0) {
    return 0;
  } elseif (($y % 4) == 0) {
    return 1;
  } else {
    return 0;
  }
}
function audit_trail($uid, $desc, $others, $con) {
  #use current_date instead of curr_date_time to allow searching by sd and ed
  $result = mysql_query("insert into audit_trail
   (dt, staff_id, descr, ot, dt2) values(NOW(), '$uid', '$desc', '$others', CURDATE())", $con) 
   or die("Cannot execute SQL audit_trail " . mysql_error());
}

function user_type($uid, $perm, $con) {
  $result = mysql_query("select * from user 
    where id = '$uid' and type='$perm'", $con) or 
  die ("Cannot execute SQL query" . mysql_error());
  return mysql_num_rows($result) > 0;
}

function connect() {
  global $dbserver, $dbusername, $dbpassword, $database;
  
  $con = mysql_connect($dbserver, $dbusername, $dbpassword) 
     or die("Cannot connect to database server " . mysql_error());
    mysql_select_db($database, $con) 
     or die("Cannot select database" . mysql_error());
  return $con;
}

function calc_age($curr_secs, $prev_secs) {
  $secs = $curr_secs - $prev_secs;
  
  $years = floor($secs / 31449600);
  $secs = $secs % 31449600;
  
  //1 year = 52 weeks
  $weeks = floor($secs / 604800);
  $secs = $secs % 604800;
  
  $days = floor($secs / 86400);
  $secs = $secs % 86400;

  $hours = floor($secs / 3600);
  $secs = $secs % 3600;

  $mins = floor($secs / 60);
  $secs = $secs % 60;
  
  if ($years > 0) {
    if ($years == 1) {
      $temp = "$years year ";
    } else {
      $temp = "$years years ";
    }
  } else if ($weeks > 0) {
    if ($weeks == 1) {
      $temp = "$weeks week ";
    } else {
      $temp = "$weeks weeks ";
    }
  } else if ($days > 0) {
    if ($days == 1) {
      $temp = "$days day ";
    } else {
      $temp = "$days days ";
    }
  } elseif ($hours > 0) {
    if ($hours == 1) {
      $temp = "$hours hour ";
    } else {
        $temp = "$hours hours ";
    }
  } elseif ($mins > 0) {
    if ($mins == 1) {
      $temp = "$mins min ";
    } else {
      $temp = "$mins mins ";
    }
  } else {
    if (($secs == 1) || ($secs == 0)) {
      $temp = "$secs second ";
    } else {
      $temp = "$secs seconds ";
    }
  }
  return $temp;
}
 

function get_value($table, $field1, $field2, $field3, $con) {
  $sql="select $field1 from $table where $field2='$field3'";
  //echo "$sql<br>";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[$field1];
}
function my_query() {
  $temp = array();
  $arg_list = func_get_args();
  $result = mysql_query($arg_list[0]) or die(mysql_error());
  while($row = mysql_fetch_array($result)) {
    if (func_num_args() > 3) {
      $temp[$row[$arg_list[1]]] = 
       $row[$arg_list[2]] . " " . $row[$arg_list[3]];
    } else {
      $temp[$row[$arg_list[1]]] = $row[$arg_list[2]];
    }
  }
  return $temp;
}

function get_values($table, $field1, $field2, $field3) {
  $a = array();
  $sql="select $field1 from $table where $field2=$field3";
  $result = mysql_query($sql);
  while($row = mysql_fetch_array($result)) { 
    $a[$field2] = $row[$field1];
  }
  return $a;
}
?>
