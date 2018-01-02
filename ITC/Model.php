<?php
namespace ITC;


/**
 * Common model features here!
 *
 * @package ITC
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Model extends \Phalcon\Mvc\Model
{


    private static $months = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maart',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Augustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'December'
    ];


    public function getSchema()
    {
        if($this->getDI()->get('session')->has('auth.bedrijf')) {
            return $this->getDI()->get('session')->get('auth.bedrijf')['schema'];
        }

        throw new \Exception("No schema name found! Are you logged in?");
    }


    /**
     * 03 Januari 2016 wordt 2016-01-03
     *
     * @param $date
     * @return string
     */
    public function saveDate($date)
    {
        if(! empty($date) && !preg_match("~\d{4}\-\d{2}\-\d{2}~", $date)) {
            foreach(self::$months as $k => $v) {
                if(strpos($date, $v) !== false) {
                    $date = str_replace($v, $k, $date);
                }
            }

            return  Util::PHP2MySQLDate(\DateTime::createFromFormat('d m Y', $date));
        }

        return $date;
    }


    /**
     * 03 Januari 2016 wordt datetime object
     *
     * @param $date
     * @return \DateTime
     */
    public static function convertToDatetime($date)
    {
        if(! empty($date) && !preg_match("~\d{4}\-\d{2}\-\d{2}~", $date)) {
            foreach(self::$months as $k => $v) {
                if(strpos($date, $v) !== false) {
                    $date = str_replace($v, $k, $date);
                }
            }

            return  \DateTime::createFromFormat('d m Y', $date);
        }
    }

    /**
     *  2016-01-03 wordt 03 Januari 2016
     *
     * @param $date
     * @return string
     */
    public function loadDate($date)
    {
        if(! empty($date)) {
            foreach(self::$months as $k => $v) {
                if(substr($date, 5, 2) == $k) {
                    return substr($date, 8) . ' ' . $v . ' ' . substr($date, 0, 4);
                }
            }

            return ''; //niets gevonden, mhz klopt niet
        }

        return $date;
    }

    /**
     *  2016-01-03 wordt 03 Januari 2016
     *
     * @param $date
     * @return string
     */
    public static function convertToHumanDate($date)
    {
        if(! empty($date)) {
            foreach(self::$months as $k => $v) {
                if(substr($date, 5, 2) == $k) {
                    return substr($date, 8) . ' ' . $v . ' ' . substr($date, 0, 4);
                }
            }

            return ''; //niets gevonden, mhz klopt niet
        }

        return $date;
    }


}