<?php
use Phalcon\Mvc\Model;

/**
 * Log model
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Log extends Model
{

    const ERROR_ERROR = 'error';
    const ERROR_DENIED = 'denied';
    const ERROR_NOTFOUND = 'notFound';


    public $datum;

    public $time;

    public $user;

    public $bedrijf;

    public $role;

    public $error;

    public $log;


    public function getSchema()
    {
        return 'medipig';
    }


}
