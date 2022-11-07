<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

/**
 * Constantes voor de vergunning veld in medicaties.
 */
interface Vergunning
{

    /**
     * Vergunning voor het in de handel brengen.
     */
    const HANDEL = 0;

    /**
     * Vergunning registratie
     */
    const REGISTRATIE = 1;

    /**
     * Vergunning handel / registratie
     */
    const HANDEL_REGISTRATIE = 2;

    /**
     * Vergunning parrallelinvoer
     */
    const PARRALLELINVOER = 3;

}

/**
 * Constantes voor de aflevering veld in medicaties.
 */
interface Aflevering
{

    /**
     * Vrije aflevering (= niet aan een geneeskundig voorschrift onderworpen),
     */
    const VRIJ = 0;

    /**
     * Geneeskundig voorschrift
     */
    const VOORSCHRIFT = 1;

    /**
     * Schriftelijke aanvraag van de patiënt
     */
    const AANVRAAG = 2;

}

/**
 * Medicaties model.
 *
 * @author  Stijn Leenknegt <stijn@diagro.io>
 * @version 2.0
 */
class Medicaties extends Model
{

    /**
     * Eenheid milliliter/cc
     */
    const EENHEID_CC = 0;

    /**
     * Eenheid gram
     */
    const EENHEID_GR = 1;

    /**
     * Injectie medicatie
     */
    const VORM_INJECTIE = 0;

    /**
     * Poeder medicatie
     */
    const VORM_POEDER = 1;

    const TYPE_ONBEKEND = 0;
    const TYPE_ANTIBIOTICA = 1;
    const TYPE_VACCINATIE = 2;
    const TYPE_HORMONALE = 3;
    const TYPE_NSAID = 4;
    const TYPE_CORTICOSTEROIDE = 5;
    const TYPE_PARASITAIRE = 6;
    const TYPE_IJZER = 7;


    public $id;

    public $cti;

    public $naam;

    public $vergunning = Vergunning::HANDEL_REGISTRATIE;


    public function initialize()
    {
        //relaties
        $this->belongsTo('categorie', 'Categories', 'id', array('foreignKey' => array('allowNulls' => true)));
        $this->hasMany('id', 'Voorraad', 'medicatie');
        $this->hasMany('id', 'Verpakkingen', 'medicatie');
        $this->hasMany('id', 'Wachttijden', 'medicatie');
        $this->hasMany('id', 'MedicatiesLog', 'medicatie');
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function getWachttijd($doelgroep)
    {
        if($doelgroep instanceof Doelgroepen) $doelgroep = $doelgroep->id;
        $w = Wachttijden::findFirst([
            'conditions' => 'medicatie = ?1 AND doelgroep = ?2',
            'bind' => [1 => $this->id, 2 => $doelgroep]
        ]);

        if($w == null) return 0; //geen wachttijd gevonden
        return $w['wachttijd'];
    }


    public function afterFetch()
    {
        if($this->vorm == self::VORM_INJECTIE) {
            $this->dosis_eenheid = self::EENHEID_CC;
        } else {
            $this->dosis_eenheid = self::EENHEID_GR;
        }

        //convert 2.0 to 2 if decimal part is zero, other nothing
        $this->dosis = \ITC\Util::decimalToInt($this->dosis);
        $this->standaard_dosis = \ITC\Util::decimalToInt($this->standaard_dosis);
    }


    /**
     * Geeft een leesbare tekst terug van de dosis van deze medicatie.
     * Dit is een samenvoegsel van dosis, dosis_eenheid, dosis_kg en dosis_lg.
     * Voorbeeld zou kunnen zijn: 2gr per 120kg LG)
     *
     * @return string
     */
    public function getDosisAsString()
    {
        $eenheid = ($this->dosis_eenheid == self::EENHEID_CC) ? 'ml' : 'gram';

        if($this->dosis_lg > 0) {
            return $this->dosis . $eenheid . ' per ' . $this->dosis_lg . 'kg levend gewicht';
        } elseif($this->dosis_kg > 0) {
            return $this->dosis . $eenheid . ' per ' . $this->dosis_kg . 'liter water';
        } else {
            return $this->dosis . $eenheid . ' per dier';
        }
    }


    /**
     * Controleert of een bedrijf deze medicatie kan bestellen.
     * Afhankelijk van de categorie van deze medicatie en het label van het bedrijf.
     * Geen categorie betekent kan bestellen.
     *
     * @param Bedrijven $b
     * @return bool
     */
    public function kanBestellen(Bedrijven $b)
    {
        if(! empty($this->categorie)) {
            $regel = CategoriesLabels::findFirst([
                'conditions' => 'categorie = ?1 AND label = ?2',
                'bind' => [1 => $this->categorie, 2 => $b->label]
            ]);
            if($regel != null) {
                return (bool) $regel->kanBestellen;
            }
        }

        return true;
    }


    /**
     * Zoekt de medicaties die een bedrijf kan bestellen volgens zijn label.
     *
     * @param $label
     * @return Resultset
     */
    public static function findKanBestellen($label)
    {
        $sql = "select m.*
                from medipig.medicaties as m
                left join medipig.categories as c on(m.categorie = c.id)
                left join medipig.categories_labels as cl on(c.id = cl.categorie)
                where ((cl.kanBestellen = 1 and cl.label = ?) or m.categorie is null)
                order by m.naam asc";

        $medicatie = new Medicaties();
        return new Resultset(null, $medicatie, $medicatie->getReadConnection()->query($sql, [$label]));
    }


    public function typeAsString()
    {
        switch($this->type)
        {
            default:
            case self::TYPE_ONBEKEND:
                $str = 'N/A';
                break;
            case self::TYPE_ANTIBIOTICA:
                $str = 'Antibiotica';
                break;
            case self::TYPE_VACCINATIE:
                $str = 'Vaccinatie';
                break;
            case self::TYPE_HORMONALE:
                $str = 'Hormonale';
                break;
            case self::TYPE_NSAID:
                $str = 'NSAID';
                break;
            case self::TYPE_CORTICOSTEROIDE:
                $str = 'Coricosteroide';
                break;
            case self::TYPE_PARASITAIRE:
                $str = 'Anti-parasitair';
                break;
            case self::TYPE_IJZER:
                $str = 'Ijzer';
                break;
        }

        return $str;
    }


}