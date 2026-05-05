# Privacy Notes

AI CRM System stores lead data in custom WordPress database tables.

## Stored Data

- Lead name
- Email
- Phone
- Company
- Status
- Source
- Deal value
- Follow-up date
- Notes
- Activity history

## Data Location

Data is stored in:

- `{prefix}_ai_crm_leads`
- `{prefix}_ai_crm_activities`

## Data Export

Admins can export lead data to CSV from the CRM dashboard.

## Data Deletion

Uninstall does not delete CRM data by default.

Data is deleted only when:

- `Delete CRM data when the plugin is uninstalled` is enabled, or
- `AI_CRM_DELETE_DATA_ON_UNINSTALL` is defined as `true`.

## Access

The CRM is restricted to users with the `manage_options` capability.
