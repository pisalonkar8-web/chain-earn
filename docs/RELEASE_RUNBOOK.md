# Chain Earn release runbook

Prepared 23 October 2026. No deployment has been performed. Replace all path/account placeholders with values confirmed by MilesWeb.

## Scope and prerequisites

The application provides accounts, actual referral registration counts and a local earning simulation (see SIMULATION_GUIDE.md). Demo purchase/entitlement, commission ledger, withdrawal review and admin flows are implemented but blocked in production. Real checkout, paid entitlements and payouts are not active.

Confirm PHP >=8.3 for web and CLI, Composer 2, MySQL, required Laravel extensions plus pdo_mysql, SSH, document-root control and resource limits. The production domain must serve only `/home/CPANEL_USER/chain-earn/public`. Never expose the repository root. Enable HTTPS and verified transactional SMTP.

## Build on the development computer

Use Node >=22.12 and locked dependencies:

```powershell
npm.cmd ci
npm.cmd run build
php artisan test
php artisan view:cache
php artisan view:clear
```

Upload application source, composer.json/lock and public/build. Exclude .env, .git, .local, node_modules, vendor, public/hot, logs, sessions, generated views and generated bootstrap cache. Preserve empty writable storage directories. Keep sample untouched. Install production Composer dependencies on the server. Do not upload local MySQL files.

## First installation

Create a dedicated MySQL database and user. Privately copy .env.example to .env and configure APP_ENV=production, APP_DEBUG=false, HTTPS APP_URL, database credentials, SESSION_DRIVER=database, SESSION_SECURE_COOKIE=true and SMTP. Use MAIL_SCHEME=smtp with provider-supported STARTTLS on 587, or smtps with implicit TLS on 465. Use provider-confirmed settings. Never paste credentials into reports.

In the server terminal:

```bash
cd /home/CPANEL_USER/chain-earn
composer install --no-dev --prefer-dist --optimize-autoloader
composer check-platform-reqs --no-dev
php artisan key:generate --force
php artisan migrate --force
php artisan optimize
php artisan chain:check-production
```

Generate APP_KEY only for a new installation. Keep it securely backed up and unchanged across subsequent releases. Make storage and bootstrap/cache writable by the application's PHP process; never apply blanket 777 permissions.

## Acceptance and operations

Verify /up and homepage over HTTPS, direct routes, mobile/desktop layouts, registration, real verification mail, login/logout, invitation attribution, password reset, database session revocation and network pagination. Ensure /.env and /composer.json cannot be retrieved. Confirm browser/menu/clipboard behavior. The preflight command is read-only and cannot replace these checks.

Set up daily database and private-file backups with encrypted off-host copies, preserve APP_KEY, and restore into an isolated database before declaring backups usable. Monitor /up, application errors, disk and certificate expiry. No worker/cron is required by the currently synchronous authentication flow.

## Updates and rollback

Keep a copy of the previous release and take a consistent database/private-file backup first. Test the candidate release against a disposable database. During a maintenance window, use `php artisan down`, install the reviewed release, preserve .env and persistent storage, install dependencies, apply reviewed migrations, run optimize/preflight, then `php artisan up`. If deployment fails, retain maintenance mode while recovering.

Restore the previous release code if compatible with the schema. Do not blindly run migrate:rollback; decide whether a tested backup restore is needed, accounting for any new production writes. After recovery, rerun health and account-flow checks. Deployment paths, backup commands and release automation require the actual hosting details.
