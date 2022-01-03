<?php
/**
 * /srv/http/halarm/admin/index.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include 'config/config.php';
include 'config/memory.php';
include 'config/lang.php';

if (!isset($_SERVER["PHP_AUTH_USER"])) {
	$CURDIR = dirname(dirname(__FILE__));
	echo "<img src='images/shield-error.png' width=24 height=24 border=0><font color='#d94338'>You need to set HTTP authentication for $CURDIR !</font><br>";

	//header("Location: ../../");
	//exit;
}
if (!empty($_GET['startstop'])) {
	$startstop = $_GET['startstop'];
} else {
	$startstop = null;
}
if (!empty($_GET['scenario'])) {
	$scenario = $_GET['scenario'];
} else {
	$scenario = 'scenario0';
}
$cntsena = count($SCENARN);

echo "
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta name='theme-color' content='#000'>
<title>hAlarm WAN</title>
<META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW'>
<link rel='icon' type='image/x-icon' href='images/favicon.ico'>
<meta http-equiv='refresh' content=60>
<link rel='stylesheet' href='style.css' type='text/css'>
<script src='https://code.jquery.com/jquery-3.4.1.min.js' integrity='sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=' crossorigin='anonymous'></script>
</head>
<body>
<script type='text/javascript'>
$(document).ready(function()
{
function updateit() {
	$.getJSON('programs/programlive.php', function(json){
	if (typeof json['msg'] === 'undefined') {
	document.getElementById('rstat').innerHTML = 'Eteint';
	} else {
	document.getElementById('rstat').innerHTML = json['msg'];
	}
	document.getElementById('rSTAMP').innerHTML = json['stamp'];
	})
}
updateit();
setInterval(updateit, 2000);
});

var urlParams = new URLSearchParams(window.location.search);
const scenario = urlParams.get('scenario')
if (typeof scenario === 'undefined'|| !scenario) {
	$.getJSON('programs/programlive.php', function(json){
		if(json['status'] == 'Armed') {
		  var scenario = json['scenario'];
		  window.location.href = 'index.php?scenario=' + scenario;
		}
	})
}
</script>
";

date_default_timezone_set($DTZ);
if (file_exists('../halarm/scripts/alarm.pid')) {
	$PID = (int) file_get_contents('../halarm/scripts/alarm.pid');
	exec("ps -ef | grep $PID | grep alarm.php", $ret);
	if (!isset($ret[1])) {
		$PID = null;
		unlink('../halarm/scripts/alarm.pid');
	}
} else {
	$PID = null;
}

echo "<table width='80%' height='80%' border=0 cellspacing=0 cellpadding=20 align='left'>
<tr><td>
<h1>WAN</h1>
<h3><span id='rSTAMP'>--</span> - $lgSTAT : <span id='rstat'>--</span></h3>
<h3><span id='messageSpan'></span></h3>
</tr></td>
<tr><td>
";
if ($startstop != 'start' && $startstop != 'stop') {
	echo "<form action='index.php' method='GET'>";
	if (is_null($PID)) {
		echo "<input type='image' src='images/disarm.png' value='' width=128 height=128>
		<input type='hidden' name='startstop' value='start'>";
	} else {
		echo "<input type='image' src='images/arm.png' value='' title='Run as pid $PID' width=128 height=128>
		<input type='hidden' name='startstop' value='stop'>";
	}
	echo "
	<input type='hidden' name='scenario' value='$scenario'>
	</form>
	<form action='index.php' method='GET'>
	<select name='scenario' onchange='this.form.submit()'>";
	for ($i = 0; $i < $cntsena; $i++) {
		if ($scenario == 'scenario'.$i) {
			echo "<option SELECTED value='scenario$i'>";
		} else {
			echo "<option value='scenario$i'>";
		}
		echo "$SCENARN[$i]</option>";
	}
	echo "
</select>
<input type='hidden' name='startstop' value=null>
</form>";
}

if ($startstop == 'start' || $startstop == 'stop') {
	$now = date($DATEFORMAT . ' H:i:s');
	if ($startstop == 'start' && is_null($PID)) {
		//$command    = 'php scripts/alarm.php' . ' > /dev/null 2>&1 & echo $!;';
		$command    = "php ../halarm/scripts/alarm.php $scenario" . ' >> ../halarm/data/alarm.err 2>&1 & echo $!; ';
		$PID        = exec($command);
		$val = substr($scenario, -1);
		$stringData = "$now\tStarting by WAN ($SCENARN[$val])\n\n";
		file_put_contents('../halarm/scripts/alarm.pid', $PID);
		$stringData .= file_get_contents('../halarm/data/events.txt');
		file_put_contents('../halarm/data/events.txt', $stringData);
		$stringData = "$now\tStarting debug by WAN ($PID)\n\n";
		file_put_contents('../halarm/data/alarm.err', $stringData, FILE_APPEND);
	}
	if ($startstop == 'stop') {
		exec($ALARMOFF);
		if (!is_null($PID)) {
			$stringData = "$now\tStopping by WAN\n\n";
			$command = exec("kill $PID > /dev/null 2>&1 &");
			unlink('../halarm/scripts/alarm.pid');
			$stringData .= file_get_contents('../halarm/data/events.txt');
			file_put_contents('../halarm/data/events.txt', $stringData);
			$stringData = "$now\tStopping debug by WAN ($PID)\n\n";
			file_put_contents('../halarm/data/alarm.err', $stringData, FILE_APPEND);
		}
		$PID = null;
		unlink($MEMORY);
		unlink($KYPMEM);
	}

	echo "
<script type='text/javascript'>
  document.getElementById('messageSpan').innerHTML = \"...$lgWAIT...\";
  setTimeout(function () {
    window.location.href = 'index.php?startstop=done&scenario=$scenario';
  }, 1000);
</script>
";
}
$events   = '';
$filename = '../halarm/data/events.txt';
if (file_exists($filename)) {
	$events = file_get_contents($filename);
} else {
	$events = 'no event file found';
}
echo "</td></tr>
<tr><td><h3>$lgHISTO</h3>
<textarea style='resize: none;background-color: #DCDCDC' cols=70 rows=25>$events</textarea>";
if (file_exists($KYPMEM)) {
echo "<h3>Keypad(s) seen</h3>";	
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
<br><br>
<div align=left><INPUT TYPE='button' onClick=\"location.href='../../'\" value='&#x21A9; $lgBACK'></div>
</td></tr>
</table>
</body>
</html>";
?>
