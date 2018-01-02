<?php
namespace ITC;


/**
 * Cookie wrapper class
 *
 * @package ITC
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Cookie
{


    public static function set($name, $value, $time = null, $domain = '')
    {
        if($time == null) $time = time() + 60*60*24*365;
        setcookie($name, $value, $time, '/', $domain);
    }


    public static function delete($name, $domain = '')
    {
        unset($_COOKIE[$name]);
        setcookie($name, '', time()-3600, '/', $domain);
    }


    public static function get($name)
    {
        if(isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        } else {
            return null;
        }
    }


    public static function has($name)
    {
        return isset($_COOKIE[$name]);
    }


}