# Contributing to StudyFlow

## Branch naming

| Type | Pattern | Example |
|------|---------|---------|
| Feature | `feature/<short-description>` | `feature/ai-voice-input` |
| Bug fix | `fix/<short-description>` | `fix/csrf-token-refresh` |
| Docs | `docs/<short-description>` | `docs/install-guide` |
| Hotfix | `hotfix/<short-description>` | `hotfix/login-redirect` |

Work from `main`. Open a pull request back to `main` when ready.

## Commit message style

Use the imperative mood and keep the subject line under 72 characters:

```
<type>: <short summary>

[Optional body explaining the why, not the what]
```

Types: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`.

Examples:
```
feat: add spaced-repetition interval to flashcards
fix: prevent duplicate CSRF token on concurrent requests
docs: expand INSTALL.md with Nginx config example
```

## Reporting issues

Open a GitHub Issue and include:

1. **Environment** — OS, PHP version, MariaDB/MySQL version, browser
2. **Steps to reproduce** — numbered, minimal
3. **Expected vs actual behaviour**
4. **Relevant logs** — PHP error log snippet, browser console output

For security vulnerabilities, please e-mail the authors directly (see README) rather than filing a public issue.
