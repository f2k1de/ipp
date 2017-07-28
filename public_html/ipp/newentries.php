<?php
  require("../db.inc.php");
  $userdb = db_user_data();
  $table = "ipp_changes";
  $table_old = "ipp_changes_old";
  $vandal_table = "ipp_vandalen";
  $vandal_table_old = "ipp_vandalen_old";

  // Start SOAP output
  soap_start();

  // ask database for new entries

  $time = 0;
  if (isset($_GET['time'])) { $time = mysql_escape_string($_GET['time']); }

  // Neue Aenderungen
  $query = "SELECT * FROM $table WHERE time>" . $time . " ORDER BY time DESC LIMIT 0,30";
  $result = mysql_query($query, $userdb);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    print "  <NewEntry>\n";
    
    print "    <ID>" . $row['id'] . "</ID>\n";
    print "    <timestamp>" . $row['time'] . "</timestamp>\n";
    print "    <title>" . str_replace("&", "&amp;", $row['title']) . "</title>\n";
    print "    <changedesc>" . htmlspecialchars($row['text']) . "</changedesc>\n";
    print "    <change>" . $row['changesize'] . "</change>\n";
    print "    <user>" . $row['user'] . "</user>\n";
    print "    <spam>" . number_format($row['spam'] * 100, 1) . "</spam>\n";
    
    // Vandale?
    $result2 = mysql_query("SELECT count(*) FROM $vandal_table WHERE ip LIKE '" . $row['user'] . "'", $userdb);
    $row2 = mysql_fetch_array($result2, MYSQL_NUM);
    mysql_free_result($result2);
    if ($row2[0]>0) { print "    <vandale />\n"; }    
    
    // Neuer Artikel?
    if (strpos($row['flag'], "N") !== false)
      print "    <new />\n";
    
    print "  </NewEntry>\n";
  }

  // Alte Aenderungen
  if ($time > 0)
  {
    $query = "SELECT * FROM $table_old WHERE time>" . $time . " LIMIT 0,50";
    $result = mysql_query($query, $userdb);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
      print "  <OldEntry>\n"; 
      print "    <ID>" . $row['id'] . "</ID>\n";
      print "  </OldEntry>\n";
    }
  }

  // Neue Vandalen
  if ($time > 0)
  {
    $query = "SELECT * FROM $vandal_table WHERE time>" . $time . " LIMIT 0,20";
    $result = mysql_query($query, $userdb);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
      print "  <NewVandal>\n"; 
      print "    <user>" . $row['ip'] . "</user>\n";
      print "  </NewVandal>\n";
    }
  }

  // Alte Vandalen
  if ($time > 0)
  {
    $query = "SELECT * FROM $vandal_table_old WHERE time>" . $time . " LIMIT 0,20";
    $result = mysql_query($query, $userdb);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
      print "  <OldVandal>\n"; 
      print "    <user>" . $row['ip'] . "</user>\n";
      print "  </OldVandal>\n";
    }
  }


  // End SOAP output
  soap_end();













// ========================================================================== //
// SOAP FUNCTIONS
// ========================================================================== //

function soap_start()
{
header('Content-type: text/xml');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"
                  xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
                  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
 <soapenv:Body>
 
 ";
}

function soap_end()
{
print "

 </soapenv:Body>
</soapenv:Envelope>
";

}

?>
