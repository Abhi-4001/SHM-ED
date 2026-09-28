# VitalPath — Smart Health Monitoring

A PHP and MySQL educational early diabetes-risk screening project for XAMPP. It includes registration and sign-in, a private dashboard, a transparent rule-based assessment, recommendations, and account-scoped assessment history.

> **Health notice:** This project is educational screening only. It is not a diagnosis, medical advice, a validated clinical calculator, or a substitute for care from a qualified healthcare professional. A low result does not rule out diabetes. Do not delay care, change medication, or make treatment decisions based on this site.

## Requirements

- XAMPP with Apache, PHP 8.1+ (PDO MySQL and mbstring enabled), and MySQL/MariaDB.
- Internet access to load Bootstrap and Chart.js from the CDNs in the page header and dashboard. The app remains usable without the chart library, but the history chart will not render.

## Run with XAMPP

1. Copy the project folder to `C:\xampp\htdocs\smart-health-monitoring`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. In phpMyAdmin, import `database/schema.sql`. This creates the `smart_health_monitoring` database and its tables. Alternatively, run the SQL file using the MySQL command-line client.
4. Copy `config\local.example.php` to `config\local.php`. Adjust the database host, database name, username, and password for your local MySQL account. The shipped example has no password and contains no real credentials.
5. Open `http://localhost/smart-health-monitoring/` in a browser and create an account.

The ignored `config/local.php` is a machine-local override and must never be committed or included in a deployment archive. You may instead set `SHM_DB_HOST`, `SHM_DB_PORT`, `SHM_DB_NAME`, `SHM_DB_USER`, and `SHM_DB_PASSWORD` in the PHP/Apache environment. Set `SHM_SESSION_SECURE=1` only when serving over HTTPS.

For production, use HTTPS, a dedicated least-privilege database account, protected backups, security updates, and a web server configuration that denies direct access to configuration files. Do not expose a development XAMPP server to the public internet.

## What the screening rules do

The server calculates adult BMI from submitted height and weight, then adds simple educational points for age, BMI, glucose reading type/value, blood pressure, family history, low reported activity, smoking, and prior prediabetes/gestational diabetes/diabetes history. Gender is stored only when supplied and is not scored. Alcohol use is recorded as requested but not scored. A fasting glucose of at least 126 mg/dL, random glucose of at least 200 mg/dL, blood pressure at least 180 systolic or 120 diastolic, reported diabetes history, or five or more points produces a high category; two to four points produces moderate; zero or one produces low. These example thresholds do not establish a diagnosis; clinical interpretation and repeat testing may be needed. The rules are intentionally transparent, limited, and unsuitable as a clinical tool.

An entered blood pressure of 180/120 mmHg or higher is called out for urgent medical attention. Severe symptoms such as chest pain, shortness of breath, weakness, or confusion should prompt immediate contact with local emergency services. Other concerning screening results recommend clinician follow-up rather than asserting a diagnosis.

## Privacy and security

- Passwords use PHP `password_hash()` / `password_verify()`.
- Database operations use PDO prepared statements.
- Forms that change state use CSRF tokens; session cookies are HttpOnly, SameSite=Lax, and session IDs are regenerated on authentication.
- Assessment detail/history queries are scoped to the signed-in account.
- User-provided content is escaped before HTML output and input is validated server-side.
- Only the requested assessment fields and account name/email are stored. There are no free-text medical notes or third-party analytics.
- `config/local.php` is excluded by `.gitignore`; `config/.htaccess` denies direct Apache access to the config directory.

## Project layout

```text
assets/                 Responsive styles and browser scripts
config/                 Safe defaults and local configuration example
database/schema.sql     MySQL/MariaDB schema
includes/               Session, database, screening, and shared page layout
index.php               Public landing page
register.php/login.php  Account access
assessment.php          Validated assessment form and result persistence
result.php              Owner-checked detailed result
dashboard.php           Recent history and score chart
history.php             Account-scoped history
logout.php              CSRF-protected POST sign-out
```

## Validation

Run PHP syntax checks from the project directory:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
php tests/screening_rules_test.php
```

This demo does not include automated clinical validation or a production security certification. Review and validate any health-related rules with qualified professionals before considering any real-world use.
