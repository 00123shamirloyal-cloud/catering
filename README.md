# Daata Catering and Event Organizer

A Laravel application for the public catering website and its staff CRM. Public inquiries, website content, event records, proposals, invoices and offline payment records share one database.

## Requirements

- PHP 8.3 or newer with BCMath, Ctype, DOM, Fileinfo, Filter, Mbstring, OpenSSL, PDO, Tokenizer, XML, and the PDO driver for the chosen database.
- Composer 2.
- Node.js 20.19+ or 22.12+ with npm.
- SQLite for a local install, or MySQL 8.0+ for a hosted installation.

The application uses Laravel 13, Blade, Tailwind CSS 4, Vite and the Laravel DomPDF integration.

## Local installation

From the project root:

    composer install
    copy .env.example .env
    php artisan key:generate

For a local SQLite database, create the file if it does not exist:

    php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"

Set DB_CONNECTION=sqlite in .env, then run:

    php artisan migrate --seed
    php artisan storage:link
    npm ci
    npm run build
    php artisan serve

The local-only owner account is owner@example.test with the password in ADMIN_PASSWORD (the checked-in example value is ChangeMe-Local-Only). Change it before using the local database. Demo customers and financial records are not seeded. Website sample services, packages and FAQs are visibly marked as sample content; legal pages remain drafts.

## Configuration

| Variable | Purpose |
| --- | --- |
| APP_ENV, APP_DEBUG, APP_URL, APP_KEY | Application environment, error visibility, public URL and encryption key. Use production, false, a real HTTPS URL and a generated secret key in production. |
| APP_TIMEZONE | Application timezone. Defaults to Asia/Karachi; set this to the company’s operating timezone. |
| ADMIN_EMAIL, ADMIN_PASSWORD | Local/testing owner seed only. Production seeding deliberately creates no staff accounts. |
| EVENT_HOLD_HOURS | Lifetime of a tentative event hold. Defaults to 48 hours. |
| DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD | Database connection. The example uses SQLite; use the MySQL settings for a hosted database. |
| SESSION_DRIVER, CACHE_STORE, QUEUE_CONNECTION | Session, cache and queue stores. The example uses the database. |
| FILESYSTEM_DISK | Upload storage disk. The example uses Laravel’s local disk and public storage link for website images. |
| MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, MAIL_FROM_NAME | SMTP or other Laravel mail transport. The default log mailer does not send customer email. |

Do not put production passwords, SMTP secrets or payment credentials in source control. Store them in the hosting provider’s secret manager or protected environment configuration.

## Database

Run new migrations with:

    php artisan migrate --force

Local sample content is safe to seed only in local or testing environments:

    php artisan db:seed

Production does not seed a demo owner or demo customer records. After configuring the production database, create the first active owner using the interactive command:

    php artisan staff:create-owner

The command prompts for an email and a password of at least 12 characters and refuses to create another owner if an active owner already exists. Thereafter, the owner can add employees in Staff.

The current schema supports SQLite and MySQL. Run migrations against a disposable test database; never point php artisan test at production data.

## Frontend

After changing CSS or JavaScript, build production assets with:

    npm run build

For local hot reload:

    npm run dev

Uploaded content images are stored on Laravel’s public disk. Run php artisan storage:link once per deployment and give the web process write access to storage and bootstrap/cache.

## Daata branding

The default application and website name is **Daata Catering and Event Organizer**. The site settings page controls the editable business name, tagline, homepage copy and verified contact details. The rebrand migration updates only site settings that still match the old starter defaults; customized business values and customer, inquiry, event, quotation, invoice and payment records are left untouched. The database seeder now creates default settings and sample pages only when they do not already exist.

No official Daata logo was present in the project or uploaded media. The mark currently shown in the public header/footer, staff sign-in and CRM sidebar is an explicitly unofficial, replaceable placeholder at `public/images/daata-logo-placeholder.svg`; the matching placeholder favicon is `public/favicon.svg`. Replace those files with approved artwork when supplied, keeping the paths so the existing layouts update together. Do not treat the placeholder as official brand artwork.

The city is set to Rawalpindi, Pakistan, based on the supplied business brief. A map link points to the city only, not to a business address. Phone, email, full address, WhatsApp link and currency remain unconfigured until verified details are added in Website Settings. WhatsApp controls stay hidden until a link is entered. The booking budget choices avoid example amounts until Daata confirms its pricing.

