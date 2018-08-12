<?php
	include("design.inc.php");

	design_top("Stimmberechtigungscheck");

	$baseurl = "https://de.wikipedia.org/w/api.php";

	if(isset($_GET['title'])) {
		$title = $_GET['title'];
	} else {
		$title = "";
	}

	if($title != "") {
		if(seiteVorhanden($title) == false) {
			echo "Hier kannst du die Stimmberechtigung zu einer Adminkandidatur abfragen. Kopiere dazu den Link in das untenstehende Feld.<br />\nBitte habe etwas Geduld, währed die Abfrage ausgeführt wird.";
			echo "<br /><span style='color:red'>Die Seite $title existiert nicht!</span>";
			echo "<form action='sbcheck.php' method='get'><input type='text' name='title' value='Wikipedia:Adminkandidaturen/' style='width:40vw;'><input type='submit' value='Los gehts'></form>";
			exit;
		}
	}

	if ($title == "") {
		echo "Hier kannst du die Stimmberechtigung zu einer Adminkandidatur abfragen. Kopiere dazu den Link in das untenstehende Feld.<br />\nBitte habe etwas Geduld, währed die Abfrage ausgeführt wird.";
		
		echo "<form action='sbcheck.php' method='get'><input type='text' name='title' value='Wikipedia:Adminkandidaturen/' style='width:40vw;'>";
		//echo "<br />Wann ist die Kandidatur gestartet?  <input type='text' name='day' value='" . date ('d') . "' size='2' maxlength='2'></label>&nbsp;<label>Monat:&nbsp;<input type='text' name='mon' value='" . date('m') . "' size='2' maxlength='2' /></label>&nbsp;<label>Jahr:&nbsp;<input type='text' name='year' value='" . date('Y') . "' size='4' maxlength='4' /></label>&nbsp;<label>Uhrzeit:&nbsp;<input type='text' name='hour' value='" . date('H') . "' size='2' maxlegth='2' />&nbsp;:&nbsp;<input type='text' name='min' value='" . date('i') . "' size='2' maxlength='2' /></label></p>";
		echo "<input type='submit' value='Los gehts'></form>";
		exit;
	} 

	echo "<a href='sbcheck.php'>&lt; Zurück zur Übersicht</a><br />";
	echo "Für <a href='https://de.wikipedia.org/wiki/$title'>$title</a>";

	$users = getUsersAPI($title);
	$startdate = getStartDate($title);
	getStimmberechtigung($users, $startdate);

	function getStartDate($title) {
		global $baseurl;
		$url = $baseurl . 
		"?action=query&prop=revisions&titles=" . 
		urlencode($title) . 
		"&rvlimit=1&rvprop=timestamp&rvdir=newer&formatversion=2&format=json";
		$content = file_get_contents($url);
		$date = json_decode($content, true);
		$date = $date['query']['pages'][0]['revisions'][0]['timestamp'];
		$datearr = [
			'year' => substr($date, 0, 4),
			'month' => substr($date, 5, 2),
			'day' => substr($date, 8, 2),
			'hour' => substr($date, 11, 2),
			'min' => substr($date, 14, 2),
		];
		return $datearr;
	}

	function seiteVorhanden($title) {
		global $baseurl;
		$result = file_get_contents($baseurl . 
		"?action=query&format=php&titles=" . 
		urlencode($title) . 
		"&prop=info&formatversion=2");
		$result = unserialize($result);
		if(!isset($result['query']['pages']['0']['missing'])) {
			return true;
		} else {
			return false;
		}
	}

	function readSection($Title, $Section) {
		global $baseurl;
		$result = file_get_contents($baseurl . '?action=query&prop=revisions&format=php&rvprop=content&rvlimit=1&rvcontentformat=text%2Fx-wiki&rvdir=older&rvsection=' . urlencode($Section) . '&titles=' . urlencode($Title));
		$Answer = strstr ($result, "s:1:\"*\";");
		$Answer = substr ($Answer, 8);
		$Answer = strstr ($Answer, "\"");
		$Answer = substr ($Answer, 1);
		$Answer = strstr ($Answer, "\";}}}}}}", true);
		return  $Answer;
	}


	function getStimmberechtigung($users, $startdate) {
		$sblink = "https://tools.wmflabs.org/stimmberechtigung/";

		$i = 0;
		echo "<table>";

		while($i < count($users)) { 
			$user = $users[$i];
			$userline = "";

			$userline .= "<tr>";
			$userline .= "<td><a href='https://de.wikipedia.org/wiki/User:" . $user . "'>" . $user ."</a></td>";

			$link = $sblink . "?user=" . urlencode($user) . "&day=" . $startdate['day'] . "&mon=" . $startdate['month'] . "&year=" . $startdate['year'] . "&hour=" . $startdate['hour'] . "&min=" . $startdate['min'];
			$userline .= "<td><a href='$link'>SB</a></td>";

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $link);
			curl_setopt($curl, CURLOPT_USERAGENT, 'IPP sbcheck');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			$c = curl_exec($curl);
			curl_close($curl);

			if(stristr($c, 'Allgemeine_Stimmberechtigung">(neu)</a>: stimmberechtigt') !== false) {
				$stimmberechtigt = true;
			} elseif(stristr($c, 'Allgemeine_Stimmberechtigung">(alt)</a>: stimmberechtigt') !== false) {
				$stimmberechtigt = true;
			} else {
				$stimmberechtigt = false;
			}

			$benutzerexistiert = stripos($c, 'Benutzer "' . $user . '" existiert nicht');

			if ($stimmberechtigt === true) {
				$userline .= "<td style='color:green;'>stimmberechtigt</td>";
			} else {
				$userline .= "<td style='color:red;'>nicht stimmberechtigt</td>";
			}

			$userline .= "</tr>\n";

			if($benutzerexistiert == false AND $user != "") {
				echo $userline;
			}
			$i++;
		} 
		echo "</table>";
	}

	function getSections($taget) {
		$i = 1;
		$users = array();
		while($i < 3) {
			$page = readSection($taget, $i + 1);
			$user = explode("\n",$page);
			$users = array_merge($users, $user);
			$i++;
		}
		return $users;
	}

	function getUsersAPI($taget) {
		$users = getSections($taget);
		$i = 0;
		$return = "";

		while(count($users) > $i) {
			$users[$i] = substr($users[$i], strpos(strtoupper($users[$i]),strtoupper("[[Benutzer")));
			$users[$i] = substr($users[$i], strpos(strtoupper($users[$i]),strtoupper("[[User")));
			$users[$i] = substr($users[$i], strpos(strtoupper($users[$i]),strtoupper("[[Spezial:Beiträge")));
			$users[$i] = substr($users[$i], strpos($users[$i], ":") + 1);
			$users[$i] = substr($users[$i], 0, strpos($users[$i],"|"));
			$return .= $users[$i] . "<br />";
			$i++;
		}
		return $users;
	}

	design_unten("<a href='https://de.wikipedia.org/wiki/Wikipedia:Stimmberechtigung'>Wikipedia:Stimmberechtigung</a>");
?>