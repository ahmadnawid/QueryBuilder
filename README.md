# Query Builder for Moodle

A report plugin that allows Moodle administrators and developers to build and
execute custom SQL queries against the Moodle database, with a visual builder,
advanced SQL editor, query analysis tools, and saved query management.

## Features

- **Visual Query Builder** — select Moodle entities (users, courses, enrolments,
  grades, etc.) and fields without writing SQL
- **Advanced SQL Editor** — write and run raw SELECT queries with syntax support
- **Query Explain Plan Analyzer** — analyze PostgreSQL query execution plans,
  showing index usage, sequential scans, and performance warnings
- **Saved Queries** — save, categorize, edit, and delete queries for reuse
- **Pagination and Sorting** — results are paginated and sortable via
  Moodle's flexible_table
- **Export** — download results as CSV, ODS, or Excel directly from the results table
- **Security** — only SELECT queries are permitted; all dangerous keywords are blocked

## Requirements

- Moodle 4.0 or later (tested on Moodle 4.5)
- PHP 7.4 or later
- PostgreSQL (required for the Explain Plan Analyzer feature)

## Installation

1. Download the plugin zip file
2. Go to **Site Administration → Plugins → Install plugins**
3. Upload the zip file and follow the installation wizard
4. After installation, visit **Site Administration → Notifications** to complete
   the database setup

Or install manually:

```bash
cd /path/to/moodle/report/
git clone https://github.com/yourusername/moodle-report_querybuilder querybuilder
```

Then visit **Site Administration → Notifications** to run the upgrade.

## Configuration

After installation, grant the `report/querybuilder:view` capability to the
roles that should have access. By default only managers have access.

Go to **Site Administration → Users → Permissions → Define roles** and assign
the capability as needed.

## Usage

### Visual Builder Mode

1. Navigate to **Site Administration → Reports → Query Builder**
2. Select a base entity (e.g. User, Course, Enrolment)
3. Select the fields you want to include
4. Optionally add joins and filters
5. Click **Run SQL** to execute the generated query

### Advanced SQL Editor

1. Click **Switch to Advanced SQL Mode**
2. Write a SELECT query using Moodle table prefix notation, e.g.:

```sql
SELECT u.firstname, u.lastname, u.email
FROM {user} u
WHERE u.deleted = 0
LIMIT 100
```

3. Click **Run SQL** to execute
4. Click **Analyze Query** to see the PostgreSQL execution plan

### Saving Queries

1. In Advanced SQL Editor, click **New**
2. Enter a category and query name
3. Write your SQL in the editor
4. Click **Save Query**

Saved queries can be loaded from the dropdown at the top of the editor.

## Security

This plugin only permits SELECT statements. The following are blocked:

- `UPDATE`, `DELETE`, `INSERT`, `DROP`, `ALTER`, `TRUNCATE`
- Multiple statements (semicolons)
- Only users with the `report/querybuilder:view` capability can access the plugin

All queries run with the same database permissions as the Moodle application
user — no elevated privileges are granted.

## License

This plugin is licensed under the **GNU General Public License v3 or later**.
See the [LICENSE](LICENSE) file for details.

## Author

Ahmad Nawid Mustafazada <ahmadnawid.mz@gmail.com>

## Contributing

Bug reports and pull requests are welcome via the plugin's GitHub repository.

## Changelog

See [CHANGES.md](CHANGES.md) for version history.
