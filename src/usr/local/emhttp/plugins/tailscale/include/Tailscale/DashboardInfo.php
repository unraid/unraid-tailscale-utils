<?php

namespace Tailscale;

class DashboardInfo
{
    /** @var array<string> $TailscaleIPs */
    public array $TailscaleIPs = array();

    public string $HostName = "";
    public string $DNSName  = "";
    public string $Online   = "";
}
