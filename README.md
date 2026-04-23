# StudyFlow

**An AI-powered, multi-tenant productivity platform for students and educators.**

StudyFlow is a self-hosted web application that combines an AI tutor, rich note-taking, flashcards, assignment tracking, study goals, a calendar, contacts, a spreadsheet editor, and a browser-based code editor into a single platform. It is built on plain PHP/MySQL and requires no framework or build step to deploy.

**Authors:** Ryan J. de la Gente and Oliver D. Pavillar
**Affiliation:** College of Computer Studies, Carlos Hilado Memorial State University, Talisay Campus, Bacolod City, Negros Occidental, Philippines
**Contact:** ryan.delagente@chmsu.edu.ph · oliver.pavillar@chmsu.edu.ph

**License:** [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
**DOI:** [![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.19711045.svg)](https://doi.org/10.5281/zenodo.19711045)

---

## Features

- **AI Tutor** — multi-turn chat powered by the OpenRouter API (model-agnostic)
- **AI-assisted Notes** — rich-text note editor with inline AI suggestions via TinyMCE 6
- **Flashcards** — create and review card decks with AI-generated hints
- **To-Dos** — task lists with AI-powered breakdown suggestions
- **Assignments** — assignment tracker with due dates and AI assistance
- **Study Goals** — goal-setting and progress tracking with AI coaching
- **Calendar** — monthly/weekly view with event management and AI scheduling tips
- **Contacts** — personal address book
- **Spreadsheets** — in-browser spreadsheet powered by Handsontable
- **Code Editor** — browser-based IDE using Monaco Editor with AI code assistance
- **Multi-tenant architecture** — each organisation or class is an isolated tenant; data never crosses tenant boundaries
- **Access logs** — per-tenant audit trail of user activity
- **CSRF protection** — token-based protection on every state-changing request

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0+ |
| Database | MySQL 5.7+ or MariaDB 10.3+ |
| Styling | Tailwind CSS (CDN) |
| Icons | Lucide Icons (CDN) |
| Rich-text editor | TinyMCE 6 (CDN) |
| Spreadsheet | Handsontable (CDN) |
| Code editor | Monaco Editor (CDN) |
| AI gateway | OpenRouter API |

---

## Prerequisites

- **Web server:** Apache 2.4+ (mod_rewrite enabled) or Nginx 1.18+
- **PHP:** 8.0+ with PHP-FPM and the extensions: `mysqli`, `curl`, `json`, `session`, `openssl`
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **OpenRouter account:** free tier available at <https://openrouter.ai>

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/ryandelagente/studyflow.git
cd studyflow
```

### 2. Create the database and import the schema

```sql
CREATE DATABASE productivity_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
mysql -u root -p productivity_hub < sql/productivity_hub.sql
```

### 3. Configure secrets

```bash
cp secrets.example.php secrets.php
```

Open `secrets.php` and set your OpenRouter API key:

```php
define('OPENROUTER_API_KEY', 'sk-or-v1-...');
```

### 4. Configure the database connection

Edit `config.php` and update the four `DB_*` constants to match your environment:

```php
define('DB_SERVER',   'localhost');
define('DB_USERNAME', 'your_db_user');
define('DB_PASSWORD', 'your_db_password');
define('DB_NAME',     'productivity_hub');
```

### 5. Point your web server at the repo root

**Apache** — set `DocumentRoot` to the cloned directory and ensure `AllowOverride All` is enabled.

**Nginx** — set `root` to the cloned directory and pass `.php` requests to PHP-FPM.

For a step-by-step walkthrough on Ubuntu 22.04 and Windows/XAMPP see [INSTALL.md](INSTALL.md).

---

## First Run

1. Open `http://localhost/studyflow/register.php` and create your account.
2. Log in at `http://localhost/studyflow/login.php`.
3. You will land on the main dashboard (`index.php`).

---

## Configuration

### Changing the AI model

The default model is `nvidia/nemotron-3-super-120b-a12b:free` (free tier on OpenRouter). To use a different model, add the following constant to your `secrets.php`:

```php
define('OPENROUTER_MODEL', 'openai/gpt-4o');
```

Any model slug listed at <https://openrouter.ai/models> can be used. Free models have `:free` appended to their slug.

---

## Project Structure

```
studyflow/
├── api/                  # Server-side API endpoints (JSON responses)
│   ├── ai-chat.php       # AI tutor — calls OpenRouter and persists chat history
│   ├── ai-assist.php     # Inline AI assist for notes, todos, etc.
│   ├── editor/           # Code-editor backend (file CRUD, AI code agent)
│   ├── get-free-models.php
│   ├── load-chat.php
│   ├── save-session.php
│   └── update-plan.php
├── assets/               # Global CSS and JS
│   ├── style.css
│   └── js/main.js
├── pages/                # Feature pages (require login)
│   ├── ai-tutor.php
│   ├── notes.php
│   ├── flashcards.php
│   ├── todos.php
│   ├── assignments.php
│   ├── study-goals.php
│   ├── calendar.php
│   ├── contacts.php
│   ├── sheets.php
│   ├── code-editor.php
│   ├── access-logs.php
│   └── ...
├── partials/             # Shared HTML fragments (header, sidebar, footer)
├── sql/                  # Database schema and incremental migrations
│   └── productivity_hub.sql   # Full schema — import this on a fresh install
├── config.php            # Database credentials, CSRF helpers, session helpers
├── secrets.example.php   # Template — copy to secrets.php and fill in keys
├── index.php             # Main dashboard (requires login)
├── login.php             # Login form
├── register.php          # Registration form
├── logout.php            # Session teardown
└── landing.php           # Public landing page
```

### Repository structure notes

As of v1.0.1, the repository contains a single canonical application at the root. An earlier `stud/` subdirectory from the development phase was removed because it duplicated the root application, used an older API integration, and risked confusing reviewers and contributors who clone the repository.

---

## Security Notes

- **CSRF tokens** — every state-changing request (POST and XHR) requires a valid per-session CSRF token.
- **Session authentication** — all pages and API endpoints check `$_SESSION['loggedin']` and redirect or return HTTP 401 if the check fails.
- **Prepared statements** — all database queries use `mysqli_prepare` / `mysqli_stmt_bind_param` to prevent SQL injection.
- **Per-tenant data isolation** — every query filters by `tenant_id`, which is set at login and never exposed to the client.

---

## Citation

If you use StudyFlow in your research, please cite the following article:

> de la Gente, R. J., & Pavillar, O. D. (2026). StudyFlow: An AI-powered multi-tenant
> productivity platform for students and educators. *Software Impacts*.
> https://doi.org/10.1016/j.simpa.XXXX.XXXXXX   ← replace with actual DOI after acceptance
>
> **Software archive:** https://doi.org/10.5281/zenodo.19711045

---

## Support

For bug reports and feature requests open a GitHub Issue. For direct correspondence email ryan.delagente@chmsu.edu.ph.
