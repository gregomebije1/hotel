<?php
$menus = array();
	 
 $user = array('Reception', 'Manager', 'Front Office Cashier');
 $sub_menu = array('guest.php?action=List' => 'Check In', 
	  'room_status.php?action=Form' => 'Room Status', 
	  'account_status.php?action=Form'=> 'Guest Account Status', 
	  'auto_billing.php?action=Form' => 'Auto Billing', 
	  'check_out.php?action=Form' => 'Check Out', 
	  'group_company_guests.php?action=List'=> 'Group/Company', 
	  'reservation.php?action=List' => 'Reservation',
   );
 $menus["Front Office"] = array($user, $sub_menu);
 
	 
$user = array('Front Office Cashier', 'Restaurant Cashier', 'Manager', 'Accountant');
$sub_menu = array('receive_payment.php?action=Form'   => 'Cash Transactions', 
 'record_expenses.php?action=Form'=> 'Credit Transactions',
 'summary_report.php'	=> 'Summary'); 
 $menus["Transactions"] = array($user, $sub_menu);
 

$user = array('Manager','Accountant');
$sub_menu = array('charge.php' => 'Charges',
   'audit_log.php' => 'Void Transactions',
   'chart_of_accounts.php' => 'Charts Of Accounts',
   'journal_entry.php' => 'Journal Entry',
   'trial_balance.php' => 'Trial Balance',
   'general_ledger.php' => 'General Ledger',
   'profit_and_loss.php' => 'Profit and Loss',
   'balance_sheet.php' => 'Balance Sheet');
$menus["Financials"] = array($user, $sub_menu);


$user = array('Manager');
$sub_menu = array('users.php?action=List'  => 'Users', 
'room_category.php?action=List' => 'Room Category',
'room.php?action=List' => 'Rooms', 
'department.php?action=List' => 'Department',    
'hotel_info.php' => 'Hotel Information', 
'backup_restore.php' => 'Backup and Restore'); 
$menus["Utilities"] = array($user, $sub_menu);


if (isset($_SESSION['uid'])) {
  $uid = $_SESSION['uid'];
  $text = "Welcome " . get_value('user', 'username', 'id', $_SESSION['uid'], $con);
} else {
  $uid = 1;
  $text = "Welcome ";
}

main_menu("Mangital Hotel Software", $text, $menus, $uid, $con);	




?>