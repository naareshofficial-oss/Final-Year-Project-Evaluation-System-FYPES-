# FYPES – Implemented Requested Workflows

This build implements the 15 requested changes.

## Configuration-dependent features

- **Google sign-in** uses Google OAuth 2.0/OpenID Connect. Configure `includes/local_config.php` from `includes/local_config.example.php`.
- **Temporary lecturer password email** uses SMTP when configured. Failed delivery is logged and the one-time password is shown to Admin as a fallback.
- Import a fresh `database/fypes_db.sql` because this build adds Google identity, email logs, detailed-rubric assessment, and global theme fields/tables.

## Decisions applied

- Forgot Password uses a **direct identity-verification reset form** for Student, Lecturer, and Admin.
- Matric format is **1–10 digits plus one optional final letter** (maximum 11 characters).
- Reopening a category or progress form applies to the whole programme/category and preserves existing submissions.
- Detailed-rubric marks are a separate **staff-only** assessment. They do not change the official weighted final result. Students see only the released official evaluation percentages.

## Detailed rubric model

- Four separate sets: Supervisor, Coordinator, Report Panel, Presentation Panel.
- Coordinator-only create/edit/publish/version controls.
- Unlimited criteria subject to an administrative total cap of 200.
- Each criterion has a 0–10 range slider.
- Assigned staff can score each student using the published detailed rubric.
- Coordinators can see all four staff-only detailed totals while controlling the final result.
