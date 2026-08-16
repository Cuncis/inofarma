<?php

namespace App\Support\Inventory;

use RuntimeException;

/**
 * Thrown when a movement would take a branch's available stock below zero.
 *
 * Available stock (`quantity - reserved_quantity`) is checked and the movement
 * applied inside one locked transaction, so this can only fire before anything
 * is written — never leaves a half-applied change behind.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct("Stok tidak cukup: diminta {$requested}, tersedia {$available}.");
    }
}
