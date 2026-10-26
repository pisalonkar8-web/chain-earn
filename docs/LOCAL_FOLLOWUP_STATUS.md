# Local follow-up — 23 October 2026

## Completed changes

- Actual registered direct-referral counts and four-level descendant counts replace fictional network counts.
- Direct referrals are paginated, scoped to the authenticated member, and omit email and mobile details. An empty state replaces invented member activity.
- A read-only `chain:check-production` command checks configuration, assets, writable paths, database tables and pending migrations without exposing exception details or sending mail.
- Release runbook covers first installation, updates, maintenance, secret preservation, backups and rollback.
- No application schema changes, secret changes, payments, payouts or deployment.

## Verification

- SQLite: 29 tests, 137 assertions passed, including network scope, privacy, four-level limit, legacy profiles, pagination and preflight failure under local configuration.
- Dedicated MySQL test database: 29 tests, 137 assertions passed after starting the existing local MySQL server. The initial run had connection failures while that server was stopped. The application database was not used for tests.
- Blade cache/clear passed.
- Vite production build passed with `npm.cmd run build -- --configLoader native`; default bundled config loader failed with spawn EPERM on Windows.
- The production check correctly fails local production requirements; no production readiness is claimed.
- Browser inventory has no connected browsers, so visual/menu/clipboard acceptance remains pending.

## Decisions and external setup still needed

Existing specification: INR 1000 guide; commissions L1 500, L2 250, L3 100, L4 50; purchase eligibility; no redistribution; INR 500 minimum UPI withdrawals with admin approval. Product file is not ready. Refund/reversal rules, payout fee rounding/failure behavior and exact staff permissions remain to be settled.

Await user selection between a clearly labelled simulation and payment-provider sandbox. Do not infer a provider account, credentials or approval from the request to finish development. Real SMTP, exact MilesWeb plan, domain, production database, deployment access and backups need private configuration and verification. Current work is local and financial values remain DEMO.
