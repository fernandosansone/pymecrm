# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

"PymeCRM": a Spanish-language (UI and comments, Argentine formats) CRM built on Laravel 12 / PHP 8.2+, Breeze auth (Blade + Alpine.js), Tailwind, Vite, and `spatie/laravel-permission`. Domain: Contacts → Opportunities → Followups (+ Quotes/QuoteItems), plus Agenda, Dashboard, Commercial Report, and Users/Roles/Permissions admin.

## Commands

- `composer setup` — install, copy `.env`, key:generate, migrate, npm install + build
- `composer dev` — runs `artisan serve`, `queue:listen`, `pail`, and `npm run dev` concurrently
- `composer test` — clears config, then `php artisan test` (PHPUnit; sqlite `:memory:`, see `phpunit.xml`)
- Single test: `php artisan test --filter=TestName` or `php artisan test tests/Feature/ProfileTest.php`
- Format: `vendor/bin/pint`
- Seed roles/permissions: `php artisan db:seed --class=RolesAndPermissionsSeeder`
- Frontend: `npm run dev` / `npm run build`

Default DB is sqlite; session and queue use the `database` driver.

## Architecture

**Authorization is two-layered (Spatie permissions + Policies).** `database/seeders/RolesAndPermissionsSeeder.php` is the source of truth for permission names: `{resource}.{view|create|update|delete}` plus extras (`dashboard.view`, `agenda.view`, `opportunities.view_all`, `*.update_all`, `*.delete_all`, `*.change_status_all`, `followups.*_all`, `reports.export`). Roles: Admin (all), Supervisor (all except deletes on users/roles/permissions), Ejecutivo (basic CRM). Policies (registered in `AppServiceProvider` via `Gate::policy`) implement the ownership rule: a user needs the base permission, and then either the matching `*_all` permission or to be the `assigned_user_id` of the record. Adding a new permission means updating the seeder, and re-running it.

**Sidebar/menu.** Menu items are built per-request in `app/Services/SidebarData.php` (filtered by permission) and gated by feature flags in `config/pymecrm.php` → `modules.*` (env `PYMECRM_SHOW_*`). `ViewServiceProvider` separately injects `overdueCount` into `components.sidebar`; `SidebarData` has its own cached (60s per user) overdue computation — the two overlap. "Overdue" = opportunities assigned to the user whose latest followup (by `contact_date`) has `next_contact_date` < today; `AgendaMetricsService` is the other implementation of this.

**Routes.** All app routes in `routes/web.php` sit in one `auth` + `verified` group using `Route::resource`, with extras: `contacts/quick-store` (modal quick-create), `agenda/followups` (followup creation from agenda), `reports/commercial`, and JSON `opportunities/{opportunity}/followups` (`FollowupHistoryController`, feeds `components.followups-modal`). Route-level permission middleware is not used; authorization happens in controllers/policies.

**Enums** in `app/Enums` (`OpportunityStatus`, `UnitOfMeasure`) back status/unit fields. Form validation lives in `app/Http/Requests`.

**Frontend.** `resources/js/pymecrm.js` defines global Alpine helpers (e.g. `window.moneyInput` for AR-format `1.234,56` inputs that submit a raw `1234.56`). Shared Blade components (`card`, `table`, `badge`, `filterbar`, `modal`, `sidebar`, `nav-icon`) are in `resources/views/components`; badge colors are centralized in `config/pymecrm.php` → `badge_variants`. Default new-user role: `PYMECRM_DEFAULT_ROLE` (default `Ejecutivo`).

## Tests

Only Breeze-generated tests exist (`tests/Feature/Auth`, `ProfileTest`, example tests); there is no coverage of CRM domain logic yet.
