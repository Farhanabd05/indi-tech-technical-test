# Rencana Teknis Lanjutan `plan2.md`

## Ringkasan

Buat `plan2.md` di root proyek sebagai rencana teknis fase 2 setelah test backend inti lulus. Isi dokumen harus berangkat dari kondisi aktual kode: backend ticket workflow, assignment, internal notes, role policy, SLA, notifications dasar, dan test inti sudah berjalan; namun UI Blade, dashboard per role, ekspor CSV, API Sanctum, admin CRUD view/form, laporan, dan polish operasional belum selesai.

Dokumen harus menjadi roadmap implementasi yang bisa langsung dieksekusi tanpa menyalin ulang `PLAN.md`, dengan fokus pada sisa pekerjaan, urutan teknis, query, endpoint, view structure, dan acceptance criteria.

## Inventarisasi Sisa Tugas

- **Blade UI utama**
  - Ganti view ticket minimal menjadi UI fungsional penuh untuk index, create, show, comment, assignment, status update, attachment, dan activity timeline.
  - Bangun layout aplikasi dengan sidebar berbasis role, topbar notifikasi, flash message, filter form, pagination, dan modal konfirmasi.
  - Tambahkan seluruh view admin untuk role, user, category, label, priority, SLA rule, dan activity log.

- **Dashboard per role**
  - Ganti route `/dashboard` closure menjadi `DashboardController`.
  - Buat view dashboard: `admin`, `supervisor`, `agent`, `customer`.
  - Tambahkan query agregasi status, priority, overdue, workload, dan recent updates sesuai role.

- **Admin CRUD**
  - Controller admin untuk role/user/category/label sudah ada sebagian, tetapi belum lengkap dari sisi view, pagination, search, delete guard, dan priority/SLA/log CRUD.
  - Tambahkan `PriorityController`, `SlaRuleController`, `ActivityLogController`.
  - Tambahkan policy admin/master-data bila ingin enforcement selain middleware `role:administrator`.

- **Export CSV**
  - Tambahkan `ExportController` untuk streaming CSV tanpa menyimpan file.
  - Gunakan filter yang sama dengan ticket index.
  - Scope data berdasarkan role: admin semua, supervisor team, agent assigned, customer own tickets.

- **API Sanctum**
  - Sanctum belum terpasang dan `routes/api.php` belum ada.
  - Tambahkan endpoint API v1 untuk auth, ticket list/detail/create, status update, comment create, dan logout.
  - Tambahkan resource JSON yang menyaring internal notes untuk customer.

- **Notifications dan email**
  - Notification classes sudah ada, tetapi perlu audit channel `mail/database`, queue, mail view, dan wording.
  - Minimal satu notification harus `ShouldQueue`.
  - Pastikan `notifications` table, queue table, dan README queue/mail setup selesai.

- **Attachments**
  - Model/migration polymorphic sudah ada, tetapi UI upload/list/download authorization belum lengkap.
  - Tambahkan form upload di ticket show dan comment flow.
  - Tambahkan controller atau method download dengan `TicketPolicy::view`.

- **Reporting/logs**
  - Activity logging service sudah ada, tetapi belum ada halaman logs admin/supervisor/customer simplified timeline yang matang.
  - Tambahkan filter log dan render timeline per ticket.

## Arsitektur Blade

Struktur final `resources/views`:

```text
resources/views/
├── layouts/
│   ├── app.blade.php
│   └── guest.blade.php
├── components/
│   ├── sidebar.blade.php
│   ├── status-badge.blade.php
│   ├── priority-badge.blade.php
│   ├── confirm-modal.blade.php
│   ├── flash-message.blade.php
│   ├── empty-state.blade.php
│   └── filter-panel.blade.php
├── dashboard/
│   ├── admin.blade.php
│   ├── supervisor.blade.php
│   ├── agent.blade.php
│   └── customer.blade.php
├── tickets/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── filters.blade.php
│       ├── comment-form.blade.php
│       ├── comment-list.blade.php
│       ├── attachment-list.blade.php
│       ├── status-form.blade.php
│       ├── assign-form.blade.php
│       └── activity-timeline.blade.php
├── admin/
│   ├── roles/
│   ├── users/
│   ├── categories/
│   ├── labels/
│   ├── priorities/
│   ├── sla-rules/
│   └── logs/
└── emails/
    ├── ticket-created.blade.php
    ├── ticket-assigned.blade.php
    ├── ticket-commented.blade.php
    └── ticket-resolved.blade.php
```

