<?php
  require("../db.inc.php");
  $userdb = db_user_data();
  $table = "ipp_vandalen";
  $table_old = "ipp_vandalen_old";

  $ip = $_GET['ip'];

  // Löschen
  mysql_query("DELETE FROM `$table_old` WHERE ip = '" . mysql_escape_string($ip) . "'", $userdb);
  mysql_query("DELETE FROM `$table` WHERE ip = '" . mysql_escape_string($ip) . "'", $userdb);

  // Einfügen
  mysql_query("INSERT INTO `$table` (ip, time) VALUES ('" . mysql_escape_string($ip) . "', '" . time() . "')", $userdb);

  if (!isset($_GET['dnr']))
    header("Location: index.php");

?>
