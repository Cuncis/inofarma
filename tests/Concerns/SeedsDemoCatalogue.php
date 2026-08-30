<?php

namespace Tests\Concerns;

/**
 * The sizes of the seeded demo data, so a test can say "one more than we
 * started with" without hard-coding a number that drifts when the seeder moves.
 *
 * Pair with `RefreshDatabase` and call `$this->seed()` in `setUp()`.
 */
trait SeedsDemoCatalogue
{
    protected const BRANCH_COUNT = 10;

    protected const CATEGORY_COUNT = 7;

    protected const SUPPLIER_COUNT = 5;

    protected const PRODUCT_COUNT = 12;

    protected const CUSTOMER_COUNT = 6;

    protected const ORDER_COUNT = 7;
}
