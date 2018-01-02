<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

/**
 * Medicaties model.
 *
 * dosis_eenheid is een numerieke waarde, zie de constantes Medicaties::EENHEID_*
 * dosis_kg is gewijzigd naar hoeveel dosis per liter water. Bv: 2gram per 10 liter water, dan is 10 hier de waarde.
 * dosis_lg is hoe groot de dosis per kilogram levend gewicht (bv: 2gr per 120kg LG), in deze kolom staat het getal 120.
 *
 * Via de methode #getDosisAsString wordt de dosis weergegeven als een leesbare tekst.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class MedicatiesLog extends Model
{


    public $id;

    public $user;

    public $medicatie;

    public $data;

    public $datum;


    public function initialize()
    {
        //relaties
        $this->belongsTo('user', 'Users', 'id', array('foreignKey' => true));
        $this->belongsTo('medicatie', 'Medicaties', 'id', array('foreignKey' => true));
    }


    public function getSchema()
    {
        return 'medipig';
    }

}