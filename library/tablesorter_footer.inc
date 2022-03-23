<?php
if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print'))
     || (!isset($_REQUEST['action']))) {
  ?>
   <div id="pager" class="pager">
   <form>

   <img src="library/css/tablesorter/first.png" class="first"/>
   <img src="library/css/tablesorter/prev.png" class="prev"/>
   <input type="text" class="pagedisplay"/>
   <img src="library/css/tablesorter/next.png" class="next"/>
   <img src="library/css/tablesorter/last.png" class="last"/>
   <select class="pagesize">
    <option selected="selected"  value="10">10</option>
    <option value="20">20</option>
    <option value="30">30</option>
    <option  value="40">40</option>
   </select>
  </form>
 </div>

 <?php } ?>
