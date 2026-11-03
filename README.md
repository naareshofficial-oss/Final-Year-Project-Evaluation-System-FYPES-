# FYPES — Final Year Project Evaluation System

FYPES is a PHP/MySQL Final Year Project management and evaluation system for Universiti Selangor (UNISEL). This corrected build uses separate Report Panel and Presentation Panel roles throughout the database, dashboards, permissions and evaluation workflow.

## Requirements

- XAMPP or another Apache + PHP 8.1+ + MySQL/MariaDB environment
- PHP extensions: PDO MySQL, fileinfo and GD
- A modern browser

## Fresh installation

1. Copy the `fypes_working` folder into your web server document root. For XAMPP, place it under `htdocs` and rename it to `fypes` unless you also change `BASE_URL`.
2. Start Apache and MySQL.
3. In phpMyAdmin, delete any previous `fypes_db` database used by the older build.
4. Import `database/fypes_db.sql`. The included SQL contains both the original seed data and the full correction migration.
5. Open `includes/config.php` and confirm:
   - `BASE_URL` matches the web folder, normally `/fypes`.
   - Database name, user and password match your MySQL setup.
6. Open `http://localhost/fypes/`.

For non-XAMPP environments, database settings may also be supplied through `FYPES_DB_HOST`, `FYPES_DB_NAME`, `FYPES_DB_USER` and `FYPES_DB_PASS` environment variables.

## Seeded login accounts

All seeded users initially use the password:

```text
password
```

The corrected migration marks seeded accounts for a mandatory password change after first login.

Example accounts:

- Administrator: `admin1@unisel.edu.my`
- Lecturer: `farahn1@unisel.edu.my`
- Student: `2023100001@student.unisel.edu.my`

Student self-registration creates an inactive, unverified account. An administrator must approve and activate it before login.

## Academic roles

A lecturer can hold one or more programme-specific roles:

- Coordinator
- Supervisor
- Report Panel
- Presentation Panel

The previous generic Assessor role is no longer used by the application. Every evaluated student can have two independent panel members:

1. Report Panel — evaluates the written report and can review project submissions.
2. Presentation Panel — evaluates the oral presentation after the coordinator records the presentation as completed.

The system prevents the Supervisor, Report Panel and Presentation Panel from being the same lecturer for a student.

## Evaluation weighting

### FYP1

| Component | Contribution |
|---|---:|
| Supervisor | 40 |
| Report Panel | 30 |
| Presentation Panel | 20 |
| Coordinator | 10 |
| **Final total** | **100** |

### FYP2

| Component | Contribution |
|---|---:|
| Supervisor | 50 |
| Report Panel | 10 |
| Presentation Panel | 30 |
| Coordinator | 10 |
| **Final total** | **100** |

The final result is the sum of the four weighted contributions. It is not an average of the components.

## Main workflow

1. A student account is created through registration or by an administrator with a validated matric number, institutional email, programme and real FYP stage.
2. The student requests an eligible Supervisor.
3. The Supervisor accepts or declines.
4. A programme Coordinator confirms the accepted request before the Supervisor becomes officially assigned.
5. The Coordinator assigns one Report Panel member and one Presentation Panel member, then schedules the presentation.
6. Students upload immutable submission versions before the relevant deadline.
7. Supervisor and Report Panel decisions are stored separately. A later review cannot overwrite another reviewer’s decision.
8. Each assigned academic role completes its own locked rubric evaluation. The Presentation Panel evaluation opens only after the presentation is marked completed.
9. The Coordinator calculates and finalizes the result only after all four components are complete.
10. The Coordinator releases the finalized result to the student.
11. A passing student can receive a personalized completion certificate. Lecturer documents are invitation letters only.
12. A Coordinator can promote an eligible FYP1 student into a new FYP2 semester without overwriting the historical FYP1 record.

## Correct student stage display

The student dashboard now reads the saved `programme_type` value. It displays the actual stage, such as Degree FYP2 or Diploma FYP1, instead of always showing Diploma FYP1.

## Security and data integrity

- Role and programme authorization is rechecked by the server for sensitive actions.
- Sessions regenerate after successful login.
- Inactive administrator, lecturer and student accounts cannot log in.
- Admin-created accounts receive a temporary password and must change it.
- Uploads are MIME-checked, extension-checked, size-limited, randomly renamed and served through an authenticated download controller.
- Upload folders block direct web access and script execution.
- Reviewed submission versions are preserved rather than physically deleted.
- Completed evaluations are locked. A Coordinator can reopen an unreleased evaluation, which clears the unreleased finalized result.
- Rubric versions are locked after publication and must total the official component weight.
- Assignment changes are blocked after the related evaluation has been completed.
- Lecturer role removal and account deactivation are blocked while active students or evaluations depend on that lecturer.
- Finalized results are stored as semester-specific snapshots and remain hidden until released.
- Semester-specific submissions, evaluations, schedules, results and certificates are separated.
- Debug output is disabled by default.

## Documents

- Student: personalized completion certificate after a released passing result.
- Lecturer: personalized invitation letter for Supervisor, Report Panel or Presentation Panel duty.
- The build contains no bundled font files. Document generation uses the server's GD support and supplied image templates.

## Chat assistant

The included assistant is a role-aware FAQ matcher, not a generative AI model. It follows the administrator's chatbot enabled/disabled setting, validates CSRF requests and separates student, lecturer and administrator FAQ audiences.

## Important upgrade note

Do not reuse the old database unchanged. The two-panel model adds and changes tables, columns, roles, evaluations, reviews, semester links and result records. Use a fresh database import from the included SQL file for the most reliable installation.


