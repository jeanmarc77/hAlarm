<?php
// Put your keppad(s) in sleep mode, awake them, restart browser and could sens SMS if needed via push
// https://llamalab.com/automate/cloud/
// Check the hAlarm flow https://llamalab.com/automate/community/flows/34512
// Save as utf-8 encoding
if (!defined('checkaccess')) {die('Direct access not permitted');}

$AUTOMSECRET = null;
$EMAIL = null;

// Allow keypad(s) to lock screen to save power
$LCKARM = true; // When armed, it will send awake on leaving, entrance dectection and alarm
$LCKOFF = true; // When halarm don't run 
?>
