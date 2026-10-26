# Chain Earn development instructions

Keep this Laravel application in the repository root. Preserve the user's `sample` file.

Work is local only. Do not deploy. Phase one covers Blade/Tailwind UI, session authentication, protected demo dashboard, and user/profile migrations. Do not implement real payment collection, wallet transfers, payouts, or referral commissions without a new user request. Clearly label sample metrics.

Do not store secrets in source or commit `.env`. If PHP, Composer, Node/npm, or MySQL are unavailable, report the exact missing tools and installation steps; do not claim checks passed or automatically install system prerequisites.

After prerequisites are installed, run appropriate authentication tests (`php artisan test`), Blade compilation, and the Vite build for relevant changes. Runtime checks were unavailable during the initial implementation; see README.md.
