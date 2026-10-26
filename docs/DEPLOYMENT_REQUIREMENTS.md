# Future deployment requirements

Planning only. No deployment is authorized. MilesWeb compatibility is unverified: obtain the exact plan and written capability details before selecting an approach. A generic claim of Laravel support is insufficient.

| Requirement | Evidence to obtain before deployment |
| --- | --- |
| PHP | Web and CLI PHP >=8.3 compatible with the eventual Composer lock; preferably a maintained 8.4/8.5 build. Match CLI/FPM versions and run Composer platform checks. |
| Extensions | Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer and XML; pdo_mysql for MySQL; pdo_sqlite/sqlite3 for local tests. ZIP for dependency/archive tooling; OPcache recommended. Confirm actual php.ini and disabled functions. |
| Dependencies/build | Composer 2, SSH or an approved release mechanism, adequate memory/disk. Build assets using compatible Node >=22.12 and the lockfile in CI if hosting lacks Node. Deploy compiled assets and production Composer dependencies; never expose node_modules. |
| Database | A supported MySQL service, separate restricted application credentials, InnoDB transactions/foreign keys, utf8mb4 and backup/restore access. Validate host-specific version and limits. SQLite is a local/testing alternative, not proof of production ledger suitability. |
| Web root | Domain document root must point to Laravel `public/`. Keep `.env`, source, logs, private product files and backups outside public access. Enable appropriate front-controller rewrites and directory listing protections. |
| Queues | Current auth source does not require a worker. Future queued mail/provider jobs require durable queues and supervised workers, restart controls, failed-job alerts and retry limits. Shared hosting worker restrictions may require a plan upgrade or managed worker service. |
| Scheduler | Future scheduled tasks require reliable minute-level cron invoking `php artisan schedule:run`, correct working path/PHP binary, overlap locks and monitoring. Check plan limits. |
| SMTP | Authorized transactional SMTP with TLS, private credentials, sender/domain verification, SPF/DKIM/DMARC and deliverability checks. Log mail is for local work, not delivery. |
| SSL/session | Valid auto-renewed certificate, HTTPS APP_URL, secure cookies, HTTP-only/SameSite settings and correct proxy trust. Set APP_ENV=production and APP_DEBUG=false. Restrict debug/log access. |
| Storage | Writable storage and bootstrap/cache, correct ownership, private downloadable product storage and future entitlement-checked delivery. Public symlink only for deliberately public files. Check storage/bandwidth quotas. |
| GitHub release | Private secrets outside source, reviewed CI checks and lockfiles, restricted deployment credentials, controlled migrations, health checks and rollback. Never deploy `.env` from Git or commit vendor/node_modules. |
| Backups | Daily database and private-file backups, encrypted off-host copies, retention/access policy and successful restore drill. Preserve APP_KEY securely for recovery; agree recovery objectives before launch. |
| Monitoring | Application/queue/scheduler alerts, disk/certificate checks, restricted audit logs and eventual payment reconciliation with accountable operators. |
| Provider networking | For a future approved integration, confirm outbound HTTPS/TLS and inbound authenticated webhooks, time synchronization, timeout/retry behavior and any RazorpayX account/network restrictions. |

Ask MilesWeb for the plan name, PHP selector and CLI versions/extensions, SSH/Composer access, custom public document root support, MySQL version, cron frequency, persistent-worker support, symlink permissions, resource limits, outbound SMTP/HTTPS policies, certificate renewal and backup restore procedure. Do not assume these are included in an existing plan.

Live activation requires independent Indian legal review and explicit payment-provider approval of the actual business model, plus ready product file, domain, Razorpay account and business compliance approvals. All commissions/payouts remain simulated until those gates and a fresh user authorization are satisfied. No regulatory approval is asserted.
