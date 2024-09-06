<?php
ini_set('memory_limit', '512M'); /* Increase memory limit */

$allowed_files = ["/var/log/tailscale.log", "/var/log/tailscale-utils.log"];

$log = $_POST['log'];
if (!in_array($log, $allowed_files)) {
	return;
}

$max = intval($_POST['max']);
$lines = array_reverse(array_slice(file($log), -$max));

foreach($lines as $line) {
	echo '<span class="text">', htmlspecialchars($line), "</span>";
}

ini_restore('memory_limit'); /* Restore original memory limit */
?>
