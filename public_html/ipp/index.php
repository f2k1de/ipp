<?php
  global $user_css;
  $user_css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" title=\"main\" media=\"screen, projection\" />";

	require("../../ipp/DBCore.php");
  
	$DB = new DBCore ('db@localhost', 's53094__ippdata');
  include("design.inc.php");
  design_top("IP-Patrol");
?>

<h1>Wikipedia: IP-Patrol</h1>

<p>Diese Seite zeigt alle &Auml;nderungen an Artikeln der deutschsprachigen Wikipedia, die von
unangemeldeten Nutzern durchgef&uuml;hrt wurden und alle neuen Artikel.<br />
Oben werden dabei die neuesten &Auml;nderungen angezeigt, die Liste wird fortlaufend
aktualisiert, es gibt auch eine <a href="live.php">Live-Version</a> (ben&ouml;tigt
aktiviertes JavaScript).<br />
<b>Aktuelles und Informationen</b> in der Wikipedia unter <a href="http://de.wikipedia.org/wiki/Benutzer:APPER/IP-Patrol">Benutzer:APPER/IP-Patrol</a>. 
<b>Bitte unbedingt vor Benutzung diese Informationen bzw. die Informationen am Ende dieser Seite lesen.</b></p>

<?php

  $so = isset($_GET['so']) ? $_GET['so'] : "";
  $si = isset($_GET['si']) ? $_GET['si'] : "";
  $neu = isset($_GET['neu']) ? $_GET['neu'] : "";
  $user = isset($_GET['user']) ? $_GET['user'] : "";
  $start =isset($_GET['start']) ? $_GET['start'] : "";
  if ($so != "ASC") { $so = "DESC"; }
  if (($si != "change") && ($si != "user") && ($si != "spam")) { $si = "id"; }
  if (($neu != "1") && ($neu != "2")) { $neu = ""; }

  $table = "ipp_changes";

  function ShowZeile($id, $seite, $link, $change, $user, $beschreibung, $new, $minor, $time, $spam)
  {
    global $DB;
  
    $vandale = "";
    $vandale2 = "Vandale!"; $vanurl = "vandale";
    /* Vandale prüfen */


    $sql = "SELECT count(id) FROM ipp_vandalen WHERE user LIKE '$user'";
    $result = $DB->query($sql);
    $row = $result->fetch_row();
  	if($row[0] > 1) {
      $vandale = " class=\"wp_vandale\""; $vandale2 = "Kein Vandale!"; $vanurl = "vandale2";
    }
    /* Wenn mehr als 1000 Zeichen entfernt auch rosa machen */
    if ($change <= -300) { $vandale = " class=\"wp_vandale\""; }

    if ($change > 0) { $change = "+" . $change; }
    $link = "mark.php?id=$id";
    $contr_link = "http://de.wikipedia.org/w/wiki.phtml?title=Spezial:Contributions&amp;target=" . urlencode(str_replace(" ", "_", $user));

    $neu1 = "";
    if (strpos($flag, "N") !== false) { $neu1 = " &bull; <span class=\"wp_neu\">NEU</span>"; }

    //echo $time;

    $time = strtotime($time);

    $time += 60 * 60 * 1;

    $time1 = date("H:i", $time);
    $date1 = date("d.m.Y", $time);
    
    $spam = number_format($spam * 100, 1);
    
    $seite = htmlspecialchars($seite);
    $change = htmlspecialchars($change);
    
    
    print "  <tr$vandale>\n";
    print "    <td style=\"border-top:1px dotted #000000; text-align:center;;\"><b>$time1</b></td>\n";
    print "    <td style=\"border-top:1px dotted #000000; vertical-align:top;\"><a href=\"$link\" style=\"display:block; width:100%;\"><b>" . $seite . "</b>$neu1</a></td>\n";
    print "    <td style=\"border-top:1px dotted #000000; text-align:center; vertical-align:top;\">$change</td>\n";
    print "    <td style=\"border-top:1px dotted #000000; text-align:center; vertical-align:top;\">$spam%</td>\n";
    print "    <td style=\"border-top:1px dotted #000000;\"><a href=\"$contr_link\">$user</a></td>\n";
    print "  </tr>\n";
    print "  <tr$vandale>\n";
    print "    <td style=\"text-align:center; font-size:8pt;\">$date1</td>\n";

    $beschreibung = htmlspecialchars($beschreibung);
    $beschreibung = str_replace("/* ", "<span class=\"wp_absatz\">", $beschreibung);
    $beschreibung = str_replace(" */", "</span>", $beschreibung);
    if ($beschreibung == "") { $beschreibung = "&nbsp;"; }
    print "    <td colspan=\"3\"><a href=\"$link\" style=\"display:block; width:100%; text-decoration:none;\"><i>" . $beschreibung . "</i></a></td>\n";
    print "    <td>IP-Patrol: <a href=\"index.php?user=$user\">Beitr&auml;ge</a>, <a href=\"$vanurl.php?ip=$user\">$vandale2</a></td>\n";
    print "  </tr>\n";
  }

  // Kein User angegeben...
  if ($user == "")
  {
    $sql = "SELECT count(id) FROM $table";
    $result2 = $DB->query($sql);
    $row = mysqli_fetch_array($result2, MYSQL_NUM);
    mysqli_free_result($result2);
    $anzahl = $row[0];
  }
  else
  {
    // Userinfotext ausgeben
    print "  <b>Die IP $user hat folgende Artikel bearbeitet.</b><br />\n";
    print "  Zur&uuml;ck zur <a href=\"index.php\">Gesamtliste</a>.\n";
    print "  <br />&nbsp;<br />";

    $result2 = $DB->query("SELECT count(id) FROM $table WHERE user = '$user';");
    $row = mysqli_fetch_array($result2, MYSQL_NUM);
    mysqli_free_result($result2);
    $anzahl = $row[0];
  }

