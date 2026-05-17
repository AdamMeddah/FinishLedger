# FinishLedger

FinishLedger is a PHP, MySQL, JavaScript, and D3.js web application built for Finest Finish, a Hamilton-based finishing and renovation business. It tracks client orders, revenue, costs, and profit trends from one dashboard so the client can move routine analysis out of spreadsheets.

This repository is a polished portfolio iteration of an original McMaster 1XD3 client project. The application was cleaned up for public review with safer configuration, stronger validation, a clearer data model, asynchronous form workflows, and recruiter-friendly documentation.

## Resume Context

**Software Developer, Finest Finish**  
Jan. 2025 - May 2025

- Implemented regex input validation, cutting invalid form submissions by 85% and improving data reliability.
- Built a PHP and MySQL web application for tracking orders, costs, and revenue; reduced form submission latency by 80% (2.5s to 0.5s) through asynchronous JavaScript requests.
- Developed D3.js visualizations to surface order, cost, revenue, and profit trends, reducing manual spreadsheet-based analysis for the client.

## Features

- Order dashboard with total revenue, total cost, total profit, order count, and recent order activity.
- Add, update, and delete order records without full-page reloads using `fetch` and JSON PHP endpoints.
- Shared server-side regex validation for client names, service types, invoice IDs, currency values, dates, and registration fields.
- MySQL schema with decimal money columns, date indexing, service-type indexing, and clean sample data.
- D3.js charts for cumulative revenue, cost, and profit over time, plus profit by service type.
- Authentication flow using prepared statements and `password_hash` / `password_verify`.
- Environment-based database configuration with a local config file kept out of Git.

## Tech Stack

- PHP 8+
- MySQL / MariaDB
- JavaScript
- D3.js v7
- HTML5 and CSS3

## Project Structure

```text
css/              Shared app, login, and help page styles
data/             MySQL schema and seed data
html/             Static app pages
js/               Dashboard, async form, auth toggle, and D3 logic
php/              JSON endpoints, auth handlers, config, validation, database helpers
index.html        Main order dashboard
```

## Local Setup

1. Create the database and seed demo data:

   ```bash
   mysql -u root -p < data/schema.sql
   ```

2. Create a local PHP config file:

   ```bash
   cp php/config.example.php php/config.local.php
   ```

3. Update `php/config.local.php` with your local MySQL username and password.

4. Start a local PHP server from the repository root:

   ```bash
   php -S localhost:8000
   ```

5. Open the app:

   ```text
   http://localhost:8000/
   ```

Demo login:

```text
Email: demo@example.com
Password: DemoPass123!
```

## Validation Rules

The project uses matching client-side and server-side validation so invalid data is caught before it reaches MySQL:

- Client name: 2-80 characters, beginning with a letter.
- Service type: 3-80 characters, beginning with a letter.
- Revenue and cost: non-negative currency values with up to two decimals.
- Invoice ID: positive integer.
- Order date: valid `YYYY-MM-DD` date.

The PHP validation layer remains authoritative. Frontend validation improves feedback speed, while backend validation protects the database.

## API Endpoints

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `php/addfunctionality.php` | `POST` | Create an order and return calculated profit |
| `php/fetchEntry.php` | `POST` | Fetch a single order for editing |
| `php/updatefunctionality.php` | `POST` | Update an order |
| `php/deletefunctionality.php` | `POST` | Delete an order |
| `php/getSummary.php` | `GET` | Load dashboard metrics and recent orders |
| `php/getProfit.php` | `GET` | Load cumulative revenue, cost, and profit trend data |
| `php/getServiceTypeProfit.php` | `GET` | Load profit grouped by service type |

## Quality Checks

Run PHP syntax checks:

```bash
find php -name '*.php' -print0 | xargs -0 -n1 php -l
```

Run JavaScript syntax checks:

```bash
for file in js/*.js; do node --check "$file"; done
```

## Notes for Recruiters

This is intentionally presented as a public, portfolio-ready version of the project. The original class/client codebase was expanded and modernized so the repository demonstrates the engineering work behind the resume bullets: validation, asynchronous form handling, MySQL-backed CRUD operations, and D3.js reporting.
