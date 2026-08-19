<?php

namespace App\Support\Pickup;

use RuntimeException;

/** A hand-over attempt that failed for a reason the counter staff should see verbatim. */
class PickupCodeException extends RuntimeException {}
