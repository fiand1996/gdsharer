<?php

/**
 *
 */
class Token
{

    public static function generate()
    {
        if (function_exists('mcrypt_create_iv')) {
            $token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        } else {
            $token = bin2hex(random_bytes(32));
		}
		
        return Session::set("csrftoken", $token);
    }

    public static function check($token)
    {
        $tokenName = "csrftoken";

        if (Session::exists($tokenName) && $token === Session::get($tokenName)) {
            Session::delete($tokenName);
            return true;
        }
        return false;
    }
}
