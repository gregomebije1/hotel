<?php 

require_once "util.inc";

function msg_box($msg, $url, $urlname) {
  return "<table>
             <tr><td><h3>$msg <a style='color:orange; text-decoration:none'
             href='$url'>$urlname</a></h3></td></tr></table>";
}

function gen_list($table, $url, $first_col, $skip=array(), $sql, $con) {
 
 $q="describe $table";
 $result = mysql_query($q) or die(mysql_error());
 while($field = mysql_fetch_array($result)) {
  if (in_array($field[0], $skip))
    continue;
   $cols[] = $field[0];
 }

  //Display Column titles
  echo "<thead><tr>";
  foreach($cols as $col) {
    if (in_array($col, $skip)) 
      continue;
    echo "<th>" . format_label($col). "</th>";
  }
  echo "</tr></thead><tbody>";

  $result = mysql_query($sql, $con);
  while($row = mysql_fetch_array($result)) {
    echo "<tr>";
    $sql="describe $table";
    $result2 = mysql_query($sql) or die(mysql_error());

	if (mysql_num_rows($result2) <= 0) {
       echo "<tr style='text-align:center;'>
         <td colspan='6'><h4>Not Found</h4></td></tr></form></table>";
       exit;
     }
    while($field = mysql_fetch_array($result2)) {
      if (in_array($field[0], $skip) || ($field[0] == 'id')) 
        continue;

      echo "<td class='style2'>";
	  if ((!empty($first_col)) && ($first_col == $field[0])) {
        echo "<a href='$url?action=Edit&table=$table&id={$row['id']}'>{$row[$field[0]]}</a>";
      } 
      else if (eregi("_id", $field[0])) {
        $t = substr($field[0], 0, stripos($field[0], "_id"));		
		$name = get_value($t, 'name', 'id', $row[$field[0]], $con);
        echo $name; 
      } else
        echo $row[$field[0]];
      echo "</td>";
    }
    echo "</tr>";
  } 
  echo "</tbody>";
}

function generate_form($action, $url, $id, $table, $row, $skip, $referer, $find="", $con) {
  echo "
   <form method='post' action='$url' name='form1' id='form1' enctype='multipart/form-data'>
    <input type='hidden' name='table' value='$table' />
    <input type='hidden' name='id' value='$id' />
    <table>
     <tr class='class1'><td colspan='2' class='class3'>$action  " . format_label($table) . "</td></tr>
     <tr>
      <td>
       <table>
  ";

  $result = mysql_query("describe $table", $con) or die(mysql_error());
  while($field = mysql_fetch_array($result)) {
    if (in_array($field[0], $skip))
      continue;
    if (eregi("_image", $field[0]))
      continue;
    echo "<tr><td>". format_label($field[0]) . "</td><td>";
    if ($field[1] == 'text')
      echo textarea($field[0], $row[$field[0]]);

    else if ($field[1] == 'date') {
      $date = empty($row[$field[0]]) ? date('Y-m-d') : $row[$field[0]];
     echo inputfield('name',$field[0],'value', $date,'size','10','type','text');

    } else if (eregi("_id", $field[0])) {
      $t = substr($field[0], 0, stripos($field[0], "_id"));
      $sql = "select * from $t $find order by id";
      $arr = my_query($sql, 'id', 'name');
	  
	  echo selectfield($arr, $field[0], $row[$field[0]]); 
    } else if (eregi("enum", $field[1])) {
      echo selectfield(parse_enum($field[1]),$field[0], $row[$field[0]]);

    } else 
      echo inputfield('name',$field[0],'value',$row[$field[0]], 'size', '30', 'type','text');
    echo "</td></tr>";
  }

  echo "</table>
        </td>
        <td style='vertical-align:top;'>
        <table>";

  $result = mysql_query("describe $table", $con) or die(mysql_error());
  while($field = mysql_fetch_array($result)) {
    if (!eregi("_image", $field[0]))
      continue;
    else {
      echo "<tr><td>". format_label($field[0]) . "</td><td>";
      echo inputfield('name',$field[0],'value',$row[$field[0]],'type','file');
      echo "</td></tr>";
      if (($action=='Edit') || ($action =='View')) {
        echo "<tr><td><img src='upload/{$row[$field[0]]}' width='200'
          'height'></td></tr>";
      } 
    }
  }
  echo "</table></td></tr>";
       
  echo "<tr>
         <td>
          <table>
           <tr>";
          
  if ($action == 'Delete') {
    echo "<td>".inputfield('name','action','value','Delete','type','submit')
    . "</td>";
  } else {
    $value = (($action == 'Edit') || ($action == 'Edit Only')) ? 'Update' : 'Insert';
    echo "<td>".inputfield('name','action','value',$value,'type','submit')
    ."</td>";

    if ($action == 'Edit') {
      echo "<td>".inputfield('name','action','value','Delete','type','submit')
      . "</td>";
    }
  }
  if (isset($referer))
    echo "<input name='REFERER' type='hidden' value='{$referer}'/>";
  
  echo "</tr></table></td></tr></table></form>";
}


