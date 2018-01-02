<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

/**
 * Bedrijf label model
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Labels extends Model
{


    public $id;

    public $naam;

    /**
     * @var int wachttijd factor voor de laatste 30 dagen
     */
    public $factor;


    public function initialize()
    {
        //relaties
        $this->hasMany('id', 'Bedrijven', 'label');
        $this->hasMany('id', 'CategoriesLabels', 'label');
    }


    public function getSchema()
    {
        return 'medipig';
    }


}
