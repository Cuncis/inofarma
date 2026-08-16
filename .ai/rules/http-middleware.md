---
paths:
  - 'app/Models/*.php,app/Http/Middleware/Ensure*IsAuthenticated.php,config/auth.php'
---

# Http Middleware

## Two auth guards: web (staff) and customer (shoppers) — never merge them
`web` guard + `users` table = admin staff (`App\Models\User`, `EnsureAdminIsAuthenticated`, session key prefix `admin_*`). `customer` guard + `customers` table = shoppers (`App\Models\Customer`, `EnsureCustomerIsAuthenticated`, session key prefix `customer_*`). Separate password-reset brokers/token tables too (`password_reset_tokens` vs `customer_password_reset_tokens`) so a shared email address between a staff member and a customer can never cross-redeem a reset link.

Password reset notifications are NOT Laravel's stock `Illuminate\Auth\Notifications\ResetPassword` — that class hard-codes the route name `password.reset`, which doesn't exist here. `User`/`Customer` override `sendPasswordResetNotification()` to send `App\Notifications\AdminResetPassword` / `CustomerResetPassword` instead, pointed at `admin.atur-ulang-sandi` / `ui.new-password`. Same pattern for email verification (`Customer::sendEmailVerificationNotification()` → `CustomerVerifyEmail`, not the `MustVerifyEmail` contract's default).

`User` has `HasRoles` (spatie/laravel-permission) and `branch_id` (null = central/pusat staff, set = confined to one branch — see BranchScope). `Customer` has neither; it's query-scoped to a branch via `CustomerBranchScope` based on order history, not a column on the row.
