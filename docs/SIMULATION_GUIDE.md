# Chain Earn simulation — 23 October 2026

## Implemented

The user selected simulation first. It uses separate sim_* tables and never collects money or UPI details. Production environments reject simulation pages and mutations regardless of the feature flag. CHAIN_SIMULATION_ENABLED=false disables it locally too.

- One demo INR 1,000 PDF purchase per member. The PDF is pending: no download endpoint or fabricated file. Refunded accounts cannot repurchase in this demo.
- Fixed rewards: INR 500 / 250 / 100 / 50 across four levels. Sponsors qualify through an active demo purchase at the time of the invited member's purchase. No retroactive rewards, compression or redistribution. Unallocated amounts receive no wallet credit.
- Integer-paise ledger with credits, reservations, releases and reversals. Repeated purchase/request/review/refund submissions do not duplicate entries. Balances cannot be edited through the application.
- Minimum demo withdrawal INR 500, whole rupees. Balance is reserved immediately. Pending requests can be approved/rejected; approved requests can become simulated_paid/failed. Failure/rejection releases the reservation once. Invalid transitions and self-review are blocked.
- Super admin: review withdrawals, reverse purchases, view audit history. Reviewer: review withdrawals only. Both require verified accounts. No default/shared admin, signup role field or arbitrary balance-edit endpoint.
- Demo reversals revoke entitlement and debit the original reward recipients. Negative available balances can result after reservations or completed demo payouts; new withdrawals and approval/completion are blocked while funds are insufficient. Future demo earnings offset deficits. This is a demo convention, not a finalized live refund policy.
- Role changes and demo actions are audited. Ledger/audit entries have no edit/delete UI; this is not tamper-proof storage against database administrators.
- MySQL transactions use a shared row lock to serialize demo mutations. Multi-process contention/load testing has not been performed; this is not a claimed production financial engine.
- Demo fees are INR 0. No external payment, PDF delivery or payout is performed.

## Use locally

Start the existing local MySQL instance if stopped, then run from the project root:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Sign in with a verified account at http://127.0.0.1:8000 and select **Open product & wallet simulation**, or visit http://127.0.0.1:8000/simulation.

To exercise rewards: a sponsor makes a demo purchase, shares their referral link, and an invited member registers, verifies and makes a demo purchase. The sponsor receives INR 500 simulated credit and can request a demo withdrawal. Organic purchases generate no sponsor rewards. No seed accounts or verification bypasses were created.

## Assign demo admin later

The user deferred admin assignment. No account was granted permissions. After choosing an existing verified account, run locally:

```powershell
php artisan chain:simulation-access "YOUR_EXISTING_ACCOUNT_EMAIL" super
```

Use `reviewer` for withdrawal-only permissions, or `revoke` to remove access. The command runs only with APP_ENV=local and the simulation enabled. Admin page: http://127.0.0.1:8000/simulation/admin. Another authorized account must review an admin's own withdrawal.

## Verification

- SQLite: 42 tests, 187 assertions passed.
- Dedicated disposable MySQL test database: 42 tests, 187 assertions passed. Tests did not use the application database.
- Local additive migration applied without resetting existing users.
- Blade compilation/clear and Vite build passed. Windows build command: `npm.cmd run build -- --configLoader native`.
- Original sample SHA-256 unchanged: 797CCE1DAEAD21BD1C5D540E1D08E1B6A1D1D5CD95D8BAC48B2B54E2F079A437.
- Browser/mobile appearance, menu and clipboard checks remain pending: no browser is connected.

## Still pending

PDF file, chosen admin account, real SMTP delivery, browser acceptance, MilesWeb plan/domain/access, backups and operational setup. Real fee/refund rules and provider integrations require later work. No deployment or live financial activation occurred.
