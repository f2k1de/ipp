#!/usr/bin/php
<?php
include "DBCore.php";
$DB = new DBCore ('db@localhost', 's53094__ippdata');

$f = fopen("php://stdin", 'r' );
while( $line = fgets( $f ) ) 
{
	if(substr("$line", 0, 5) == "data:") {
		$l = json_decode(substr($line, 5), true);
	} else { 
		$l = "";
	}

	if($l != "") {
		if($l['wiki'] == "dewiki") {
			if($l['bot'] != 1) {
				if($l['type'] == "edit") {
					$revision = $l['revision']['new'];
					$oldid = $l['revision']['old'];
					$new = json_decode(curlRequest("de.wikipedia.org/w/api.php?action=query&prop=revisions&revids=" . $revision . "&rvprop=timestamp|user|comment|content|userid&formatversion=2&format=json"), true);
					$userid = $new['query']['pages'][0]['revisions'][0]['userid'];
					$old = json_decode(curlRequest("de.wikipedia.org/w/api.php?action=query&prop=revisions&revids=" . $oldid . "&rvprop=timestamp|user|comment|content|userid&formatversion=2&format=json"), true);
					$user = urlencode($new['query']['pages'][0]['revisions'][0]['user']);
					$comment = urlencode($new['query']['pages'][0]['revisions'][0]['comment']);
					$title = urlencode($new['query']['pages'][0]['title']);
					$newcontent = $new['query']['pages'][0]['revisions'][0]['content'];
					$oldcontent = $old['query']['pages'][0]['revisions'][0]['content'];

					if($userid == 0) {
						$anon = true;
					} else {
						$anon = false;
					}

					if($revision == 0) {
						$new = true;
					} else {
						$new = false;
					}

					if($l['minor'] == 1) {
						$minor = true;
					} else {
						$minor = false;
					}


					$changelenth = strlen($newcontent) - strlen($oldcontent);
					$score = 0;

					$scorewords = array("haha", "fuuuu", "!!!!!!", "??????", "lesb", "faggot", "hihi", "hahaha", "pimmel", "kack", "cool", "rape", "raping", "bla", "sex", "tits", "porn", "yeah", "yea", "yee", "balls", "weed", "arse", "stupid", "hello", "homosexual", "hallo", "idiot", "doof","crackhead", "bieber", "porn", "bold text", "italic text", "crap", "p3n1s", "omg", "lmao", "rofl", "blabla", "hure", "pr0n", "p0rn", "sh1t", "bullshit", "scheiße", "scheisse", "scheis", "piss", "pisse", "arsch", "arschkrampe", "fag", "shit", "gay", "bitch", "penis", "awesome", "gays", "faggots", "suck", "sucks", "boobs", "pussy", "cunt", "poop", "poo", "whore", "schwuchtel", "ficken", "hitler", "stinkt", "kaka", ":)", ":-)", ":P", ":D", "pen1s", "b1tch", "p1ss", "fuck", "asshole", "retard", "=)", "(:", "asshat", "fucknugget", "motherfucker", "fucktard", "arsehole", "swag", "butt", "-.-", "dipshit", "dipstick", "asswipe", "cunt", "twat", "yolo", "fuckoff", "fuck you", "wanker", "sucks dick", "lol", "boobs", "masturbate", "fucking", "nigger", "niga", "nigga", "derp", "derpy", "butthole", "tit", "bum", "arschloch", "titten", "hurensohn", "hurensöhne", "peenis", "peeenis", "peeeenis", "peeeeenis");

					for($i = 0; count($scorewords) > $i; $i++) {
						$oldscore = substr_count($oldcontent, $scorewords[$i]);
						$newscore = substr_count($oldcontent, $scorewords[$i]);

						$scoresum = $newscore - $oldscore;

						if($scoresum != 0) {
							$score = $score + $scoresum;
						}
					}

					$score = $score * 30;
					if($anon == true) {
						$sql  = "INSERT INTO `ipp_changes` (`id`, `title`, `oldid`, `newid`, `changesize`, `user`, `minor`, `new`, `comment`, `time`, `spam`) VALUES ('$revision', '$title ', '$oldid', '$revision', '$changelenth', '$user', '$minor', '$new', '$comment', CURRENT_TIMESTAMP, '$score');";
        					$DB->modify($sql);
					}
				}
			}			
		}
	}
}

  /** curlRequest
  * Sendet einen Curl-Request an eine beliebige Webseite
  * @author: Freddy2001 <freddy2001@wikipedia.de>
  * @param $url - URL der Seite
  * @param $https - true:benutze https, false: benutze http
  */

function curlRequest($url, $https = true) {
    if($https == true) {
      $protocol = 'https';
    } else {
      $protocol = 'http';
    }    
    $baseURL = $protocol . '://' . 
           $url;
    $Job = $baseURL;

    $curl = curl_init();

    if (!$baseURL) 
      throw new Exception('no arguments for http request found.');
    // set curl options
    curl_setopt($curl, CURLOPT_USERAGENT, "Cygnus");
    curl_setopt($curl, CURLOPT_URL, $baseURL);
    curl_setopt($curl, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_COOKIEFILE, realpath('Cookies' . $Job . '.tmp'));
    curl_setopt($curl, CURLOPT_COOKIEJAR, realpath('Cookies' . $Job . '.tmp'));
    curl_setopt($curl, CURLOPT_POST, 0);
    curl_setopt($curl, CURLOPT_POSTFIELDS, '');

    // perform request
    $rqResult = curl_exec($curl);
    if ($rqResult === false)
      throw new Exception('curl request failed: ' . curl_error($curl));
    return $rqResult;
  }

fclose( $f );
?>
