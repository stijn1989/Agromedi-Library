<?php
use ITC\Model;

/**
 * BedrijvenDoelgroepen model.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class BedrijvenDoelgroepen extends Model
{


    public $id;

    public $bedrijf;

    public $doelgroep;


    public function initialize()
    {
        //relaties
        $this->belongsTo('bedrijf', 'Bedrijven', 'id', array('foreignKey' => true));
        $this->belongsTo('doelgroep', 'Doelgroepen', 'id', array('foreignKey' => true));
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function getSource()
    {
        return 'bedrijven_doelgroepen';
    }


}
