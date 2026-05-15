# Ticket Support System

A Laravel-based support ticket management system built around operational roles, SLA enforcement, auditability, and maintainable application architecture. The application supports the full ticket workflow between Customers, Agents, Supervisors, and Administrators, with clear boundaries for authorization, assignment, status changes, attachments, reporting, and destructive actions.

## Project Overview

Ticket Support System is a role-based support desk application for managing customer requests from creation to resolution. Customers can submit tickets and participate in conversations, Agents work on assigned tickets, Supervisors coordinate tickets inside their own teams, and Administrators manage the system with elevated operational privileges.

The project is designed as a Laravel technical implementation with strong emphasis on business-rule correctness, backend integrity, and readable Blade-based interfaces.

## Key Features & Business Logic

### Role-Based Access Control (RBAC)

The system enforces strict authorization boundaries through Laravel Policies and role-aware request validation:

- **Customer**: creates tickets, views only their own tickets, and participates in ticket conversations.
- **Agent**: works only on tickets assigned to them.
- **Supervisor**: manages assignment and operational status changes only for tickets scoped to agents in their own team.
- **Administrator**: has full management privileges, including system-wide ticket visibility and soft deletion.

### Advanced SLA Lifecycle

SLA timing is handled as part of the ticket status lifecycle:

- SLA resolution time is automatically **paused** when a ticket enters `Waiting for Customer`.
- When the ticket moves back to an active status, the paused duration is accumulated and the ticket due date is shifted forward.
- When a ticket is `Reopened`, the SLA due date is recalculated from the beginning based on the current priority rules.
- The overdue checker ignores tickets whose SLA clock is currently paused.

This prevents agents from being penalized for time spent waiting on customer response.

### Destructive Actions & Activity Logging

Destructive operations are intentionally constrained and auditable:

- Administrators can soft-delete tickets.
- Users can delete their own uploaded attachments, while Administrators can delete any attachment.
- Ticket deletion, attachment deletion, attachment upload, assignment, reassignment, status changes, and SLA overdue events are written to Activity Logs.
- Critical write flows use atomic database transactions so audit-log failures can roll back the surrounding operation instead of silently losing accountability.

### Server Optimization

The application includes safeguards for data-heavy operations:

- Ticket export is capped at **1,000 rows** to avoid excessive memory usage.
- Dashboard average resolution calculations are performed using SQL aggregation rather than loading all resolved tickets into PHP collections.
- SLA pause duration is subtracted directly in the dashboard aggregation query so performance metrics reflect actual working time.

## Architecture Highlights

The codebase follows pragmatic Laravel architecture patterns:

- **Skinny Controller, Fat Service**: controllers delegate business workflows to service classes such as ticket status handling, ticket creation, attachment management, and activity logging.
- **Form Request Validation**: request classes isolate validation and authorization entry points for ticket creation, filtering, assignment, updates, comments, and status transitions.
- **Policies for Authorization**: Laravel Policies define permission boundaries for tickets, attachments, comments, and operational actions.
- **Blade Components for DRY UI**: reusable components such as cards, tables, and status badges keep Blade templates cleaner and more consistent.
- **Database Transactions**: multi-step operations that affect important data are wrapped in transactions to preserve integrity.

## Installation Guide

### 1. Clone the Repository

```bash
git clone <repository-url>
cd indi-tech-technical-test
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure the Database

You can use either SQLite for a quick local setup or MySQL/MariaDB for a more production-like environment.

#### Option A: SQLite

Create the SQLite database file:

```bash
touch database/database.sqlite
```

Then update `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/indi-tech-technical-test/database/database.sqlite
```

#### Option B: MySQL or MariaDB

Create a database, then update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=indi_tech_ticket_support
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

The seeder creates roles, teams, priorities, categories, labels, and demo users for reviewer testing.

### 6. Link Public Storage

```bash
php artisan storage:link
```

This is required for ticket and comment attachments stored on the public disk.

### 7. Build Frontend Assets

```bash
npm run build
```

For active frontend development, use:

```bash
npm run dev
```

### 8. Start the Development Server

```bash
php artisan serve
```

The application is usually available at:

```text
http://127.0.0.1:8000
```

## Default Test Credentials

After running `php artisan migrate --seed`, use the following demo accounts:

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@admin.com` | `password` |
| Supervisor | `supervisor@admin.com` | `password` |
| Supervisor 2 | `supervisor2@admin.com` | `password` |
| Agent | `agent@admin.com` | `password` |
| Agent 2 | `agent2@admin.com` | `password` |
| Customer | `customer@demo.com` | `password` |

Additional seeded users are generated for broader team and ticket-scope testing.

## Running Tests

Run the automated test suite with:

```bash
php artisan test
```

The test suite covers feature behavior, authorization integration, ticket lifecycle rules, SLA logic, destructive access control, filtering, dashboard reporting, and isolated business logic.

## Operational Notes

### Queue Worker

If notifications are queued in your environment, keep a queue worker running:

```bash
php artisan queue:work
```

### Scheduler

The SLA overdue checker is intended to run through Laravel's scheduler. On a server, configure cron to execute:

```bash
* * * * * cd /path/to/indi-tech-technical-test && php artisan schedule:run >> /dev/null 2>&1
```

For local verification, scheduled commands can also be run manually when needed.
