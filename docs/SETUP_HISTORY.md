# Chain Earn — phase one

A local Laravel 13 application using Blade, Tailwind CSS 4, Vite, and MySQL. Source files are in this folder; no nested application and no deployment.

## Running locally (15 October 2026)

MySQL 8.4.9 is configured as a local background process bound to `127.0.0.1:3306`, with MySQL X disabled. It is not a Windows service and does not start automatically after reboot. Its data and logs are in Git-ignored `.local/mysql`; preserve that folder. The `chain_earn` database and dedicated application user are configured in the ignored `.env`. A generated administrator password is stored there as `LOCAL_MYSQL_ROOT_PASSWORD`; the application uses its separate database account. The previous `.env` is preserved in ignored `.local/env-before-mysql.backup`.

All four migrations have run. Authentication tests passed against both in-memory SQLite and the separate `chain_earn_test` MySQL database (9 tests, 36 assertions each). The test database is disposable; never point these tests at the application database. The Vite build and Blade compilation passed. HTTP checks confirmed homepage/login responses, guest dashboard redirection, and the built stylesheet link. Visual browser checks remain pending because no browser is connected to this session.

The development server was started at http://127.0.0.1:8000. Register your own account; no default account was created. After restarting Windows, run from this repository:

```powershell
.\scripts\start-local-mysql.cmd
php artisan serve --host=127.0.0.1 --port=8000
```

Allow MySQL a few seconds to start. To shut it down cleanly, run `php scripts/stop-local-mysql.php`. Both database helpers were verified by stopping/restarting MySQL and checking migration status. Stop a foreground Laravel server with Ctrl+C. Current-session background server logs are in `.local/app-output.log` and `.local/app-error.log`.

Earlier setup/audit sections below are historical and superseded by this section.

## Earlier local verification (15 October 2026)

PHP 8.4.26 and Composer 2.10.3 are available. Enabled and verified `fileinfo`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, and `zip` in the active PHP configuration after creating a backup beside it. Composer dependencies are installed and `composer.lock` now records the resolved versions. Created a Git-ignored local `.env` and generated its application key without publishing the key.

Verified successfully: `composer validate --strict`, `composer check-platform-reqs`, `composer audit` (no security vulnerability advisories), `php artisan test` (9 passed, 36 assertions), `php artisan view:cache`, and `php artisan view:clear`. Tests use in-memory SQLite; no MySQL database was created or migrated.

At the user's explicit request, installed Node.js 24.20.0 (npm 11.19.0) and MySQL Community Server 8.4.9 using Windows Package Manager. Added MySQL's bin folder to the user PATH; reopen IDE terminals to pick up installed tools. Frontend dependencies are installed with package-lock.json, npm reported zero vulnerabilities, and npm.cmd run build passed (Vite 8.3.4). Repeated php artisan test (9 passed, 36 assertions), Blade compilation, and compiled-view cleanup successfully. MySQL client and server version commands pass, but no MySQL service is configured yet: server configuration, database/account creation, local .env database credentials, and MySQL migrations remain pending. Browser verification is also pending. The setup/audit descriptions below record the earlier environment and are historical where they conflict with this update.

## Initial audit status (15 October 2026)

The repository audit is recorded in [docs/AUDIT.md](docs/AUDIT.md). PHP, Composer, Node.js, npm and the MySQL CLI are unavailable on PATH; Git 2.56.0.windows.2 is available. Setup, migrations, application changes and runtime verification are blocked pending prerequisites. No dependencies were installed and no `.env` was created. All existing project files are untracked and the repository has no commits; nothing was discarded or committed.

Agreed requirements: [specification](docs/CHAIN_EARN_SPEC.md), [implementation plan](docs/IMPLEMENTATION_PLAN.md), and [deployment requirements](docs/DEPLOYMENT_REQUIREMENTS.md). These describe planned work, not completed features. Password reset, email verification, required mobile capture, immutable sponsor attribution and blue-gradient styling remain Phase 1 work. Independent Indian legal review and payment-provider approval are mandatory before live payments, commissions or payouts. Phase 2 requires a separate confirmation.

## Setup status

The initial folder contained only `sample` and Git metadata. The official Laravel 13.x application skeleton was downloaded and customized in place. `sample` is preserved.

