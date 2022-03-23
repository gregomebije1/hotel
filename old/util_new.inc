<?php
include_once('config.inc');

function format_label($label) { 
  $label = ereg_replace('_image', ' ', $label);
  $label = ereg_replace('_id', ' ', $label);
  $label = ereg_replace('_', ' ', $label);
  return ucwords(strtolower($label)); 
}

function get_value($table, $field1, $field2, $field3, $con) {
  $sql="select $field1 from $table where $field2=$field3";
  //echo "$sql<br>";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[$field1];
}


/*All characters which have HTML character entity equivalents are translated into these entities
Input: A 'quote' is <b>bold</b>
Output: A &#039;quote&#039; is &lt;b&gt;bold&lt;/b&gt;
*/
function my_htmlentities() {
 foreach ($HTTP_POST_VARS as $key => $value) {
      $_REQUEST[$key] = htmlentities($value, ENT_QUOTES);
 }
}
 
function add_user ($u, $p,  $p2, $pid, $f, $l, $e, $con) {
  if (($u == '')  || ($p == '') || ($p2 == '') || ($pid == '') 
      || ($f == '') || ($l == '') ||
      (strlen($u) < 2) || (strlen($u) > 30) ||
      (strlen($p) < 2) || (strlen($p) > 30) ||
      ($p != $p2)) { 
   return " 
          There is an an error in the data you typed in your form
          Please note that username, password, firstname and lastname 
          <br />
         can only contain letters, 
           digits, dashes and underscores, and should be between 
          2 and 15 characters long. 
         <br />
          Also check if your passwords are the same
         And resubmit the form
       ";
 } else {
   $sql="SELECT * FROM user WHERE name ='$u'";
   $result = mysql_query($sql, $con);
   if (mysql_num_rows($result) > 0) {
        return "This username has already being taken. Please choose another";
   } 
   else {
    $sql = "INSERT INTO user (name, passwd, 
      entity_id, firstname, lastname) 
      VALUES ('$u', sha1('$p'), '$e', '$f', '$l')";
	
	mysql_query($sql, $con)
      or die("SQL Error util.inc@add_user:1 " . mysql_error());
	  
    $sql2="INSERT INTO user_permissions (uid, pid) 
          VALUES ('" . mysql_insert_id($con) . "', '$pid')";
	mysql_query($sql2, $con)
       or die("SQL Error util.inc@add_user:2 ".mysql_query());;
       return 1;
     }
  }
}
function get_uid($name, $con) {
  $row = mysql_fetch_array(mysql_query("SELECT id FROM user 
    WHERE name = '$name'", $con));
  return $row['id'];
}
function get_uname($id, $con) {
  $result = 
  mysql_query("SELECT name FROM user
     WHERE id =$id", $con);
  $row = mysql_fetch_array($result);
  return $row['name'];
}

function check_date ($d, $m, $y) {
  if ( (($m == 9) || ($m == 4) || ($m == 6) || ($m == 11)) && ($d <= 30)) {
   return 1;
  } elseif ( (($m == 1) || ($m == 3) || ($m == 5) || ($m == 7)
       || ($m == 8) || ($m == 9) || ($m == 10) || ($m == 12)) && ($d <= 31)) {
    return 1;
  } elseif (($m == 2) && is_leap_year($y) && ($d <= 29)) {
    return 1;
  } elseif (($m == 2) && (!is_leap_year($y)) && ($d <= 28)) {
    return 1;
  } else {
    return 0;
  }
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

function audit_trail($uid, $desc, $shift, $others, $con) {
  #use current_date instead of curr_date_time to allow searching by sd and ed
  $result = mysql_query("insert into audit_trail
   (dt, staff_id, descr, shift, ot, dt2) values(NOW(), '$uid', '$desc', '$shift', 
   '$others', CURDATE())", $con) or die("Cannot execute SQL audit_trail " . mysql_error());
}


function user_type($uid, $perm, $con) {
  $result = mysql_query("select * from permissions p left join 
    (user u, user_permissions up) on (u.id = up.uid and up.pid = p.id) 
    where u.id = '$uid' and p.name='$perm'", $con) or 
  die ("Cannot execute SQL query" . mysql_error());
  return mysql_num_rows($result) > 0;
}
function get_user($uid, $con) {
  $result = mysql_query("select firstname, lastname from user where id=$uid", $con);
  $row = mysql_fetch_array($result);
  return $row['firstname'].' '.$row['lastname'];
}
function connect() {
  global $dbserver, $dbusername, $dbpassword, $database;
  
  //echo "$dbserver $dbusername  $dbpassword $database<br>";
  $con = mysql_connect($dbserver, $dbusername, $dbpassword) 
     or die("Cannot connect to database server " . mysql_error());
    mysql_select_db($database, $con) 
     or die("Cannot select database" . mysql_error());
  return $con;
}
function gen_date($name) { 
?><td>
          <select name='<?=$name?>day' id='<?=$name?>day'>
<?
  for ($i=1; $i<=31; $i++) {
    echo("<option value='$i'>$i</option>");
  }
?>
          </select>
          <select name='<?=$name?>month' id='<?=$name?>month'>
           <option value='1'>January</option>
           <option value='2'>February</option>
           <option value='3'>March</option>
           <option value='4'>April</option>
           <option value='5'>May</option>
           <option value='6'>June</option>
           <option value='7'>July</option>
           <option value='8'>August</option>
           <option value='9'>September</option>
           <option value='10'>October</option>
           <option value='11'>November</option>
           <option value='12'>December</option>
          </select>
          <select name='<?=$name?>year' id='<?=$name?>year'>
		   <option>2011</option>
           <option>2012</option>
	       <option>2013</option>
          </select>
         </td>
<?
}
?>
