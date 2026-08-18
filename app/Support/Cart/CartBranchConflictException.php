<?php

namespace App\Support\Cart;

use App\Models\Branch;
use RuntimeException;

/**
 * Thrown when a product from a different branch is about to be added to a
 * non-empty cart. One cart = one branch (ROADMAP.md 3.3) — the caller is
 * expected to catch this, ask the shopper whether to empty the cart and
 * switch, and retry `CartManager::addItem()` with `$switchBranch = true`.
 */
class CartBranchConflictException extends RuntimeException
{
    public function __construct(public readonly Branch $currentBranch)
    {
        parent::__construct("Keranjang Anda berisi produk dari {$currentBranch->name}.");
    }
}
