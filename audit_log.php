<?
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "hotel.inc";
require_once "ui.inc";
require_once "util.inc";
require_once "acc.inc";

$con = connect();

/*
if (!(user_type($_SESSION['uid'], 'Report Viewer', $con)
 || user_type($_SESSION['uid'], 'Accountant', $con)
 || user_type($_SESSION['uid'], 'Admin', $con)
  )){
  main_menu($_SESSION['uid'],
    $_SESSION['first_name'] . " " . $_SESSION['last_name'], $con);
  echo msg_box('Access Denied!', 'index.php?action=logout', 'Continue');
  exit;
}
*/
if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Audit Log', 'audit_log.php',  
      'Back', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], '1', '', $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Generate Log')) {
    if (empty($_REQUEST['sd']) || empty($_REQUEST['ed'])) {
       echo msg_box_hotel('Please enter correct starting and end date', 
        'audit_log.php', 'Back');
       exit;
    }
   ?>
   <table>
   <tr class='class1'>
   <td colspan='8'><h3>Audit Log</h3></td>
   </tr>
   <tr>
   <tr>
    <td colspan='6'>
     <table>
      <tr>
       <td><b>Start Date</b></td>
   <?
   if (empty($_REQUEST['sd'])) {
    echo "<td> All Transactions</td>";
   } else {
    echo "<td>{$_REQUEST['sd']}</td>";
   }
   echo "<td><b>End Date</b></td>";
   if (empty($_REQUEST['ed'])) {
     echo "<td> All Transactions</td>";
   } else {
     echo "<td>{$_REQUEST['ed']}</td>";
   }
   ?>
           </tr>
          </table>
         </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr style="background-color:silver;">
         <td><b>Date</b></td>
         <td><b>Staff</b></td>
         <td><b>Description</b></td>
        </tr>
  <?  $dsql;
  if ((!empty($_REQUEST['sd']))  && (!empty($_REQUEST['ed']))) {
    $dsql = " dt2 between '{$_REQUEST['sd']}' and '{$_REQUEST['ed']}'";
  } elseif ((!empty($_REQUEST['sd'])) && (empty($_REQUEST['ed']))){
    $dsql = " and dt2 = '{$_REQUEST['sd']}'";
  } elseif ((!empty($_REQUEST['ed'])) && (empty($_REQUEST['sd']))) {
    $dsql = " and dt2 = '{$_REQUEST['ed']}'";
  } else {
    $dsql = "";
  }
  $sql2 = "SELECT * from audit_trail where ot not like '%account%' and $dsql ";
  $result = mysql_query($sql2, $con);
  while($row = mysql_fetch_array($result)) {
    $staff = get_value('user', 'firstname', 'id', $row['staff_id'], $con);
    $staff = $staff . " " . 
      get_value('user', 'lastname', 'id', $row['staff_id'], $con);

    ?>
    <tr>
     <td><?=$row['dt']?></td>
     <td><?php echo $staff; ?></td>
     <td>
     <?php echo $row['descr'];
      echo "<a href='audit_log.php?action=RollBack&id={$row['id']}'>
       RollBack</a></td></tr>";
   }
   echo ("</table>");
   exit;
  }
  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'RollBack')) {
    if (empty($_REQUEST['id'])) {
      echo msg_box('Please specify the transaction ID', 'audit_log.php', 
       'Back');
    }
    $sql="select * from audit_trail where id={$_REQUEST['id']}";
    $result = mysql_query($sql) or die(mysql_error());  
    $row = mysql_fetch_array($result);
    $data = explode("|", $row['ot']);
    if (isset($data)) {
      foreach($data as $value) {
        $data2 = explode(" ", $value);
        if ((count($data2) > 1) && (!empty($data2[0]))) {
        
          $sql="delete from {$data2[0]} where ";
          unset($data2[0]);
          foreach($data2 as $value2) {
          //for($i=1; $i<=count(data2); $i++) {
          //$sql .= " id={$data2[$i]} and ";
            $sql .= " id=$value2 or ";
          }  
          $sql = substr($sql, 0, -4);
          mysql_query($sql) or die(mysql_error());
          //echo "$sql<br>";
        }
      }
    } 
    //Now delete entry in audit_trail
    $sql="delete from audit_trail where id={$_REQUEST['id']}";
    mysql_query($sql) or die(mysql_error());
    //echo "$sql<br>";
    echo msg_box('Transaction has been rolled back', 'audit_log.php','Back');
    exit;
  }
}
?>
  <table>
      <tr class='class1'>
       <td colspan="4">
        <h3>Audit Log</h3>
        <form action="audit_log.php" method="post">
       </td>
      </tr>
      <tr>
       <td>Start Date</td>
       <td><input type='text' 
         name='sd' value='<?php echo date('Y-m-d');?>' size='10' 
         maxlength='10'></td>
      </tr>
      <tr>
       <td>End Date</td>
       <td><input type='text'
        name='ed' value='<?php echo date('Y-m-d');?>' size='10'
        maxlength='10'></td> 
      </tr>
      <tr><td><input name="action" type="submit" value="Generate Log">
      </td></tr>
     </table>
  <? 
  main_footer();
?>
