<?php

namespace SI\ElectronPdfPhp\Exceptions;

use Symfony\Component\Process\Process;

/**
 * This exception is thrown whenever the PDF generation fails.
 */
class GenerationFailed extends \Exception
{
    /**
     * GenerationFailed constructor.
     *
     * @param string $from
     * @param string $to
     * @param Process $process
     */
    public function __construct(string $from, string $to, Process $process)
    {
        parent::__construct('The PDF generation from ' . $from . ' to ' . $to . ' failed. ['
            . $process->getCommandLine() . ': ' . $process->getErrorOutput() . ']');
    }
}