?>

  <!-- yet another ie fix :/ -->
  <div style="width:100%;">
  <table cellpadding="0" cellspacing="0" style="border-width:0; width:100%; font-family:Verdana,sans-serif; font-size:10pt;">
  <tr>
    <td style="width:112px; text-align:center;"><b>Uhrzeit</b>
<?php
  $bild1 = "pg_o.png"; $bild2 = "pg_u.png";
  if (($si == "id") && ($so == "ASC")) { $bild1 = "ps_o.png"; }
  if (($si == "id") && ($so == "DESC")) { $bild2 = "ps_u.png"; }
?>
    
    <a href="index.php?si=id&amp;so=ASC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild1; ?>" alt="&Auml;lteste zuerst" width="10" height="10" /></a><a
    href="index.php?si=id&amp;so=DESC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild2; ?>" alt="Neueste zuerst" width="10" height="10" /></a></td>
    <td style="text-align:center;">

<?php
 if($user != "")
 {
   print "<b>Artikel</b>";
 }
 elseif($neu == "1")
 {
   print "<b>Neue Artikel</b><br />(<a href=\"index.php?start=0&amp;si=$si&amp;so=$so&amp;neu=0\">Alle &Auml;nderungen</a> | <a href=\"index.php?start=0&amp;si=$si&amp;so=$so&amp;neu=2\">Nur &Auml;nderungen</a>)";
 }
 elseif($neu == "2")
 {
   print "<b>Nur &Auml;nderungen</b><br />(<a href=\"index.php?start=0&amp;si=$si&amp;so=$so&amp;neu=0\">Alle &Auml;nderungen</a> | <a href=\"index.php?start=0&amp;si=$si&amp;so=$so&amp;neu=1\">Nur Neue</a>)";
 }
 else
 {
   print "<b>Alle Artikel</b><br />(<a href=\"index.php?start=0&amp;si=$si&amp;so=$so&amp;neu=1\">Nur Neue</a> | <a href=\"index.php?start=0&amp;si=$si&amp;so=$so&amp;neu=2\">Nur &Auml;nderungen</a>)";
 }
?>

    </td>
    <td style="width:140px; text-align:center;"><b>Ver&auml;nderung</b>
<?php
  $bild1 = "pg_o.png"; $bild2 = "pg_u.png";
  if (($si == "change") && ($so == "ASC")) { $bild1 = "ps_o.png"; }
  if (($si == "change") && ($so == "DESC")) { $bild2 = "ps_u.png"; }
?>
    
    <a href="index.php?si=change&amp;so=ASC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild1; ?>" alt="Kleinste zuerst" width="10" height="10" /></a><a
    href="index.php?si=change&amp;so=DESC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild2; ?>" alt="Gr&ouml;&szlig;te zuerst" width="10" height="10" /></a></td>
    <td style="width:75px; text-align:center;"><b>SPAM</b>
<?php
  $bild1 = "pg_o.png"; $bild2 = "pg_u.png";
  if (($si == "spam") && ($so == "ASC")) { $bild1 = "ps_o.png"; }
  if (($si == "spam") && ($so == "DESC")) { $bild2 = "ps_u.png"; }
?>
    <a href="index.php?si=spam&amp;so=ASC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild1; ?>" alt="Absteigend sortieren" width="10" height="10" /></a><a
    href="index.php?si=spam&amp;so=DESC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild2; ?>" alt="Aufsteigend sortieren" width="10" height="10" /></a></td>

    <td style="width:250px; text-align:center;"><b>IP</b>
<?php
  $bild1 = "pg_o.png"; $bild2 = "pg_u.png";
  if (($si == "user") && ($so == "ASC")) { $bild1 = "ps_o.png"; }
  if (($si == "user") && ($so == "DESC")) { $bild2 = "ps_u.png"; }