PHP, Composer, Node.js, npm, and the MySQL CLI were not found on PATH during setup. Dependencies have NOT been installed, no application key has been generated, no database has been created or migrated, and the app/build/tests have NOT been run. No browser preview was possible. MySQL might exist outside PATH; check Windows Services before installing another instance.

## Install these prerequisites on Windows

1. **PHP 8.4 or 8.5 x64**: install Laravel Herd for Windows (https://herd.laravel.com/windows), or download PHP from https://windows.php.net/download/. Laravel 13 requires PHP 8.3+, but PHP 8.4+ is recommended for the supplied scaffold. Put `php.exe` on PATH. For a manual installation, create `php.ini` from `php.ini-development`, set `extension_dir="ext"`, and enable `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, and `zip`. Laravel also needs the bundled Ctype, DOM, Filter, Hash, PCRE, PDO, Session, Tokenizer, and XML extensions. Install the Microsoft Visual C++ runtime if the PHP installer requires it. SQLite is used only by the test suite.
2. **Composer 2**: run the Windows `Composer-Setup.exe` installer from https://getcomposer.org/download/ and select the installed PHP executable. Herd may already supply Composer; check first.
3. **Node.js 24 LTS, including npm**: use the Windows installer from https://nodejs.org/en/download. Vite requires modern Node; this project declares Node >=22.12.
4. **MySQL Community Server 8.4 LTS**: use the Windows MSI from https://dev.mysql.com/downloads/mysql/8.4.html. Configure and start the service on port 3306. Set your own administrative password. Workbench is optional; add MySQL's `bin` folder to PATH to use the CLI.

Restart the IDE terminal after installation and verify:

```powershell
php -v
php -m
composer -V
node -v
npm.cmd -v
mysql --version
```

Use `npm.cmd` if PowerShell blocks `npm.ps1`; no execution-policy change is needed.

## Configure the database

Open a MySQL administrator session (`mysql -u root -p`, or Workbench). Execute these statements after replacing the password placeholder with a password you choose:

```sql
CREATE DATABASE chain_earn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'chain_earn'@'localhost' IDENTIFIED BY '<choose-your-own-password>';
GRANT ALL PRIVILEGES ON chain_earn.* TO 'chain_earn'@'localhost';
```

The placeholder is not a working credential. Keep the actual password only in the untracked `.env`. Configure a matching MySQL account host if your server restricts local TCP access differently.

## Install and run from this folder

```powershell
Set-Location 'C:\Users\Suniket Jagtap\chain earn'
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Copy `.env.example` only on first setup; keep an existing `.env` and key. Run `php artisan key:generate` only if `APP_KEY` is blank; never rotate an existing key during setup. Edit `.env` before migrating:

```dotenv
APP_NAME="Chain Earn"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chain_earn
DB_USERNAME=chain_earn
DB_PASSWORD="<the-password-you-chose>"
```

Then run:

```powershell
php artisan config:clear
php artisan migrate
if (Test-Path package-lock.json) { npm.cmd ci } else { npm.cmd install }
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://localhost:8000. Register your own account; no default login is seeded. The first Composer and npm installation generates lockfiles; commit those for reproducible installs. No lockfiles were fabricated here.

There is currently no `composer.lock` or `package-lock.json`. Consequently the first install resolves dependency ranges; it cannot reproduce a locked dependency set. Inspect and retain the resulting lockfiles, then use `composer install` and `npm.cmd ci` thereafter. Run `composer validate --strict`, `composer check-platform-reqs`, `composer audit`, and `npm.cmd audit` after installation. Do not use `--ignore-platform-reqs`. The repository's `.npmrc` disables package lifecycle scripts; if installation/build reports a required dependency script, review that specific package before enabling it.

### Temporary SQLite local preview

If MySQL is unavailable after PHP/Composer/Node are installed, SQLite is appropriate for local UI/authentication work. Enable `pdo_sqlite` and `sqlite3`. Preserve any existing database settings before editing your local `.env`; do not copy real credentials into documentation. Create the file without truncating an existing database:

```powershell
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
```

Set `DB_CONNECTION=sqlite`, `DB_DATABASE="C:/Users/Suniket Jagtap/chain earn/database/database.sqlite"`, and remove any local `DB_URL` override. Run `php artisan config:clear` followed by `php artisan migrate`. Database sessions/cache still need migrations. Never commit the SQLite file; keep it locally excluded using `.git/info/exclude` if needed. SQLite tests do not establish MySQL compatibility; repeat migration and auth checks against a separate MySQL development database before accepting setup. Never use `migrate:fresh` on an existing database.

For live frontend development, run `npm.cmd run dev` in a second terminal while `php artisan serve` runs in the first. The browser entry point remains port 8000. Stop both with Ctrl+C. No queue worker or Redis is required for phase one.

## Implemented behavior

- Responsive homepage with planned digital product categories, how it works, and signup/login links. Products are explicitly marked coming soon.
- Laravel session authentication: registration, login, optional remember-me, POST logout, validation, CSRF protection, password hashing, throttled authentication submissions, and session regeneration.
- Protected dashboard with **demo** wallet balance, direct referrals, team members, total commissions, and fictional activity. Every monetary/count display is labeled as demo.
- Desktop sidebar and collapsible mobile navigation; keyboard focus styles, skip link, responsive cards, and horizontally scrollable table.
- Real per-user UUID referral URL with clipboard copy and manual-copy fallback. The register page acknowledges referral invitations. Attribution, team building, and commissions are not calculated or persisted.
- Users and one-to-one basic profiles; each profile has a unique referral code, optional phone and bio, and a cascading user foreign key. Registration creates both records in a transaction.

There is no payment collection, wallet ledger or transfers, payout processing, withdrawal action, checkout, or deployment. Email verification and password reset are outside this phase. Passwords are never redisplayed after validation failures. Mail uses the local log driver; no email credentials are required.

## Main files

| File | Purpose |
| --- | --- |
| `routes/web.php` | Public, guest authentication, and protected dashboard routes |
| `app/Http/Controllers/AuthController.php` | Register/login/logout using Laravel's session guard |
| `app/Models/User.php`, `app/Models/Profile.php` | Authenticated user and profile relation |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Users, password reset tokens, and database sessions |
| `database/migrations/2026_10_13_000003_create_profiles_table.php` | Basic profile schema and unique referral codes |
| `database/migrations/0001_01_01_000001_create_cache_table.php` | Database cache for login throttling |
| `resources/views/home.blade.php` | Homepage and planned product information |
| `resources/views/auth/` | Signup/login forms and validation messages |
| `resources/views/dashboard.blade.php` | Dashboard, demo metrics, network preview, and referral link |
| `resources/views/layouts/app.blade.php`, `resources/views/components/brand.blade.php` | Shared page shell and brand |
| `resources/css/app.css` | Tailwind import, design components, and responsive styling |
| `resources/js/app.js` | Mobile menu toggle and referral link copying |
| `vite.config.js`, `package.json` | Tailwind/Vite asset pipeline; system fonts avoid font-download dependencies |
| `.env.example`, `config/database.php` | MySQL configuration; application key and DB password are blank |
| `tests/Feature/AuthenticationTest.php` | Auth protection, registration/hash/profile, duplicate validation, login/logout, throttle, and cascade tests |

The remaining application bootstrap, public entry point, framework configuration, storage folders, users factory, and development dependencies come from the official Laravel skeleton. The default homepage was removed and the default account seeder was disabled.

## Verification after installing prerequisites

```powershell
php artisan test
php artisan view:cache
php artisan view:clear
npm.cmd run build
```

Feature tests use SQLite in memory and bypass Vite, so they do not modify the MySQL development database. `pdo_sqlite` must be enabled. Tests have been written but could not be executed in the current environment.

Manual checks: view `/` on desktop and a 375px mobile viewport; confirm a guest visiting `/dashboard` is redirected to `/login`; register an account; check the demo notices and referral link; open the mobile menu and its anchors; copy the link; log out; try a bad password and a duplicate signup; log back in with the created account.

Troubleshooting: `could not find driver` means enable `pdo_mysql` (or `pdo_sqlite` for tests) in the active PHP config shown by `php --ini`; access denied means check DB credentials/account host; missing tables means run migrations; missing Vite manifest means run `npm.cmd run build`; missing encryption key means run `php artisan key:generate`. After any `.env` changes run `php artisan config:clear`.

Official references: https://laravel.com/framework/docs, https://laravel.com/framework/docs/releases, https://tailwindcss.com/docs/installation/framework-guides/laravel/vite.
