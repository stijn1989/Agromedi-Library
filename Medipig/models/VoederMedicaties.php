<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

/**
 * VoederMedicaties model.
 *
 * Deze stelt de medicaties voor die in het voeder mogen.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class VoederMedicaties extends Model
{


    const TYPE_ONBEKEND = 0;
    const TYPE_ANTIBIOTICA = 1;
    const TYPE_VACCINATIE = 2;
    const TYPE_HORMONALE = 3;
    const TYPE_NSAID = 4;
    const TYPE_CORTICOSTEROIDE = 5;
    const TYPE_PARASITAIRE = 6;
    const TYPE_IJZER = 7;


    public $id;

    public $medicatie;

    public $duurtijd;

    public $type;


    public function initialize()
    {
        //relaties
        $this->hasMany('id', 'Wachttijden', 'voeder_medicatie');
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function getWachttijd($doelgroep)
    {
        if($doelgroep instanceof Doelgroepen) $doelgroep = $doelgroep->id;
        $w = Wachttijden::findFirst([
            'conditions' => 'voeder_medicatie = ?1 AND doelgroep = ?2',
            'bind' => [1 => $this->id, 2 => $doelgroep]
        ]);

        if($w == null) return 0; //geen wachttijd gevonden
        return $w['wachttijd'];
    }


}