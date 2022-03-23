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
  || user_type($_SESSION['uid'], 'inventory', $con)
 || user_type($_SESSION['uid'], 'Sales Clerk', $con)
 )){
  main_menu($_SESSION['uid'],
    $_SESSION['first_name'] . " " . $_SESSION['last_name'], $con);
  echo msg_box('Access Denied!', 'index.php?action=logout', 'Continue');
  exit;
}
*/

if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('General Ledger Transactions', 'general_ledger.php',  
      'Back', $con);
} else {
  main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], '', '', $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Process')) {
    if (empty($_REQUEST['sdate']) || (empty($_REQUEST['edate']))) { 
      echo msg_box('Please enter correct both the start and end dates', 
       'general_ledger.php', 'Back');
      exit;
    }
    $sdate = $_REQUEST['sdate'];
    $edate = $_REQUEST['edate'];
    if (!isset($_REQUEST['ac'])) {
      echo msg_box('Please add an account', 'general_ledger.php', 'Back');
       exit;
    }
    ?>
    <table>
     <tr>
      <td colspan="6">
       <table>
        <tr class='class1'>
         <td>
          <form action="general_ledger.php">
           <input type="submit" name="action" value="Print">
           <input type="hidden" name="sdate" value="<?=$_REQUEST['sdate']?>">
           <input type="hidden" name="edate" value="<?=$_REQUEST['edate']?>">             </form>
         </td>
        </tr>
       </table>
      </td>
     </tr>
     <tr><td colspan='6'><h3>GENERAL LEDGER TRANSACTIONS</h3></td></tr>
     <tr>
      <td>
       <table width="100%">
        <tr>
         <td><b>Start Date</b></td><td><?=$sdate?></td>
         <td><b>End Date</b></td><td><?=$edate?></td>
        </tr>
       </table>
      </td>
     </tr>
     <?
     $result = mysql_query("SELECT id, name from account_type", $con)
      or die(mysql_error());
     if (mysql_num_rows($result) == 0) {
        msg_box('Please add account types', 'general_ledger.php', 'Back');
        exit;
     }
     $count = 0;
     //Get Account Types e.g., Assets, Account Receivables
     while($acc_type = mysql_fetch_array($result)) {
       //Only print account type name if there is a journal entry 
       //for any account of that account type;
	  
        $sql="select * from journal j join account a 
	 on j.acc_id=a.id and a.acc_type_id={$acc_type['id']}";
	$resultx = mysql_query($sql, $con) or die(mysql_error());
	if (mysql_num_rows($resultx) > 0) {
	  //Get Accounts (e.g., Guest Accounts) of the Account Type
	  //Get All accounts of account type
          if ($_REQUEST['ac'] == 0) {
            $sql="SELECT * FROM account where 
             acc_type_id ={$acc_type['id']}";
            $accountr = mysql_query($sql, $con) or die(mysql_error());
           echo"<tr><td&nbsp;</td></tr>";
	    echo"<table><tr class='class1'><td><h3>{$acc_type['name']}</h3> 
             </td></tr></table>";
	  } else {
	    //Get Specific account of account type
            $sql= "SELECT * FROM account where 
             acc_type_id={$acc_type['id']} and id={$_REQUEST['ac']}";
            $accountr = mysql_query($sql) or die(mysql_error());
	  } 
	  //Get Journal entries of account(s) within specified date
          while($accounts = mysql_fetch_array($accountr)) {  
            $sql = "SELECT j.id, j.acc_id, j.entity_id, j.d_entry,  
             j.descr, j.t_type, j.amt FROM journal j 
             join account a on j.acc_id = a.id 
             where j.acc_id={$accounts['id']}
             and j.d_entry between 
             '$sdate' and '$edate' order by id asc";		   
            $journalr = mysql_query($sql, $con);
            //If they are journal entries for this account
            if (mysql_num_rows($journalr) > 0) {
	      //Display header information
	      if ($_REQUEST['ac'] != '0') {
	        echo"<tr><td&nbsp;</td></tr>";
	        echo"<table><tr class='class1'><td><h3>{$acc_type['name']}
                 </h3></td></tr></table>";
	      }
              ?>
            <tr><td>
             <table>
              <tr><td>&nbsp;</td></tr>
              <tr><td><b><?=$accounts['name']?></b></td></tr>
              <tr style="background-color:silver;">
               <td><b>Date</b></td><td><b>Description</b></td>
               <td><b>Debit</b></td>
               <td><b>Credit</b></td>
               <td><b>Balance</b></td>
              </tr>
              <?
	      //Print journal entries
              $bal = 0;
              while($journal = mysql_fetch_array($journalr)) {
                echo "<tr><td>{$journal['d_entry']}</td><td>
                 {$journal['descr']}</td>";
                $acc_type_id = get_acc_type($journal['acc_id'], '1', $con);

                if (($acc_type_id % 2) == 1) {
                  if ($journal['t_type'] == 'Debit') {
                    $bal = $bal + $journal['amt']; 
                    echo "<td>".number_format($journal['amt'], 2)
                     ."</td><td>&nbsp;</td>";
                  } else if ($journal['t_type'] == 'Credit') {
                    $bal = $bal - $journal['amt'];
                    echo "<td>&nbsp;</td><td>".number_format($journal['amt'], 2)
                      ."</td>";
                  }
               } else if (($acc_type_id % 2) == 0) {
                 if ($journal['t_type'] == 'Credit') {
                    $bal = $bal + $journal['amt']; 
                    echo "<td>&nbsp;</td><td>".number_format($journal['amt'], 2)
                     ."</td>";
                  } else if ($journal['t_type'] == 'Debit') {
                    $bal = $bal - $journal['amt'];
                    echo "<td>".number_format($journal['amt'], 2)
                      ."</td><td>&nbsp;</td>";
                  }
               } 
               echo "<td>". number_format($bal, 2). "</td></tr>";
             }
              echo "<tr><td>&nbsp;</td></tr></table></td></tr>";
          } // end of journal entry loop
        } //end account loop 
      } //end if account type has journal entry
    } //end of account types loop
    exit;  //Dont process Process beyond this point
  } // end of 'action=Process'
}  // end of else
if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')) 
   ||(!isset($_REQUEST['action']))) {
  ?>
  <table> 
   <tr class="class1">
    <td colspan="4"><h3>General Ledger</h3></td>
   </tr>
    <form action="general_ledger.php" method="post">
     <tr>
      <td>Account</td>
      <td>
       <select name="ac">
        <option value="0">All</option>
        <?
        $result = mysql_query("select id, name from account");
        while($row = mysql_fetch_array($result)) {
          echo "<option value='".$row['id']."'>".$row['name']."</option>";
        }
        ?>
       </select>
      </td>
     </tr>
     <tr>
      <td>Start Date</td>
      <td>
       <input type='text' name='sdate' value='<?php echo date('Y-m-d');?>'>
      </td>
     </tr>
     <tr> 
      <td>End Date</td>
      <td>
       <input type='text' name='edate' value='<?php echo date('Y-m-d');?>'>
      </td>
     </tr>
     <tr>
      <td>
       <input name="action" type="submit" value="Process">
       <input name='action' type='submit' value='Cancel'>
      </td>
     </tr>
    </form>
   </table>
   <?php  main_footer(); } ?>
