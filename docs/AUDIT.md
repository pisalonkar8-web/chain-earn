# Repository and environment audit

Historical audit. For the implementation and verification completed on 22 October 2026, see [Phase 1 status](PHASE_ONE_STATUS.md) and the root README. Missing-tool and missing-feature observations below describe the earlier state.

Date: 15 October 2026. Static review only; no runtime or vulnerability-clearance claim.

## Environment and preservation

- Working tree: existing Laravel application at the repository root. No Git commits exist on `master`; all project files are untracked. No reset, clean, checkout, staging or commit was performed.
- `sample` is a 12-byte file, not a folder. Preserved SHA-256: `797CCE1DAEAD21BD1C5D540E1D08E1B6A1D1D5CD95D8BAC48B2B54E2F079A437`.
- `Get-Command` found Git only: `C:\Program Files\Git\cmd\git.exe`, version `2.56.0.windows.2`.
- PHP, Composer, Node.js, npm and MySQL CLI were not found on PATH. This does not prove they are absent elsewhere on disk. No Windows service matching MySQL/MariaDB was returned; remote or differently named services remain unverified.
- `.env`, `vendor`, `node_modules`, `composer.lock` and `package-lock.json` are absent. No actual installed PHP or Laravel version can be reported.
- Prerequisite-dependent commands were intentionally stopped. No system prerequisites were installed.

## Source inspected

Reviewed Composer/npm manifests, routes, auth controller, models, migrations, factories/seeder, Blade views, CSS/JS, bootstrap/providers, configuration, PHPUnit configuration, feature/unit tests, `.env.example`, README, ignore rules and project instructions.

## Findings

| Area | Evidence and consequence |
| --- | --- |
| Versions | Composer requests PHP `^8.3`, Laravel `^13.17`, PHPUnit `^12.5.12`; package.json requests Node `>=22.12.0`, Vite `^8.0.0`, Tailwind 4. Laravel 13 and PHPUnit 12 require PHP 8.3+. The declared baselines align, but dependency resolution and platform checks remain unexecuted. |
| Reproducibility | No lockfiles exist. First installation must resolve versions, then preserve generated locks. Dependency advisories cannot be assessed reliably until that resolution is available. |
| Auth implemented | Registration/login validation, password hashing cast, transactional user/profile creation, session regeneration, POST logout/session invalidation, remember-me, six-per-minute auth throttling, Blade CSRF fields, escaped output and auth-protected dashboard exist in source. Runtime behavior is unverified. |
| Password reset missing | Token table and broker configuration exist, but no request/reset routes, controllers, forms or tests. A token table alone does not provide password recovery. |
| Email verification missing | User's verification contract is commented out; registration dispatches no Registered event; signed verification/resend routes and verified middleware are absent. Dashboard currently requires authentication only. |
| Signup incomplete | Only name/email/password are accepted. Profile has nullable `phone`, but signup does not populate it. Lowercase email validation rejects uppercase input rather than normalizing it. |
| Attribution absent | UUID referral URLs exist. GET `ref` only triggers an informational notice; the form does not persist it. No sponsor column, validation, immutable assignment or referral tracking tests exist. |
| Authorization | No admin routes, roles, policies, staff permissions or audit log exist. There is no exposed admin balance-edit endpoint. Admin authorization must be designed before adding admin capabilities. |
| UI | Responsive breakpoints, mobile menu and referral copy fallback exist. Palette is green, not the agreed blue gradient. Homepage presents several planned categories instead of the agreed single INR 1000 product. Demo statistics and fictional commission activity are labeled; sample figures are not business-rule calculations. |
| Local configuration | MySQL defaults need a real local database/account. APP_KEY is blank in the template; database sessions/cache require migrations. Mail defaults to log and does not deliver email. No SMTP credentials or service are configured. |
| Security posture | `.env`, vendor and node_modules are ignored. Local debug=true and HTTP defaults must never become production configuration. Secure-cookie setting is environment-dependent; HTTP-only and SameSite lax defaults exist. Log mail can contain future reset/verification links and must stay private. No live payment or payout endpoint was found. This static review is not a penetration test. |
| Tests | Existing feature tests cover guest protection, registration/profile/hash, duplicate/invalid signup, login/logout, incorrect password, throttling and profile cascade; homepage smoke test and trivial unit test exist. SQLite memory/array services and withoutVite are configured. No test results are available. |

## Commands and results

Executed read-only PowerShell inspection using `Get-Location`, `Get-Content`, `Get-Command`, `Get-Service`, `Get-Item`, `Get-ChildItem`, `Get-FileHash`, `Test-Path`, `rg --files`, and `rg -n`; executed `git status --short`, `git --version`, and `git log -1 --oneline`. Git log reported no commits. Manifest JSON parsing and sample hash/document checks were used for documentation verification.

Not executed: Composer/npm installs, artisan commands, migrations, PHP syntax checks, tests, Blade compilation, Vite build or browser verification. Missing runtimes block these checks, so none passed. Only documentation was changed during this audit.

## Unblock sequence

Follow README Windows prerequisite installation instructions, restart the terminal, and verify executable paths and versions. Preserve any existing credentials. Install dependencies, generate/review lockfiles, create a local key only when missing, configure MySQL or temporary SQLite, and run migrations. Run `php artisan test`, `php artisan view:cache`, `php artisan view:clear` and `npm.cmd run build`. Then implement and test the remaining Phase 1 foundation in the implementation plan. Phase 2 is not authorized by this audit.

## Primary compatibility references

- [Laravel 13 release notes](https://laravel.com/docs/13.x/releases)
- [PHPUnit 12.5 installation requirements](https://docs.phpunit.de/en/12.5/installation.html)
- [Vite installation requirements](https://vite.dev/guide/)
