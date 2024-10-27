<?php

namespace Tailscale;

class StatusInfo
{
    public LockInfo $LockInfo;
    public string $TsVersion     = "";
    public string $KeyExpiration = "";
    public string $Online        = "";
    public string $InNetMap      = "";
    public string $Tags          = "";
    public string $LoggedIn      = "";
    public string $TsHealth      = "";
    public string $LockEnabled   = "";
}
