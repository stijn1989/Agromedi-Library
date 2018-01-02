<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

/**
 * Users model.
 *
 * Een user kan bij meerdere bedrijven verschillende rollen uitoefenen.
 * Om te zien of een user tot een bedrijf hoort, kan je zijn rollen opvragen bij dat bedrijf.
 * Als die null opleveren, behoort hij niet toe tot dat bedrijf.
 *
 * Records worden niet gedelete maar worden op inactief geplaatst.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Users extends Model
{


    const ROLE_USER = 1;
    const ROLE_AGROMEDI = 2;
    const ROLE_DANAPIG = 3;
    const ROLE_VEEARTS = 4;


    public $id;

    public $username;

    public $password;

    public $last_login;

    public $role;

    public $password_change;


    public function initialize()
    {
        //relaties
        $this->hasMany('id', 'UsersBedrijven', 'user');
        $this->hasMany('id', 'Klanten', 'created_by');
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function getBedrijven()
    {
        return $this->usersBedrijven;
    }


    public function isDemo()
    {
        return $this->demo == 1;
    }


    public static function createRandomPassword()
    {
        $chars = array_merge(
            range(0,9),
            range('a','z'),
            range('A','Z'),
            ['$','_','@']
        );
        $password = '';
        $l = count($chars) - 1;
        for($i = 0 ; $i < 8 ; $i++) {
            $password .= $chars[rand(0, $l)];
        }
        return $password;
    }


    public function getRoleAsString()
    {
        $str = 'n/a';
        switch($this->role)
        {
            case self::ROLE_VEEARTS:
                $str = 'Veearts';
                break;
            case self::ROLE_DANAPIG:
                $str = 'Danapig';
                break;
            case self::ROLE_USER:
                $str = 'Agromedi';
                break;
            case self::ROLE_AGROMEDI:
                $str = 'Auteur';
                break;
        }

        return $str;
    }


}
