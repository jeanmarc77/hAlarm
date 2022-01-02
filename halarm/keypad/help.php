<?php
/**
 * /srv/http/halarm/keypad/help.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config.php';
include '../config/memory.php';
include '../config/lang.php';
include '../secure.php';
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
<script src='https://code.jquery.com/jquery-3.4.1.min.js' integrity='sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=' crossorigin='anonymous'></script>
</head>
<body>
<table width='80%' border=0 cellspacing=0 cellpadding=0 align='left'>
<tr><td><h3>tmpfs</h3>
Make sure you only use a tmpfs, a temporary filesystem that resides in memory.<br>";
$datareturn = null;
$datareturn = exec("df -h |grep $TMPFS | grep tmpfs");
if ($datareturn) {
	echo "<img src='../images/sign-check.png' width=24 height=24 border=0> $TMPFS is ok ";
} else {
	echo "<img src='../images/sign-error.png' width=24 height=24 border=0> $TMPFS is -NOT- OK ";
}
$datareturn = file_put_contents($TMPFS. '/test', 'test');
if ($datareturn) {
	echo "<img src='../images/sign-check.png' width=24 height=24 border=0> ok to write";
	unlink($TMPFS. '/test');
} else {
	echo "<img src='../images/sign-error.png' width=24 height=24 border=0> -NOT- OK to write";
}
echo "
</td></tr>
<tr><td valign='top'><h3>/var/lock permissions</h3>";
$datareturn = file_put_contents('/var/lock/test', 'test');
$alt = substr(sprintf('%o', fileperms('/var/lock')), -4);
if ($datareturn) {
	echo "<img src='../images/sign-check.png' width=24 height=24 border=0 alt='$alt'> ok to write";
	unlink('/var/lock/test');
} else {
	echo "<img src='../images/sign-error.png' width=24 height=24 border=0 alt='$alt'> -NOT- OK to write";
}
$whoami = exec('whoami');
$CURDIR = dirname(dirname(__FILE__));
echo "<br><br>Some distros have 755 by default, some application need to write port lock in there.
<br>Change permissions to 777 (e.g. 'cp /usr/lib/tmpfiles.d/legacy.conf /etc/tmpfiles.d/' and 'nano /etc/tmpfiles.d/legacy.conf') and reboot.
</td></tr>
<tr><td valign='top'><h3>Hardware and communication apps. rights</h3>
<br>- Grant the permission to execute your com. apps. Locate them with 'whereis mycomapp' and 'chmod a+x /pathto/mycomapp.py'.<br>
<br>- Allow the access the communication ports as ";
$whoami = exec('whoami');
echo "$whoami user</b>. $whoami currently belong to those groups: ";
$datareturn = exec("groups $whoami");
echo "$datareturn
<br>The peripherals are usually owned by the uucp or dialout group, check (e.g. 'ls -al /dev/ttyUSB0'), add your user to the group: (e.g. 'usermod -a -G uucp $whoami')<br><br>";
echo "- Since PHP 7.4 there is hardening options.";
echo ' Current version is ' . PHP_VERSION;
echo ". Allow to use your com. devices by setting to PrivateDevices=false in php-fpm.service. (e.g. systemctl edit --full php-fpm.service)
<br>
<br> After change you need to restart php and your webserver. (e.g. 'systemctl restart php-fpm' and 'systemctl restart nginx')
</td>
</tr>
<tr><td>
<br><INPUT TYPE='button' onClick=\"location.href='index.php'\" value='&#x21A9; Back'>
<br><div align=center><a href='../kiva.html'>hAlarm is free !</a></div></tr></td>
</table>
</body>
</html>";
?>
