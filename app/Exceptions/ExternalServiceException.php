<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an outbound call to a third-party service (mail, payment
 * gateway, identity verification) fails due to connectivity or an
 * unrecoverable API error.  Caught globally and rendered as 503.
 */
class ExternalServiceException extends RuntimeException {}
