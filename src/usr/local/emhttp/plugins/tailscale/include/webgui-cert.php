<?php

function refreshWebGuiCert($restartIfChanged = true)
{
    $status = getTailscaleStatus();

    $certDomains = $status->CertDomains;

    if (count($certDomains ?? array()) === 0) {
        logmsg("Cannot generate certificate for WebGUI -- HTTPS not enabled for Tailnet.");
        return;
    }

    $dnsName = $certDomains[0];

    $certFile = "/boot/config/plugins/tailscale/state/certs/{$dnsName}.crt";
    $keyFile  = "/boot/config/plugins/tailscale/state/certs/{$dnsName}.key";
    $pemFile  = "/boot/config/ssl/certs/ts_bundle.pem";

    clearstatcache();

    $pemHash = '';
    if (file_exists($pemFile)) {
        $pemHash = sha1_file($pemFile);
    }

    logmsg("Certificate bundle hash: {$pemHash}");

    run_command("tailscale cert --cert-file={$certFile} --key-file={$keyFile} --min-validity=720h {$dnsName}");

    if (
        file_exists($certFile) && file_exists($keyFile) && filesize($certFile) > 0 && filesize($keyFile) > 0
    ) {
        file_put_contents($pemFile, file_get_contents($certFile));
        file_put_contents($pemFile, file_get_contents($keyFile), FILE_APPEND);

        if ((sha1_file($pemFile) != $pemHash) && $restartIfChanged) {
            logmsg("WebGUI certificate has changed, restarting nginx");
            run_command("/etc/rc.d/rc.nginx reload");
        }
    } else {
        logmsg("Something went wrong when creating WebGUI certificate, skipping nginx update.");
    }
}
