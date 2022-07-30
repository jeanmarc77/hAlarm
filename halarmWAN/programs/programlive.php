<?php
/**
 * /srv/http/halarm/admin/programs/programlive.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../../halarm/config/config.php';
include '../../halarm/config/memory.php';
date_default_timezone_set($DTZ);

if (file_exists($MEMORY)) {
	$data     = file_get_contents($MEMORY);
	$array = json_decode($data, true);
	$nowutc = strtotime(date('Ymd H:i:s'));
	$array['stamp'] = date("H:i:s");
	if (isset($array['UTC'])) {
		if ($nowutc - $array['UTC'] > 15) {
			$array['stamp'] .= ' (communication lost)';
		}
	}
} else {
	$array['stamp'] = date("H:i:s");
}

header("Content-type: application/json");
echo json_encode($array);
?>
