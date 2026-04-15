# StudyFlow — Database Migrations

Run these in phpMyAdmin (database: `productivity_hub`) in order.

## Required (run once after fresh install)

| File | What it does |
|------|-------------|
| `productivity_hub.sql` | Main schema — all base tables |
| `add_study_sessions.sql` | Creates `study_sessions` table for the Studied Today widget |
| `add_access_logs.sql` | Creates `access_logs` table for login history |
| `add_tenant_columns.sql` | Adds `tenant_id` to `flashcards` and `goal_categories` |

## Seed data (optional — wipes all data!)

| File | What it does |
|------|-------------|
| `seed.php` | Creates 2 tenants + 4 demo users with sample content. Access via browser. **Delete after use.** |

## Demo accounts (after running seed.php)

| Email | Password | Role | Workspace |
|-------|----------|------|-----------|
| alice@demo.com | alice123 | admin | Alice's Workspace |
| bob@demo.com | bob123 | member | Alice's Workspace |
| carlos@demo.com | carlos123 | admin | Carlos' Workspace |
| diana@demo.com | diana123 | member | Carlos' Workspace |
