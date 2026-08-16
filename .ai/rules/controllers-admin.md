---
paths:
  - 'app/Support/Auth/**,app/Http/Controllers/Admin/**'
---

# Controllers Admin

## Branch confinement is a global scope, not a controller check — but service layer bypasses it
Fase 3.2: `BranchScope` (Order, BranchStock, InventoryBatch, InventoryMovement), `TransferBranchScope` (StockTransfer, matches from_branch OR to_branch), `CustomerBranchScope` (Customer, via whereHas orders at the branch) auto-apply in each model's `booted()`. They no-op unless `Auth::guard('web')->user()` exists and has a non-null `branch_id` — central staff and everything outside an authenticated admin request (console, tests, queues) see everything.

`StockAllocator` and `StockAdjuster` deliberately query with `withoutGlobalScope(BranchScope::class)` — they take an explicit `Branch $branch` param and are trusted to act on whatever branch the caller authorized, including cross-branch writes (a transfer's receiving half touches the *other* branch). The controller is where branch-ownership must be checked for actions (see `BranchStockController::authorizeBranch()` and `StockTransferController::authorizeSide()`) — the scope alone would either silently corrupt a legitimate cross-branch write or wrongly hide it, not refuse it.

New branch-scoped model → add its own scope + apply in `booted()`, or reuse `BranchScope` if it has a plain `branch_id` column.
