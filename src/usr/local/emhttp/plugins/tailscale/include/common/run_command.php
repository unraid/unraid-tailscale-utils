<?php

/**
 * @return array<string>
 */
function run_command(string $command, bool $alwaysShow = false, bool $show = true): array
{
    $output = array();
    $retval = null;
    if ($show) {
        logmsg($command);
    }
    exec("{$command} 2>&1", $output, $retval);

    if (($retval != 0) || $alwaysShow) {
        logmsg("Command returned {$retval}" . PHP_EOL . implode(PHP_EOL, $output));
    }

    return $output;
}
