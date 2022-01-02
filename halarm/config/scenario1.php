<?php
/**
 * /srv/http/halarm/admin/config/scenario1.php
 *
 * @package default
 */


// Save as utf-8 encoding
if (!defined('checkaccess')) {die('Direct access not permitted');}

// Main request command, must retrieve each inputs (I), outputs (O) and tampers (T) as JSON
$REQCOMMAND="reqalarmeth -stat";
$MAXREQ = 50; // Will auto kill if requests failed x times with central, set 0 to disable

// Inputs and Naming as array
$I[0] = null; //Z1
$I[1] = null; //Z2
$I[2] = null; //Z3
$I[3] = null; //Z4

// Input(s) in entrance where the soft keyboard are
$E[0] = 0; // mean that $I[0] is the entrance

// Output relays naming as array
$O[0] = null; // disabled
$O[1] = null;
$O[2] = null;
$O[3] = null;

// Alarm commands
$ALARMCOMMAND[0]='reqalarmeth -r1';
$ALARMCOMMAND[1]='reqalarmeth -r2';
// Alarm off
$ALARMOFF='reqalarmeth -roff';

// Warning, on entrance detection
$WARNCOMMAND='reqalarmeth -r3'; // buzzer
// off
$WARNOFF='reqalarmeth -r3off';

// Notification
// Pushover
$POAKEY= null;
$POUKEY = null;
// Telegram
$TLGRTOK= null;
$TLGRCID= null;

// Keyboard code
$KEYB[0] = '1234';
// User name
$KEYU[0] = 'Invité';
?>