?>
    
    <a href="index.php?si=user&amp;so=ASC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild1; ?>" alt="Absteigend sortieren" width="10" height="10" /></a><a
    href="index.php?si=user&amp;so=DESC&amp;neu=<?php print $neu; ?>"><img src="<?php print $bild2; ?>" alt="Aufsteigend sortieren" width="10" height="10" /></a></td>
  </tr>
  
<?php

  if ($si == "change") { 
    $si = "changesize"; 
  }
  //  if ($si == "id") { $si = "time"; }
  $si = mysql_escape_string($si);
  $so = mysql_escape_string($so);
  $start = mysql_escape_string($start);
  if ($start < 1) {
    $start = 0; 
  }
  $query = "SELECT * FROM `$table` ORDER BY `$si` $so LIMIT $start,50";
  if ($neu == "1") { $query = "SELECT * FROM $table WHERE new NOT LIKE '0' ORDER BY '$si' $so LIMIT $start,50"; }
  if ($neu == "2") { $query = "SELECT * FROM $table WHERE new LIKE '0' ORDER BY '$si' $so LIMIT $start,50"; }
  if ($user != "") { $query = "SELECT * FROM $table WHERE user = \"$user\" ORDER BY '$si' $so LIMIT $start,50"; }
  $result = $DB->query($query);
  while ($row = mysqli_fetch_array($result, MYSQL_ASSOC))
  {
    ShowZeile($row["id"], urldecode($row["title"]), $row["newid"], $row["changesize"], urldecode($row["user"]), urldecode($row["comment"]), $row["new"], $row["minor"], $row["time"], $row["spam"]);
  }

  mysqli_free_result($result);
?>

  </table>
  </div>

<?php
  if ($si == "changesize") { $si = "change"; }

  if ($anzahl == 0)
  {
    print "<b>Derzeit liegen keine ungepr&uuml;ften Ver&auml;nderungen vor.</b>";
  }
  if ($start > 0)
  {
    $neustart = $start - 50;
    if($neustart < 0) { $neustart = 0; }
    print "<br /><a href=\"index.php?start=$neustart&amp;si=$si&amp;so=$so&amp;neu=$neu\">&lt;&lt; Zu den vorherigen 50 &Auml;nderungen</a>\n";
  }
  if ($anzahl-$start > 50)
  {
    $neustart = $start + 50;
    print "<br /><a href=\"index.php?start=$neustart&amp;si=$si&amp;so=$so&amp;neu=$neu\">&gt;&gt; Zu den n&auml;chsten 50 &Auml;nderungen</a>\n";
  }

?>

  <p><span style="text-decoration:underline;">Hinweise</span>:
  &quot;Ver&auml;nderung&quot; zeigt die Anzahl der Ver&auml;nderung in Zeichen.
  Wenn ein Versionsunterschied (Klick auf Artikelnamen) aufgerufen wird, wird dieser
  automatisch aus der Liste entfernt (beim n&auml;chsten Reload der Seite sichtbar).
  Sollte die Bearbeitung noch nicht gesichtet sein, dies bitte auch gleich nachholen.
  Unsichere F&auml;lle daher bitte selber merken und in der Wikipedia zur Diskussion
  bringen. Mit dem Link &quot;Beitr&auml;ge&quot; k&ouml;nnen alle Beitr&auml;ge
  einer IP aufgerufen werden. Mit &quot;Vandale!&quot; kann ein Nutzer als Vandale
  gekennzeichnet werden - seine Beitr&auml;ge werden dann zuk&uuml;nftig rot
  dargestellt.<br />

  Diese Liste basiert auf dem Wikimedia EventStream.
  Jeder Artikel wird hier nur einmal gelistet, auch wenn er von mehreren IPs bearbeitet
  wurde, da der Link zum Unterschied immer den Eintrag in dieser Liste mit der neuesten
  Version vergleicht und somit auch alle nachfolgenden &Auml;nderungen gepr&uuml;ft
  werden.<br />
  Von Benutzern nach IP-&Auml;nderungen durchgef&uuml;hrte Bearbeitungen mit dem Inhalt
  &quot;revert&quot; oder &quot;&Auml;nderungen von Benutzer:abc r&uuml;ckg&auml;ngig
  gemacht und letzte Version von Benutzer:abc wiederhergestellt&quot; werden
  automatisch aus dieser Liste entfernt.<br />
  Kontakt: freddy2001@wikipedia.de</p>

<?php
  design_unten("<a href='https://de.wikipedia.org/wiki/Spezial:Letzte_%C3%84nderungen' target='_blank'  >Wikipedia:Letzte &Auml;nderungen</a>");
?>
