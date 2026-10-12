<?php

namespace App\Support\Shipping;

use RuntimeException;

/**
 * Shipping cannot be priced or booked because data is missing on our side
 * (a branch or address without map coordinates), found before Biteship is
 * called. Biteship answers a (0, 0) point with the unhelpful "invalid or
 * missing postal code", so the reason is stated here instead: `getMessage()`
 * is for staff and the log, `customerMessage` is safe to show a shopper.
 */
class ShippingUnavailableException extends RuntimeException
{
    public function __construct(string $message, public readonly string $customerMessage)
    {
        parent::__construct($message);
    }
}
