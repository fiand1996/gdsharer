<?php
$langs = [];

// US English
$langs[] = [
    "code" => "en-US",
    "shortcode" => "en",
    "name" => "English",
    "localname" => "English"
];

// Indonesian
$langs[] = [
    "code" => "id-ID",
    "shortcode" => "id",
    "name" => "Indonesian",
    "localname" => "Bahasa Indonesia"
];


Config::set("applangs", $langs);
Config::set("default_applang", "en-US");
