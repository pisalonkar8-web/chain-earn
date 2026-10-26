# Chain Earn agreed specification

Status: requirements recorded on 15 October 2026, not a statement of implementation or regulatory approval. Local work only. Preserve the repository-root Laravel application and `sample` file.

## Phase 1 foundation

English, mobile-responsive Blade/Tailwind interface with blue-gradient SaaS styling. Free registration accepts name, email, mobile number, password/confirmation and optional referral code. Validate referral codes server-side; assign a sponsor once during account creation, atomically with the user/profile, and prevent later reassignment including through mass assignment. No sponsor is valid for an organic signup. Decide phone normalization and invalid-code UX explicitly before implementation.

Provide session login/logout, password recovery via Laravel's broker, SMTP-backed email verification with signed expiring links and throttled resends, protected routes and appropriate authorization. Keep public verification/recovery entry points usable without granting dashboard privileges. Never redisplay passwords. Demo balances, transactions, network counts and commissions must be clearly marked DEMO and have no financial effect.

Current implementation and remaining acceptance items are listed in PHASE_ONE_STATUS.md. Recovery and verification flows are implemented locally; real SMTP and browser acceptance remain pending. No admin dashboard is implemented.

## Future business rules (separate phase confirmation required)

- One downloadable digital product, price INR 1000. Registration is free and purchase is optional at signup. Product file is not ready.
- Four commission levels: L1 INR 500, L2 INR 250, L3 INR 100, L4 INR 50. Each referrer must have purchased the product to qualify. Missing/ineligible levels receive nothing, with no redistribution; the company retains unallocated amounts. These allocations are business requirements, not a validated tax/margin calculation.
- Activate eligible commissions immediately following verified eligible purchase, without a routine holding period. Refunds, reversals and fraud controls still require explicit handling.
- Customer checkout: planned Razorpay, initially test mode only. Browser redirects must never establish successful payment. Verify server-side provider evidence and authenticated webhook signatures; process retries idempotently.
- Withdrawals: minimum INR 500, UPI only, admin approval followed by planned RazorpayX payout. Actual disclosed payout fees are charged to the user. Define rounding, fee quotes and failure/refund treatment before implementation.
- Wallet: auditable ledger, atomic balance reservation, concurrency control, idempotent event processing, reconciliation and reversal handling. No uncontrolled credits or unrestricted balance edits.
- Admin: super admin and permission-limited staff; auditable actions and controlled adjustments with reason and authorization. No default/shared admin credentials.
- Email uses SMTP and email verification. Hosting target is the existing MilesWeb plan pending compatibility checks. Future deployment is GitHub-based. Backups run daily with restore verification.

## Mandatory gates

All commission and payout behavior remains simulated until independent Indian legal review and explicit payment-provider approval of this paid-product multi-level referral model. Do not claim regulatory approval. Do not implement fake successful payments. Live payments additionally require a ready product, domain, provider account, approved business/compliance documents and operational readiness. None of those are established by this specification. Phase 2 and live integration work require fresh user confirmation; documentation is not authorization to proceed.
