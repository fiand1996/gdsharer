<?php

// Default timezone
date_default_timezone_set("UTC");

// Defaullt multibyte encoding
mb_internal_encoding("UTF-8");

// Application version name
define("APP_VERSION", "1.0");

// ASCII Secure random crypto key
define("CRYPTO_KEY", "def00000d459ad4482a1a6d23a907db6c65c81fd936a78a387a906a4333286f85b99c556574f6ed96f1bd8fc608bac246631187e7dc038aa02a1b28d04dd07e005a56137");

// General purpose salt
define("SALT", "7FwupCc5YpXJtnsz");

// Path to instagram sessions directory
define("SESSIONS_PATH", BASEPATH . "/storage/sessions");

define("GOOGLE_CLIENT_ID", "GOOGLE_CLIENT_ID");

define("GOOGLE_CLIENT_SECRET", "GOOGLE_CLIENT_SECRET");

define("GOOGLE_API_KEY", "GOOGLE_API_KEY");

define("GOOGLE_REDIRECT_URL", APPURL . "/signin");

define("GOOGLE_SCOPES", [
    "https://www.googleapis.com/auth/plus.login",
    "https://www.googleapis.com/auth/userinfo.email",
    "https://www.googleapis.com/auth/userinfo.profile",
    "https://www.googleapis.com/auth/plus.me",
    "https://www.googleapis.com/auth/drive",
]);

define("DRIVE_FOLDER_NAME", "GDSharer");
