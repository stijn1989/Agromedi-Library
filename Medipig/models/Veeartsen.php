<?php
use Phalcon\Mvc\Model;

/**
 * Veeartsen model.
 *
 * Bevat alle veeartsen gekend in het systeem. Een bedrijf heeft een standaard veearts.
 * Deze relatie naar bedrijven toe is unidirectioneel.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Veeartsen extends Model
{


    public $id;

    public $naam;

    public $bestel_email;

    public $email;

    public $user;


    public function initialize()
    {
        //relaties
        $this->belongsTo('user', 'Users', 'id', array('foreignKey' => array('allowNulls' => true)));
        $this->hasMany('id', 'Bedrijven', 'veearts');
    }


    public function getSchema()
    {
        return 'medipig';
    }


}
