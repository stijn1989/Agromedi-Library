<?php
use Phalcon\Mvc\Model;

/**
 * Verpakkingen model.
 *
 * Een medicatie kan in verschillende verpakkingen op de markt gebracht worden.
 * Vb: 1 fles van 10 dosis, 1 fles van 50 dosis...
 *
 * Andere medicaties hebben dan maar één verpakking. Iedere medicatie heeft standaard één verpakking
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Verpakkingen extends Model
{


    /**
     * Verpakking types
     */
    const TYPE_ZAK = 0;
    const TYPE_DOOS = 1;
    const TYPE_PET = 2;
    const TYPE_GLAS = 3;
    const TYPE_CONTAINER = 4;
    const TYPE_SECURITAINER = 5;
    const TYPE_EMMER = 6;
    const TYPE_BUS = 7;
    const TYPE_POT = 8;
    const TYPE_COMBITIN = 9;
    const TYPE_HDPE = 10;
    const TYPE_VERPAKKING = 11;
    const TYPE_CYL_CONTAINER = 12;
    const TYPE_REC_CONTAINER = 13;


    public $id;

    public $medicatie;

    public $inhoud;

    public $doses;

    public $type;


    public function initialize()
    {
        //relaties
        $this->belongsTo('medicatie', 'Medicaties', 'id', array('foreignKey' => array('allowNulls' => true)));
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public static function getTypes()
    {
        return [
            self::TYPE_ZAK => 'Zak',
            self::TYPE_DOOS => 'Doos',
            self::TYPE_PET => 'PET fles',
            self::TYPE_GLAS => 'Glazen fles',
            self::TYPE_CONTAINER => 'Container',
            self::TYPE_SECURITAINER => 'Securitainer',
            self::TYPE_EMMER => 'Emmer',
            self::TYPE_BUS => 'Bus',
            self::TYPE_POT => 'Pot',
            self::TYPE_COMBITIN => 'Combi-tin',
            self::TYPE_HDPE => 'HDPE fles met schroefdop',
            self::TYPE_VERPAKKING => 'Verpakking',
            self::TYPE_CYL_CONTAINER => 'Cylindervormige container',
            self::TYPE_REC_CONTAINER => 'Rechthoekige container',
        ];
    }


    public function typeAsString()
    {
        switch($this->type)
        {
            default:
            case self::TYPE_ZAK:
                $str = 'Zak';
                break;
            case self::TYPE_DOOS:
                $str = 'Doos';
                break;
            case self::TYPE_PET:
                 $str = 'Fles';
                break;
            case self::TYPE_GLAS:
                 $str = 'Fles';
                break;
            case self::TYPE_CONTAINER:
                $str = 'Container';
                break;
            case self::TYPE_SECURITAINER:
                $str = 'Securitainer';
                break;
            case self::TYPE_EMMER:
                $str = 'Emmer';
                break;
            case self::TYPE_BUS:
                $str = 'Bus';
                break;
            case self::TYPE_POT:
                $str = 'Pot';
                break;
            case self::TYPE_COMBITIN:
                $str = 'Combi-tin';
                break;
            case self::TYPE_HDPE:
                $str = 'HDPE fles met schroefdop';
                break;
            case self::TYPE_VERPAKKING:
                $str = 'Verpakking';
                break;
            case self::TYPE_CYL_CONTAINER:
                $str = 'Cylindervormige container';
                break;
            case self::TYPE_REC_CONTAINER:
                $str = 'Rechthoekige container';
                break;
        }

        return $str;
    }


    public function __toString()
    {
        $str = '';
        $m = $this->medicaties;

        //ml of gram
        if($m->vorm == Medicaties::VORM_INJECTIE) $ext = 'ml';
        else $ext = 'gr';

        //dosis weergeven?
        if(($m->dosis > 1 && empty($m->dosis_kg) && empty($m->dosis_lg)) ||! empty($this->doses)) {
            if(! empty($this->doses)) {
                $dosis = $this->doses;
            } else {
                $dosis = $this->inhoud / $m->dosis;
            }

            $str .= $this->typeAsString() . ' van ' . $dosis . ' doses';
            $str .= ' (' . $this->inhoud . $ext . ')';
        } else {
            $str .= $this->typeAsString() .  ' (' . $this->inhoud . $ext . ')';
        }



        return $str;
    }


}