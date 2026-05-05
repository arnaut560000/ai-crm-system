# Release Checklist

Use this before packaging AI CRM System for a public beta or paid release.

## Functional Testing

- Activate on a fresh WordPress install.
- Add at least 30 leads and confirm pagination works.
- Search by name, email, company, and phone.
- Filter every lead status.
- Edit a lead and confirm fields save.
- Add an activity note to an edited lead.
- Update status from the leads table.
- Delete a test lead.
- Export CSV with and without filters.
- Save settings for currency, default status, leads per page, and uninstall cleanup.

## Security Review

- Confirm all forms use nonces.
- Confirm admin actions require `manage_options`.
- Confirm outputs are escaped.
- Confirm database writes sanitize inputs.
- Confirm uninstall deletes data only when enabled.

## Product Readiness

- Add real screenshots to `screenshots/`.
- Create a release ZIP from the plugin folder.
- Test the release ZIP on a clean WordPress install.
- Write a short product page and setup guide.
- Decide whether this is a free beta, paid beta, or v1.0 release.