Blade behavior wajib:

- Sidebar membaca `auth()->user()->role->slug` dan menampilkan menu sesuai role.
- Ticket index menampilkan filter `status`, `priority_id`, `category_id`, `label_id`, `assigned_agent_id`, `created_from/to`, `due_from/to`, `overdue`, `search`, `sort_by`, `sort_direction`.
- Ticket show menampilkan title, description, status badge, priority badge, assignee, due date, attachments, comments, dan activity timeline.
- `comment-list` tidak merender internal note untuk customer. Filtering dilakukan di server/view, bukan CSS.
- `status-badge` menerima enum/string status dan memetakan warna.
- `confirm-modal` dipakai untuk delete/reassign/status destructive actions.
- Admin forms menggunakan partial `_form.blade.php` per module untuk create/edit agar tidak duplikatif.

## Logika Dashboard

Tambahkan `app/Http/Controllers/DashboardController.php` dengan method `__invoke()` yang memilih query berdasarkan role dan mengembalikan view spesifik.

- **Customer dashboard**
  - Base query: `Ticket::where('created_by', auth()->id())`.
  - Cards: total own tickets, open tickets, resolved tickets, overdue own tickets.
  - Recent list: `latest('updated_at')->limit(5)`.
  - Status chart: `selectRaw('status, count(*) as total')->groupBy('status')`.

- **Agent dashboard**
  - Base query: `Ticket::where('assigned_agent_id', auth()->id())`.
  - Cards: assigned count, overdue assigned, in progress, waiting for customer.
  - Recently updated: assigned tickets ordered by `updated_at`.
  - Status distribution grouped by status.
  - Overdue query must exclude resolved/closed.

- **Supervisor dashboard**
  - Team agent IDs: `User::where('team_id', auth()->user()->team_id)->whereHas('role', slug agent)->pluck('id')`.
  - Base query: tickets assigned to those agent IDs.
  - Cards: team tickets, open, overdue, escalated.
  - Workload: group by `assigned_agent_id`, count active tickets.
  - Average resolution: `AVG(TIMESTAMPDIFF...)` for resolved/closed tickets where `resolved_at` is not null. For SQLite tests, keep dashboard integration test DB-agnostic or compute in PHP collection.

- **Admin dashboard**
  - Base query: all tickets.
  - Cards: total, overdue, unassigned, created this week.
  - Groupings: status, priority, category.
  - Top agents: resolved tickets grouped by `assigned_agent_id`, ordered desc, limit 5.
  - Average resolution: only tickets with `resolved_at`.

All dashboard queries must reuse model scopes where possible: `Ticket::overdue()`, `Ticket::unassigned()`, and role-scoped helper methods if extracted.

## Alur Export CSV

Tambahkan route:

- `GET /exports/tickets` named `exports.tickets`, middleware `auth`.
- Authorization: admin and supervisor can export; agent/customer optional only their scoped data if enabled. Default: admin/supervisor only.

Tambahkan `ExportController@tickets(Request $request)`:

- Build query from `Ticket::query()->with(['priority', 'category', 'creator', 'assignedAgent'])`.
- Apply role scope:
  - admin: no restriction
  - supervisor: assigned agent in same `team_id`
  - agent: `assigned_agent_id = auth()->id()`
  - customer: `created_by = auth()->id()`
- Apply same filters as ticket index.
- Return `response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv'])`.
- Inside callback:
  - `fopen('php://output', 'w')`
  - write header: `ticket_number,title,status,priority,category,customer,agent,created_at,due_at,resolved_at`
  - use `chunkById(500)` to avoid memory spike
  - write each row with `fputcsv`
  - close handle
- Do not store generated files in `storage`.

Acceptance: export respects role scope, filters, and streams valid CSV with no internal notes.

## Rancangan API Sanctum

Install and configure Sanctum:

