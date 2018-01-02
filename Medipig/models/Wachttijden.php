<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

/**
 * Wachttijden model.
 *
 * De wachttijden per doelgroep
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Wachttijden extends Model
{

    public $id;

    public $medicatie;

    public $voeder_medicatie;

    public $doelgroep;

    public $wachttijd;


    public function initialize()
    {
        //relaties
        $this->belongsTo('medicatie', 'Medicaties', 'id', array('foreignKey' => array('allowNulls' => true)));
        $this->belongsTo('voeder_medicatie', 'VoederMedicaties', 'id', array('foreignKey' => array('allowNulls' => true)));
        $this->belongsTo('doelgroep', 'Doelgroepen', 'id', array('foreignKey' => true));
    }


    public function getSchema()
    {
        return 'medipig';
    }


}