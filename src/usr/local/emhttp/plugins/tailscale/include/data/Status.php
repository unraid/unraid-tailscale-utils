<?php

namespace Tailscale;

require_once "/usr/local/emhttp/plugins/tailscale/include/common.php";

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

switch ($_POST['action']) {
    case 'get':
        if ( ! $tailscaleConfig->Enable) {
            echo("{}");
            return;
        }

        $tailscaleInfo = $tailscaleInfo ?? new Info($tr);
        $rows          = "";

        foreach ($tailscaleInfo->getPeerStatus() as $peer) {
            $user       = $peer->SharedUser ? $tr->tr('status_page.shared') : $peer->Name;
            $online     = $peer->Online ? ($peer->Active ? $tr->tr('status_page.active') : $tr->tr('status_page.idle')) : $tr->tr('status_page.offline');
            $exitNode   = $peer->ExitNodeActive ? $tr->tr('status_page.exit_active') : ($peer->ExitNodeAvailable ? $tr->tr('status_page.exit_available') : "");
            $connection = $peer->Active ? ($peer->Relayed ? $tr->tr('status_page.relay') : $tr->tr('status_page.direct')) : "";
            $active     = $peer->Active ? $peer->Address : "";
            $txBytes    = $peer->Traffic ? $peer->TxBytes : "";
            $rxBytes    = $peer->Traffic ? $peer->RxBytes : "";
            $pingHost   = ($peer->SharedUser || $peer->Active || ! $peer->Online) ? "" : "<input type='button' class='ping' value='Ping' onclick='pingHost(\"{$peer->Name}\")'>";

            $rows .= <<<EOT
                <tr>
                    <td>{$user}</td>
                    <td>{$peer->IP}</td>
                    <td>{$peer->LoginName}</td>
                    <td>{$online}</td>
                    <td>{$exitNode}</td>
                    <td>{$connection}</td>
                    <td>{$active}</td>
                    <td>{$txBytes}</td>
                    <td>{$rxBytes}</td>
                    <td>{$pingHost}</td>
                </tr>
                EOT;
        }

        $output = <<<EOT
            <table id="t1" class="unraid t1">
                <thead>
                    <tr>
                        <td>{$tr->tr('info.dns')}</td>
                        <td>{$tr->tr('info.ip')}</td>
                        <td>{$tr->tr('status_page.login_name')}</td>
                        <td>{$tr->tr('status')}</td>
                        <td>{$tr->tr('status_page.exit_node')}</td>
                        <td>{$tr->tr('status_page.connection_type')}</td>
                        <td>{$tr->tr('status_page.connection_addr')}</td>
                        <td>{$tr->tr('status_page.tx_bytes')}</td>
                        <td>{$tr->tr('status_page.rx_bytes')}</td>
                        <td>{$tr->tr('status_page.action')}</td>
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
    case 'ping':
        $pingHost = escapeshellarg($_POST['host']);

        $out = Utils::run_command("tailscale ping --c 3 {$pingHost}");
        echo implode("<br>", $out);

        break;
}
