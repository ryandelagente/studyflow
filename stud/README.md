# stud/ — Legacy Development Snapshot

This directory is an **earlier version** of StudyFlow, preserved here for historical reference. It was the active codebase as of approximately October 2025, before the application was refactored and moved to the repository root.

## Key differences from the current root application

| Aspect | `stud/` (legacy) | Root (current) |
|--------|-----------------|----------------|
| Last modified | ~October 2025 | April 2026 |
| AI backend | Google Gemini API (`gemini-pro`) | OpenRouter API (model-agnostic) |
| API credentials | Hardcoded key in `config.php` | `secrets.php` (gitignored) |
| API endpoint count | 2 files | 7+ files |
| Admin panel | `admin/` subdirectory | Merged into `pages/users.php` |
| Multi-tenant support | Partial | Full |
| CSRF protection | Partial | All endpoints |

## Why is it still here?

The `stud/` directory is kept to provide a clear diff-able record of how the application evolved. It is **not deployed** and should not be used as a starting point for new installations.

**For installation, configuration, and usage, refer to the root-level [README.md](../README.md) and [INSTALL.md](../INSTALL.md).**
