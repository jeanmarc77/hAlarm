<?php
/**
 * /srv/http/halarm/scripts/tester.php
 *
 * @package default
 */


include 'loadcfg.php';
include '../config/tampers.php';
file_put_contents('../data/halarm_test.txt', '');
$stringData = '';
while (true) { // To infinity ... and beyond!
	///// Memory
	if (file_exists($MEMORY)) {
		$data        = file_get_contents($MEMORY);
		$memarray1st = $data;
		$memarray    = json_decode($data, true);
	}
	$stringData1st = $stringData;
	$stringData = '';
	// reset values
	//$memarray['time'] = null;
	$cnti = count($I);
	for ($i=0;$i<$cnti;$i++) {
		$memarray["I$i"]   = null;
	}
	$cnto = count($O);
	for ($i=0;$i<$cnto;$i++) {
		$memarray["O$i"]   = null;
	}
	$cntt = count($T);
	for ($i=0;$i<$cntt;$i++) {
		$memarray["T$i"]   = null;
	}
	if (!isset($memarray['status'])) {
		$memarray['status'] = 'test';
		$memarray['msg'] = 'En test';
	}

	$datareturn = null;
	exec($REQCOMMAND, $datareturn);
	$json   = trim(implode($datareturn));
	$jarray = json_decode($json, true);

	if (json_last_error() == JSON_ERROR_NONE) {
		$giveup           = 0;
		//$memarray['UTC']  = strtotime(date('Ymd H:i:s'));
		//$memarray['time'] = $jarray['time'];

		for ($i=0;$i<$cnti;$i++) {
			if (isset($jarray["I$i"])) {
				$val = $jarray["I$i"];
				$memarray["I$i"]   = $val;
				if ($val!='off') {
					$stringData .= "$I[$i] : $val ";
				}
			} else {
				$memarray["I$i"]   = null;
			}
		}
		for ($i=0;$i<$cnto;$i++) {
			if (isset($jarray["O$i"])) {
				$val = $jarray["O$i"];
				$memarray["O$i"]   = $val;
				if ($val!='off') {
					$stringData .= "$O[$i] : $val ";
				}
			} else {
				$memarray["O$i"]   = null;
			}
		}
		for ($i=0;$i<$cntt;$i++) {
			if (isset($jarray["T$i"])) {
				$val = $jarray["T$i"];
				$memarray["T$i"]   = $val;
				if ($val!='off') {
					$stringData .= "$T[$i] : $val ";
				}
			} else {
				$memarray["T$i"]   = null;
			}
		}


	}

	$data = json_encode($memarray);
	if ($data != $memarray1st) { // Reduce write
		$data = json_encode($memarray);
		file_put_contents($MEMORY, $data);
	}
	if ($stringData != $stringData1st && !empty($stringData)) {
		$now = date($DATEFORMAT . ' H:i:s');
		$data = "$now $stringData\n\n". file_get_contents('../data/halarm_test.txt');
		file_put_contents('../data/halarm_test.txt', $data);
	}
	usleep($DELAY);
} // infinity
?>
