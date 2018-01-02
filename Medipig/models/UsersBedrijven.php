<?php
use Phalcon\Mvc\Model;

/**
 * UsersBedrijven model.
 *
 * Een gebruiker heeft toegang tot meerdere bedrijven. Dit kan met verschillende rollen zijn.
 * De role veld is binair getal waar bitwise AND operatie wordt op toegepast.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class UsersBedrijven extends Model
{


    public $id;

    public $user;

    public $bedrijf;

    public $role = 0;


    public function initialize()
    {
        //relaties
        $this->belongsTo('user', 'Users', 'id', array('foreignKey' => true));
        $this->belongsTo('bedrijf', 'Bedrijven', 'id', array('foreignKey' => true));
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function getSource()
    {
        return 'users_bedrijven';
    }


}
