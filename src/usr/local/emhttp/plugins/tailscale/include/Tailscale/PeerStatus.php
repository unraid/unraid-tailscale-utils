<?php

namespace Tailscale;

class PeerStatus
{
    public string $Name      = "";
    public string $IP        = "";
    public string $LoginName = "";
    public bool $SharedUser  = false;

    public string $Address = "";

    public bool $Online  = false;
    public bool $Active  = false;
    public bool $Relayed = false;

    public bool $Traffic = false;
    public int $TxBytes  = 0;
    public int $RxBytes  = 0;

    public bool $ExitNodeActive    = false;
    public bool $ExitNodeAvailable = false;
}
