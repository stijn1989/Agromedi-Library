<?php
namespace ITC;


/**
 * Utility klasse met handige functies.
 *
 * @package ITC
 */
class Util
{


    public static $date_format = 'd/m/Y';

    public static $date_empty_text = 'N/A';


    public static function PHP2MySQLDate(\DateTime $dt = null)
    {
        if($dt == null) $dt = new \DateTime();
        return $dt->format('Y-m-d');
    }


    public static function MySQL2PHPDate($mysql_date)
    {
        if(empty($mysql_date)) return self::$date_empty_text;

        return \DateTime::createFromFormat('Y-m-d', $mysql_date)->format(self::$date_format);
    }


    public static function roleToString($roleInt)
    {
        $role = 'Gast';
        switch($roleInt) {
            case 1:
                $role = ROLE_VIEWER;
                break;
            case 2:
                $role = ROLE_BESTELLER;
                break;
            case 3:
                $role = ROLE_TOEDIENER;
                break;
            case 4:
                $role = ROLE_BEHEERDER;
                break;
        }

        return $role;
    }


    public static function rolesToArray()
    {
        return [
            1 => ROLE_VIEWER,
            2 => ROLE_BESTELLER,
            3 => ROLE_TOEDIENER,
            4 => ROLE_BEHEERDER
        ];
    }


    public static function bestellingFaseColor($fase, $compleet = 1)
    {
        $color = "text-primary-dark";
        if($fase == 1) {
            $color = "text-primary-dark";
        } elseif($fase == 2) {
            $color = "text-info";
        } elseif($fase == 3) {
            $color = "text-warning";
        } elseif($fase == 4 && $compleet == 1) {
            $color = "text-success";
        } elseif($fase == 4 && $compleet == 0) {
            $color = "text-danger";
        }

        return $color;
    }


    public static function decimalToInt($var)
    {
        if(is_numeric($var)) {
            if(is_string($var)) $var = floatval($var);
            $a = (float) intval($var); //12.3 wordt 12.0
            if($a == $var) { //als $var 12.0 is, dan is $a = 12.0, dan mag de .0 weg in $var
                $var = intval($a);
            }
        }

        return $var;
    }


}