# Chain Earn — local Phase 1 preview

Laravel 13, Blade, Tailwind CSS 4, Vite and MySQL. The application remains in the repository root; `sample` is preserved. No deployment or live financial functionality is enabled. The user-authorized local earning simulation is documented in [the simulation guide](docs/SIMULATION_GUIDE.md), which supersedes older missing-simulation notes.

## Start locally

From this directory, start MySQL and allow it a few seconds to initialize:

```powershell
.\scripts\start-local-mysql.cmd
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://127.0.0.1:8000. These processes do not start automatically after reboot. Stop Laravel with Ctrl+C; stop MySQL cleanly using `php scripts/stop-local-mysql.php`. Preserve ignored `.local/mysql`, which contains application data. Earlier setup details are in [setup history](docs/SETUP_HISTORY.md); historical tool/feature claims there are superseded by this README.

## Implemented

- Registration, session login/logout, remembered login, CSRF protection, password hashing and request throttling.
- Required Indian mobile capture on new signups. Accepts 10 digits starting with 6–9, optional `91`/`+91`, spaces, parentheses or hyphens; stores +91 plus 10 digits. Format validation does not verify ownership. Existing phones remain unchanged.
- Optional UUID referral invitation validated server-side. Account, phone, profile and sponsor are saved atomically. Organic signups have no sponsor. Model updates cannot change sponsor/owner/referral identity; sponsor is excluded from mass assignment. Sponsors with referred members cannot be deleted. No sponsor backfill is fabricated.
- Password recovery with generic responses, throttling, expiring single-use tokens, password confirmation, remembered-login rotation and database-session revocation. Keep SESSION_DRIVER=database for session revocation.
- Email verification at registration, signed expiring links, resend throttling and verified dashboard access. Existing unverified accounts must verify too; login and resend remain available.
- Responsive blue-gradient UI, one planned digital guide, referral-link copying and explicitly fictional DEMO metrics/activity.

## Local email

MAIL_MAILER=log captures verification/reset messages in ignored storage/logs/laravel.log. No external delivery occurs. Register using your own email and open the local verification URL from that log. Recovery works the same way. Treat those URLs as credentials; never share or commit logs. No default account or verification bypass exists.

Real SMTP is supported by the existing Laravel configuration but has not been configured or delivery-tested. Supply credentials privately in .env, use a verified sender and correct APP_URL, then run `php artisan config:clear`. Never commit .env or rotate the existing APP_KEY during setup.

## Database and checks

The additive sponsor migration has been applied to local MySQL. With dependencies/private configuration installed:

```powershell
php artisan migrate
php artisan test
php scripts/test-mysql.php
php artisan view:cache
php artisan view:clear
npm.cmd run build
```

Default tests use SQLite in memory. The MySQL helper explicitly selects disposable chain_earn_test, reuses local application credentials privately and refuses to use the configured application database. The test database must already exist with access granted; its tables are disposable. Never test against chain_earn.

On 22 October 2026, the expanded suite passed on SQLite and MySQL (24 tests each). Blade compilation and Vite build passed. Homepage, login, registration and both recovery pages returned HTTP 200. See [Phase 1 status](docs/PHASE_ONE_STATUS.md). Browser visual checks remain pending because no browser is connected.

## Local follow-up — 23 October 2026

The dashboard now counts actual registered descendants across four levels and lists only the signed-in member's direct referrals, with pagination and without contact details. Financial figures remain explicitly DEMO. Registration is not purchase eligibility.

Run `php artisan chain:check-production` for read-only deployment configuration checks. A failure is expected locally; the command does not send mail or modify data. See docs/RELEASE_RUNBOOK.md for first installation, updates and rollback.

On Windows, if Vite's default config bundling encounters spawn EPERM, use `npm.cmd run build -- --configLoader native`. This completed successfully on 23 October without changing dependencies or disabling security settings.

## Remaining launch requirements

Real SMTP delivery testing, desktop/mobile acceptance, exact MilesWeb plan compatibility, domain/HTTPS, private production configuration, backup/restore verification, monitoring and a reviewed release/rollback process. See [deployment requirements](docs/DEPLOYMENT_REQUIREMENTS.md). Nothing has been deployed.

The PDF is pending. Demo purchases/entitlements, commissions, ledger, withdrawal review, admin roles and audit history are implemented locally, with 42 passing tests on SQLite and MySQL. Admin assignment is deferred at the user's request. Real checkout, file delivery and provider integrations remain pending. See docs/SIMULATION_GUIDE.md for usage and limitations. Live financial activation also requires the legal/provider approvals in [the specification](docs/CHAIN_EARN_SPEC.md).

## Local completion review — 25 October 2026

The dashboard now includes a prominent simulation entry when enabled in local/testing environments, explains the unavailable PDF and fictional money, and displays account verification status messages. The shared layout's existing simulation shortcut is retained.

SQLite authentication/application checks passed: 42 tests, 187 assertions. Final Blade compilation/clear and Vite build passed. An overlapping duplicate test run hit a Windows compiled-view rename lock; the sequential rerun passed. Run view-cache commands and test suites sequentially. The original `sample` SHA-256 remains unchanged. No private configuration, admin assignments, deployment or live financial functionality was added.

The dedicated MySQL suite also passed: 42 tests, 187 assertions, after starting the existing local server. The first attempt encountered connection errors while the server was stopped. Only `chain_earn_test` was used for database tests.

Remaining acceptance requires the actual guide PDF, a selected admin account, real SMTP configuration/delivery testing and desktop/mobile browser review. These items are not claimed complete by the automated checks.
