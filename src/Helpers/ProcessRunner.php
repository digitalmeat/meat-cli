<?php

namespace Meat\Cli\Helpers;

use Symfony\Component\Process\Process;

class ProcessRunner
{
    /**
     * @param $command
     * @param bool $print_output
     * @param null $timeout
     * @return int|Process
     */
    public function run($command, $print_output = true, $timeout = null)
    {
        $process = new Process($command);
        $process->setTimeout($timeout);
        if ($print_output) {
            return $process->run(function ($type, $buffer) {
                echo $buffer;
            });
        }
        $process->run();
        return $process;
    }

    /**
     * @param $command
     * @return null
     */
    public function execPrint($command) {
        $result = array();
        $return_status = null;
        exec($command, $result, $return_status);
        foreach ($result as $line) {
            print($line . "\n");
        }

        return $return_status;
    }
}