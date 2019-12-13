<?php
/**
 * Common Helper
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */

/**
 * Special version of htmlspecialchars
 * @param  string $string
 * @return string
 */
function htmlchars($string = "")
{
    return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
}

/**
 * Delete file or folder (with content)
 * @param string $path Path to file or folder
 */
function delete($path)
{
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file) {
            delete(realpath($path) . '/' . $file);
        }

        return rmdir($path);
    } else if (is_file($path) === true) {
        return unlink($path);
    }

    return false;
}

/**
 * Get an array of timezones
 * @return array
 */
function getTimezones()
{
    $timezoneIdentifiers = DateTimeZone::listIdentifiers();
    $utcTime = new DateTime('now', new DateTimeZone('UTC'));

    $tempTimezones = array();
    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new DateTimeZone($timezoneIdentifier);

        $tempTimezones[] = array(
            'offset' => (int) $currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier,
        );
    }

    // Sort the array by offset,identifier ascending
    usort($tempTimezones, function ($a, $b) {
        return ($a['offset'] == $b['offset'])
        ? strcmp($a['identifier'], $b['identifier'])
        : $a['offset'] - $b['offset'];
    });

    $timezoneList = array();
    foreach ($tempTimezones as $tz) {
        $sign = ($tz['offset'] > 0) ? '+' : '-';
        $offset = gmdate('H:i', abs($tz['offset']));
        $timezoneList[$tz['identifier']] = '(UTC ' . $sign . $offset . ') ' .
            $tz['identifier'];
    }

    return $timezoneList;
}

/**
 * Validate date
 * @param  string  $date   date string
 * @param  string  $format
 * @return boolean
 */
function isValidDate($date, $format = 'Y-m-d H:i:s')
{
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * Create SEO friendly url slug from string
 * @param  string $string
 * @return string
 */
function url_slug($string = "", $dashes = "-")
{
    if (!is_string($string)) {
        $string = "";
    }

    $s = trim(mb_strtolower($string));

    // Replace azeri characters
    $s = str_replace(
        array("ü", "ö", "ğ", "ı", "ə", "ç", "ş"),
        array("u", "o", "g", "i", "e", "c", "s"),
        $s);

    // Replace cyrilic characters
    $cyr = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м',
        'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ',
        'ы', 'ь', 'э', 'ю', 'я');
    $lat = array('a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l',
        'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch',
        'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya');
    $s = str_replace($cyr, $lat, $s);

    // Replace all other characters
    $s = preg_replace("/[^a-z0-9]/", "$dashes", $s);

    // Replace consistent dashes
    $s = preg_replace("/-{2,}/", "$dashes", $s);

    return trim($s, "$dashes");
}

/**
 * Generate human readable random text
 * @param  integer $length length of the returned string
 * @return string          Random string
 */
function readableRandomString($length = 6)
{
    $string = '';
    $vowels = array("a", "e", "i", "o", "u");
    $consonants = array(
        'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
        'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z',
    );
    // Seed it
    srand((double) microtime() * 1000000);
    $max = $length / 2;
    for ($i = 1; $i <= $max; $i++) {
        $string .= $consonants[rand(0, 19)];
        $string .= $vowels[rand(0, 4)];
    }
    return $string;
}

function readableFileSize($size, $precision = 2)
{
    if ($size < 0) {
        $size = 0;
    }

    $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $step = 1024;
    $i = 0;

    $max = count($units) - 1;

    while (($size / $step) > 0.9) {
        $size = $size / $step;
        $i++;

        if ($i > $max) {
            return false;
        }
    }

    return round($size, $precision) . $units[$i];
}

function Setting($name, $field = null)
{
    if (!is_string($name)) {
        return null;
    }

    if (!isset($GLOBALS["Setting"]) || !is_array($GLOBALS["Setting"])) {
        $GLOBALS["Setting"] = array();
    }

    if (isset($GLOBALS["Setting"][$name])) {
        $settings = $GLOBALS["Setting"][$name];
    } else {
        $settings = Controller::model("Setting", $name);
        $GLOBALS["Setting"][$name] = $settings;
    }

    if (is_string($field)) {
        return htmlchars($settings->get("data." . $field));
    }

    return $settings;
}

/**
 * Get settings
 * @return string
 */
function generalSetting($field = null)
{
    return Setting("general", $field);
}

/**
 * Add a new option or update if it exists
 * @param string  $option_name  Name of the option
 * @param string|int  $option_value Value of the option
 */
function save_option($option_name, $option_value)
{
    if (!is_string($option_name)) {
        // Option name must be string
        return false;
    }

    if ($option_value === false || $option_value === null) {
        $option_value = "";
    }

    // Save to the database
    $opt = \Controller::model("Option", $option_name);
    $opt->set("option_name", $option_name)
        ->set("option_value", $option_value)
        ->save();

    return true;
}

/**
 * Get the value of the given option
 * @param  string  $option_name
 * @param  boolean $default_value If option is not available,
 *                                then return $default_value
 * @return [mixed]                Either option value or $default_value
 */
function get_option($option_name, $default_value = false)
{
    if (!is_string($option_name)) {
        // Option name must be string
        return $default_value;
    }

    $opt = \Controller::model("Option", $option_name);
    if (!$opt->isAvailable()) {
        return $default_value;
    }

    // Return the value
    return $opt->get("option_value");
}

/**
 * Header Redirect
 *
 * Header redirect in two flavors
 * For very fine grained control over headers, you could use the Output
 * Library's set_header() function.
 *
 * @param    string    $uri    URL
 * @param    string    $method    Redirect method 'auto', 'location' or 'refresh'
 * @param    int    $code    HTTP Response status code
 * @return    void
 */
