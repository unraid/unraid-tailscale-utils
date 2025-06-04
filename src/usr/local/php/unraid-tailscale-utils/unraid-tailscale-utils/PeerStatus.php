<?php

namespace Tailscale;

class PeerStatus
{
    public string $Name      = "";
    public string $LoginName = "";
    public bool $SharedUser  = false;

    /** @var string[] */
    public array $IP = array();

    public string $Address = "";

    public bool $Online  = false;
    public bool $Active  = false;
    public bool $Relayed = false;

    public bool $Traffic = false;
    public int $TxBytes  = 0;
    public int $RxBytes  = 0;

    public bool $ExitNodeActive    = false;
    public bool $ExitNodeAvailable = false;
    public bool $Mullvad           = false;
}
