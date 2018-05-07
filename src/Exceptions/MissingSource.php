<?php

namespace SI\ElectronPdfPhp\Exceptions;


/**
 * This exception is thrown whenever the input source is not set.
 */
class MissingSource extends \Exception
{
    /**
     * MissingSource constructor.
     */
    public function __construct()
    {
        parent::__construct('No input source specified.');
    }
}
