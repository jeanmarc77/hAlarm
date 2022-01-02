<?php
/**
 * /srv/http/halarm/scripts/tamper.php
 *
 * @package default
 */


if (isset($_POST["tamper"])) {
	$tamper=$_POST["tamper"];
} else {
	die();
	//$tamper = '{"T0":"off", "T1":"on"}';
}

$jsonarray    = json_decode($tamper, true);
if (json_last_error() == JSON_ERROR_NONE) {
	define('checkaccess', TRUE);
	include '../config/config.php';
	include '../config/memory.php';
	include '../config/tampers.php';
	$cntt = count($T);
	$goesoff= false;

	$now = date($DATEFORMAT . ' H:i:s');
	if (file_exists($TMEM)) {
		$data        = file_get_contents($TMEM);
		$tmemarray    = json_decode($data, true);
	}

	for ($i=0;$i<$cntt;$i++) {
		if (!isset($tmemarray[$i])) { // flag
			$tmemarray[$i]=false;
		}

		if (!$tmemarray[$i] && $jsonarray["T$i"]=='on' && isset($T[$i])) {
			$tmemarray[$i] = true;
			$stringData = "$now\tTamper default $T[$i]\n\n";
			logevents($stringData);
			if (!empty($POUKEY)) {
				$pushover = pushover($POAKEY, $POUKEY, "hAlarm Warning", $stringData);
			}
			if (!empty($TLGRTOK)) {
				$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
			}
			$cnt = count($TAMPCOMMAND);
			for ($i=0;$i<$cnt;$i++) {
				exec($TAMPCOMMAND[$i]);
			}
		}

		if ($tmemarray[$i] && $jsonarray["T$i"]=='off') {
			$goesoff = true;
			$tmemarray[$i] = false;
			$stringData = "$now\tTamper default off $T[$i]\n\n";
			logevents($stringData);
			if (!empty($POUKEY)) {
				$pushover = pushover($POAKEY, $POUKEY, "hAlarm", $stringData);
			}
			if (!empty($TLGRTOK)) {
				$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
			}
		}
	}

	if ($goesoff) {
		$alloff = true;
		for ($i=0;$i<$cntt;$i++) {
			if ($tmemarray[$i]=='on' && isset($T[$i])) {
				$alloff = false;
			}
		}
		if ($alloff) {
			exec($TAMPOFF);
			$stringData = "$now\tAll tampers default are over\n\n";
			logevents($stringData);
			if (!empty($POUKEY)) {
				$pushover = pushover($POAKEY, $POUKEY, "hAlarm", $stringData);
			}
			if (!empty($TLGRTOK)) {
				$telegram = telegram($TLGRTOK, $TLGRCID, "hAlarm $stringData\n\n");
			}
		}
	}

	$data = json_encode($tmemarray);
	file_put_contents($TMEM, $data);
} else {
	die();
}


/**
 *
 * @param unknown $stringData
 */
function logevents($stringData) // Log to events
{
	$stringData .= file_get_contents('../data/events.txt');
	file_put_contents('../data/events.txt', $stringData);
}


/**
 *
 * @param unknown $aid
 * @param unknown $uid
 * @param unknown $title
 * @param unknown $msg
 */
function pushover($aid, $uid, $title, $msg) // Push-over
{
	curl_setopt_array($ch = curl_init(), array(
			CURLOPT_URL => 'https://api.pushover.net/1/messages.json',
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_POSTFIELDS => array(
				'token' => "$aid",
				'user' => "$uid",
				'title' => "$title",
				'message' => "$msg"
			)
		));
	curl_exec($ch);
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


?>
