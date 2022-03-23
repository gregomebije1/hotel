<?php
session_start(); 
error_reporting(E_ALL);

require_once 'util.inc';
require_once 'ui.inc';

$con = connect();
if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'logout')) {
  unset($_SESSION['uid']);
  unset($_SESSION['firstname']);
  unset($_SESSION['lastname']);
  unset($_SESSION['session_id']);
  unset($_SESSION['term_id']);
  $result = mysql_query("Select * from settings");
  while ($row = mysql_fetch_array($result)) { 
	    unset($_SESSION[$row['name']]);
  }
  session_destroy();
  login_form('');
  exit;
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'login')) {
  if (empty($_REQUEST['u']) || empty($_REQUEST['p'])) {
	login_form("Please enter username/password");
    exit;
  }
  if (empty($_REQUEST['session_id'])) {
    login_form("Please choose a session");
	exit;
  }
  if (empty($_REQUEST['term_id'])) {
    login_form("Please choose a term");
	exit;
  }
   if ( (!(empty($_REQUEST['u'])))  && (!(empty($_REQUEST['p']))) ) {
    $q = "SELECT * from user where name='{$_REQUEST['u']}' and passwd=sha1('{$_REQUEST['p']}')";
    $result = mysql_query($q) or die("Cannot execute SQL Query");
    if (mysql_num_rows($result)) {
      $row = mysql_fetch_array($result);
      $_SESSION['uid'] = $row['id'];  #Store a session variable 
      $_SESSION['firstname'] = $row['firstname'];   
      $_SESSION['lastname'] = $row['lastname'];   
      $_SESSION['session_id'] = $_REQUEST['session_id'];
      $_SESSION['term_id'] = $_REQUEST['term_id'];

      /* Lets add all the settings value */
      $result = mysql_query("Select * from settings");
      while ($row = mysql_fetch_array($result)) { 
        $_SESSION[$row['name']] = $row['value'];
      }
      //header('Location: student.php');
      welcome_screen('location', $con);
	  
    } else {
	 login_form("Wrong password");
	 exit;
    } 
  }
}

if (!isset($_SESSION['uid'])) {
  login_form('Please enter correct login details');
  exit;
} else {
  //include_once('student.php');
  welcome_screen('include', $con);
}

function login_form($message) { 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
 <title>AcadPro</title>
 <script type='text/javascript' src='school.js'></script>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 <style>
  * {
   font-family: tahoma, sans-serif;
   line-height: 125%;
  }
  html,body {
   /*background-color: #425073;  */
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
 <form name='form1' action='index.php' method='post'>
 <table border="0" class='table-style'>
  <tr class='class1'>
   <td colspan='2' style='text-align:center;font-size:2em;'>GREATER INTERNATIONAL COLLEGE</td></tr>
  <tr class='class1'><td colspan='2' style='text-align:center; font-size:0.7em; font-weight:normal;'>AcadPro 2011</td></tr>
	 
  <?php
  if (!empty($message)) 
    echo "<tr><td style='text-align:center;' colspan='2'><b>$message</b></td></tr>";
  else 
    echo "<tr><td style='text-align:center;' colspan='2'><b>Log in</b></td></tr>";
  ?>
  <tr>
   <td>Username</td>
   <td><input id='u' name='u' autocomplete='off' type='text' size='40'></td>
  </tr>
  <tr>
   <td>Password</td>
   <td><input id='p' name='p' autocomplete='off' type='password' size='40'></td>
  </tr>
  <tr>
   <td>Session</td>
   <td>
    <select name="session_id" onchange="get_terms('session_id', 'terms');">
	 <option></option>
     <?php
     $sql="select * from session";
     $result = mysql_query($sql);
     while ($row = mysql_fetch_array($result)) {
       echo "<option value='{$row['id']}'>{$row['name']}</option>";
     }
     ?>
    </select>
   </td>
  </tr>
  <tr>
   <td>Term</td>
   <td><div id='terms'></div></td>
  </tr>
  <input type='hidden' name='action' value='login'>
  <tr>
   <td style='text-align:center;' colspan='2'><input type='submit'   value='     Login      '></td>
  </tr>
 </table>
 </form>
</body>
</html>
<?php
}
?>
