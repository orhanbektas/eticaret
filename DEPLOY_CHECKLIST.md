# Production Deploy Checklist (cPanel Git)

## 1) Backup
- Create full file backup zip from `public_html`.
- Export MySQL database dump.

## 2) Database Repair
- Run `database-repair.sql` on production database.

## 3) Deploy
- Ensure cPanel Git repository is up to date.
- Set `DEPLOY_PATH` to target directory (usually `/home/<user>/public_html`).
- Run `Deploy HEAD Commit` from cPanel Git interface.

## 4) Smoke Test
- `GET /api`
- `GET /api/health`
- `GET /api/settings/public`
- `GET /api/products`
- `GET /api/blog`
- Refresh frontend deep links (no 404).

## 5) Post-Deploy Log Check
- Inspect `logs/error.log`.
- Confirm no new SQL column/enum errors and no router 500 errors.

## 6) Rollback
- Restore file backup zip.
- Restore DB dump.
