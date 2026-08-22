<?php

namespace App\Domain\SecurityLog;

interface SecurityRepositoryInterface
{
    /**
     * This function muust be used to memorise dangerous operation (of one of this API Consummer)
     * for security purpose.
     * @param Trace                 $trace An object that decribe a dangerous operation
     * @throws FailedToKeepTrace    An exception that indicates that something went wrong while recording the security log
     * @return void                 Nothing is return if everything is clear or went smootly 
     */
    public function keepTrace(Trace $trace): void;
}