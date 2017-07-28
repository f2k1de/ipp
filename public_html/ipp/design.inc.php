<?php
  date_default_timezone_set("UTC");

  function design_top($titel)
  {
  	// META-Keywords und Description
    global $description, $keywords, $user_javascript, $user_css, $user_onload;
    if ($description != "") { $description = "<meta name=\"description\" content=\"" . utf8_encode($description) . "\" />\n"; }
    if ($keywords != "") { $keywords = "<meta name=\"keywords\" content=\"" . utf8_encode($keywords) . "\" />\n"; }

  	print '<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
' . $description . $keywords . '
<link rel="stylesheet" type="text/css" href="wikipedia.css" title="main" media="screen, projection" />
<link rel="stylesheet" type="text/css" href="druck.css" title="main" media="print" />

<title>' . $titel . '</title>

' . $user_javascript . '
' . $user_css . '
</head>

<body'. $user_onload . '>

<div id="info_balken">
  <div id="info_text">' . $titel . '</div>
</div>
<div id="url">' . $titel . '</div>

<div id="inhalt">

    ';
  }


  function design_unten($seealso)
  {
 //   setlocale(LC_TIME, "de_DE");
    $datum = strftime("%B %e, %Y %H:%M");
  
	print '

</div>

<div id="navi_balken">
  <p><b>See also:</b> ' . $seealso . '</p>
</div>

<div id="copyright">
' . $datum . ' UTC<br />
Freddy2001<br /><small>rewritten based on the <a href="https://tools.wmflabs.org/ipp/">ipp project</a> by Christian Thiele.</small>
</div>

</body>
</html>
	';
  }

?>
