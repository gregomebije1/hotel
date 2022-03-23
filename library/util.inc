<?php
include_once("config.inc");

$con = connect();

function check($value, $msg, $url, $back='Back') {
  if (empty($value)) {
    echo msg_box($msg, $url, $back);
    exit;
  }
}
function send_mail($school_id, $email, $subject, $msg, $con) {
  // multiple recipients
  $to  = "$email" . ', '; // note the comma
  //$to .= 'wez@example.com';

  $school_name = get_value('school', 'name', 'id', $school_id, $con);

  // message
  $message = " 
   <html>
    <body> $msg </body>
   </html>
  ";

  // To send HTML mail, the Content-type header must be set
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

  // Additional headers
  $headers .= "To: {$school_name}<{$email}>" . "\r\n";
  $headers .= 'From: Acadbase <contact@acadbase.com>' . "\r\n";
  $headers .= 'Bcc: all@acadbase.com' . "\r\n";

  // Mail it
  mail($to, $subject, $message, $headers);
}
function validate_email($email) {
  return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function update($table, $sql, $con) {
  //Since this is an upgrade, we need to check if this table exist
 //If it doesn't exist then create it automatically
 $result = mysql_query("show tables", $con) or die(mysql_error());
 while($row = mysql_fetch_array($result))
   if($row['Tables_in_school'] == $table)
	 return;
 mysql_query($sql, $con) or die(mysql_error());
}
 
function connect() {
  global $dbserver, $dbusername, $dbpassword, $database;
  
  $con = mysql_connect($dbserver, $dbusername, $dbpassword) 
     or die("Cannot connect to database server " . mysql_error());
    mysql_select_db($database, $con) 
     or die("Cannot select database" . mysql_error());
  return $con;
}

function my_redirect($url, $text) {
 echo " 
   <html>
   <body>
    <form name='x' action='$url' method='post'>
     $text
    </form>
    <script language='javascript'>
      document.x.submit();
    </script>
   </body>
  </html>
  ";
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
function get_value($table, $field1, $field2, $field3, $con) {
  $sql="select $field1 from $table where $field2='$field3'";
  //echo "$sql<br>";
  $result = mysql_query($sql, $con);
  $row = mysql_fetch_array($result);
  return $row[$field1];
}

function gen_insert_sql($table, $skip, $con) {
  $sql1 = "Insert into $table(";
  $sql2="describe $table";
  $result = mysql_query($sql2, $con) or die(mysql_error());
  while($field = mysql_fetch_array($result)) {
    if (($field[0] == 'id') || (in_array($field[0], $skip)))
          continue;
    $sql1 .= "{$field[0]}, ";
  }
  $sql1 = substr($sql1, 0, -2);
  $sql1 .= ") values(";

  $result = mysql_query($sql2) or die(mysql_error());
  while($field = mysql_fetch_array($result))  {
    if (($field[0] == 'id') || (in_array($field[0], $skip)))
          continue;
    else if (eregi("_image", $field[0]))
      $sql1 .= "'{$_FILES[$field[0]]['name']}', ";
    else $sql1 .= "'{$_REQUEST[$field[0]]}', ";
  }
  $sql1 = substr($sql1, 0, -2);
  $sql1 .= ")";
  return $sql1;
}
function gen_update_sql($table, $id, $skip, $con) {
  $sql="update $table set ";
  $sql1 = "show columns from $table";
  $result1 = mysql_query($sql1) or die(mysq_error());
  while($field = mysql_fetch_array($result1)) {
    if (($field[0] == 'id') || (in_array($field[0], $skip)))
      continue;
    else if (eregi("_image", $field[0])) {
	  if(!empty($_FILES[$field[0]]['name'])) 
        $sql .= "$field[0] = '{$_FILES[$field[0]]['name']}', ";
    } else
      $sql .= "$field[0]='{$_REQUEST[$field[0]]}', ";
  }

  $sql = substr($sql, 0, -2);
  $sql .= " where id=$id";
  return $sql;
}
function gen_delete_sql($table, $id, $con) {
  return "delete from $table where id=$id";
}
function parse_enum($field) {
  $label = ereg_replace('enum', '', $field);
  $label = str_replace('(', '', $label);
  $label = str_replace(')', '', $label);
  $label = str_replace("'", '', $label);
  $arr = explode(",", $label);
  foreach($arr as $index => $value)
    $arr2[$value] = $value;
  return $arr2;
}

function format_label($label) { 
  $label = ereg_replace('_image', ' ', $label);
  $label = ereg_replace('_id', ' ', $label);
  $label = ereg_replace('_', ' ', $label);
  return ucwords(strtolower($label)); 
}


function upload_file($filename, $url) {
  if ($_FILES[$filename]['error'] != 4) {  
  //Lets upload the file
    if ($_FILES[$filename]['error'] > 0) {
      switch($_FILES[$filename]['error']) {
        case 1: echo msg_box('File exceeded upload max_filesize', 
          $url, 'OK'); break;
        case 2: echo msg_box('File exceeded max_file_size', 
          $url, 'OK'); break;
        case 3: echo msg_box('File only partially uploaded', 
          $url, 'OK'); break;
      }
      exit;
    } elseif ($_FILES[$filename]['type']
      != ('image/jpeg' || 'image/gif' || 'image/png')) {
      echo msg_box('Prolem: file is not an image', $url, 'OK');
      exit;
    } else {
      $upfile = "upload/". $_FILES[$filename]['name'];
      if(is_uploaded_file($_FILES[$filename]['tmp_name'])) {
        if(!move_uploaded_file($_FILES[$filename]['tmp_name'], $upfile)) {
          echo msg_box('Problem: Could not move file to destination directory', $url, 'OK');
          exit;
        }
      } else {
        echo msg_box("Problem: Possible file upload attack. Filename: " .
          $_FILES[$filename]['name'], $url, 'OK');
        exit;
      }
    }
  }
}

function calc_age($curr_secs, $prev_secs) {
$secs = $curr_secs - $prev_secs;
  
  /*
  $years = floor($secs / 31449600);
  $secs = $secs % 31449600;

  //1 year = 52 weeks
  $weeks = floor($secs / 604800);
  $secs = $secs % 604800;
  */
 
  $days = floor($secs / 86400);
  $secs = $secs % 86400;

  /*
  $hours = floor($secs / 3600);
  $secs = $secs % 3600;

  $mins = floor($secs / 60);
  $secs = $secs % 60;
  */

  $temp = 0;
  /*
  if ($years > 0) {
    if ($years == 1) {
      $temp = "$years year ";
    } else {
      $temp = "$years years ";
    }
  } 
  else if ($weeks > 0) {
    if ($weeks == 1) {
	$temp = "$weeks week ";
    } else {
      $temp = "$weeks weeks ";
    }
  } else 
  */
  if ($days > 0) {
    if ($days == 1) {
      $temp = "$days day ";
    } else {
      $temp = "$days days ";
    }
  } 
  elseif ($hours > 0) {
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

function make_date($y, $m, $d) {
  if ($d < 10) {
    $d = "0$d";
  }
  if ($m < 10) {
    $m = "0$m";
  }
  return "$y-$m-$d";
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
function user_type($uid, $users, $con) {
  
  foreach($users as $perm) {
    $sql="select * from permissions p left join 
    (user u, user_permissions up) on (u.id = up.uid and up.pid = p.id) 
    where u.id =$uid and p.name='$perm'";
	
    $result = mysql_query($sql, $con) or die ("Cannot execute SQL query" . mysql_error());
	if (mysql_num_rows($result) > 0)
	  return true;
  }
  return false;
}

?>
