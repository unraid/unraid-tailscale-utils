<?php

namespace Tailscale;

require_once "/usr/local/emhttp/plugins/tailscale/include/common.php";

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

if ( ! $tailscaleConfig->Enable) {
    echo("{}");
    return;
}

switch ($_POST['action']) {
    case 'get':
        $tailscaleInfo = $tailscaleInfo ?? new Info($tr);
        $rows          = "";

        $mullvad = filter_var($_POST['mullvad'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $shared  = filter_var($_POST['shared'] ?? false, FILTER_VALIDATE_BOOLEAN);

        foreach ($tailscaleInfo->getPeerStatus() as $peer) {
            if ($peer->Mullvad && ! $mullvad && ! $peer->Active) {
                continue;
            }
            if ($peer->SharedUser && ! $shared && ! $peer->Active) {
                continue;
            }

            $user       = $peer->SharedUser ? $tr->tr('status_page.shared') : $peer->Name;
            $online     = $peer->Online ? ($peer->Active ? $tr->tr('status_page.active') : $tr->tr('status_page.idle')) : $tr->tr('status_page.offline');
            $exitNode   = $peer->ExitNodeActive ? $tr->tr('status_page.exit_active') : ($peer->ExitNodeAvailable ? ($peer->Mullvad ? "Mullvad" : $tr->tr('status_page.exit_available')) : "");
            $connection = $peer->Active ? ($peer->Relayed ? $tr->tr('status_page.relay') : $tr->tr('status_page.direct')) : "";
            $active     = $peer->Active ? $peer->Address : "";
            $txBytes    = $peer->Traffic ? $peer->TxBytes : "";
            $rxBytes    = $peer->Traffic ? $peer->RxBytes : "";
            $pingHost   = ($peer->SharedUser || $peer->Active || ! $peer->Online || $peer->Mullvad) ? "" : "<input type='button' class='ping' value='Ping' onclick='pingHost(\"{$peer->Name}\")'>";
            $ips        = implode("<br />", $peer->IP);

            $rows .= <<<EOT
                <tr>
                    <td>{$user}</td>
                    <td>{$ips}</td>
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
                        <th>{$tr->tr('info.dns')}</th>
                        <th>{$tr->tr('info.ip')}</th>
                        <th>{$tr->tr('status_page.login_name')}</th>
                        <th class="filter-select filter-match" id="status">{$tr->tr('status')}</th>
                        <th class="filter-select filter-match" id="exitnode">{$tr->tr('status_page.exit_node')}</th>
                        <th class="filter-select filter-match" id="conntype">{$tr->tr('status_page.connection_type')}</th>
                        <th class="filter-false">{$tr->tr('status_page.connection_addr')}</th>
                        <th class="filter-false">{$tr->tr('status_page.tx_bytes')}</th>
                        <th class="filter-false">{$tr->tr('status_page.rx_bytes')}</th>
                        <th class="filter-false">{$tr->tr('status_page.action')}</th>
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
        $tailscaleInfo = $tailscaleInfo ?? new Info($tr);
        $out           = "Could not find host.";

        foreach ($tailscaleInfo->getPeerStatus() as $peer) {
            if ($peer->Name == $_POST['host']) {
                $out = implode("<br>", Utils::run_command("tailscale ping --c 3 {$peer->IP[0]}"));
                break;
            }
        }

        echo $out;
        break;
}
