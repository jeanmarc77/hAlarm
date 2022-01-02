<?php
/**
 * /srv/http/halarm/admin/programs/programnight.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config.php';
date_default_timezone_set($DTZ);

$sun_info = date_sun_info((strtotime(date('Ymd'))), $LATITUDE, $LONGITUDE);
$nowUTC   = strtotime(date('Ymd H:i'));
if ($nowUTC < ($sun_info['sunrise']-1800) || $nowUTC > ($sun_info['sunset']+1800)) {
	$array['night'] = true;
} else { // day
	$array['night'] = false;
}
if (file_exists('../scripts/alarm.pid')) {
	$array['run'] = true;
} else {
	$array['run'] = false;
}
//$array['night'] = true;
header("Content-type: application/json");
echo json_encode($array);
?>
