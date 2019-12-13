<?php 

$langs = [];
foreach (Config::get("applangs") as $l) {
    if (!in_array($l["code"], $langs)) {
        $langs[] = $l["code"];
    }

    if (!in_array($l["shortcode"], $langs)) {
        $langs[] = $l["shortcode"];
    }
}
$langslug = $langs ? "[".implode("|", $langs).":lang]" : "";


App::addRoute("GET|POST", "/", "Index");
App::addRoute("GET|POST", "/" . $langslug . "?/?", "Index");

App::addRoute("GET|POST", "/signin/?", "Signin");
App::addRoute("GET|POST", "/signout/?", "Signout");

App::addRoute("GET|POST", "/file/[**:file]/?", "File");

App::addRoute("GET|POST", "/dashboard/?", "Dashboard");
App::addRoute("GET|POST", "/mydrive/?", "Mydrive");
App::addRoute("GET|POST", "/shared/?", "Shared");
App::addRoute("GET|POST", "/upload/?", "Upload");
App::addRoute("GET|POST", "/copy/?", "Copy");
App::addRoute("GET|POST", "/bulklink/?", "Bulklink");
App::addRoute("GET|POST", "/profile/?", "Profile");
App::addRoute("GET|POST", "/users/?", "Users");
App::addRoute("GET|POST", "/users/[i:id]/?", "User");
App::addRoute("GET|POST", "/settings/?", "Settings");

