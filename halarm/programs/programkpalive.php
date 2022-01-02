<?php
/**
 * /srv/http/halarm/admin/programs/programkbalive.php
 *
 * @package default
 */

define('checkaccess', TRUE);
include '../config/config.php';
include '../config/memory.php';
date_default_timezone_set($DTZ);
$memarray = array();

if (!empty($_GET['kpident']) && $_GET['kpident'] != 'null') {
	$ident = $_GET['kpident'];
	$nowutc = strtotime(date('Ymd H:i:s'));
	if (file_exists($KYPMEM)) {
		$data     = file_get_contents($KYPMEM);
		$kbmemarray = json_decode($data, true);

		if (isset($kbmemarray["$ident"]['UTC'])) {
			if ($nowutc - $kbmemarray["$ident"]['UTC'] > 5) {
				$kbmemarray["$ident"]['UTC'] = $nowutc;
			}
		} else {
			$kbmemarray["$ident"]['UTC'] = $nowutc;
		}
	} else {
		$kbmemarray["$ident"]['UTC'] = $nowutc;
	}

	$data = json_encode($kbmemarray);
	file_put_contents($KYPMEM, $data);
} 

//header("Content-type: application/json");
?>
