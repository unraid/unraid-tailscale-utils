<?php

function getLog(string $log, int $max): void
{
    $allowed_files = ["/var/log/tailscale.log", "/var/log/tailscale-utils.log"];

    if ( ! in_array($log, $allowed_files)) {
        return;
    }

    if ( ! file_exists($log)) {
        echo '<span class="text">', htmlspecialchars($log), " not found.</span>";
        return;
    }

    $lines = array_reverse(array_slice(file($log) ?: array(), -$max));

    foreach ($lines as $line) {
        echo '<span class="text">', htmlspecialchars($line), "</span>";
    }
}

ini_set('memory_limit', '512M'); // Increase memory limit

try {
    if ( ! is_string($_POST['log']) || ! is_numeric($_POST['max'])) {
        throw new InvalidArgumentException("Invalid input");
    }

    getLog($_POST['log'], intval($_POST['max']));
} catch (Throwable $e) {
    echo '<span class="text">', htmlspecialchars($e->getMessage()), "</span>";
}

ini_restore('memory_limit'); // Restore original memory limit
