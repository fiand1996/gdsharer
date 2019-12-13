<?php

// Required loader...
require_once APPPATH . "/core/Autoloader.php";
require_once BASEPATH . "/vendor/autoload.php";

// instantiate Whoops Error Handler
if (ENVIRONMENT == "development") {
    $run = new \Whoops\Run;
    $handler = new \Whoops\Handler\PrettyPageHandler;
    $handler->setPageTitle("Whoops! There was a problem.");
    $run->pushHandler($handler);
    if (\Whoops\Util\Misc::isAjaxRequest()) {
        $run->pushHandler(new \Whoops\Handler\JsonResponseHandler);
    }
    $run->register();
}

// instantiate the loader
$loader = new Autoloader;

// register the autoloader
$loader->register();

// register the base directories for auto loading
$loader->addBaseDir(APPPATH . '/libraries');
$loader->addBaseDir(APPPATH . '/core');
$loader->addBaseDir(APPPATH . '/controllers');
$loader->addBaseDir(APPPATH . '/models');

// Required config and helpers...
require_once APPPATH . "/helpers/helpers.php";
require_once APPPATH . "/config/config.php";