<?php

define('checkaccess', TRUE);
include '../secure.php';


if(isset($_COOKIE['kypident'])) {
	$ident = $_COOKIE['kypident'];
} else {
    $ident = 'default';
}

if (!empty($_GET['ident'])) {
	$ident = htmlspecialchars($_GET['ident'], ENT_QUOTES, 'UTF-8');
	setcookie("kypident",$ident, time() + (10 * 365 * 24 * 60 * 60)); // 10y
} 

echo "
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta name='theme-color' content='#000'>
<title>hAlarm</title>
<META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW'>
<link rel='icon' type='image/x-icon' href='../images/favicon.ico'>
<link rel='stylesheet' href='../style.css' type='text/css'>
</head>
<body>
<br>You are about to assign a Keypad identification, see config/automate.php for the whole configuration
<br><br>
<form action='kypident.php' method='GET'>
<input type='text' name='ident' value=\"$ident\" size=20>
<INPUT TYPE='button' onClick=\"location.href='index.php'\" value='&#x21A9; Back'>

<INPUT type='submit' value='&#x2713 Save'>
</form>
</body>
</html>";
?>
