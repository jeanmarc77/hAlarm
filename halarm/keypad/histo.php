<?php
/**
 * /srv/http/halarm/keypad/histo.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../secure.php';
include '../config/lang.php';

$events   = '';
$filename = "../data/events.txt";
if (file_exists($filename)) {
	$events = file_get_contents($filename);
} else {
	$events = 'no event file found';
}

echo "
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta name='theme-color' content='#000'>
<title>hAlarm LAN</title>
<META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW'>
<link rel='icon' type='image/x-icon' href='../images/favicon.ico'>
<link rel='stylesheet' href='../style.css' type='text/css'>
</head>
<body>
<h2>$lgHISTO</h2>
<textarea style='background-color: #DCDCDC' cols=40 rows=28>$events</textarea>
<div align=left><br><INPUT TYPE='button' onClick=\"location.href='index.php'\" value='&#x21A9; $lgBACK'></div>
</body>
</html>";
?>
