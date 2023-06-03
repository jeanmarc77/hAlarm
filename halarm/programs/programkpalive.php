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
$memarray = array();

if (!empty($_GET['kpident']) && $_GET['kpident'] != 'null') {
	$ident = $_GET['kpident'];
	$nowutc = strtotime(date('Ymd H:i:s'));
	$kbmemarray["$ident"]['UTC'] = $nowutc;
	$data = json_encode($kbmemarray);
	file_put_contents($KYPMEM, $data);
} 


// Allow sleeping Keypads when not running
if (!file_exists('../scripts/alarm.pid') && file_exists($KYPMEM) && !empty($AUTOMSECRET) && !empty($EMAIL)) {
$nowutc = strtotime(date('Ymd H:i:s'));
$cntkyp = count($KYP);

	if	(file_exists($KYPMEM)) {
	$data        = file_get_contents($KYPMEM);
	$kbmemarray    = json_decode($data, true);

		for($i=0; $i<$cntkyp; $i++) {
		$key = $KYP[$i];
			if (isset($kbmemarray[$key]['UTC'])) {
				if ($nowutc - $kbmemarray[$key]['UTC'] > 360) {
					$automatemsg['cmd'] = 'sleep';
					$automatemsg['ident'] = $key;
					pushautomate($AUTOMSECRET, $EMAIL, json_encode($automatemsg));
				}
			} 
		} 
	unlink($KYPMEM);	
	}
}			

header("Content-type: application/json");
?>
