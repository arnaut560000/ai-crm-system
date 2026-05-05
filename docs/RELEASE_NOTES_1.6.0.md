# AI CRM System 1.6.0 Release Notes

AI CRM System 1.6.0 focuses on making the dashboard feel more like a sellable CRM product without adding heavy dependencies.

## What's New

- Status Distribution analytics panel.
- Pipeline Value analytics panel grouped by status.
- Follow-up Focus panel for active leads with scheduled follow-ups.
- Better styled CSV import file input.
- Cleaner settings checkbox alignment.
- Version bump for refreshed admin styles.

## Testing Checklist

1. Install the release ZIP through `Plugins > Add New > Upload Plugin`.
2. Activate `AI CRM System`.
3. Open `AI CRM`.
4. Confirm dashboard metrics, analytics, and follow-up focus render.
5. Add a lead with a deal value and follow-up date.
6. Update the lead status and confirm the analytics bars change.
7. Import `sample-data/leads-import-sample.csv`.
8. Export leads to CSV.
9. Open `AI CRM > Settings`, save settings, and confirm the checkbox alignment is clean.

## Release Package

Use `dist/ai-crm-system-1.6.0.zip` for beta/customer installs.