function print_header($title, $url, $urltext, $con) {
  $result = mysql_query("SELECT * FROM hotel_info", $con) 
    or die("Cannot execute SQL query at print_header" . mysql_error());
  $row = mysql_fetch_array($result);
  ?>
     <html>
      <head>
       <title><?=$title?></title>
       <style>
       a:hover, a:active, a:visited, a:link {
         text-decoration:none;
		 font-size:1em;
		 background-color:#ebf3ff;
		 font-weight:bold;
         color:black;
       }
       .h table td {
        font: 78%/1.5 arial, helvetica, serif;
        text-align: left;
        padding: 0;
        /*margin: 2em; */
       }
	   th {
	    font: 78%/1.5 arial, helvetica, serif;
        text-align: left;
        padding: 0;
		font-weight:bold;
	    }
       .h td {
        font: 78%/1.5 arial, helvetica, serif;
        text-align: left;
        padding: 0;
       }
       .s {
        font: 78%/1.5 arial, helvetica, serif;
        text-align: center;
        padding: 0;
        border-width:1px; border-style:solid;
       }
	   table .class1 {
		border-bottom: #ffffff 0.1em solid;
	    /*background-color:#d6e8ff;*/
        background-color:#ebf3ff;
        font-weight:bold;
       }
	  table {
       color:black;
       background-color: white;
       border-width:1px;
       text-align: left;
       padding: 0;
       width:100%;
       table-layout: float;
       border: #d6e8ff 0.1em solid;
       border-collapse:collapse;
     }
       </style>
      </head>
      <body style="text-align:center;" >
       <a href='<?=$url?>'><?=$urltext?></a>
       <table class="s" align="center" width="70%" 
        cellpadding="0" cellspacing="0"> 
         <tr><td><h2><?=$row['name']?></h2></td></tr>
         <tr><td><?=$row['address']?></td></tr>
         <tr><td><?=$row['phone']?></td></tr>
         <tr><td><?php echo $row['email'] . " " . $row['web']; ?></td></tr>
         <tr><td>&nbsp;</td></tr>
       </table>
       <table class="h" align="center" rules="rows" border="1"
        style="
          border-width:1px; 
          border-style:solid;"
         border="0" width="70%" cellspacing="0" cellpadding="0">
         <tr>
          <td style="background-color:#ebf3ff; text-align:center;" colspan="8">
            <h3><?=$title?></h3>
          </td>
         </tr>
  <?
}
/*
function print_header($title, $url, $urltext, $con) { 
  $result = mysql_query("SELECT * FROM school", $con) 
    or die("Cannot execute SQL query at print_header" . mysql_error());
  $row = mysql_fetch_array($result);
  
  ?>
    <html>
      <head>
       <title><?=$title?></title>
       <link rel='stylesheet' type='text/css' href='css/print.css'>
       </style>
      </head>
      <body style="text-align:center;" >
       <!--<a href='<?=$url?>'><?=$urltext?></a>-->
	   <!--
       <table class="s" align="center" width="70%" 
        cellpadding="0" cellspacing="0"> 
         <tr><td><h2><?=$row['name']?></h2></td></tr>
         <tr><td><?=$row['address']?></td></tr>
         <tr><td><?=$row['phone']?></td></tr>
         <tr><td><?php echo $row['email'] . " " . $row['web']; ?></td></tr>
         <tr><td>&nbsp;</td></tr>
       </table>
	   -->
       <table class="h" align="center" rules="rows" border="1"
        style="
          border-width:1px; 
          border-style:solid;"
         border="0" width="70%" cellspacing="0" cellpadding="0">
         <tr>
          <td style="background-color:#ebf3ff" colspan="8">
          <!--<h3><?=$title?></h3>-->
          </td>
         </tr>
  <?
}
*/
function generate_menu($heading, $menu) {  
?>
  <li><?=$heading?></li>
  <li>
   <ul>
   <? 
    foreach($menu as $url => $name) {
      echo "<li><a href=\"$url\">$name</a></li>\n";
    } 
   ?>
   </ul>
  </li>
<?
} 

