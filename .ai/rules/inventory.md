---
paths:
  - 'app/Support/Inventory/**'
---

# Inventory

## Stock only ever moves through StockAllocator or StockAdjuster — never touch branch_stocks.quantity directly
Two services own every stock change, both in App\Support\Inventory:

- `StockAllocator::consume()` — FEFO sale/transfer-out. Picks batches oldest-expiry-first, locks the branch_stocks row and the batches, decrements both, writes one inventory_movements row per batch touched. Throws InsufficientStockException if available (quantity - reserved_quantity) is short.
- `StockAllocator::receive()` — stock arriving with a batch number + expiry (purchase or transfer-in). Same batch_number at the same branch extends the existing batch rather than duplicating it.
- `StockAdjuster::adjust()` — a delta correction not tied to any batch (opname, damage, retur). Never use this for new purchased stock — that needs a batch and an expiry, which StockAdjuster doesn't take.

All three run inside DB::transaction with lockForUpdate(), so never wrap a caller's own transaction around them expecting to catch partial state — either the whole thing commits or the exception rolls it all back.

StockTransferManager (App\Support\Inventory) drives the diminta→dikirim→diterima lifecycle on top of StockAllocator: ship() calls consume() and stores the resulting manifest as StockTransfer::batches_shipped (JSON — batch_number + expires_at + quantity per line); receive() replays that exact manifest through receive() at the destination. This is why a transfer preserves expiry dates across branches — don't shortcut it with a plain quantity adjustment on both sides.

Admin controllers currently pass `null` for the userId param on every call — EnsureAdminIsAuthenticated is still the Fase 3.1 session-array prototype, there is no real `users` row to attribute a movement to yet. Wire this up when real admin auth lands.
