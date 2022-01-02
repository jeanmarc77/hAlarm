<?php
/**
 * /srv/http/halarm/admin/config/memory.php
 *
 * @package default
 */


// Save as utf-8 encoding
if (!defined('checkaccess')) {die('Direct access not permitted');}

// Make sure you only use a tmpfs. Don't put a / at the end of the variable path.
$TMPFS = '/dev/shm';
$MEMORY = "$TMPFS/halarm_MEMORY.json"; // main data
$KYPMEM = "$TMPFS/halarm_kyp.json"; // track if keypads are alive
$TMEM = "$TMPFS/halarm_tampers.json"; // tampers
?>
