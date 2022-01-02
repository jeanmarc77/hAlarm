#!/usr/bin/php
<?php
if (isset($_SERVER['REMOTE_ADDR'])) {
    die('Direct access not permitted');
}
// Communicate via arduino eth shield
// chmod +x then ln -s /srv/http/comapps/alarm/reqalarmeth.php /usr/bin/reqalarmeth
$ip = '192.168.0.17';

if (!isset($argv[1])) {
	die("No command\n");
}

if ($argv[1] == '-stat') {
$cmd = 'stat';
} elseif ($argv[1] == '-val') {
$cmd = 'val';
} elseif ($argv[1] == '-r1') {
$cmd = 'r1';
} elseif ($argv[1] == '-r2') {
$cmd = 'r1';
} elseif ($argv[1] == '-r3') {
$cmd = 'r3';
} elseif ($argv[1] == '-r3off') {
$cmd = 'r3off';
} elseif ($argv[1] == '-roff') {
$cmd = 'roff';
} else {
$cmd = null;
}

if(!is_null($cmd)) {
    $ch = curl_init($ip.'?'.$cmd);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000); // error
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ((curl_exec($ch)) === false) {
        die(curl_error($ch) . "\n");
    } else {
    $json = curl_exec($ch);
    header("Content-type: application/json");
	echo $json;
    }
    curl_close($ch);
} else {
	die("No valid command\n");
}
?>

