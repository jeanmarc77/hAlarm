<?php
/**
 * /srv/http/halarm/admin/config/scenario0.php
 *
 * @package default
 */


// Save as utf-8 encoding
if (!defined('checkaccess')) {die('Direct access not permitted');}

// Main request command, must retrieve each inputs (I), outputs (O) and tampers (T) as JSON
$REQCOMMAND="reqalarmeth -stat";

// Inputs, naming them as array
$I[0] = 'Entrée'; //Z1
$I[1] = 'Salon'; //Z2
$I[2] = 'Buandrie'; //Z3
$I[3] = 'Hall de nuit'; //Z4

// Input(s) in the entrance(s), where the soft keyboard are
$E[0] = 0; // mean that $I[0] is the entrance
//$E[1] = 1;
//$E[2] = 3;

// Output relays, naming them as array
$O[0] = null; // disabled
$O[1] = 'Sirène int.';
$O[2] = 'Sirène ext.';
$O[3] = 'Buzzer';

// Alarm commands
$ALARMCOMMAND[0]='reqalarmeth -r1';
$ALARMCOMMAND[1]='reqalarmeth -r2';
// Alarm off
$ALARMOFF='reqalarmeth -roff';

// Warning, on entrance detection
$WARNCOMMAND='reqalarmeth -r3'; // buzzer
// off
$WARNOFF='reqalarmeth -r3off';
?>
