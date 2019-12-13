<?php 
/**
 * Main App Core
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class App
{
    protected $router;
    protected $controller;

    // An array of the URL routes
    protected static $routes = [];

    /**
     * summary
     */
    public function __construct()
    {
        $this->controller = new Controller;
    }

    /**
     * Adds a new route to the App:$routes static variable
     * App::$routes will be mapped on a route 
     * initializes on App initializes
     * 
     * Format: ["METHOD", "/uri/", "Controller"]
     * Example: App:addRoute("GET|POST", "/post/?", "Post");
     */
    public static function addRoute()
    {
        $route = func_get_args();
        if ($route) {
            self::$routes[] = $route;
        }
    }

    /**
     * Get App::$routes
     * @return array An array of the added routes
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

   /**
     * Get IP info
     * @return stdClass 
     */
    private function ipinfo()
    {
        if (filter_var(Input::server("HTTP_CLIENT_IP"), FILTER_VALIDATE_IP)) {
            $ip = Input::server("HTTP_CLIENT_IP");
        } else if (filter_var(Input::server("HTTP_X_FORWARDED_FOR"), FILTER_VALIDATE_IP)) {
            $ip = Input::server("HTTP_X_FORWARDED_FOR");
        } else {
            $ip = Input::server("REMOTE_ADDR");
        }

        if (! Session::exists($ip)) {
            $res = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip), true);

            $ipinfo = [
                "request" => "", // Requested Ip Address
                "status" => "", // Status code (200 for success)
                "credit" => "",
                "city" => "",
                "region" => "",
                "areaCode" => "",
                "dmaCode" => "",
                "countryCode" => "",
                "countryName" => "",
                "continentCode" => "",
                "latitude" => "",
                "longitude" => "",
                "regionCode" => "",
                "regionName" => "",
                "currencyCode" => "",
                "currencySymbol" => "",
                "currencySymbol_UTF8" => "",
                "currencyConverter" => "",
                "timezone" => "", // Will be used only in registration
                                  // process to detect user's 
                                  // timezone automatically
                "neighbours" => [], // Neighbour country codes (ISO 3166-1 alpha-2)
                "languages" => [] // Spoken languages in the country
                                  // Will be user to auto-detect user language
            ];
            if (is_array($res)) {
                foreach ($res as $key => $value) {
                    $key = explode("_", $key, 2);
                    if (isset($key[1])) {
                        $ipinfo[$key[1]] = $value;
                    }
                }
            }

            if ($ipinfo["latitude"] && $ipinfo["longitude"]) {

                $username = generalSetting('geonamesorg_username');
                
                if ($username) {
                    // Get timezone
                    if (!empty($ipinfo["latitude"]) && !empty($ipinfo["longitude"])) {
                        $res = @json_decode(file_get_contents("http://api.geonames.org/timezoneJSON?lat=".$ipinfo["latitude"]."&lng=".$ipinfo["longitude"]."&username=".$username));

                        if (isset($res->timezoneId)) {
                            $ipinfo["timezone"] = $res->timezoneId;
                        }
                    }


                    // Get neighbours
                    if (!empty($ipinfo["countryCode"])) {
                        $res = @json_decode(file_get_contents("http://api.geonames.org/neighboursJSON?country=".$ipinfo["countryCode"]."&username=".$username));

                        if (!empty($res->geonames)) {
                            foreach ($res->geonames as $r) {
                                $ipinfo["neighbours"][] = $r->countryCode;
                            }
                        }
                    }

                    // Get country
                    if (!empty($ipinfo["countryCode"])) {
                        $res = @json_decode(file_get_contents("http://api.geonames.org/countryInfoJSON?country=".$ipinfo["countryCode"]."&username=".$username));

                        if (!empty($res->geonames[0]->languages)) {
                            $langs = explode(",", $res->geonames[0]->languages);
                            foreach ($langs as $l) {
                                $ipinfo["languages"][] = $l;
                            }
                        }
                    }
                }
            }

            Session::set($ip, $ipinfo);
        }

        return json_decode(json_encode(Session::get($ip)));
    }

    /**
     * Create database connection
     * @return App 
     */
    private function db()
    {
        $config = [
            'driver' => 'mysql', 
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => DB_ENCODING,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];

        new \Pixie\Connection('mysql', $config, 'DB');
        return $this;
    }

    private function CSRFToken()
    {
        if (! Session::exists("csrftoken")) {
            Token::generate();
        }
            
        if (Input::exists()) {
            if (Token::check(Input::post("csrftoken"))) {
                Token::generate();
            } else if (Token::check(Input::server("HTTP_X_CSRF_TOKEN"))) {
                Token::generate();
            } else {

                header("HTTP/1.0 405 Method Not Allowed");
                
                if (Input::isAjaxRequest()) {
                    jsonecho([
                        "result" => 0,
                        "message" => "Method Not Allowed"
                    ]);
                }

                echo "Method Not Allowed";
                exit; 
            }
        }

        return Session::get("csrftoken");
    }

    /**
     * Check and get authorized user data
     * Define $AuthUser variable
     */
    private function auth()
    {
        $AuthUser = null;
        if (Cookie::exists("sessid")) {
            $hash = explode(".", Cookie::get("sessid"), 2);

            if (count($hash) == 2) {
                $User = Controller::Model("User", $hash[0]);

                if ($User->isAvailable() &&
                    $User->get("is_active") == 1 &&
                    md5($User->get("password")) == $hash[1]) 
                {
                    $AuthUser = $User;
                    if (Cookie::exists("sessrmm")) {
                        $hash = $User->get("id") . "." . md5($User->get("password"));
                        Cookie::set("sessid", $hash, time() + 86400 * 30);
                        Cookie::set("sessrmm", "1", time() + 86400 * 30);
                    }                 
                }
            }
        }

        return $AuthUser;
    }

    private function i18n()
    {   
        $Route = $this->controller->getVariable("Route");
        $AuthUser = $this->controller->getVariable("AuthUser");
        $IpInfo = $this->controller->getVariable("IpInfo");

        if (isset($Route->params->lang)) {
            // Direct link or language change
            // Getting lang from route
            $lang = $Route->params->lang;
        } elseif (Cookie::exists("lang")) {
            $lang = Cookie::get("lang");
        } else {
            $lang = Config::get("default_applang");

            if ($IpInfo->languages) {
                foreach ($IpInfo->languages as $l) {
                    foreach (Config::get("applangs") as $al) {
                        if ($al["code"] == $l || $al["shortcode"] == $l) {
                            // found, break loops
                            $lang = $al["code"];
                            break 2;
                        }
                    }
                }
            }
        }

        $active_lang = Config::get("default_applang");
        foreach (Config::get("applangs") as $al) {
            if ($al["code"] == $lang || $al["shortcode"] == $lang) {
                $active_lang = $al["code"];
                break;
            }
        }

        define("ACTIVE_LANG", $active_lang);

        Cookie::set("lang", ACTIVE_LANG, time() + 30 * 86400);

        $Translator = new Gettext\Translator;

        $path = APPPATH . "/locale/" . ACTIVE_LANG . "/messages.po";
        if (file_exists($path)) {
            $translations = Gettext\Translations::fromPoFile($path);
            $Translator->loadTranslations($translations);
        }

        $Translator->register();

        try {
             \Moment\Moment::setLocale(str_replace("-", "_", ACTIVE_LANG));
        } catch (Exception $e) {
            
        }
    }

    /**
     * Analize route and load proper controller
     * @return App
     */
    private function route()
    {
        // Initialize the router
        $router = new AltoRouter();
        $router->setBasePath(BASEROUTE);

        // Load plugin/theme routes first
        // TODO: Update router.map in modules to App::addRoute();
        $GLOBALS["_ROUTER_"] = $router;
        \Event::trigger("router.map", "_ROUTER_");
        $router = $GLOBALS["_ROUTER_"];

        // Load global routes
        include APPPATH."/config/routes.config.php";
        
        // Map the routes
        $router->addRoutes(App::getRoutes());

        // Match the route
        $route = $router->match();
        $route = json_decode(json_encode($route));

        if ($route) {
            if (is_array($route->target)) {
                require_once $route->target[0];
                $controller = $route->target[1];
            } else {
                $controller = $route->target."Controller";
            }
        } else {
            $controller = "NotfoundController";
        }

        $this->controller = new $controller;
        $this->controller->setVariable("Route", $route);
    }

    /**
     * Process
     */
    public function process()
    {
        /**
         * Create database connection
         */
        $this->db();

         /**
         * Get CSRF Token
         */
        $CSRFToken = $this->CSRFToken();

        /**
         * Get IP Info
         */
        $IpInfo = $this->ipinfo();

        /**
         * Auth.
         */
        $AuthUser = $this->auth();

        /**
         * Google Client
         */
        $Google = new Google($AuthUser);

        /**
         * Analize the route
         */
        $this->route();

        $this->controller->setVariable("CSRFToken", $CSRFToken);
        $this->controller->setVariable("IpInfo", $IpInfo);
        $this->controller->setVariable("AuthUser", $AuthUser);
        $this->controller->setVariable("Google", $Google);

        /**
         * Init. locales
         */
        $this->i18n();

        $this->controller->index();
    }
}