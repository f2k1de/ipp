<?php
  require("../db.inc.php");
  $userdb = db_user_data();
  $table = "ipp_vandalen";
  $table_old = "ipp_vandalen_old";

  $ip = $_GET['ip'];

  // L�schen
  mysql_query("DELETE FROM `$table` WHERE ip = '" . mysql_escape_string($ip) . "'", $userdb);

  // Einf�gen in Gel�scht-Tabelle
  mysql_query("INSERT INTO `$table_old` (ip, time) VALUES ('" . mysql_escape_string($ip) . "', '" . time() . "')", $userdb);

  Header("Location: index.php");

?>
