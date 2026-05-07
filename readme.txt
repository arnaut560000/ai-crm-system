=== AI CRM System ===
Contributors: arnaut
Tags: crm, leads, sales, follow-up, admin
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.6.2
License: MIT
License URI: https://opensource.org/licenses/MIT

A clean WordPress admin CRM for tracking leads, follow-ups, notes, pipeline status, imports, and CSV exports.

== Description ==

AI CRM System is a lightweight CRM workspace inside WordPress admin. It helps site owners capture leads, update pipeline status, track follow-up dates, record activity notes, import leads, and export CRM data.

This version is intended for private beta, client demos, and early custom deployments.

== Features ==

* Add, edit, and delete leads.
* Search and filter leads.
* Update status per lead or in bulk.
* Import leads from CSV.
* Export leads to CSV.
* Track deal value and follow-up dates.
* View dashboard analytics for lead status and pipeline value.
* See a follow-up focus panel for upcoming active leads.
* Add notes and activity history.
* Configure currency, default status, records per page, and uninstall cleanup.

== Installation ==

1. Upload the plugin ZIP through `Plugins > Add New > Upload Plugin`, or copy the `ai-crm-system` folder into `wp-content/plugins/`.
2. Activate `AI CRM System`.
3. Open `AI CRM` from the admin sidebar.
4. Configure defaults under `AI CRM > Settings`.

== Frequently Asked Questions ==

= Does uninstall delete CRM data? =

Not by default. Data is deleted only if the delete-data-on-uninstall setting is enabled or the cleanup constant is defined.

= Who can access the CRM? =

Users with the `manage_options` capability.

== Changelog ==

= 1.6.2 =
* Added server-side validation for lead name and email.
* Improved invalid CSV/form handling.

= 1.6.1 =
* Reworked dashboard analytics into a clearer status distribution and pipeline value graph.

= 1.6.0 =
* Added lightweight dashboard analytics and follow-up focus panels.
* Improved import and settings panel polish.

= 1.5.1 =
* Added release packaging documentation and sample import data.

= 1.5.0 =
* Added CSV import and bulk lead workflows.

= 1.4.0 =
* Added pagination and activity-note improvements.

= 1.3.0 =
* Hard rebuilt plugin files for fresh installs.
