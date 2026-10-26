# Ebook bundle preparation — 25 October 2026

The current checkout is a local preview, not an established production payment integration. On 25 October the user confirmed ChainEarn EverPass, INR 1,000, one-time billing, lifetime product access without scheduled expiry or renewal. No voluntary cancellation/refund follows successful payment and activation; duplicate charges, incorrect deductions and legally required refunds are exceptions. Refunded/chargeback-reversed payments lose their associated access. Razorpay server verification and reconciliation are required before activation. Production purchaser records are not available here.

Prepared a four-title catalog in config/ebooks.php and updated the existing homepage product section with the bundle wording and short descriptions. All four original PDFs are copied unchanged into ignored storage/app/private/ebooks. Source and destination SHA-256 hashes match for each file. The dedicated ebooks disk is private and does not serve temporary URLs. There is no public download route or link.

Do not use sim_purchases to unlock these files: those records represent fictional purchases. No paid entitlement, payment integration, customer download feature, purchaser backfill or deployment is claimed complete. Keep the original PDFs in private persistent storage when preparing a release; they are intentionally excluded from Git.

Required to continue: actual production URL and current source; hosting/deployment access and private configuration; Razorpay sandbox credentials/webhook secret; existing purchase/entitlement schema and records. Credentials must be supplied through private environment configuration, never source or reports. Deployment authorization is already supplied by the user for this change, conditional on passing checks.

End-to-end acceptance remains pending: verified sandbox payment, repeated events, failed/unverified payments, refund/cancellation/expiry behavior, eligible and ineligible download authorization, returning customers, existing-purchaser eligibility, complete PDF opening and mobile/desktop review.

Validation: Vite build with the native config loader and Blade compilation passed. The artisan test wrapper produced no output and was interrupted; direct PHPUnit then passed all 42 existing tests with 187 assertions. These validate existing application behavior, not the pending payment/download integration. The supplied PDF copies match the originals byte for byte; PDF opening/rendering has not been verified.

## Cover links follow-up

The four supplied PNGs are copied unchanged to public/images/ebooks and mapped explicitly in config/ebooks.php. Only covers are public; PDFs remain private. Homepage cover links open /ebooks/{catalog-key} in a new tab. Delivery requires authenticated, email-verified access and the access-paid-ebooks Gate ability, which currently has no production grant and therefore denies access. The future verified payment integration must implement that ability using authoritative paid entitlement state; simulation purchases must never satisfy it.

Styles preserve natural cover aspect ratios with responsive width, shrink on hover using scale(0.95) and a 250ms ease transition, expose keyboard focus, and disable motion for reduced-motion users. Tests exercise guest/default denial and exact inline PDF bytes using an explicit test-only authorization grant; they do not establish production purchase eligibility or constitute payment verification.

## Policy pages follow-up

Added public Privacy Policy, Terms & Conditions, Refund & Cancellation Policy, Digital Delivery Policy, About and Contact pages, linked from every page footer. Content reflects current data handling, the confirmed EverPass terms, and the pending payment/delivery implementation. The preview status must be updated when checkout actually launches. Plan details and confirmed tax inclusion are recorded in config/plan.php.

The user supplied merchant name `chain earn pvt.ltd` and public support email `info@chainearn.in`, now used as configurable defaults, and confirmed ₹1,000 includes all applicable taxes with no additional charges. Before publication, supply the business address, public support phone, and privacy/grievance contact name/email using BUSINESS_* environment settings documented in .env.example. Until completed, pages visibly mark these details as awaiting confirmation. The user considers an address unnecessary for a digital business; no exemption has been established, so the address field remains pending. Confirm retention practices, actual production processors and refund handling timelines before the final merchant policy review. No merchant approval or legal compliance certification is asserted. Checkout acceptance enforcement is still pending the checkout implementation.
