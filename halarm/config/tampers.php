<?php
/**
 * /srv/http/halarm/config/tampers.php
 *
 * @package default
 */


// Save as utf-8 encoding
if (!defined('checkaccess')) {die('Direct access not permitted');}


// Tampers naming
$T[0] = null;
$T[1] = 'sirène';

// Tampers commands
$TAMPCOMMAND[0] = null;

// Tampers off, when all values are returned to 'off'
$TAMPOFF='reqalarm -roff';
?>
