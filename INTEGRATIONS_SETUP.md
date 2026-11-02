# Google Sign-In and Email Setup

FYPES does not include private Google or SMTP credentials. Configure them once from **Admin Dashboard > Settings**.

## Google Sign-In

1. Create a Google OAuth 2.0 **Web application** for the FYPES site.
2. Add the exact callback shown in Admin Settings as an authorised redirect URI. For the default Laragon URL it is:

   `http://localhost/fypes/auth/google-login.php`

3. Paste the Client ID, Client Secret and identical Redirect URI into **Admin Dashboard > Settings > Google Sign-In**.
4. Save settings, log out, and test the Student or Lecturer Google button.

Google sign-in accepts existing active FYPES accounts only:

- `@student.unisel.edu.my` -> Student account
- `@unisel.edu.my` -> Lecturer account

Administrators use email/password and Forgot Password.

## Temporary-password email

In **Admin Dashboard > Settings > Email / SMTP**, enter the SMTP server details and use **Send Test Email** before creating accounts.

Typical fields are host, port, encryption, username, password/app password, sender email and sender name. The latest SMTP error is displayed below the test form and saved in `email_delivery_logs`.

When SMTP is configured, newly created Student and Lecturer accounts receive the temporary password by email. If delivery is unavailable, FYPES shows the one-time password to the administrator once instead of losing access to the account.

Secrets can alternatively be stored in `includes/local_config.php`; copy `includes/local_config.example.php` and replace the placeholders. Database values entered in Admin Settings take priority, while blank database settings correctly fall back to `local_config.php`.
