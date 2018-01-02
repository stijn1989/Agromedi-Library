<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

/**
 * Iedere label (certus, codiplan, ...) heeft bepaalde regels per categorie.
 * Bv of een label medicaties in die categorie kan bestellen of niet.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class CategoriesLabels extends Model
{


    public $id;

    public $categorie;

    public $label;

    /**
     * @var omdat phalcon "label" niet erkent in forms #newbs
     */
    public $label_intern;

    public $kanBestellen;


    public function initialize()
    {
        //relaties
        $this->belongsTo('label', 'Labels', 'id', array('foreignKey' => true));
        $this->belongsTo('categorie', 'Categories', 'id', array('foreignKey' => true));
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function beforeValidation()
    {
        $this->label = $this->label_intern;
    }


    public function afterFetch()
    {
        $this->label_intern = $this->label;
    }


}
