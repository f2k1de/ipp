<?php

  require("../db.inc.php");
  $userdb = db_user_data();
  $wikidb = db_dewiki();

  $th = $_REQUEST['threshold'];
  $th2 = $_REQUEST['thresholdipp'];
  if ($th == '') { $th = 50; }
  if ($th2 == '') { $th2 = 1.01; }

  $lasttime1 = time();
  $lasttime2 = time();
  $lasttime3 = time();
  $lastsended = time();

  print "                                                                                                                    \n";
  flush();
  ob_flush();

  while(1)
  {
    $query = "SELECT * FROM npp_new_pages WHERE time>$lasttime1 ORDER BY spam DESC LIMIT 0,1";
    $result = mysql_query($query, $userdb);
    if (mysql_num_rows($result) > 0)
    {
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      if (($row['spam'] * 100) >= $th)
      {
        print "<article><title>" . $row['title'] . "</title><spam>" . $row['spam'] . "</spam></article>\n";
        $lastsended = time();
      }
      $lasttime1 = $row['time'];
    }

    if ($th2 <= 1.00)
    {
      $query = "SELECT * FROM ipp_changes WHERE time>$lasttime2 AND spam>=" . mysql_escape_string($th2) . " AND changesize<250 LIMIT 0,1;";
      $result = mysql_query($query, $userdb);
      if (mysql_num_rows($result) > 0)
      {
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        print "<article what='ipp'><title>" . $row['title'] . "</title><link>" . $row['changelink'] . "</link><spam>" . $row['spam'] . "</spam></article>\n";
        $lastsended = time();
        $lasttime2 = $row['time'];
      }
    }
   
    $query = "SELECT * FROM page, revision
      WHERE page_namespace=3 
      AND page_latest=rev_id
      AND page_title REGEXP '^[0-9]+\\\\.[0-9]+\\\\.[0-9]+\\\\.[0-9]+$'
      AND rev_timestamp > " . strftime("%Y%m%d%H%M%S", $lasttime3 - 24*60*60) . " 
      AND rev_timestamp < " . strftime("%Y%m%d%H%M%S", time() - 24*60*60) . " 
      AND page_id NOT IN (SELECT tl_from FROM templatelinks WHERE tl_namespace=10 AND (tl_title='Statische_IP' OR tl_title='Bitte_behalten' OR tl_title='IP-Sperrung'))
      AND page_title NOT IN (SELECT page_title FROM page WHERE page_namespace=2 AND page_title REGEXP '^[0-9]+\\\\.[0-9]+\\\\.[0-9]+\\\\.[0-9]+$')
      AND ((rev_parent_id != 0) OR (rev_user_text != page_title))
      LIMIT 0,10;";
    $result = mysql_query($query, $wikidb);
    print "query ready\n";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
      print "<article><title>BD:" . $row['page_title'] . "</title><spam>1</spam></article>\n";
      $lastsended = time();
      $lasttime3 = time();
    }

    if ((time() - $lastsended) > 10)
    {
      print "\n";
      $lastsended = time();
    }

    print "                                                                                                                    \n";    
    flush();
    ob_flush();
    sleep(1);
  }

?>
