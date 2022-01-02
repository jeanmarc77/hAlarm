<?php
/**
 * /srv/http/halarm/admin/config/config.php
 *
 * @package default
 */


// Save as utf-8 encoding
if (!defined('checkaccess')) {die('Direct access not permitted');}

$DTZ='Europe/Brussels'; // Timezone
$DELAY = 500; // Avoid sucking logger cpu in ms
$DATEFORMAT='j/m/Y'; // Date format
$AMOUNTLOG=100; // Amount of log files
$LATITUDE=50.609; // Switch the keypad(s) in night mode by sun info
$LONGITUDE=4.635;

// Time to enter or leaving home in sec
$TENTR = 30;
// Time auto off alarm sirens
$TAOFF = 540;
// Max Keyboard tries
$MAXFAIL = 5;

// Active senario(s) and naming, edit then each senarioX.php
$SCENARN[0] = 'Normal';
$SCENARN[1] = 'Test';
//$SCENARN[2] = 'Test';

// Wan Network test
$NETIP = '1.1.1.1'; // high availability server

?>
