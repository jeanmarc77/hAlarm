<?php
/**
 * /srv/http/halarm/scripts/loadcfg.php
 *
 * @package default
 */


// Load configuration
if (isset($_SERVER['REMOTE_ADDR'])) {
	die('Direct access not permitted');
}

$input = '{ "jsontest" : " <br>Json extension loaded" }';
$val   = json_decode($input, true);
if ($val["jsontest"] != "") {
} else {
	die("/!\ Json extension -NOT- loaded. Abording, please update php.ini.\n");
}

define('checkaccess', TRUE);
$ADMDIR = dirname(dirname(__FILE__));
if (is_readable("$ADMDIR/config/config.php")) {
	include "$ADMDIR/config/config.php";
} else {
	die("Abording. Can't open config.php.\n");
}
if (is_readable("$ADMDIR/config/automate.php")) {
	include "$ADMDIR/config/automate.php";
} else {
	die("Abording. Can't open automate.php.\n");
}
if (is_readable("$ADMDIR/config/memory.php")) {
	include "$ADMDIR/config/memory.php";
} else {
	die("Abording. Can't open memory.php.\n");
}
if (file_exists($MEMORY) && !is_writable($MEMORY)) {
	die("Abording. Can't write $MEMORY.\n");
}
include "$ADMDIR/config/lang.php";

if (isset($argv[1])) {
	$scenario = $argv[1];
} else {
	$scenario = 'scenario0';
}
include "$ADMDIR/config/$scenario.php";
if (file_exists($MEMORY)) {
	file_put_contents($MEMORY, '');
}

date_default_timezone_set($DTZ);

// Initialize variables
$DELAY *= 1000;
$giveup     = 0;
$memarray1st = array();
$comlost  = false;
$minlist = array(
	'00',
	'05',
	'10',
	'15',
	'20',
	'25',
	'30',
	'35',
	'40',
	'45',
	'50',
	'55'
);
// Flags
$flagwarn = false; // warn
$flagalarm = false; // alarm
$flagoff = false; // off
$flaglive = false; // alive
$flagleav = false; // leaving
$flagarm = false;
$kbflag = false; // keyb test
$netflag = false; // network ip test
$INALARM = false;
$INWARN = false;
$prevmsg = '';
$prevkpmsg = '';

// Count
$cnti = count($I);
$cnto = count($O);
$cnte = count($E);

for ($i=0;$i<$cnte;$i++) {
	if (!isset($E[$i])) {
		$E[$i] = null;
	}
}

// Will track Keypads timers
if (file_exists($KYPMEM)) {
unlink($KYPMEM);
}

// Using automate
if (!empty($AUTOMSECRET) && !empty($EMAIL)) {
$automate = true;
} else {
$automate = false;
}

/**
 *
 * @param unknown $aid
 * @param unknown $uid
 * @param unknown $title
 * @param unknown $msg
 */
function pushover($aid, $uid, $title, $msg, $snd) // Push-over
{
	curl_setopt_array($ch = curl_init(), array(
			CURLOPT_URL => 'https://api.pushover.net/1/messages.json',
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_POSTFIELDS => array(
				'token' => "$aid",
				'user' => "$uid",
				'title' => "$title",
				'message' => "$msg",
				'sound' => "$snd" 
			)
		));
	curl_exec($ch);
	curl_close($ch);
}

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

/**
 *
 * @param unknown $token
 * @param unknown $chatid
 * @param unknown $msg
 */
function telegram($token, $chatid, $msg) // Telegram
{
	$tosend = array('chat_id' => $chatid, 'text' => $msg);
	$ch = curl_init('https://api.telegram.org/bot'.$token.'/sendMessage');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tosend));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$output = curl_exec($ch);
	curl_close($ch);
}


/**
 *
 * @param unknown $stringData
 */
function logevents($stringData) // Log to events
{
	$dir = dirname(dirname(__FILE__));
	$stringData .= file_get_contents("$dir/data/events.txt");
	file_put_contents("$dir/data/events.txt", $stringData);
}


/**
 *
 * @param unknown $domain
 * @return unknown
 */
function pingDomain($domain) {
	$starttime = microtime(true);
	$file      = @fsockopen($domain, 80, $errno, $errstr, 10);
	$stoptime  = microtime(true);
	$status    = 0;

	if (!$file)
		$status = -1;
	else {
		fclose($file);
		$status = ($stoptime - $starttime) * 1000;
		$status = floor($status);
	}
	return $status;
}


// clean up
if (file_exists("$ADMDIR/data/alarm.err")) {
	$myFile = "$ADMDIR/data/alarm.err";
	$lines = file($myFile);
	$cnt   = count($lines);
	if ($cnt >= $AMOUNTLOG) {
		array_splice($lines, 0, $AMOUNTLOG);
		$file2 = fopen($myFile, 'w');
		fwrite($file2, implode('', $lines));
		fclose($file2);
	}
}
if (file_exists("$ADMDIR/data/events.txt")) {
	$lines = file("$ADMDIR/data/events.txt");
	$cnt        = count($lines);
	if ($cnt >= $AMOUNTLOG) {
		array_splice($lines, $AMOUNTLOG);
		$file2 = fopen("$ADMDIR/data/events.txt", 'w');
		fwrite($file2, implode('', $lines));
		fclose($file2);
		$stringData = date($DATEFORMAT . ' H:i:s') . "\tClean up logs\n\n";
		$lines      = file("$ADMDIR/data/events.txt");
	}
} else {
	file_put_contents("$ADMDIR/data/events.txt", '');
}
?>
