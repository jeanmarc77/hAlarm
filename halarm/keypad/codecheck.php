<?php
/**
 * /srv/http/halarm/keypad/codecheck.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config.php';
include '../config/memory.php';
include '../config/automate.php';
include '../secure.php';

if (!empty($_GET['scenario'])) {
	$scenario = $_GET['scenario'];
} else {
	die('not scenario');
}
include "../config/$scenario.php";

function pushautomate($AUTOMSECRET, $EMAIL, $msg) // Automate
{
	$payload = json_encode(array( 'secret'=> $AUTOMSECRET, 'to' => $EMAIL, 'device' => null, 'priority' => 'normal', 'payload' => "$msg"));
	$ch = curl_init('llamalab.com/automate/cloud/message');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000); // error
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload ); // payload
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	$result = curl_exec($ch);
	curl_close($ch);
}

if (!empty($_GET['code'])) {
	$code = $_GET['code'];
} else {
	$code = null;
}
if (!empty($_GET['fail'])) {
	$fail = $_GET['fail'];
} else {
	$fail = 1;
}
date_default_timezone_set($DTZ);
$cnt = count($KEYB);
$check = false;
$name = 'inconnu';

for ($i=0;$i<$cnt;$i++) {
	if ($code==$KEYB[$i]) {
		$check = true;
		$name = $KEYU[$i];
	}
}
$now = date($DATEFORMAT . ' H:i:s');

if ($check) {
	if (file_exists('../scripts/alarm.pid')) {
		$PID = (int) file_get_contents('../scripts/alarm.pid');
		exec("ps -ef | grep $PID | grep alarm.php", $ret);
		if (!isset($ret[1])) {
			$PID = null;
		}
	} else {
		$PID = null;
	}
	exec($ALARMOFF);
	if (!is_null($PID)) {
		$stringData = "$now\tStopping on keypad LAN code by $name\n\n";
		$command = exec("kill $PID > /dev/null 2>&1 &");
		unlink('../scripts/alarm.pid');
		$stringData .= file_get_contents('../data/events.txt');
		file_put_contents('../data/events.txt', $stringData);
		$stringData = "$now\tStopping debug on keypad LAN code by $name ($PID)\n\n";
		file_put_contents('../data/alarm.err', $stringData, FILE_APPEND);
		if (!empty($AUTOMSECRET) && !empty($EMAIL) && $LCKOFF) {
				$automatemsg['cmd'] = 'sleep';
				pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
		}
	}
	$PID = null;
	unlink($MEMORY);
	header("Location: index.php");
} else {
	$fail++;
	if ($fail == $MAXFAIL) {
		$stringData = "$now\tMax fail code\n\n";
		$stringData .= file_get_contents('../data/events.txt');
		file_put_contents('../data/events.txt', $stringData);
	}
	header("Location: keyp.php?fail=$fail");
}

?>
