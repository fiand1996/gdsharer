<?php 
/**
 * Event System
 *
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */
class Event
{
    /**
     * Array of binded event callbacks
     * 
     * @var array
     */
    public static $callbacks = [];


    /**
     * @param $event
     * @param callable $func
     */
    public static function bind($event, Callable $func)
    {
        if(empty(self::$callbacks[$event]) || !is_array(self::$callbacks[$event])){
            self::$callbacks[$event] = [];
        }

        self::$callbacks[$event][] = $func;
    }


    /**
     * @return mixed
     */
    public static function trigger()
    {
        $args = func_get_args();
        $event = $args[0];
        unset($args[0]);

        if (isset(self::$callbacks[$event])) {
            foreach(self::$callbacks[$event] as $func) {
                call_user_func_array($func, $args);
            }
        }
    }
}
