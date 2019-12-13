<?php 

$twig->addFunction(new \Twig\TwigFunction('readableFileSize', function (int $size) {
    return readableFileSize($size);
}));

$twig->addFunction(new \Twig\TwigFunction('generalSetting', function (string $key) {
    return generalSetting($key);
}));

$twig->addFunction(new \Twig\TwigFunction('readableRandomString', function (int $size) {
    return readableRandomString($size);
}));

$twig->addFunction(new \Twig\TwigFunction('getFlashMessage', function () {
    return getFlashMessage();
}));

$twig->addFunction(new \Twig\TwigFunction('activeMenu', function (string $current) {
    return activeMenu($current);
}));

$twig->addFunction(new \Twig\TwigFunction('fileIcon', function (string $mime) {
    return fileIcon($mime);
}));

$twig->addFunction(new \Twig\TwigFunction('dateFormat', function (string $date, string $format, string $timezone) {
    return dateFormat($date, $format, $timezone);
}));

$twig->addFunction(new \Twig\TwigFunction('lang', function (string $original) {
    return __($original);
}));

$twig->addExtension(new \Twig\Extension\DebugExtension());

$twig->addGlobal("currentUrl", currentUrl());
$twig->addGlobal("Config", New Config);
$twig->addGlobal("Input", New Input);

