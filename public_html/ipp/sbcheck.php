<?php
	include("design.inc.php");
	//include("../db.inc.php");
	//$wikidb = db_dewiki();

	design_top("Stimmberechtigungscheck");

	class sbcheck {
		public $sblink = "https://tools.wmflabs.org/stimmberechtigung/";
		function __construct() {
			$title = $_GET['title'];

			if($title != "") {
				if($this->seiteVorhanden($title) == false) {
					echo "Hier kannst du die Stimmberechtigung zu einer Adminkandidatur abfragen. Kopiere dazu den Link in das untenstehende Feld.<br />\nBitte habe etwas Geduld, währed die Abfrage ausgeführt wird.";
					echo "<br /><span style='color:red'>Die Seite $title existiert nicht!</span>";
					echo "<form action='sbcheck.php' method='get'><input type='text' name='title' value='Wikipedia:Adminkandidaturen/' style='width:40vw;'><input type='submit' value='Los gehts'></form>";
					exit;
				}
			}

			if ($title == "") {
				echo "Hier kannst du die Stimmberechtigung zu einer Adminkandidatur abfragen. Kopiere dazu den Link in das untenstehende Feld.<br />\nBitte habe etwas Geduld, währed die Abfrage ausgeführt wird.";
				
				echo "<form action='sbcheck.php' method='get'><input type='text' name='title' value='Wikipedia:Adminkandidaturen/' style='width:40vw;'><input type='submit' value='Los gehts'></form>";
				exit;
			} 

			echo "<a href='sbcheck.php'>&lt; Zurück zur Übersicht</a><br />";
			echo "Für <a href='https://de.wikipedia.org/wiki/$title'>$title</a>";

			$users = $this->getUsersAPI($title);
			$this->getStimmberechtigung($users);
		}

		function __destruct() {
			design_unten("<a href='https://de.wikipedia.org/wiki/Wikipedia:Stimmberechtigung'>Wikipedia:Stimmberechtigung</a>");
		}

		public function seiteVorhanden($title) {
			$result = $this->httpRequest("action=query&format=php&titles=" . urlencode($title) . "&prop=info&formatversion=2");
			$result = unserialize($result);
			if(!isset($result['query']['pages']['0']['missing'])) {
					return true;
			} else {
					return false;
				}

		}

	 	public function readSection($Title, $Section) {
  			try {
				$result = $this->httpRequest('action=query&prop=revisions&format=php&rvprop=content&rvlimit=1&rvcontentformat=text%2Fx-wiki&rvdir=older&rvsection=' . urlencode($Section) . '&titles=' . urlencode($Title));
			} catch (Exception $e) {
				throw $e;
			}
			$Answer = strstr ($result, "s:1:\"*\";");
			$Answer = substr ($Answer, 8);
			$Answer = strstr ($Answer, "\"");
			$Answer = substr ($Answer, 1);
			$Answer = strstr ($Answer, "\";}}}}}}", true);
			return  $Answer;
		}


		public function getStimmberechtigung($users) {
			$i = 0;
			echo "<table>";

			while($i < count($users)) { 
					$user = $users[$i];
					$userline = "";

					$userline .= "<tr>";
					$userline .= "<td><a href='https://de.wikipedia.org/wiki/User:" . $user . "'>" . $user ."</a></td>";
					$link = $this->sblink . "?user=" . urlencode($user);
					$userline .= "<td><a href='$link'>SB</a></td>";


					$ch = curl_init();
        			curl_setopt($ch, CURLOPT_URL, $link);
					curl_setopt($ch, CURLOPT_USERAGENT, "https://tools.wmflabs.org/freddy2001/ipp/sbcheck.php");					
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$c = curl_exec($ch);
					//echo $c;
        			curl_close($ch);      
		
					//$c = file_get_contents($link);
					$stimmberechtigt = stripos($c, 'Allgemeine_Stimmberechtigung">(neu)</a>: stimmberechtigt') !== false;
					$benutzerexistiert = stripos($c, 'Benutzer "' . $user . '" existiert nicht');
					
					

			
					if ($stimmberechtigt)
						$userline .= "<td style='color:green;'>stimmberechtigt</td>";
					else
						$userline .= "<td style='color:red;'>nicht stimmberechtigt</td>";

					$userline .= "</tr>\n";

					if($benutzerexistiert == false AND $user != "") {
						echo $userline;
					}

					$i++;
			} 

			echo "</table>";
		}

		public function getSections($taget) {
			$i = 1;
			$users = array();
			while($i < 3) {
				$page = $this->readSection($taget, $i + 1);
				$user = explode("\n",$page);
				$users = array_merge($users, $user);
				$i++;
			}
			return $users;

		}

		public function getUsersAPI($taget) {
			$users = $this->getSections($taget);
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


	protected function httpRequest($Arguments, $Method = 'GET', $Target = 'w/api.php') {
		$baseURL = 'https://de.wikipedia.org/' . $Target;
		$Method = strtoupper($Method);

   		$curl = curl_init();
    	if ($curl === false) {
      		throw new Exception('curl initialization failed.');
		} else {
	      	$this->curlHandle = $curl;
		}

		if ($Arguments != '') {
		if ($Method === 'POST') {
			$requestURL = $baseURL;
			$postFields = $Arguments;
		} elseif ($Method === 'GET') {
			$requestURL = $baseURL . '?' .
					$Arguments;
		} else 
			throw new Exception('unknown http request method.');
		}
		if (!$requestURL) 
		throw new Exception('no arguments for http request found.');
		// set curl options
		curl_setopt($this->curlHandle, CURLOPT_USERAGENT, "https://tools.wmflabs.org/freddy2001/ipp/sbcheck.php");
		curl_setopt($this->curlHandle, CURLOPT_URL, $requestURL);
		curl_setopt($this->curlHandle, CURLOPT_ENCODING, "UTF-8");
		curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curlHandle, CURLOPT_COOKIEFILE, realpath('Cookies' . "job" . '.tmp'));
		curl_setopt($this->curlHandle, CURLOPT_COOKIEJAR, realpath('Cookies' . "job" . '.tmp'));
		// if posted, add post fields
		if ($Method === 'POST' && $postFields != '') {
		curl_setopt($this->curlHandle, CURLOPT_POST, 1);
		curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $postFields);
		} else {
		curl_setopt($this->curlHandle, CURLOPT_POST, 0);
		curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, '');
		}
		// perform request
		$rqResult = curl_exec($this->curlHandle);
		if ($rqResult === false)
		throw new Exception('curl request failed: ' . curl_error($this->curlHandle));
		return $rqResult;
	 }
	

	}

$page = new sbcheck();
?>