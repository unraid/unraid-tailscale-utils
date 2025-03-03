<?php

namespace Tailscale;

try {
    require_once "/usr/local/emhttp/plugins/tailscale/include/common.php";

    $tailscaleConfig = $tailscaleConfig ?? new Config();
    $tr              = $tr              ?? new Translator();

    if ( ! $tailscaleConfig->Enable) {
        echo("{}");
        return;
    }

    switch ($_POST['action']) {
        case 'get':
            $tailscaleInfo       = $tailscaleInfo ?? new Info($tr);
            $tailscaleStatusInfo = $tailscaleInfo->getStatusInfo();
            $tailscaleConInfo    = $tailscaleInfo->getConnectionInfo();

            $acceptDNSButton = $tailscaleConfig->AllowDNS ?
                (
                    $tailscaleInfo->acceptsDNS() ?
                    "<input type='button' class='ping' value='{$tr->tr("disable")}' onclick='setFeature(\"dns\", false)'>" :
                    "<input type='button' class='ping' value='{$tr->tr("enable")}' onclick='setFeature(\"dns\", true)'>"
                ) : "";

            $acceptRoutesButton = $tailscaleConfig->AllowRoutes ?
                (
                    $tailscaleInfo->acceptsRoutes() ?
                    "<input type='button' class='ping' value='{$tr->tr("disable")}' onclick='setFeature(\"routes\", false)'>" :
                    "<input type='button' class='ping' value='{$tr->tr("enable")}' onclick='setFeature(\"routes\", true)'>"
                ) : "";

            $sshButton = $tailscaleInfo->runsSSH() ?
                "<input type='button' class='ping' value='{$tr->tr("disable")}' onclick='setFeature(\"ssh\", false)'>" :
                "<input type='button' class='ping' value='{$tr->tr("enable")}' onclick='setFeature(\"ssh\", true)'>";

            $advertiseExitButton = $tailscaleInfo->usesExitNode() ? "" :
                (
                    $tailscaleInfo->advertisesExitNode() ?
                    "<input type='button' class='ping' value='{$tr->tr("disable")}' onclick='setFeature(\"advertise-exit\", false)'>" :
                    "<input type='button' class='ping' value='{$tr->tr("enable")}' onclick='setFeature(\"advertise-exit\", true)'>"
                );

            $exitLocalButton = $tailscaleInfo->exitNodeLocalAccess() ?
                "<input type='button' class='ping' value='{$tr->tr("disable")}' onclick='setFeature(\"exit-allow-local\", false)'>" :
                "<input type='button' class='ping' value='{$tr->tr("enable")}' onclick='setFeature(\"exit-allow-local\", true)'>";

            $rows = <<<EOT
                <tr><td>{$tr->tr("info.hostname")}</td><td>{$tailscaleConInfo->HostName}</td><td></td></tr>
                <tr><td>{$tr->tr("info.dns")}</td><td>{$tailscaleConInfo->DNSName}</td><td></td></tr>
                <tr><td>{$tr->tr("info.ip")}</td><td>{$tailscaleConInfo->TailscaleIPs}</td><td></td></tr>
                <tr><td>{$tr->tr("info.magicdns")}</td><td>{$tailscaleConInfo->MagicDNSSuffix}</td><td></td></tr>
                <tr><td>{$tr->tr("info.routes")}</td><td>{$tailscaleConInfo->AdvertisedRoutes}</td><td></td></tr>
                <tr><td>{$tr->tr("info.accept_routes")}</td><td>{$tailscaleConInfo->AcceptRoutes}</td><td>{$acceptRoutesButton}</td></tr>
                <tr><td>{$tr->tr("info.accept_dns")}</td><td>{$tailscaleConInfo->AcceptDNS}</td><td>{$acceptDNSButton}</td></tr>
                <tr><td>{$tr->tr("info.run_ssh")}</td><td>{$tailscaleConInfo->RunSSH}</td><td>{$sshButton}</td></tr>
                <tr><td>{$tr->tr("info.advertise_exit_node")}</td><td>{$tailscaleConInfo->AdvertiseExitNode}</td><td>{$advertiseExitButton}</td></tr>
                <tr><td>{$tr->tr("info.exit_node_local")}</td><td>{$tailscaleConInfo->ExitNodeLocal}</td><td>{$exitLocalButton}</td></tr>
                EOT;

            $output = <<<EOT
                <table id="statusTable" class="unraid statusTable">
                    <thead>
                        <tr>
                            <th class="filter-false">{$tr->tr('connection')}</th>
                            <th class="filter-false">&nbsp;</th>
                            <th class="filter-false">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rows}
                    </tbody>
                </table>
                EOT;

            $rtn         = array();
            $rtn['html'] = $output;
            echo json_encode($rtn);
            break;
        case 'set-feature':
            $features = [
                'dns'              => 'accept-dns',
                'routes'           => 'accept-routes',
                'ssh'              => 'ssh',
                'exit-allow-local' => 'exit-node-allow-lan-access',
                'advertise-exit'   => 'advertise-exit-node'
            ];

            if ( ! isset($features[$_POST['feature']])) {
                throw new \Exception("Invalid feature: {$_POST['feature']}");
            }

            if ( ! isset($_POST['enable'])) {
                throw new \Exception("Missing enable parameter");
            }

            $enable = filter_var($_POST['enable'], FILTER_VALIDATE_BOOLEAN);
            Utils::run_command("tailscale set --{$features[$_POST['feature']]}=" . ($enable ? "true" : "false"));
    }
} catch (\Throwable $e) {
    file_put_contents("/var/log/tailscale-error.log", print_r($e, true) . PHP_EOL, FILE_APPEND);
    echo "{}";
}