function redirect($uri = '', $method = 'auto', $code = null)
{
    if (!preg_match('#^(\w+:)?//#i', $uri)) {
        $uri = site_url($uri);
    }

    // IIS environment likely? Use 'refresh' for better compatibility
    if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
        $method = 'refresh';
    } elseif ($method !== 'refresh' && (empty($code) or !is_numeric($code))) {
        if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
            $code = ($_SERVER['REQUEST_METHOD'] !== 'GET')
                ? 303   // reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
                : 307;
        } else {
            $code = 302;
        }
    }

    switch ($method) {
        case 'refresh':
            header('Refresh:0;url=' . $uri);
            break;
        default:
            header('Location: ' . $uri, true, $code);
            break;
    }
    exit;
}


function activeMenu($path)
{
    $active = "";
    $parse = parse_url(currentUrl());
    if (isset($parse['path'])) {
        $paths = explode("/", $parse['path']);
        if (in_array($path, $paths)) {
            $active = "active";
        }    
    }
    return $active;
}

function setFlashMessage($type, $message)
{
    $msg = new \Plasticbrain\FlashMessages\FlashMessages();
    $msg->$type($message);
}

function getFlashMessage() 
{
    $msg = new \Plasticbrain\FlashMessages\FlashMessages();
    $msg->display();
}

function getDriveId($string)
{
    if (strpos($string, "/edit")) {
        $string = str_replace("/edit", "/view", $string);
    } else if (strpos($string, "?id=")) {
        $parts = parse_url($string);
        parse_str($parts['query'], $query);
        return $query['id'];
    } else if (strpos($string, "&id=")) {
        $parts = parse_url($string);
        parse_str($parts['query'], $query);
        return $query['id'];
    } else if (!strpos($string, "/view")) {
        $string = $string . "/view";
    }
    
    $start = "file/d/";
    $end = "/view";
    $string = " " . $string;
    $ini = strpos($string, $start);

    if ($ini == 0) {
        return null;
    }
    
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function jsonecho($response)
{
    header("Content-type: application/json; charset=utf-8");
    echo Input::get("callback") ? 
            Input::get("callback")."(".json_encode($response).")" : 
                json_encode($response);
    exit;
}

function csrftokenGenerate()
{
    if (function_exists('mcrypt_create_iv')) {
        $token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
    } else {
        $token = bin2hex(random_bytes(32));
    }
    return $token;
}

function currentUrl()
{
    $s = &$_SERVER;
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    $uri = $protocol . '://' . $host . $s['REQUEST_URI'];
    $segments = explode('?', $uri, 2);
    $url = $segments[0];
    return $url;
}

function show_404()
{
    $Controller = New NotfoundController();
    $Controller->index();
    exit;
}

function getError($exception, $default)
{
    $response = @json_decode($exception->getMessage());

    $message = $default;

    if (isset($response->error->message)) {
        $error = $response->error->message;

        if ($error == "The user's Drive storage quota has been exceeded.") {
            $message = lang("Your Google Drive account was full, please delete some files.");
        } else if ($error == "User rate limit exceeded.") {
            $message = lang("Your Google Drive account was limited, please login with another Google account. The account will be returned to normal after 24 hours.");
        } else if ($error == "Forbidden") {
            $message = lang("File cannot be opened");
        } else {
            $message = $error;
        }
    }

    return $message;
}

function dateFormat($date, $format, $timezone)
{
    $date = new \Moment\Moment($date, date_default_timezone_get());
    $date->setTimezone($timezone);
    return $date->format($format);
}

function findStr($str, $find)
{
    return (strpos($str, $find) !== false) ? true : false;
}

function lang($original, $plural = "")
{
    return __($original, $plural);
}

function downloadableLink($link)
{
    $downloadable = true;
    $content = @file_get_contents($link);

    if (findStr($content, "uc-error")) {
        $downloadable = false;
    }

    return $downloadable;
}

function fileIcon($mimeType)
{
    if (findStr($mimeType, "image/")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-image-o"></i></span>';
    } elseif (findStr($mimeType, "video/")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-movie-o"></i></span>';
    } elseif (findStr($mimeType, "application/zip")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-zip-o"></i></span>';
    } elseif (findStr($mimeType, "application/rar")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-zip-o"></i></span>';
    } elseif (findStr($mimeType, "application/x-rar")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-zip-o"></i></span>';
    } elseif (findStr($mimeType, "-compressed")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-zip-o"></i></span>';
    } elseif (findStr($mimeType, "text/")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-text-o"></i></span>';
    } elseif (findStr($mimeType, "/pdf")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-pdf-o"></i></span>';
    } elseif (findStr($mimeType, "officedocument.word")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-word-o"></i></span>';
    } elseif (findStr($mimeType, "officedocument.spreadsheet")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-excel-o"></i></span>';
    } elseif (findStr($mimeType, "officedocument.presentation")) {
        $icon = '<span class="file-icon"><i class="fa fa-file-powerpoint-o"></i></span>';
    } elseif (findStr($mimeType, "android.package")) {
        $icon = '<span class="file-icon"><i class="fa fa-android"></i></span>';
    } elseif (findStr($mimeType, "x-iso9660-image")) {
        $icon = '<span class="file-icon"><i class="glyphicon glyphicon-eject"></i></span>';
    } else {
        $icon = '<span class="file-icon"><i class="fa fa-file"></i></span>';
    }

    return $icon;
}