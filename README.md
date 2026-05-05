# AI CRM System

AI CRM System is a beginner-friendly WordPress CRM plugin for managing leads, follow-ups, pipeline status, and notes from the WordPress admin dashboard.

It is designed as a clean first sellable version: simple enough to understand, but structured with safer WordPress practices like nonces, sanitization, escaping, capability checks, and uninstall controls.

## Features

- Modern WordPress admin CRM dashboard
- Add, edit, delete, search, and filter leads
- Lead statuses: New, Contacted, Qualified, Proposal, Won, Lost
- Follow-up date tracking
- Deal value tracking with configurable currency symbol
- Notes and activity history
- Quick status updates
- CSV export
- Settings page
- Optional data cleanup on uninstall
- Clean plugin file structure

## Installation

1. Copy the `ai-crm-system` folder into `wp-content/plugins/`.
2. Log in to WordPress admin.
3. Go to `Plugins`.
4. Activate `AI CRM System`.
5. Open `AI CRM` from the WordPress admin sidebar.
6. Go to `AI CRM > Settings` to configure currency, default status, and uninstall behavior.

## Screenshots

Screenshots can be added here before release:

1. Dashboard overview
2. Add/edit lead form
3. Settings page
4. CSV export workflow

Place screenshot files in the `screenshots/` folder.

## Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer
- MySQL or MariaDB supported by WordPress
- Administrator access for CRM management

## Testing Instructions

On a local XAMPP WordPress install:

1. Place this folder at `wp-content/plugins/ai-crm-system`.
2. Activate the plugin from `WP Admin > Plugins`.
3. Open `AI CRM`.
4. Add a lead with a follow-up date and note.
5. Search for the lead by name, email, company, or phone.
6. Filter by status.
7. Edit the lead and confirm the activity history updates.
8. Use the status dropdown in the table and confirm the status changes.
9. Export CSV and confirm the downloaded file contains your visible leads.
10. Open `AI CRM > Settings`, change the currency symbol and default status, then save.
11. Deactivate and uninstall only after deciding whether `Delete CRM data when the plugin is uninstalled` should be enabled.

## Data Removal

By default, uninstalling the plugin keeps CRM data in the database.

Data is deleted only when either:

- The setting `Delete CRM data when the plugin is uninstalled` is enabled, or
- The constant `AI_CRM_DELETE_DATA_ON_UNINSTALL` is defined as `true`.

## Changelog

See `CHANGELOG.md`.