function main_menu($title, $text, $menus, $uid, $con) { 
  ?>
  <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
    'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
  <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
   <head>
   <title><?php echo $title;?></title>
   <link rel="stylesheet" type="text/css" href="library/js/themes/base/jquery.ui.all.css">

    <!-- For Table sorter -->
   <link rel='stylesheet' type='text/css' href="library/css/tablesorter/style.css" 
     media="print, projection, screen" />	

   <script type="text/javascript" src="library/js/jquery-1.4.2.min.js"></script>
   <script src="library/js/ui/jquery.ui.core.js"></script>
   <script src="library/js/ui/jquery.ui.widget.js"></script>
   <script src="library/js/ui/jquery.ui.datepicker.js"></script>
   <script src="library/js/ui/jquery.ui.mouse.js"></script>
   <script src="library/js/ui/jquery.ui.button.js"></script>
   <script src="library/js/ui/jquery.ui.draggable.js"></script>
   <script src="library/js/ui/jquery.ui.position.js"></script>
   <script src="library/js/ui/jquery.ui.resizable.js"></script>
   <script src="library/js/ui/jquery.ui.dialog.js"></script>
   <script src="library/js/ui/jquery.effects.core.js"></script>
   <script type="text/javascript" src="library/js/jquery-ui-1.8.custom.min.js"></script>
	

   <script type='text/javascript' src='library/js/jquery.tablesorter.min.js'></script>
   <script type='text/javascript' src='library/js/jquery.tablesorter.pager.js'></script>
   <script type='text/javascript' src='library/js/chili-1.8b.js'></script>
   <script type='text/javascript' src='library/js/docs.js'></script>
   <script type='text/javascript'>
   $(function() {
     $("table")
       .tablesorter({widthFixed: true, widgets: ['zebra']})
       .tablesorterPager({container: $("#pager")});
     });
   </script>
 
   <script>
    $(function() {
     $( "#datepicker").datepicker({ dateFormat: "yy-mm-dd" });
     $( "#Start_Date").datepicker({ dateFormat: "yy-mm-dd" });
     $( "#End_Date").datepicker({ dateFormat: "yy-mm-dd" });
    });
   </script>

      
   <link rel="stylesheet" type="text/css" href="library/css/ui-lightness/jquery-ui-1.8.custom.css">
   <link rel="stylesheet" type="text/css" href="library/css/autocomplete.css">
   
   <link rel='stylesheet' type='text/css' href="library/css/index.css">		   
   <script type="text/javascript" src="library/js/custom.js"></script>
   <script type="text/javascript" src="library/js/index.js"></script>
      <script type="text/javascript" src="hotel.js"></script>

   </head>
   <body>
    <div class='header'>
     <?php echo "<p>$title</p> <p>$text</p>"; ?> 
     <a style='float:right; color:red;' href='index.php?action=logout'> Sign Out</a>
    </div>
    <div class='bar'></div>
    <div class='navigation'>
     <ul>
      <?php
	  foreach($menus as $menu => $details) {
	    if (user_type($uid, $details[0], $con)) 
          generate_menu($menu, $details[1]);
        unset($menu);
	  }
	  ?>
     </ul>
    </div>
    <div class='content'>
<?php
}
function main_footer() {
?>
     </div> <!-- End of content -->
    </div> <!-- End of container -->
   </body>
  </html>
<?php
}
function tr($arr) {
  $output = "<tr>";
  foreach($arr as $td) {
    $output .= "<td>$td</td>";
  }
  $output .= "</tr>";
  return $output;
}

function inputfield() {
  $numargs = func_num_args();
  if ($numargs < 2) die("textfield second argument is missing");
  
  $output = "<input ";
  $arg_list = func_get_args();
  for ($i = 0; $i < $numargs; $i+=2) {
    $output .= " {$arg_list[$i]}='{$arg_list[$i+1]}' ";
  }
  $output .= ">";
  return $output;
}

function textarea($name, $value, $rows='5', $cols='30') {
  $numargs = func_num_args();
  if ($numargs < 2) die("textarea second argument is missing");
  return "<textarea name='$name' rows='$rows' cols='$cols'>$value</textarea>";
}

function selectfield($arr, $name, $default_id, $style="display:inline;", $code="") {
  $output = "<select name='$name' id='$name' style='$style' onchange='$code'>";
  foreach($arr as $id => $value) {
    if ($id == $default_id)
      $output .= "<option value='$id' selected='selected'>$value</option>";
    else
      $output .= "<option value='$id'>$value</option>";
  }
  $output .="</select>";
  return $output;
}

?>
