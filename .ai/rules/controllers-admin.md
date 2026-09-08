---
paths:
  - 'app/Support/Auth/**,app/Http/Controllers/Admin/**,app/Filament/Resources/BranchStocks/**,app/Filament/Resources/StockTransfers/**'
---

# Controllers Admin

## Branch confinement is a global scope, not a controller check — but service layer bypasses it
Fase 3.2: `BranchScope` (Order, BranchStock, InventoryBatch, InventoryMovement), `TransferBranchScope` (StockTransfer, matches from_branch OR to_branch), `CustomerBranchScope` (Customer, via whereHas orders at the branch) auto-apply in each model's `booted()`. They no-op unless `Auth::guard('web')->user()` exists and has a non-null `branch_id` — central staff and everything outside an authenticated admin request (console, tests, queues) see everything.

`StockAllocator` and `StockAdjuster` deliberately query with `withoutGlobalScope(BranchScope::class)` — they take an explicit `Branch $branch` param and are trusted to act on whatever branch the caller authorized, including cross-branch writes (a transfer's receiving half touches the *other* branch).

Since the Filament migration, branch-ownership enforcement moved from a controller check to the resource layer: `App\Filament\Resources\BranchStocks\BranchStockResource`'s table is a single flat view across every branch — a branch-scoped staff member simply never sees another branch's rows in it (the model's own `BranchScope` filters the underlying query), so there's no separate "wrong branch" check to write. `App\Filament\Resources\StockTransfers\Pages\ViewStockTransfer` still needs an explicit check (`onSide()`) since a transfer touches two branches and `StockTransfer` isn't filtered down to "just mine" the same way.

New branch-scoped model → add its own scope + apply in `booted()`, or reuse `BranchScope` if it has a plain `branch_id` column.
