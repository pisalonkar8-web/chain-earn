# Chain Earn deployment readiness — 29 October 2026

Status: local non-payment fixes and release preparation completed; remote setup and launch remain pending. The user authorized deployment preparation, supplied https://dashboard.mympanel.com/ and subsequently deferred payment integration. Preserve the existing MilesWeb hosting selection. Production launch requires the requested final approval. No hosting settings, DNS, live credentials or production database have been modified.

## Repository and implementation

- Repository: https://github.com/pisalonkar8-web/chain-earn.git; branch `master`; base commit `026552a` (`Add Chain Earn Laravel application`). Fixes in this session are uncommitted local changes; nothing has been pushed or deployed.
- Laravel 13 / Blade / Tailwind 4 / Vite 8. Locked Symfony 8.1 packages require PHP >=8.4.1; composer.json and the runbook now reflect that minimum. Verify both MilesWeb CLI and web PHP before installation.
- Symfony Mime updated from 8.1.7 to patched 8.1.10 for CVE-2026-45070; its mbstring polyfill dependency updated from 1.43.0 to 1.43.3. No unrelated framework upgrade.
- Email case/whitespace is normalized consistently at signup, login, password recovery and reset. Authentication, email verification, sponsor attribution and demo protections are preserved.
- Browser headers protect against MIME sniffing and framing by unrelated sites. Authenticated responses and reset/verification URLs receive no-store; token-bearing routes use no-referrer. Friendly 403/404/419/429/500/503 pages preserve status codes and omit internal exception messages.
- Secure cookies default on in production. `.env.production.example` supplies secret-free production defaults, daily logging, encrypted database sessions, SMTP placeholders and explicit simulation disablement. No existing `.env` or APP_KEY was replaced.
- Read-only production preflight now checks encryption-key validity, non-placeholder HTTPS URL, HTTP-only/SameSite cookies, simulation disablement and database queue tables when applicable. It is a configuration check, not external security certification.
- Ebook delivery tests use isolated fixtures instead of private local PDFs, so a fresh checkout can run them. Four real PDFs remain privately stored; no public or payment-backed access has been enabled. UI wording now distinguishes unavailable demo delivery from absent files.
- GitHub validation workflow prepared: locked installs/audits, SQLite tests, Blade compilation and Vite build, with compiled frontend artifact. It has not run on GitHub and does not deploy.
- `php scripts/build-release.php` creates a source-plus-assets ZIP under ignored `dist/`. It excludes secrets, private PDFs, dependencies, databases, logs and generated caches. It packages current working-tree changes. Server Composer installation and private environment configuration are still required.

## Verification

Local tools: PHP 8.4.26, Composer 2.10.3, Node 24.20.0, npm 11.19.0, MySQL 8.4.9. No system prerequisites were installed.

- SQLite: `php artisan test` passed, 51 tests / 320 assertions.
- MySQL: `php scripts/test-mysql.php` passed, 51 tests / 320 assertions, using disposable `chain_earn_test` only. The existing local MySQL process was started; the application database was not reset or used for tests.
- `php artisan view:cache` / `view:clear` and Vite build passed.
- Composer metadata/platform checks passed. Composer and npm audits reported no known vulnerabilities at check time; this does not prove absence of vulnerabilities.
- Release archive inspection confirmed required files, compiled assets and public/.htaccess, with prohibited/private contents absent.
- `sample` SHA-256 remains `797CCE1DAEAD21BD1C5D540E1D08E1B6A1D1D5CD95D8BAC48B2B54E2F079A437`.
- Local production preflight correctly fails local/debug/HTTP/log-mail/simulation settings. After starting MySQL it confirmed the local database is reachable, required tables exist and all migrations are applied. These results do not establish a production connection.

## External work still blocked or unverified

| Item | Current status / next requirement |
| --- | --- |
| Hosting access | Control-panel URL supplied. Browser automation previously stopped because it could not verify the active browser URL. No authenticated hosting inspection completed. |
| Deployment branch and release | Base is master/026552a; final candidate changes need review, commit/push and validation before remote installation. No deployed commit exists. |
| Domain/document root | Target domain and staging URL unknown; inspect the account. Serve only Laravel public/, keep application/private storage outside public_html, and avoid other websites. |
| PHP/dependencies | Verify server web/CLI PHP >=8.4.1, extensions and Composer. Hosting selection is retained. |
| Production database | No production database/user identified; credentials must be configured privately. Back up any existing database before reviewed migrations. Never migrate:fresh or seed production. |
| HTTPS | Certificate, renewal and HTTP-to-HTTPS redirect not verified. Configure at the hosting layer after domain setup; verify cookie headers and signed account URLs. |
| Email | SMTP credentials, verified sender and actual verification/reset delivery tests pending. Local log mail does not deliver. |
| Background processing | Current account notifications are synchronous; no scheduled business tasks exist. No cron/worker was installed or claimed operational. Reassess when jobs are introduced. |
| Backups/restore | Remote backup availability, encrypted off-host copies, retention and isolated restore drill not verified. Preserve APP_KEY and private files. |
| Browser acceptance | Desktop/mobile layouts, menu, clipboard and actual account journeys still need connected-browser testing. |
| Business contact | Privacy/grievance contact name still requires an actual supplied value. Do not publish REPLACE_ME placeholders. |
| Payments | Deferred by user: no Razorpay checkout, webhooks, production entitlement/commission ledger or RazorpayX flow implemented/tested. No real payments or transfers performed. |

## Future updates and rollback

Follow RELEASE_RUNBOOK.md: validate candidate code locally and in GitHub, record its commit, build/package assets, inspect the archive, back up remote data/configuration/private files, install into confirmed staging, configure private environment, install locked production dependencies and check platform requirements, review/apply safe migrations, refresh caches and perform HTTPS/account/email acceptance. Request final production launch approval only after staging results are reviewable. GitHub pushes do not currently deploy automatically.

For rollback, preserve the prior release and restore compatible code first; never blindly reverse migrations. If data restoration is required, account for writes since backup and use a tested recovery procedure. No backup is claimed usable until an isolated restore succeeds.

Provider/legal review remains a future financial launch gate: Razorpay and RazorpayX terms list multi-level marketing collection fees as prohibited. The four-level model requires provider assessment and independent legal review before public purchases; no approval is asserted. Sources checked 29 October 2026: https://razorpay.com/terms/ and https://razorpay.com/x/terms/.
