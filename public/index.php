<?php
// Start session
session_start();

/**
 * Define environment constants
 */
define("ENVIRONMENT", "development"); // [development|production]

/**
 * Check environment
 */

error_reporting(E_ALL);
if (ENVIRONMENT == "development") {
    ini_set('display_errors', 1);
} else if (ENVIRONMENT == "production") {
    ini_set('display_errors', 0); 
} else {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Environment is invalid. Please contact developer for more information.';
    exit;
}

/**
 * Define constants
 */
// Path to root directory of app.
define("ROOTPATH", dirname(__FILE__));

// Path to app folder.
$application_folder = '../app';

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

if (is_dir($application_folder)) {
    if (($_temp = realpath($application_folder)) !== false) {
        $application_folder = $_temp;
    } else {
        $application_folder = strtr(
            rtrim($application_folder, '/\\'),
            '/\\',
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
        );
    }
} else {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: ' . SELF;
    exit(3);
}

define('APPPATH', $application_folder . DIRECTORY_SEPARATOR);

// Check if SSL enabled.
$ssl = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off" 
     ? true 
     : false;
define("SSL_ENABLED", $ssl);

// URL of the application root. 
// This is not the URL of the app directory.
$app_url = (SSL_ENABLED ? "https" : "http")
         . "://"
         . $_SERVER["SERVER_NAME"]
         . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
         . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");
define("APPURL", $app_url);

// Define Base Path (for routing)
$base_path = trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");
$base_path = $base_path ? "/" . $base_path : "";
define("BASEROUTE", $base_path);

define("BASEPATH", dirname(APPPATH));

// Required app loader...
require_once APPPATH."/app.php";

// Run the app...
$App = new App;
$App->process();
