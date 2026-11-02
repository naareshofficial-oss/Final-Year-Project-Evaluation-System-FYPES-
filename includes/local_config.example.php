<?php
/*
 * Copy this file to local_config.php and fill in your local values.
 * Never commit or share local_config.php because it contains secrets.
 */
return [
    'db_host' => 'localhost',
    'db_name' => 'fypes_db',
    'db_user' => 'root',
    'db_pass' => '',

    'base_url' => '/fypes',
    'app_url' => 'http://localhost/fypes',

    'google_client_id' => 'YOUR_GOOGLE_OAUTH_CLIENT_ID',
    'google_client_secret' => 'YOUR_GOOGLE_OAUTH_CLIENT_SECRET',
    'google_redirect_uri' => 'http://localhost/fypes/auth/google-login.php',

    // Example for Gmail SMTP. Prefer a dedicated institutional SMTP account.
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-sender@unisel.edu.my',
    'smtp_password' => 'YOUR_APP_PASSWORD_OR_SMTP_PASSWORD',
    'smtp_encryption' => 'tls',
    'mail_from_email' => 'your-sender@unisel.edu.my',
    'mail_from_name' => 'FYPES',
];
