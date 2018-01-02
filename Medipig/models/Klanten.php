<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

/**
 * Klanten model
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Klanten extends Model
{


    public $id;

    public $naam;

    public $email;

    public $telefoon;

    public $adres;

    public $postcode;

    public $gemeente;

    public $created_at;

    public $created_by;


    public function initialize()
    {
        //relaties
        $this->belongsTo('created_by', 'Users', 'id', array('foreignKey' => true, 'alias' => 'createdBy'));
        $this->hasMany('id', 'Bedrijven', 'klant');
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function beforeCreate()
    {
        $this->created_at = date('Y-m-d');
    }


}
