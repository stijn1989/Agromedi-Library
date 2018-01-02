<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

/**
 * Medicatie categorie model
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Categories extends Model
{


    public $id;

    public $naam;


    public function initialize()
    {
        //relaties
        $this->hasMany('id', 'Medicaties', 'categorie');
        $this->hasMany('id', 'CategoriesLabels', 'categorie');
    }


    public function getSchema()
    {
        return 'medipig';
    }


}
