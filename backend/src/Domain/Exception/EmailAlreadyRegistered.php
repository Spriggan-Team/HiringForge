<?php

namespace App\Domain\Exception;

use Exception;

/**
 * This is an exception that should be thrown only when an email has already been
 * registered and hance cannot be used for another Sign up action.
 */
class EmailAlreadyRegistered extends ExceptionWithPayload
{}