# AI CRM System 1.6.2 Release Notes

AI CRM System 1.6.2 is a small production-safety release.

## What's Changed

- Added backend validation for lead name and email.
- Invalid lead submissions now show a clean dashboard error notice.
- CSV imports now skip invalid rows instead of creating bad records.

## Testing Checklist

1. Upload `dist/ai-crm-system-1.6.2.zip` through WordPress.
2. Activate or update the plugin.
3. Open `AI CRM`.
4. Add a valid lead and confirm it saves.
5. Try an invalid email and confirm the plugin does not create the lead.
6. Import the sample CSV.
7. Confirm dashboard, analytics, and settings still render.
