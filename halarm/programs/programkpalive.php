<?php
/**
 * /srv/http/halarm/admin/programs/programkbalive.php
 *
 * @package default
 */

define('checkaccess', TRUE);
include '../config/config.php';
include '../config/memory.php';
include '../config/automate.php';
date_default_timezone_set($DTZ);

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
	
if (file_exists($KYPMEM)) {
	$data        = file_get_contents($KYPMEM);
	$kbmemarray    = json_decode($data, true);
}

if (file_exists('../scripts/alarm.pid')) {
	if (!empty($_GET['kpident']) && $_GET['kpident'] != 'null') {
		$ident = $_GET['kpident'];
		$nowutc = strtotime(date('Ymd H:i:s'));
		$kbmemarray["$ident"]['UTC'] = $nowutc;
		$data = json_encode($kbmemarray);
		file_put_contents($KYPMEM, $data);
	} 
} elseif (!file_exists('../scripts/alarm.pid' && file_exists($KYPMEM) && !empty($AUTOMSECRET) && !empty($EMAIL))) { // Allow sleeping Keypads when not running
	$nowutc = strtotime(date('Ymd H:i:s'));
	$cntkyp = count($KYP);
	$data        = file_get_contents($KYPMEM);
	$kbmemarray    = json_decode($data, true);
		for($i=0; $i<$cntkyp; $i++) {
		$key = $KYP[$i];
			if (isset($kbmemarray[$key]['UTC'])) {
					$automatemsg['cmd'] = 'sleep';
					$automatemsg['ident'] = $key;
					pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
			} 
		} 
	unlink($KYPMEM);	
}			

header("Content-type: application/json");
?>
