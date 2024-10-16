<?php

$version = parse_ini_file('/etc/unraid-version') ?: array();

if ($version['version'] == "6.12.0") {
    TailscaleHelpers::logmsg("Unraid 6.12.0: Checking SSH startup script");
    $ssh = file_get_contents('/etc/rc.d/rc.sshd') ?: "";

    if (str_contains($ssh, '$family')) {
        TailscaleHelpers::logmsg("Unraid 6.12.0: Repairing SSH startup script");
        $ssh = str_replace('$family', 'any', $ssh);
        file_put_contents('/etc/rc.d/rc.sshd', $ssh);
    }
} else {
    TailscaleHelpers::logmsg("Unraid 6.12.0: SSH startup script not applicable");
}
