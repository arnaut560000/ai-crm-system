# Testing Guide

Use this checklist before delivering the plugin to a client or publishing a release.

## Fresh Install Test

1. Install WordPress locally in XAMPP.
2. Upload `dist/ai-crm-system-1.5.1.zip`.
3. Activate the plugin.
4. Confirm `AI CRM` appears in the sidebar.

## CRM Workflow Test

1. Add a lead.
2. Edit the lead.
3. Add an activity note.
4. Update the lead status from the table.
5. Search by name, email, company, and phone.
6. Filter by each status.
7. Add enough leads to trigger pagination.
8. Test bulk status change.
9. Test bulk delete.
10. Export CSV.
11. Import `sample-data/leads-import-sample.csv`.
12. Save settings.
13. Confirm uninstall cleanup stays disabled unless explicitly enabled.

## Expected Result

No fatal errors, no broken layout, no unescaped visible HTML, and no data loss unless delete-data-on-uninstall is enabled.
