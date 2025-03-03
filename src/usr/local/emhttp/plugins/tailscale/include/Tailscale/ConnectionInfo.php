<?php

namespace Tailscale;

class ConnectionInfo
{
    public string $HostName          = "";
    public string $DNSName           = "";
    public string $TailscaleIPs      = "";
    public string $MagicDNSSuffix    = "";
    public string $AdvertisedRoutes  = "";
    public string $AcceptRoutes      = "";
    public string $AcceptDNS         = "";
    public string $RunSSH            = "";
    public string $ExitNodeLocal     = "";
    public string $AdvertiseExitNode = "";
    public string $UseExitNode       = "";
}
