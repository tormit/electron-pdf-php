<?php

namespace SI\ElectronPdfPhp\Exceptions;


/**
 * This exception is thrown whenever the destination file is not set.
 */
class MissingDestination extends \Exception
{
    /**
     * MissingDestination constructor.
     */
    public function __construct()
    {
        parent::__construct('No output destination specified.');
    }
}
