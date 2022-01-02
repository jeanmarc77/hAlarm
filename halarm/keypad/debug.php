<?php
/**
 * /srv/http/halarm/keypad/debug.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config.php';
include '../config/tampers.php';
include '../config/scenario0.php';
include '../config/memory.php';
include '../config/lang.php';
include '../secure.php';

if (!empty($_GET['startstop'])) {
	$startstop = $_GET['startstop'];
} else {
	$startstop = null;
}
if (file_exists('../scripts/alarm.pid')) {
	$PID = (int) file_get_contents('../scripts/alarm.pid');
	exec("ps -ef | grep $PID | grep alarm.php", $ret);
	if (!isset($ret[1])) {
		$PID = null;
	}
} else {
	$PID = null;
}
if (!is_null($PID)) {
	$cpu = exec("ps -p $PID -o %cpu | tail -1 | awk '{print $1}'");
	$mem = exec("ps -p $PID -o %mem | tail -1 | awk '{print $1}'");
} else {
	$cpu = 0;
	$mem = 0;
}
if (file_exists('../scripts/test.pid')) {
	$PIDt = (int) file_get_contents('../scripts/test.pid');
	exec("ps -ef | grep $PIDt | grep tester.php", $ret);
	if (!isset($ret[1])) {
		$PIDt = null;
		unlink('../scripts/test.pid');
		header('Location: debug.php');
	}
} else {
	$PIDt = null;
}

if ($startstop == 'start' || $startstop == 'stop') {
	$now = date($DATEFORMAT . ' H:i:s');
	if ($startstop == 'start' && is_null($PIDt)) {
		$command    = 'php ../scripts/tester.php' . ' >> ../data/alarm.err 2>&1 & echo $!; ';
		$PIDt       = exec($command);
		$stringData = "$now\tStarting test by WAN\n\n";
		file_put_contents('../scripts/test.pid', $PIDt);
		$stringData .= file_get_contents('../data/events.txt');
		file_put_contents('../data/events.txt', $stringData);
		$stringData = "$now\tStarting test by WAN ($PIDt)\n\n";
		file_put_contents('../data/alarm.err', $stringData, FILE_APPEND);
	}
	if ($startstop == 'stop') {
		if (!is_null($PIDt)) {
			$stringData = "$now\tStopping test by WAN\n\n";
			$command = exec("kill $PIDt > /dev/null 2>&1 &");
			unlink('../scripts/test.pid');
			$stringData .= file_get_contents('../data/events.txt');
			file_put_contents('../data/events.txt', $stringData);
			$stringData = "$now\tStopping debug by WAN ($PIDt)\n\n";
			file_put_contents('../data/alarm.err', $stringData, FILE_APPEND);
		}
		$PIDt = null;
		unlink($MEMORY);
	}
}
$cnti = count($I);
$cnto = count($O);
$cntt = count($T);
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
<script type='text/javascript'>
$(document).ready(function()
{
function updateit() {
	$.getJSON('../programs/programlive.php', function(json){
	var txt = JSON.stringify(json, null, 2); // spacing level = 2";

for ($i=0;$i<$cnti;$i++) {
	if (isset($I[$i])) {
		echo "
			if (typeof json['I$i'] === 'undefined') {
			document.getElementById('ival$i').innerHTML = '-I$i-';
			} else {
				if (json['I$i']==='on') {
				document.getElementById('ival$i').innerHTML = '<font color=\'#FF0000\'>' + json['I$i'] + '</font>';
				} else {
				document.getElementById('ival$i').innerHTML = json['I$i'];
				}
			}
			";
	}
}
for ($i=0;$i<$cnto;$i++) {
	if (isset($O[$i])) {
		echo "
			if (typeof json['O$i'] === 'undefined') {
			document.getElementById('oval$i').innerHTML = '-O$i-';
			} else {
				if (json['O$i']==='on') {
				document.getElementById('oval$i').innerHTML = '<font color=\'#FF0000\'>' + json['O$i'] + '</font>';
				} else {
				document.getElementById('oval$i').innerHTML = json['O$i'];
				}
			}
			";
	}
}
for ($i=0;$i<$cntt;$i++) {
	if (isset($T[$i])) {
		echo "
			if (typeof json['T$i'] === 'undefined') {
			document.getElementById('tval$i').innerHTML = '-T$i-';
			} else {
				if (json['T$i']==='on') {
				document.getElementById('tval$i').innerHTML = '<font color=\'#FF0000\'>' + json['T$i'] + '</font>';
				} else {
				document.getElementById('tval$i').innerHTML = json['T$i'];
				}
			}
			";
	}
}


echo "
	if (typeof json['status'] === 'undefined') {
	document.getElementById('rstat').innerHTML = '$lgOFF';
	} else {
	document.getElementById('rstat').innerHTML = json['status'];
	}
	document.getElementById('rSTAMP').innerHTML = json['stamp'];
	return json;
	})
}
updateit();
setInterval(updateit, 1000);
});
</script>

<table width='80%' height='80%' border=0 cellspacing=20 cellpadding=0 align='left'>
<tr><td>";

date_default_timezone_set($DTZ);
echo "<h1>Debugger <span id='rSTAMP'>--</span></h1>";
if (file_exists('../scripts/alarm.pid')) {
	$PID = (int) file_get_contents('../scripts/alarm.pid');
	$PIDd = date("$DATEFORMAT H:i:s", filemtime('../scripts/alarm.pid'));
	echo "<h3>hAlarm run as pid $PID since $PIDd - Usage : Memory $mem% CPU $cpu%</h3>";
} else if (file_exists('../scripts/test.pid')) {
	$PIDt = (int) file_get_contents('../scripts/test.pid');
	$PIDd = date("$DATEFORMAT H:i:s", filemtime('../scripts/test.pid'));
	echo "<h3>hAlarm test run as pid $PIDt since $PIDd - Usage : Memory $mem% CPU $cpu%</h3>";
} else {
	$PID = null;
	echo '<h3>hAlarm does not run</h3>';
}
echo "
<h3>System status : <span id='rstat'>--</span></h3>
<h3>Input(s)</h3>
<table border=1 cellspacing=0 cellpadding=5 align='left' bgcolor='#000'>
<tr>";
for ($i=0;$i<$cnti;$i++) {
	$val = htmlentities($I[$i]);
	if (isset($I[$i])) {
		echo "<td>$val</td><td><span id='ival$i'>--</span></td>";
	}
}
echo "</tr>
</table>
<br><br>
<h3>Output(s)</h3>
<table border=1 cellspacing=0 cellpadding=5 align='left' bgcolor='#000'>
";
for ($i=0;$i<$cnto;$i++) {
	$val = htmlentities($O[$i]);
	if (isset($O[$i])) {
		echo "<td>$val</td><td><span id='oval$i'>--</span></td>";
	}
}
echo "</tr>
</table>
<br><br>
<h3>Tamper(s)</h3>
<table border=1 cellspacing=0 cellpadding=5 align='left' bgcolor='#000'>
";
for ($i=0;$i<$cntt;$i++) {
	$val = htmlentities($T[$i]);
	if (isset($T[$i])) {
		echo "<td>$val</td><td><span id='tval$i'>--</span></td>";
	}
}
echo "
</tr>
</table>";
if (file_exists($KYPMEM)) {
echo "<br><br><h3>Keypad(s) seen : </h3>";	
	$data        = file_get_contents($KYPMEM);
	$kbmemarray    = json_decode($data, true);
	foreach (array_keys($kbmemarray) as $key) {
		if (isset($kbmemarray[$key]['UTC'])) {
			$nowutc = strtotime(date('Ymd H:i:s'));
			$kypt   = date($DATEFORMAT . ' H:i:s', $kbmemarray[$key]['UTC']);
			echo "<b>$key</b> $kypt ";
			if ($nowutc - $kbmemarray[$key]['UTC'] > 360) {
			echo '- lost';
			}
		}
	}
}
echo "
<br>
<br>
<h3>Errors log</h3>
<textarea style='background-color: #DCDCDC' cols='100' rows='10'>";
if (file_exists('../data/alarm.err')) {
	$lines = file('../data/alarm.err');
	foreach ($lines as $line_num => $line) {
		echo "$line";
	}
}
echo "</textarea>
<h3>Walk by</h3>
<textarea style='background-color: #DCDCDC' cols='100' rows='10'>";
if (file_exists('../data/halarm_test.txt')) {
	$lines = file('../data/halarm_test.txt');
	foreach ($lines as $line_num => $line) {
		echo "$line";
	}
}
echo "</textarea>
<br><br>
<form action='debug.php' method='GET'>
<INPUT TYPE='button' onClick=\"location.href='index.php'\" value='&#x21A9; Back'>
";
if (!is_null($PID)) {
	echo "<INPUT TYPE='button' value='&#x21BB; Test' disabled>";
} else {
	if (is_null($PIDt)) {
		echo "<INPUT type='submit' value='&#x21BB; Start walk by test'><input type='hidden' name='startstop' value='start'>";
	} else {
		echo "<INPUT type='submit' value='&#x25CE; Stop walk by test'><input type='hidden' name='startstop' value='stop'>";
	}
}
echo "
<INPUT TYPE='button' onClick=\"location.href='help.php'\" value='? Help'>
</form></div>
<br><div align=center><a href='../kiva.html'>hAlarm is free !</a></div></tr></td>
</body>
</html>";
?>
