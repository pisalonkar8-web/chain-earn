# Implementation plan

Update, 22 October 2026: Phase 1 implementation and automated checks are complete; browser acceptance and real SMTP remain pending. See [current status](PHASE_ONE_STATUS.md) and the root README. The prerequisite/missing-feature notes below describe the original plan, not current completion status. Later phases still require separate scope confirmation.

## 0. Audit and local prerequisites

Static audit and requirements documentation completed; environment setup blocked. Install PHP/Composer/Node/npm and configure a database using README. Resolve dependencies once because lockfiles are absent, review and retain lockfiles, then use locked installs. Preserve credentials and sample. Establish a passing baseline with migrations, authentication tests, Blade compilation and Vite build before feature changes.

## 1. Safe foundation (current authorized scope)

1. Add required mobile capture with server-side normalization/validation and backward-compatible profile storage. Do not invent phone numbers for existing accounts.
2. Add optional referral-code capture from invitation/form; validate against existing profiles and persist sponsor once in a transaction. Use an additive nullable foreign key for existing users. Prevent self-sponsorship, sponsor changes and input-based overrides; test organic and invited signup, invalid referral rollback and immutability. Decide deletion/retention policy before adding the sponsor foreign key.
3. Implement Laravel password broker recovery with expiring single-use tokens, password confirmation, generic request responses and throttling. Test invalid/expired/reused tokens, password hashing and session/token handling.
4. Implement MustVerifyEmail, registration notification, signed verification routes, resend throttling and verified dashboard access. Test unverified redirects, correct/incorrect signatures, expiry and cross-user links. Update existing registration tests for the new verification flow. Configure local mail capture first; verify real SMTP only after credentials are supplied privately.
5. Apply blue-gradient responsive styling and single-product preview copy; retain explicit DEMO labels and no checkout/payout execution. Check keyboard navigation, errors and mobile layouts.
6. Verify authorization boundaries and deny any admin capabilities until a role/permission design exists. Test guest, unverified and verified access separately.

Acceptance: migrations work on SQLite and MySQL development databases; `php artisan test`, `php artisan view:cache`, `php artisan view:clear`, and `npm.cmd run build` succeed; manual desktop/mobile auth and mail flows pass. Record actual output, not anticipated results. No existing migrations are rewritten when an additive migration is appropriate.

## 2. Simulated product/referral/admin design (requires confirmation)

Design one-product catalog/download entitlement, immutable sponsor tree, four-level eligibility simulation, permission-limited staff and audit logs. Use fictional events and DEMO data only. Define refund/tax/fee policies and sponsor retention. No real ledger credits or financial execution.

## 3. Provider sandbox (requires confirmation)

Integrate Razorpay test checkout only after account access and an approved design. Add signature verification, verified purchase state transitions, duplicate/out-of-order event handling, reconciliation and refund tests. Design an auditable ledger/reservation model and RazorpayX sandbox only if available for the approved account. No fake success endpoints and no live credentials.

## 4. Live-readiness gate (separate approval)

Require independent Indian legal review and payment-provider approval before any live payment, commission or payout functionality. Obtain product/domain/account readiness, business policies, hosting evidence, SMTP/SSL, operational monitoring and successful backup restore. Obtain explicit user authorization before deployment or live activation. This project is currently local only.

## 5. Future operations

GitHub-based releases with reviewed lockfiles, repeatable checks, controlled migrations and rollback plan. Daily encrypted off-host backups, restore drills, least-privilege access, queue/scheduler monitoring and ledger/provider reconciliation. Hosting requirements are in DEPLOYMENT_REQUIREMENTS.md; no deployment is performed by this plan.
