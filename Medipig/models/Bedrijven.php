<?php
use ITC\Model;

/**
 * Bedrijven model.
 *
 * Docpath van een bedrijf is standaard /docs/<id>/.
 * Een leveringsdocument bevind zich dan in /docs/<id>/leveringen/<document.filename>
 *
 * Records worden niet gedelete maar worden op inactief geplaatst.
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Bedrijven extends Model
{


    const STATUS_NIET_ACTIEF = 0;
    const STATUS_ACTIEF_NIET_BETAALD = 1;
    const STATUS_ACTIEF_BETAALD = 2;
    const STATUS_BLOCKED = 3;


    public $id;

    public $klant;

    public $licentie;

    public $naam;

    public $dbname;

    public $veearts;

    public $label;

    /**
     * @var omdat phalcon "label" niet erkent in forms #newbs
     */
    public $label_intern;

    public $beslagnr;

    public $email;

    public $laatste_controle;

    public $next_t_group_id;

    public $status;

    public $installatie;


    public function initialize()
    {
        //relaties
        $this->belongsTo('veearts', 'Veeartsen', 'id', array('foreignKey' => true));
        $this->belongsTo('label', 'Labels', 'id', array('foreignKey' => true));
        $this->belongsTo('klant', 'Klanten', 'id', array('foreignKey' => true));
        $this->belongsTo('licentie', 'Licenties', 'id', array('foreignKey' => array('allowNulls' => true), 'alias' => 'CurrentLicentie'));
        $this->hasMany('id', 'UsersBedrijven', 'bedrijf');
        $this->hasMany('id', 'Berichten', 'bedrijf');
        $this->hasMany('id', 'BedrijvenDoelgroepen', 'bedrijf');
        $this->hasMany('id', 'Licenties', 'bedrijf');
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function getDocPath()
    {
        return dirname(__FILE__) . '/../../../agromedi.be/docs/' . $this->id . '/';
    }


    public function beforeValidation()
    {
        $this->label = $this->label_intern;
    }


    public function afterFetch()
    {
        $this->label_intern = $this->label;
    }


    public function getGroupId()
    {
        $id = $this->next_t_group_id;
        $this->next_t_group_id += 1; //updaten
        $this->save();
        return md5($id); //hashen
    }


    public function getDoelgroepenAsString()
    {
        $str = '';
        foreach($this->getBedrijvenDoelgroepen() as $bd) {
            if(! empty($str)) $str .= ', ';
            $str .= $bd->doelgroepen->doelgroep;
        }

        return $str;
    }


    public function getDoelgroepen()
    {
        $d = [];
        foreach($this->getBedrijvenDoelgroepen() as $bd) {
            $d[] = $bd->doelgroepen;
        }

        return $d;
    }


    public function getStatusAsString()
    {
        switch($this->status) {
            case self::STATUS_NIET_ACTIEF:
                return "Niet actief";
            case self::STATUS_ACTIEF_NIET_BETAALD:
                return "Actief niet betaald";
            case self::STATUS_ACTIEF_BETAALD:
                return "Actief";
            case self::STATUS_BLOCKED:
                return "Geblokkeerd";
        }

        return "n/a";
    }


}
