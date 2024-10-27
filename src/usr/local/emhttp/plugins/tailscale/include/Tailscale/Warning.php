<?php

namespace Tailscale;

class Warning
{
    public string $Message;
    public string $Priority;

    public function __construct(string $message = "", string $priority = "system")
    {
        $this->Message  = $message;
        $this->Priority = $priority;
    }
}
