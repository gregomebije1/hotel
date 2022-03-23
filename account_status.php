<?
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);
require_once "hotel.inc";
require_once "util.inc"; 

$con = connect();
if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) { 
    print_header('Account Statement', 'account_status.php?action=Form', 'Back To Main Menu', $con);
} else {
  main_menu($_SESSION['uid'], 
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
      $_SESSION['entity_id'], $_SESSION['shift'], $con);
}
if (isset($_REQUEST['action']) && (
  ($_REQUEST['action'] == 'Account Details') || 
  ($_REQUEST['action'] == 'Print'))) {
  if (!isset($_REQUEST['choice'])) {
    echo msg_box('Please make a choice',
      'account_status.php?action=Form', 'Back');
       exit;
  }
  ?>
  <table>
   <tr class='class1'>
    <td colspan="4">
     <table>
     <?
     if ($_REQUEST['action'] != 'Print') {
     ?>
      <tr class='class1'>
       <td>
        <form action="account_status.php" method='POST'>
        <input type="submit" name="action" value="Print">
        <input type="hidden" name="choice" value="<?=$_REQUEST['choice']?>">
        <input type="hidden" name="rid" value="<?=$_REQUEST['rid']?>">
        <input type="hidden" name="group_acc_id" 
         value="<?php if (isset($_REQUEST['group_acc_id']))
         echo $_REQUEST['group_acc_id'];?>">
        </form>
       </td>
      </tr>
      <? } ?>
     </table>
    </td>
   </tr>
   <?
   if ($_REQUEST['choice'] == 'Room') {
    $result = mysql_query("SELECT number, acc_id, occupancy from room where 
      id=". $_REQUEST['rid'], $con);
    $room = mysql_fetch_array($result);
    $result = mysql_query("select g.title, g.firstname, g.lastname, 
      g.address, g.arrival_date, g.departure_date, r.number, rc.rate 
      from guest g left join (room r, room_category rc) on 
      (g.room_id = r.id and r.category_id = rc.id) where r.id=" 
      . $_REQUEST['rid'], $con);
    $g = mysql_fetch_array($result);
    ?>
   <tr>
    <td>
     <table>
      <tr>
       <td><h3>Account Status For <?=$g['title']?>
          <?=$g['firstname']?> <?=$g['lastname']?>
       <?
       if ($room['occupancy'] == 'Booked') {
        echo('<span style="color:red">(Reservation)</span>');
       }
       ?>
       </h3>
       </td>
      </tr>
      <tr><td>&nbsp;</td></tr>
      <tr>
      <td>
       <table>
        <tr>
         <td><b>Name:</b></td>
	 <td>
	  <?=$g['title']?> 
        <?=$g['firstname']?> 
        <?=$g['lastname']?>
       </td>
       <td><b>Rate</b></td><td><?php echo number_format($g['rate'], 2);?></td>
      </tr>
      <tr>
       <td><b>Check In:</b></td><td><?=$g['arrival_date']?></td>
      </tr>
      <tr>
       <td><b>Room:</b></td>
       <td><? echo get_room($_REQUEST['rid'], $con); ?></td>
       <td><b>Expected Depature:</b></td><td><?=$g['departure_date']?></td>
      </tr>
     </table>
    </td>
    </tr>
     </table>
    </td>
   </tr>
   <tr>
    <td>
    <?
    //print_account_status(
    //get_unique_guest_name($_REQUEST['rid'], $con), '', '', $con);  
    print_account_status($_REQUEST['choice'], $room['acc_id'], 
     $room['number'], '', '', $con);
    ?>
    </td>
   </tr>
   <?
     exit;
   } 
   ####Process Group Account Status####
   elseif ($_REQUEST['choice'] == 'GroupCompany') { 
     $sql = "select gc.name, r.acc_id, gc.address, g.arrival_date, 
      g.departure_date, r.occupancy  from room r left join 
      (guest g, group_company_guests gc) 
      on (g.room_id = r.id and g.grp_cmp_id = gc.id) where 
      gc.acc_id = '" . $_REQUEST['group_acc_id'] . "' group by r.acc_id";
     $result = mysql_query($sql, $con);
     $gcid = mysql_fetch_array($result);

     $sql="select number from room where acc_id={$_REQUEST['group_acc_id']}";
     $result = mysql_query($sql, $con);
     $r = mysql_fetch_array($result);
   ?>
   <tr>
    <td>
     <table>
      <tr>
       <td><h3>Account Status For 
       <? 
       echo $gcid['name'];
       if ($gcid['occupancy'] == 'Booked') {
         echo('<span style="color:red">(Reservation)</span>');
       }
       ?>
        </h3>
       </td>
      </tr>
      <tr><td>&nbsp;</td></tr>
      <tr>
       <td><b>Number of Rooms: <?=mysql_num_rows($result)?></b></td>
       <!--<td><b>Address:</b></td><td><?=$gcid['address']?></td>-->
      </tr>
     </table>
    </td>
   </tr>
   <tr>
    <td>
    <?
    print_account_status($_REQUEST['choice'], 
    $gcid['acc_id'], $_REQUEST['gcid'], '', '', $con);
    //echo "<h1>".get_unique_group_name2($_REQUEST['gcid'], $con) . "</h1>";
    //print_account_status(get_acc_name($_REQUEST['group_acc_id'], $con),
    //'', '', $con);  
    ?>
    </td>
   </tr>
   <?
    exit;
  }
  ####Process Summary Account Status####
  elseif ($_REQUEST['choice'] == 'Summary') {
    $d_bal = 0;
    $c_bal = 0;
  ?>
  <tr>
   <td>
    <table border='1'>
     <tr class='class1'>
      <td colspan="7"><h3>Account Summary For All The Rooms</h3></td>
     </tr>
     <tr class='class1'>
      <td><b>Room No</b></td>
     <!-- 
      <td><b>Check In</b></td>
      <td><b>Occupant</b></td>
     -->
      <td><b>Debit</b></td>
      <td><b>Credit</b></td>
      <td><b>Balance</b></td>
     </tr>
     <?
     ####Lets print all the 'Individual Rooms'####
     $result2 =  mysql_query("select r.id, r.number, g.arrival_date, 
      g.title, g.firstname, g.lastname, r.acc_id, r.occupancy from guest g 
      join room r on g.room_id = r.id where bill_to = 'individual'", $con);
     while($g2 = mysql_fetch_array($result2)) {
       echo "<tr><td>{$g2['number']} {$g2['title']} 
       {$g2['firstname']} {$g2['lastname']}</td>";

       /* Old report 
       $name = get_unique_guest_name($g2['id'], $con);
       $sql="SELECT sum(amount) as 'sum' FROM sales where 
        name_of_guest='$name' and p_type != 'Cash Sales' 
        and p_type ='Credit Sales'";

       $result3 = mysql_query($sql, $con);
       $s1 = mysql_fetch_array($result3);
       echo('<td>' . number_format($s1['sum'],2) . '</td>');
 
       $debit = $s1['sum'];
       $sql="SELECT sum(amount) as 'sum' FROM sales where 
        name_of_guest='$name' and p_type != 'Cash Sales' 
        and p_type != 'Credit Sales'";
       $result4 = mysql_query($sql, $con); 
       $s2 = mysql_fetch_array($result4); 
       echo('<td>' . number_format($s2['sum'], 2) . '</td>');

       $credit = $s2['sum'];
       $bal = $debit - $credit;
	   
       echo  "<td>" . number_format($bal, 2) . "</td></tr>";
       if ($bal > 0) {
         $d_bal += $bal;
       } elseif ($bal < 0) {
         $c_bal += $bal;
       }
       */
       //Get all debits entries
       $sql="select sum(amt) as 'sum' from journal where acc_id={$g2['acc_id']}
        and t_type='Debit'";
       $result = mysql_query($sql) or die(mysql_error());
       $row = mysql_fetch_array($result);
       echo "<td>" . number_format($row['sum'], 2) . "</td>";
 
       //Get all Credit entries
       $sql="select sum(amt) as 'sum' from journal where acc_id={$g2['acc_id']}
        and t_type='Credit'";
       $result = mysql_query($sql) or die(mysql_error());
       $row = mysql_fetch_array($result);
       echo "<td>" . number_format($row['sum'], 2) . "</td>";

       $sql = "select j.bal from journal j join account a on j.acc_id = a.id
         where j.acc_id={$g2['acc_id']} order by j.id desc";
       $result = mysql_query($sql, $con) or die(mysql_error());
       $journal_bal = mysql_fetch_row($result);
       echo "<td>" . number_format($journal_bal[0], 2) . "</td></tr>";
      
       if ($journal_bal[0] > 0) 
         $d_bal += $journal_bal[0];
       else if ($journal_bal[0] < 0) 
         $c_bal += $journal_bal[0];
     }	 
     ####Lets print all the 'Group Rooms'####
     #We get all the groups that currently have accounts with us
     $result6 = mysql_query("select gc.name, gc.id, gc.acc_id 
      from group_company_guests gc join account a on gc.acc_id = a.id", $con);
     while($gc = mysql_fetch_array($result6)) {
       echo "<tr><td colspan='3'><h3>{$gc['name']} Group</h3></td></tr>";

       $result7 = mysql_query("select r.id, r.number, g.arrival_date, g.title,
        g.firstname, g.lastname, gc.name, r.acc_id, r.occupancy from 
        guest g left join 
        (room r, group_company_guests gc) on 
        (g.room_id = r.id and g.grp_cmp_id = gc.id)
        where gc.id=".$gc['id'], $con);
       
        while($row7 = mysql_fetch_array($result7)) {
          ####Get Individual Guest details for Group/Company Account#### 
          echo "<tr>
           <td>{$row7['title']} {$row7['firstname']} {$row7['lastname']}
             {$row7['number']}</td></tr>";
        }

          /* Old Report
          $name = get_unique_group_name($g4['id'], $con);
          //echo('<tr><td colspan="3"></td>');
          $sql="SELECT sum(amount) as 'sum' FROM sales where 
           name_of_guest='$name' and p_type != 'Cash Sales' 
           and p_type = 'Credit Sales'";
	   
           $result8 = mysql_query($sql, $con);
           $row = mysql_fetch_array($result8);
           $debit = $row['sum'];
           echo "<td> "  . number_format($row['sum'], 2) . "</td>";

           $sql="SELECT sum(amount) as 'sum' FROM sales where 
            name_of_guest='$name' and p_type != 'Cash Sales' 
            and p_type != 'Credit Sales'";
           //echo "<br>$sql";
	   
           $result9 = mysql_query($sql, $con);
           $row = mysql_fetch_array($result9);
           $credit = $row['sum'];
           echo "<td>" . number_format($row['sum'], 2) . "</td>";

           $bal = $debit - $credit;
           echo  "<td>" . number_format($bal, 2) . "</td></tr>";
            if ($bal > 0) {
            $d_bal += $bal;
           } elseif ($bal < 0) {
             $c_bal += $bal;
           }
           */
 
         echo "<tr><td>&nbsp;</td>";
         //Get all debits entries
         $sql="select sum(amt) as 'sum' from journal where 
            acc_id={$gc['acc_id']} and t_type='Debit'";
         $result = mysql_query($sql) or die(mysql_error());
         $row = mysql_fetch_array($result);
         echo "<td>" . number_format($row['sum'], 2) . "</td>";
 
         //Get all Credit entries
         $sql="select sum(amt) as 'sum' from journal where 
           acc_id={$gc['acc_id']} and t_type='Credit'";
         $result = mysql_query($sql) or die(mysql_error());
         $row = mysql_fetch_array($result);
         echo "<td>" . number_format($row['sum'], 2) . "</td>";

         $sql = "select j.bal from journal j join account a on j.acc_id = a.id
          where j.acc_id={$gc['acc_id']} order by j.id desc";
         $result = mysql_query($sql, $con) or die(mysql_error());
         $journal_bal = mysql_fetch_row($result);
         echo "<td>" . number_format($journal_bal[0], 2) . "</td></tr>";
      
         if ($journal_bal[0] > 0) 
           $d_bal += $journal_bal[0];
         else if ($journal_bal[0] < 0) 
           $c_bal += $journal_bal[0]; 

        echo "<tr><td>&nbsp;</td></tr>";
     }
     ?>
     <tr height="20px"></tr>
     <tr>
      <td><b>Refundable to Guests</b></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><b><?php echo number_format($c_bal, 2); ?></b></td>
     </tr>
     <tr>
      <td><b>Outstanding Balances</b></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td><b><?php echo number_format($d_bal, 2); ?></b></td>
     </tr>
    </table>
   </td>
  </tr>
  <?
  exit;
  }
} 
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Form')) {
  ?>   
  <table> 
   <tr class='class1'>
    <td colspan='2'> 
     <h3>Account Status</h3>
     <form name="myform" action="account_status.php" method="post">
    </td>
   </tr>
   <tr>
    <td width="20%">
     <input type="radio" name="choice" value="Room"
            onClick="display_element('rid'); 
                     hide_element('group_acc_id')" >
     Room Number
    </td>
    <td> <select id="rid" name="rid" style="display:none;">

 <?
  $result = mysql_query("select r.id, r.number, g.title, g.firstname,
    g.lastname from guest g join room r on
   g.room_id = r.id where g.bill_to = 'individual'", $con);
  while($r = mysql_fetch_array($result)) {
  ?>
   <option value="<?=$r['id']?>">
   <?=$r['number']?>&nbsp;<?=$r['title']?>
     &nbsp;<?=$r['firstname']?>&nbsp;<?=$r['lastname']?></option>
  <?
  }
  ?>
     </select>
    </td>
   </tr>
   <tr>
    <td width="30%">
          <input type="radio" name="choice" value="GroupCompany" 
            onClick="display_element('group_acc_id'); 
                     hide_element('rid');">
          Group/Company 
    </td>
    <td> <select id="group_acc_id" name="group_acc_id" style="display:none">
	  
  <?
  $result = mysql_query("Select a.id, grp.name from group_company_guests grp 
    left join (guest g, account a) on (g.grp_cmp_id = grp.id and grp.acc_id = a.id) 
	 where g.bill_to = 'GroupCompany' 
    group by grp.name", $con);
  while($g = mysql_fetch_array($result)) {
    ?>
	<option value="<?=$g['id']?>"><?=$g['name']?></option>
	<?
  }
  ?>
     </select>
    </td>
   </tr>
   <tr>
   <td><input type="radio" name="choice" value="Summary"
            onClick="hide_element('rid');
                     hide_element('group_acc_id');">
           Account Summary For All Rooms
   </td>
   
  </tr>
  <tr>
   <td>
    <input name="action" type="submit" value="Account Details">
    <input name="action" type="submit" value="Cancel">
   </td>
  </tr>
 </table>
 <?
 }
 main_footer();
