# AI CRM System

AI CRM System is a clean, beginner-friendly WordPress CRM plugin for managing leads inside the WordPress admin area.

It is built as a simple first sellable version with lead capture, editing, status tracking, search/filtering, follow-up dates, notes/activity history, settings, and CSV export.

## Features

- AI CRM admin menu inside WordPress
- Add, edit, and delete leads
- Update lead status from the leads table
- Search leads by name, email, company, or phone
- Filter leads by status
- Track deal value and follow-up dates
- Notes and activity history
- Separate activity notes on lead records
- CSV export
- Settings page for currency symbol and default status
- Configurable leads per page
- Optional delete-data-on-uninstall setting
- Safe uninstall cleanup helper

## Installation

1. Copy the `ai-crm-system` folder into `wp-content/plugins/`.
2. Open WordPress admin.
3. Go to `Plugins`.
4. Activate `AI CRM System`.
5. Open `AI CRM` from the WordPress admin sidebar.
6. Open `AI CRM > Settings` to configure currency, default status, and uninstall cleanup.

## Screenshots

Add screenshots to the `screenshots/` folder before release:

1. Dashboard overview
2. Add/edit lead form
3. Leads table
4. Settings page
5. CSV export

## Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer
- MySQL or MariaDB supported by WordPress
- Administrator access

## XAMPP Testing Instructions

1. Place this folder at `C:\xampp\htdocs\mywordpress\wordpress\wp-content\plugins\ai-crm-system`.
2. Start Apache and MySQL in XAMPP.
3. Open your local WordPress admin.
4. Go to `Plugins` and activate `AI CRM System`.
5. Confirm the `AI CRM` menu appears in the admin sidebar.
6. Add a lead with name, email, status, deal value, follow-up date, and notes.
7. Search for the lead.
8. Filter by status.
9. Edit the lead and save changes.
10. Use the status dropdown in the leads table and click `Update`.
11. Delete a test lead.
12. Click `Export CSV` and confirm a CSV file downloads.
13. Open `AI CRM > Settings`, change currency/default status, and save.
14. Change `Leads Per Page` and confirm pagination behaves correctly after adding enough leads.

## Uninstall Behavior

By default, uninstalling keeps CRM data.

CRM data is deleted only when:

- `Delete CRM data when the plugin is uninstalled` is enabled in settings, or
- The constant `AI_CRM_DELETE_DATA_ON_UNINSTALL` is defined as `true`.

## Changelog

See `CHANGELOG.md`.

## Release Readiness

Before selling publicly, complete the checklist in `RELEASE_CHECKLIST.md`.
