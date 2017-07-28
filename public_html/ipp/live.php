<?php
  global $user_css, $user_javascript, $user_onload;
  $user_css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" title=\"main\" media=\"screen, projection\" />";
  
  $remove = "true";
  if (isset($_GET['noremove'])) $remove = "false";
  $user_javascript = "
  
  <script type='text/javascript' src='ajax.js'></script>
  <script type='text/javascript'>
  <!--
  
  // settings
  
  set_newentries = 'top'; // 'top' or 'bottom';
  set_maxentries = 40;
  set_remove = " . $remove . "; 
  
  
  // removes an ip patrol entry
  function entry_remove(id)
  {
    if (id.substring(0, 6) != 'entry_') { id = 'entry_' + id; }
    var e = document.getElementById(id);
    if (set_remove)
    {
      if (e != null) e.parentNode.removeChild(e);
    }
    else
    {
      e.style.color = '#777777';
      var aTags = e.getElementsByTagName('a');
      for (i = 0; i < aTags.length; i++) 
        aTags[i].style.color = '#777777';
    }
  }
  // end entry_remove()
  
  // adds an ip patrol entry
  function entry_add(id, timestamp, title, desc, change, user, isnew, spam)
  {
    var newentry = document.createElement('tr');
    newentry.setAttribute('id', 'entry_' + id);
    newentry.className = 'et_entry';
    if (parseInt(change) < -200) { newentry.className = 'et_vandale'; }
    
    // add columns
    
    var cd = new Date(parseInt(timestamp) * 1000);
    var zeit = ((cd.getHours() < 10) ? '0' : '') + cd.getHours() + ':';
    zeit += ((cd.getMinutes() < 10) ? '0' : '') + cd.getMinutes();
    var datum = ((cd.getDate() < 10) ? '0' : '') + cd.getDate() + '.';
    datum += (((cd.getMonth()+1) < 10) ? '0' : '') + (cd.getMonth()+1) + '.';
    datum += cd.getFullYear();
    
    var col1 = document.createElement('td');
    col1.innerHTML = '<b>' + zeit + '</b><br /><span class=\"date\">' + datum + '</span>';
    col1.className = 'textcenter';
    
    // make /* */ grey...
    desc = desc.replace(/\/\*(.*)\*\//, \"<span style=\'color:#AAAAAA;\'>$1</span>\");
    if (desc == '') desc = '&nbsp;';
    
    // new entry
    output_new = '';
    if (isnew > 0) { output_new = ' &bull; <span class=\"wp_neu\">NEU</span>'; }
    
    var col2 = document.createElement('td');
    col2.innerHTML = '<a href=\"mark.php?id=' + id + '\" onclick=\"entry_remove(\'' + id + '\');\" target=\"_blank\" style=\"display:block; width:100%; text-decoration:none;\"><b style=\"text-decoration:underline;\">' + title + '</b>' + output_new + '<br /><i>' + desc + '</i></a>';
    
    var col3 = document.createElement('td');
    col3.innerHTML = change;
    if (change > 0) { col3.innerHTML = '+' + col3.innerHTML; }
    col3.className = 'textcenter';

    var col4 = document.createElement('td');
    col4.innerHTML = spam + '%';
    col4.className = 'textcenter';
    
    var col5 = document.createElement('td');
    col5.innerHTML = '<a href=\"http://de.wikipedia.org/w/wiki.phtml?title=Spezial:Contributions&amp;target=' + user + '\" id=\"entry_' + id + '_user\" target=\"_blank\">' + user + '</a><br />';
    col5.innerHTML += '<a href=\"javascript:clickVandale(\'' + id + '\');\" id=\"entry_' + id + '_vandallink\">IP markieren</a>';

    newentry.appendChild(col1);
    newentry.appendChild(col2);
    newentry.appendChild(col3);
    newentry.appendChild(col4);
    newentry.appendChild(col5);

    var d = document.getElementById('edittable');
    d = d.getElementsByTagName(\"TBODY\")[0]; // this is needed for IE
 
    // new entry at the top or at the bottom?
    if (set_newentries == 'bottom')
      d.appendChild(newentry);
    else
      d.insertBefore(newentry, document.getElementById('et_heading').nextSibling);
      
    // remove all entries, which are too much ;)
    if (d.getElementsByTagName('TR').length > set_maxentries + 1)
    {
      var e = d.getElementsByTagName('TR');
      // remove one element at the top
      if (set_newentries == 'bottom')
        entry_remove(e[1].id);
      // remove one element at the bottom
      else
        entry_remove(e[e.length-1].id);
    }
  }
  // end entry_add()

  function markVandale(id)
  {
    if (id.substring(0, 6) != 'entry_') { id = 'entry_' + id; }
    document.getElementById(id).className = 'et_vandale';
    document.getElementById(id + '_vandallink').innerHTML = 'IP demarkieren';
  }
  function unmarkVandale(id)
  {
    if (id.substring(0, 6) != 'entry_') { id = 'entry_' + id; }
    document.getElementById(id).className = 'et_entry';
    document.getElementById(id + '_vandallink').innerHTML = 'IP markieren';
  }
  function toggleVandale(id)
  {
    if (id.substring(0, 6) != 'entry_') { id = 'entry_' + id; }
    if (document.getElementById(id).className == 'et_entry')
      markVandale(id);
    else
      unmarkVandale(id);
  }
  function clickVandale(id)
  {
    // what ip?
    var user = getInnerText(document.getElementById('entry_' + id + '_user'));
  
    // send to server
    if (document.getElementById('entry_' + id).className == 'et_entry')
    {
      sendRequest('vandale.php','?ip=' + user,REQUEST_GET,2);
      vandal_new(user);
    }
    else
    {
      sendRequest('vandale2.php','?ip=' + user,REQUEST_GET,2);
      vandal_old(user);    
    }
  }
  
  function getInnerText(xmlelement)
  {
    if (xmlelement.firstChild == null)
      return '';
    else
      return xmlelement.firstChild.data;
  }
  
  function entries_new(entriesXML)
  {
    for (i = (entriesXML.length - 1); i >= 0; i--)
    {
      var entry = entriesXML[i];
      
      var id = getInnerText(entry.getElementsByTagName('ID')[0]);
      var timestamp = getInnerText(entry.getElementsByTagName('timestamp')[0]);
      var title = getInnerText(entry.getElementsByTagName('title')[0]);
      var changedesc = getInnerText(entry.getElementsByTagName('changedesc')[0]);
      var change = getInnerText(entry.getElementsByTagName('change')[0]);
      var user = getInnerText(entry.getElementsByTagName('user')[0]);
      var spam = getInnerText(entry.getElementsByTagName('spam')[0]);
      
      entry_add(id, timestamp, title, changedesc, change, user, entry.getElementsByTagName('new').length, spam);
      
      if (entry.getElementsByTagName('vandale').length > 0)
        markVandale(id);
      
      lasttime = timestamp;
    }
  }
  // end entries_new()
  
  function vandal_new(vandal)
  {
    var rows = document.getElementById('edittable').getElementsByTagName('tr');
    for (i=1; i<rows.length;i++)
    {
      if (getInnerText(document.getElementById(rows[i].id + '_user')) == vandal)
        markVandale(rows[i].id);
    }
  }
  
  function vandal_old(vandal)
  {
    var rows = document.getElementById('edittable').getElementsByTagName('tr');
    for (i=1; i<rows.length;i++)
    {
      if (getInnerText(document.getElementById(rows[i].id + '_user')) == vandal)
        unmarkVandale(rows[i].id);
    }
  }
  
  function processData(xmlHttp, intID)
  {
    // ID=1 -> new data
    if (intID == 1)
    { 
      var tmpXML = xmlHttp.responseXML;
      entries_new(tmpXML.getElementsByTagName('NewEntry'));
      
      var entriesXML;
      entriesXML = tmpXML.getElementsByTagName('OldEntry');
      for (i=0; i < entriesXML.length; i++)
      {
        var entry = entriesXML[i];
        entry_remove(getInnerText(entry.getElementsByTagName('ID')[0]));
      }

      entriesXML = tmpXML.getElementsByTagName('NewVandal');
      for (i=0; i < entriesXML.length; i++)
      {
        var entry = entriesXML[i];
        vandal_new(getInnerText(entry.getElementsByTagName('user')[0]));
      }        
      entriesXML = tmpXML.getElementsByTagName('OldVandal');
      for (i=0; i < entriesXML.length; i++)
      {
        var entry = entriesXML[i];
        vandal_old(getInnerText(entry.getElementsByTagName('user')[0]));
      }        
    }
  }
  // end processData()

  function handleAJAXError(xmlHttp, intID)
  {
    if (intID == 1)
      alert('Kann keine Edits laden.');
    else
      ;
  }
  // end handleAJAXError()
  
  var lasttime = 0;
  function autoActualize()
  {
    sendRequest('newentries.php','?time=' + lasttime,REQUEST_GET,1);
    window.setTimeout('autoActualize()', 15000);
  }
  
  -->
  </script>
  
  "; 
  // End of Javascript
  
  // CSS for this page
  $user_css .= "
  
  <style type='text/css'>
  <!--
  
  #edittable { border-width:0; width:100%; }
  #edittable tr:first-child td { text-align:center; }
  #edittable td { border-bottom:1px dotted #000000; vertical-align:top; }
  #edittable .et_entry {  }
  #edittable .et_vandale { background-color:#CC0000; }
  
  #edittable .date { font-size:8pt; }
  #edittable .textcenter { text-align:center; }
  
  .link { text-decoration:underline; cursor:pointer; }
  
  -->
  </style>
  
  ";
  // End of CSS
  
  $user_onload = " onload='autoActualize();'";

  include("design.inc.php");
  design_top("IP-Patrol");
?>

  <h1>IP-Patrol: de.wikipedia</h1>

  <p>Diese Seite zeigt alle &Auml;nderungen an Artikeln der deutschsprachigen Wikipedia, 
  die von unangemeldeten Nutzern durchgef&uuml;hrt wurden.<br />
  Oben werden dabei die neuesten &Auml;nderungen angezeigt, die Liste wird fortlaufend 
  automatisch aktualisiert. Es gibt auch eine <a href="index.php">statische, sortierbare 
  Version</a>.</p>
  
  <table cellpadding="0" cellspacing="0" id="edittable"> 
  <tr id="et_heading">
    <td style="width:112px;"><b>Uhrzeit</b></td>
    <td><b>Artikel</b></td>
    <td style="width:140px;"><b>Ver&auml;nderung</b></td>
    <td style="width:60px;"><b>SPAM</b></td>
    <td style="width:250px;"><b>User</b></td>
  </tr>
  </table>

  <p>&nbsp;</p>

<?
  design_unten();
?>

