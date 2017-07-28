<?php
	$id = $_GET['id'];
	require("../../ipp/DBCore.php");
	$DB = new DBCore ('db@localhost', 's53094__ippdata');
  
	$table = "ipp_changes";
	$table_old = "ipp_changes_old";

	// IP prüfen
	$sql = "SELECT * FROM `$table` WHERE `id` = '" .  intval($id) . "'";
	$result = $DB->query($sql);
	print_r(mysqli_num_rows($result));
	if(mysqli_num_rows($result)== 1) {
		$sql = "DELETE FROM `$table` WHERE `id` = '" .  intval($id) . "'";
		$DB->query($sql);
		$sql = "INSERT INTO `$table_old` (`id`) VALUES ('" . intval($id) . "')";
		$DB->query($sql);

		header("Location: https://de.wikipedia.org/wiki/Special:Diff/" . intval($id));
		exit;

	} else {
		header("Content-Type: text/html; charset=utf-8");
		echo "Wurde bereits schon von jemand anderen überprüft!";
	}
   
?>