- `composer require laravel/sanctum`
- publish/migrate Sanctum personal access tokens
- add `routes/api.php`
- ensure API routing is loaded in `bootstrap/app.php` if Laravel skeleton does not load it yet.

Routes:

```text
POST   /api/v1/login
POST   /api/v1/logout              auth:sanctum
GET    /api/v1/tickets             auth:sanctum
POST   /api/v1/tickets             auth:sanctum
GET    /api/v1/tickets/{ticket}    auth:sanctum + policy
PATCH  /api/v1/tickets/{ticket}/status   auth:sanctum + policy
POST   /api/v1/tickets/{ticket}/comments auth:sanctum + policy
```

Controllers/resources:

- `Api/V1/AuthController`
  - login validates email/password, returns `{ token, user }`
  - logout deletes current access token
- `Api/V1/TicketController`
  - index applies same role scope and filters as web
  - store reuses ticket creation service logic
  - show enforces `TicketPolicy::view`
- `Api/V1/TicketStatusController`
  - reuses `UpdateTicketStatusRequest`
- `Api/V1/CommentController`
  - reuses `StoreCommentRequest`
- `TicketResource`
  - include relations: category, priority, creator, assigned_agent, labels, attachments
  - include comments through `CommentResource`
  - customer must only receive public comments
- `CommentResource`
  - fields: id, user, body, is_internal only when viewer is non-customer, created_at
  - never leak internal comments to customer

API error format default:

```json
{
  "message": "Validation failed",
  "errors": {}
}
```

Use Laravel defaults unless custom wrapper is already introduced.

## Urutan Eksekusi

1. **Hardening fondasi UI**
   - Replace temporary ticket views with complete ticket index/show/create.
   - Add shared Blade components and role sidebar.
   - Add flash message and pagination styling.

2. **Dashboard**
   - Create `DashboardController`.
   - Replace `/dashboard` closure.
   - Add role-specific dashboard views and query tests.

3. **Admin CRUD completion**
   - Add missing Priority, SLA Rule, and Activity Log controllers/routes/requests.
   - Build all admin Blade views.
   - Add pagination/search to existing admin controllers.
   - Add delete guards consistently.

4. **Attachments**
   - Add upload handling to ticket/comment flows.
   - Add attachment list and download authorization.
   - Add validation for file size/type and storage link documentation.

5. **Export CSV**
   - Add `ExportController`, route, and stream download.
   - Reuse ticket filters and role scope.
   - Add feature tests for headers, row visibility, and customer/internal data exclusion.

6. **Notifications and queues**
   - Audit notification classes for `mail` and `database`.
   - Make at least one notification queued.
   - Add email Blade templates.
   - Verify `tickets:check-overdue` schedule and README instructions.

7. **Sanctum API**
   - Install/configure Sanctum.
   - Add API routes/controllers/resources.
   - Add API auth and ticket tests.
   - Confirm internal notes are excluded for customer JSON.

8. **Final acceptance**
   - Run `./test.sh`.
   - Add tests for dashboard, export, API, attachments, and admin CRUD.
   - Update README with setup, queue, seeded credentials, and API examples.

## Test Cases dan Skenario

- Blade ticket index shows only tickets visible to current role.
- Ticket show hides internal notes from customers and shows them to agent/supervisor/admin.
- Dashboard cards match seeded fixture counts per role.
- Export CSV respects filters and role scope.
- API login returns token; protected endpoints reject unauthenticated requests.
- API customer ticket detail excludes internal comments.
- Admin CRUD can create/edit/delete safe records and refuses deleting records linked to tickets/users.
- Attachment upload rejects unsafe file types and files over 2 MB.
- Notification database rows are created for assignment/resolution/escalation/overdue.
- `./test.sh` remains green after every module.

## Asumsi

- Role slug aktual proyek adalah `administrator`, `supervisor`, `agent`, `customer`; plan harus mengikuti slug aktual, bukan mengubah ke `admin`.
- Current backend tests are the regression baseline and must remain green.
- UI uses Blade + Breeze + Tailwind already present in the project.
- CSV export must stream directly to response and must not create temporary server files.
- API v1 will use token-based Sanctum for external clients, not SPA-cookie mode.
