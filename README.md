# Oxfordia Institute

Website and management system for a training institute. It includes a public course catalogue, online course purchase, a student portal with results, an admin back office, and certificates that can be verified online.

Built with Laravel 13 (PHP 8.4), Blade, Tailwind CSS v4, Alpine.js, Laravel Fortify, dompdf and chillerlan/php-qrcode.

## Features

| Area | What it does |
|---|---|
| Public site | Home, About, Courses (search and category filter), Admissions enquiry, Contact, Certificate verification |
| Student accounts | Self-registration (student role only), email verification, password reset |
| Checkout | Buy a priced course → pending order → payment gateway → enrollment + registration number |
| Student portal | Read-only view of enrollments, module-wise marksheet, certificate PDF downloads |
| Admin / staff | Courses and modules, categories, students, enrollments, marks entry grid, orders, enquiries, contact messages |
| Admin only | Issue and revoke certificates, institute settings and grade bands, admin/staff accounts, activity log |

### Business rules
- **Results:** a student must reach the pass mark in **every** module. The overall percentage then maps to the grade bands set in **Settings** (default A+ ≥ 90, A ≥ 75, B ≥ 60, C ≥ 50, otherwise Fail).
- **Certificate eligibility:** a certificate can be issued only when the enrollment is **completed** and **passed**.
- **One valid certificate per enrollment:** to correct an error, revoke the certificate and issue a replacement. Revoked certificates still verify publicly as *revoked*.
- **Locked marks:** marks can't be changed while a valid certificate exists. A module's max and pass marks lock once any marks are recorded.
- **Duplicate enrollments:** a student can't enroll in the same course twice while an existing enrollment is active or passed. Re-enrolling is allowed after withdrawal or a fail.
- **No deleting history:** courses and students with enrollments, orders or certificates can't be deleted. Archive the course or mark the student inactive instead.
- **Enquiries don't create students:** they become students only when staff use **Create student from enquiry**.
- **Number formats:** `OXF-2026-0001` (student), `ENR-2026-00001` (enrollment), `OXF-CERT-2026-000001` (certificate). QR codes link to `/verify-certificate/{32-character random identifier}`.

## Local setup (Laragon / Windows)

```bash
composer install
npm install
cp .env.example .env          # then edit the values below
php artisan key:generate
```

Edit `.env`:

```dotenv
DB_CONNECTION=mysql
DB_DATABASE=oxfordia_db       # create this empty database first (HeidiSQL or `CREATE DATABASE oxfordia_db`)
DB_USERNAME=root
DB_PASSWORD=
PAYMENT_DRIVER=dummy          # simulated gateway – development only
SEED_ADMIN_EMAIL=admin@oxfordia.test
SEED_ADMIN_PASSWORD=choose-a-strong-local-password
```

Then:

```bash
php artisan migrate
php artisan db:seed           # sample courses/students + the admin account from SEED_ADMIN_*
php artisan storage:link      # public URLs for uploaded images, syllabi and signatures
npm run build                 # or `npm run dev` while editing views
composer run dev              # runs the server, queue worker and Vite together
```

With Laragon, open `http://oxfordia.test`. If it shows the Laragon start page, reload Apache from the Laragon menu (**Menu → Apache → Reload**). Without Laragon, use `php artisan serve`.

The seeder never deletes data. It skips sample courses when courses already exist, and it creates no admin unless `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD` are set.

### Email and queues
Emails are sent through the queue: verification, purchase receipts, certificate issued, and alerts to the institute email about new enquiries and messages. Run a worker with `php artisan queue:work`; `composer run dev` already includes one. Password-reset and invitation emails are sent immediately rather than queued, so that live tokens are never stored in the `jobs` table. With `MAIL_MAILER=log`, messages go to `storage/logs/laravel.log`.

## Tests

```bash
php artisan test --compact
```

Tests use in-memory SQLite (see `phpunit.xml`) and never touch the MySQL database.

## Deploying to Hostinger shared hosting

1. Upload the project. Point the domain's document root at `public/`, or move the contents of `public/` into `public_html` and fix the paths in `index.php`.
2. Set up `.env` with `APP_ENV=production`, `APP_DEBUG=false`, the real `APP_URL`, the MySQL credentials and SMTP mail settings. Run `php artisan key:generate` if needed.
3. Run `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, `php artisan config:cache route:cache view:cache`, and upload the built `public/build` folder (build locally with `npm run build`).
4. **Storage link:** run `php artisan storage:link`. If symlinks are blocked, create the link from the hosting file manager, or set the `public` disk root to a folder inside `public_html`.
5. **Queue:** shared hosting can't run long-lived workers. Add a cron job that runs every minute:
   `cd /path/to/app && php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1`
6. **Payments:** the `dummy` gateway is **refused when `APP_ENV=production`**. A real gateway (for example Razorpay) has to implement `App\Payments\PaymentGateway` and be registered in `AppServiceProvider` before online sales can go live.
7. Create the first administrator with `php artisan tinker` or a one-off seed using `SEED_ADMIN_*`, then remove those values from `.env`.

## Customising
- **Branding, contacts, map, signatories, certificate text and grade bands:** *Admin → Settings*.
- **Number prefixes:** `config/institute.php` (`INSTITUTE_STUDENT_PREFIX`, `INSTITUTE_CERTIFICATE_PREFIX`).
- **Placeholder text:** marked `[Placeholder]`. Replace it in *Settings* and in `resources/views/public/about.blade.php`.
- **Accreditation statement:** leave it blank unless the institute's accreditation or recognition has actually been established.
