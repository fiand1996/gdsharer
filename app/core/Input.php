<?php
/**
 * Input Core
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class Input
{

    public function __construct()
    {
    }

    /**
     * Session inputs
     * @param  string  $method         name of method (get | post | request | session | cookie)
     * @param  string  $input_name     name of input
     * @param  int|bool  $index       index in input array of treat as $trim
     * @param  boolean $trim          trim input value (if it is string) or not
     * @return mix
     */
    public static function getInput($method, $input_name, $index = true, $trim = true)
    {
        if (!in_array($method, array("get", "post", "request", "cookie", "session", "server"))) {
            throw new \Exception('Invalid method!');
        }

        $input = null;

        $method = "_" . strtoupper($method);
        if (isset($GLOBALS[$method][$input_name])) {
            $input = $GLOBALS[$method][$input_name];
        }

        if (is_array($input) && is_int($index)) {
            if ($index >= 0) {
                if (isset($input[$index])) {
                    $input = $input[$index];
                } else {
                    throw new \Exception('Index is not exists!');
                }
            } else {
                throw new \Exception('Invalid index');
            }
        }

        if (!is_array($input) || !is_int($index)) {
            $trim = (bool) $index;
        }

        if (is_string($input) && $trim) {
            $input = trim($input);
        }

        return $input;
    }

    public static function __callStatic($name, $arguments)
    {
        $name = strtolower($name);

        if ($name == "req") {
            $name = "request";
        }

        if (in_array($name, array("get", "post", "request", "cookie", "session", "server"))) {
            array_unshift($arguments, $name);
            return call_user_func_array(array('Input', 'getInput'), $arguments);
        } else {
            throw new \Exception('Invalid method');
        }
    }

    public static function isAjaxRequest()
    {
        if (self::server("HTTP_X_REQUESTED_WITH") &&
            strtolower(self::server("HTTP_X_REQUESTED_WITH")) == 'xmlhttprequest') {
            return true;
        }

        return false;
    }

    public static function exists($type = 'post')
    {
        switch ($type) {
            case 'post':
                return (!empty($_POST)) ? true : false;
                break;
            case 'get':
                return (!empty($_GET)) ? true : false;
                break;

            default:
                return false;
                break;
        }
    }

    public function __call($name, $arguments)
    {
        $name = strtolower($name);

        if ($name == "req") {
            $name = "request";
        }

        if (in_array($name, array("get", "post", "request", "cookie", "session", "server"))) {
            array_unshift($arguments, $name);
            return call_user_func_array(array('Input', 'getInput'), $arguments);
        } else {
            throw new \Exception('Invalid method');
        }
    }

}
