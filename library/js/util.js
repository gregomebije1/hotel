function display_element(id) {
  document.getElementById(id).style.display='block';
}
function hide_element(id) {
  document.getElementById(id).style.display='none';
}

function do_me(elem_id) {
  var id = "xxx_" + elem_id;
  var sIndex = document.getElementById(id).selectedIndex;
  var text = document.getElementById(id).options[sIndex].text;
  document.getElementById(elem_id).value=text;
  hide_element(id);
}
	 
function showResult(str, elem_id, livesearch, sql) {
  if (str.length==0) { 
    document.getElementById(elem_id).innerHTML="";
    document.getElementById(elem_id).style.border="0px";
    return;
  }
  if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else {// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
	  var json = eval (xmlhttp.responseText); 
	  /*
	  var newData = "<ul style=\"list-style-type:none; padding: 0; margin:0; border: 1px solid grey\">" + "\n";
	  var style = " font:8pt tahoma; color:#204d89; padding:0; margin: 0; cursor:pointer;";
	  for(i=0; i < json.length; i++) {
		//newData += "<li id=\"" + i + "\" style=\"" + style + "\"><a onclick=\"display(" + i + "," + elem_id + ")\">" + json[i] + "</a></li>";
		newData += "<li id=\"" + i + "\" onmouseover=\"this.style.color='red';\"  onmouseout=\"this.style.color='#204d89';\" onclick=\"do_me(" + i + ");\" style=\"" + style + "\">" + json[i] + "</li>" + "\n";
	  }
	  newData += "</ul>";
	  alert(newData);
	  */
	 
	  var newData = "<select id='xxx" + "_" + elem_id + "' name='xxx" + "_" + elem_id + "' size='" + (json.length + 2) + "' onchange=\"do_me('" + elem_id + "');\">";
	  for(i=0; i < json.length; i++) {
	    newData += "<option>" + json[i] + "</option>";
	  }
	  newData += "</select>";
	  document.getElementById(livesearch).innerHTML=newData;
	  document.getElementById(livesearch).style.border="1px solid #A5ACB2";
	}
  }
  url="livesearch.php?q="+str+"&sql="+sql;
  xmlhttp.open("GET",url,true);
  xmlhttp.send();
}
