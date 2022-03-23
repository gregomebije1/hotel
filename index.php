<?php
session_start(); 

/*Program: Hotel Management Software
Author: Greg Omebije
Last Updated: Aug 22, 2012
File: index.php
Description:
  The first page a user will see after loggin in
*/

error_reporting(E_ALL);
require_once 'library/util.inc';

$con = connect();

if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'logout')) {
 
  unset($_SESSION['uid']);
  session_destroy();
  login_form('');
  exit;
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'login')) {
  if ( (!(empty($_POST['u'])))  && (!(empty($_POST['p']))) ) {
    $sql = "SELECT * from user where username='{$_POST['u']}' and password=sha1('{$_POST['p']}')";
    $result = mysql_query($sql) or die(mysql_error());
    if (mysql_num_rows($result)) {
      $row = mysql_fetch_array($result);
      $_SESSION['uid'] = $row['id'];  #Store a session variable 
      header('Location: guest.php');
    } else {
    #include_once('login.html');
	 login_form("Wrong password");
	 exit;
    } 
  }
}

if (!isset($_SESSION['uid'])) {
  login_form('');
  exit;
} else {
  include_once('guest.php');
}

function login_form($message) { 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
 <title>Mangital Hotel Software</title>
 <script language='javascript' src='hotel.js'></script>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 <style>

*
{
  font-family: tahoma, sans-serif;
  line-height: 125%;
}

html,body
{
  /*background-color: #425073;*/
  background-color:white;
  color: black;
  font-size: 14px;
}

 .table-style {
   position:absolute;
   top:10em;
   left:25em;
   color:black;
   background-color: white;
   border-width:5px;
   text-align: left;
   padding: 0;
   width:40%;
   table-layout: auto;
   border: #d6e8ff 0.1em solid;
   border-collapse:collapse;
  }
  td {
   text-align:left;
   /*border: 1px solid #ebf3ff; */
   border: 1px solid white;
   padding: 0.1em 1em;
  }

  .class1 {
    border-bottom: #ffffff 0.1em solid;
    background-color:#ebf3ff;
    font-weight:bold;
  }
  .class2 {
    border-bottom: #ffffff 0.1em solid;
    background-color:#ebf3ff;
  }

</style>
</head>
<body>
 <form action='index.php' method='post'>
 <table border="0" class='table-style'>
  <tr class='class1'>
  <td colspan='4' style='text-align:center;font-size:2em;'>
     Mangital Hotel Software</td></tr>
<?
  if (!empty($message)) 
    echo "<tr><td style='text-align:center;' colspan='2'><b>$message</b></td></tr>";
  else 
    echo "<tr><td style='text-align:center;' colspan='2'><b>Log In</b></td></tr>";
?>
  <tr>
   <td>Username</td>
   <td><input id='u' name='u' autocomplete='off' type='text' 
     size='40'></td>
  </tr>
  <tr>
   <td>Password</td>
   <td><input id='p' name='p' autocomplete='off' type='password' 
     size='40'></td>
  </tr>
   <input type='hidden' name='action' value='login'>
  <tr>
   <td colspan='3' style='text-align:center;' colspan='2'>
    <input type='submit'   value='     Login      '></td>
  </tr>
 </table>
 </form>
</body>
</html>
<?
}
?>
