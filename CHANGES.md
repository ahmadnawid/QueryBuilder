# Changelog

All notable changes to the Query Builder plugin are documented here.

## 1.0.0 (2026-03-13)

Initial release.

### Added
- Visual query builder with Moodle entity and field selection
- Support for 26 built-in Moodle entities including users, courses, enrolments,
  grades, assignments, quizzes, forums, cohorts, badges, and competencies
- Advanced SQL editor with monospace font and resize support
- PostgreSQL Query Explain Plan Analyzer with performance warnings
- Detection of sequential scans, missing indexes, filesort, and large row estimates
- Saved queries with category support
- Edit, delete, and categorize saved queries
- Category filter for saved query list
- Pagination and column sorting via Moodle flexible_table
- CSV, ODS, and Excel export via flexible_table download
- SQL validation — only SELECT statements permitted
- Blocks UPDATE, DELETE, INSERT, DROP, ALTER, TRUNCATE
- Capability: report/querybuilder:view
- Switch between visual builder and advanced SQL editor
- AMD JavaScript module for query analysis (analyze.js)
- External API function: report_querybuilder_analyze_query
- Full Moodle 4.5 compatibility
- English language strings
