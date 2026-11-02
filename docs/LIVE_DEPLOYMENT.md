# MilesWeb deployment record

Updated 2 November 2026.

## Current deployment

- Public URL: https://chainearn.live
- Application directory relative to the hosting account home: `chain-earn-app`.
- Domain document root: `chain-earn-app/public`. The old document root was `caventrarcm.com`; do not deploy into that shared alias folder.
- Deployed application source: `026552aaee5ce488b3c5fd706bf1f9939566be2f` plus the production-only public `.htaccess` configuration saved in `deploy/milesweb.htaccess`.
- PHP web/CLI: 8.4.27. The CLI binary is `/opt/ecp-php84/bin/php`.
- Database: MariaDB 11.4, selected through Laravel's MySQL connection. Initial migrations completed.
- SMTP: `mail.chainearn.live`, implicit TLS (`MAIL_SCHEME=smtps`), port 465, username/sender `info@chainearn.live`. An authorized test was accepted by SMTP and confirmed received by the owner.
- Production `.env`, database credentials, APP_KEY, mailbox password, SSH private keys, private ebooks and user data remain outside Git.
- A private database/environment backup was created on the hosting account. Off-host backups, scheduled backup retention and a restore drill have not been verified.

The later security/release-preparation commit `b66a87b` is included in the repository but was not part of the above deployment. Pushing it does not deploy it.

## Verified behavior

Homepage, login, registration form, password recovery form and `/up` returned HTTP 200. The dashboard redirected unauthenticated requests to login. HTTP redirected to HTTPS, session cookies had Secure/HttpOnly attributes, compiled assets loaded, and requests for `.env` and `composer.json` were blocked. The production configuration preflight passed after SMTP setup.

Registration/verification/reset flows have not yet been tested end to end against the production mailbox. Payments, paid entitlements and payouts remain unavailable; simulations remain blocked in production. Private PDF files are excluded from source releases and must be managed separately.

## Future updates

Follow `RELEASE_RUNBOOK.md`. Build and test a reviewed revision, back up the database and private configuration, deploy source and compiled assets, preserve the server `.env` and persistent storage, install the locked production dependencies with PHP 8.4, apply reviewed migrations, refresh Laravel caches, and verify public pages and account flows.

Use `deploy/milesweb.htaccess` only on this production domain. It canonicalizes every hostname to `https://chainearn.live`; do not replace the portable `public/.htaccess` with it in local environments. Keep its error-page setting aligned with hosting configuration.

The GitHub validation workflow does not contain deployment credentials and does not publish to MilesWeb. Automatic deployment has not been configured.