Sample services, packages and FAQs remain marked as starter content and the site keeps a notice until the owner replaces or confirms them. Existing stock fallback photography is illustrative and is not Daata event photography; replace it through the website editor with approved images before launch. No testimonials, prices, phone numbers, client names or experience claims were invented.

## Authentication and local demo access

Staff sign in at /staff/sign-in. An owner can add and deactivate staff accounts. The server checks roles for staff, event, appointment, finance, payment, reports and website publishing routes; hiding a navigation link is not the authorization boundary.

Local sample account:

- Email: owner@example.test
- Password: ChangeMe-Local-Only (unless ADMIN_PASSWORD was changed before seeding)

This account is created only when APP_ENV is local or testing. Do not use this password on a public server.

## Email and queues

Configure MAIL_* with the company’s approved SMTP provider. Set MAIL_MAILER=smtp only after the provider settings are installed and verified. When the mailer is log, a proposal is not emailed; staff receive a private, expiring proposal link to share through an approved channel.

The queue driver defaults to database. To run a worker:

    php artisan queue:work --tries=3 --timeout=90

The current inquiry and contact forms save records for staff review. The current release does not dispatch customer acknowledgements, appointment reminders, or other notification jobs. Run a worker when queued notifications are added.

The scheduler can be triggered every minute by the host:

    php artisan schedule:run

No recurring reminder or follow-up schedule is currently registered. Verify php artisan schedule:list after adding scheduled work.

## Proposals, invoices and payments

Authorized sales and finance staff can build quotation revisions. Each revision keeps its own item and rate snapshot. Proposals can be sent through the configured Laravel mail transport or shared using an expiring token link. Proposal acceptance is recorded with an audit entry and does not confirm the event booking. Branded PDF downloads are generated on demand for proposals and invoices.

The current payment workflow records verified offline receipts, rejects overpayments, and uses unique transaction references for idempotency. Refunds and credit notes are separate, audited adjustment records. No card data is stored.

Online payment processing is not enabled. Select a provider and obtain its API credentials and webhook secret before implementing its checkout and signed webhook adapter. Never treat a browser redirect as proof of payment.

## Tests and formatting

Run the automated suite:

    php artisan test --compact

Run the Laravel formatter after editing PHP:

    vendor/bin/pint --format agent

## Production deployment checklist

1. Use a supported PHP 8.3+ runtime, MySQL database, HTTPS, and a real application URL.
2. Set APP_ENV=production, APP_DEBUG=false, a strong generated APP_KEY, and the correct APP_TIMEZONE.
3. Configure database, SMTP, file storage and queue credentials through protected environment settings.
4. Back up the existing database before migrations; then run php artisan migrate --force.
5. Build frontend assets with npm ci and npm run build.
6. Create the first owner with php artisan staff:create-owner; never seed local demo accounts in production.
7. Run php artisan storage:link, configure web-server document root to public/, and grant write access to storage and bootstrap/cache.
8. Keep a supervised queue worker running if queued jobs are introduced, and configure the scheduler if schedule:list shows recurring tasks.
9. Replace the placeholder mark with approved Daata artwork, review draft policy pages, confirm sample content, and add verified contact details and currency before launch.
10. Confirm mail delivery, backups, restore procedure, error logging, firewall rules and TLS renewal.

## Backup and restoration

For MySQL, create a consistent backup before deployments and on a schedule:

    mysqldump --single-transaction --routines --triggers --databases YOUR_DATABASE > backup.sql

Restore into the intended database with:

    mysql YOUR_DATABASE < backup.sql

For local SQLite development, stop the application and copy database/database.sqlite. Back up storage/app/public separately because uploaded images are files, not database rows. Test restores in a separate environment before relying on them.

## Current scope and external dependencies

Public browsing, database-backed website content with version history and restore, booking inquiries, contact submissions, staff authentication and roles, customer history, inquiry pipeline, follow-up task completion, appointments and calendar, tentative event holds and confirmation rules, quotation revisions and acceptance, invoices, offline payments, financial adjustments, CSV reporting, proposal/invoice PDFs, basic image uploads, site settings and audit entries are implemented.

SMTP delivery depends on valid provider settings. Google/Microsoft calendar sync, online payments and webhooks, outbound reminder automation, multi-image media-library workflows, event checklists and assignment tabs, and broader staff/customer CRUD remain future work. The application does not claim those integrations are active.
