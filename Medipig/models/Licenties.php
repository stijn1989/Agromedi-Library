<?php
use ITC\Model;

/**
 * Licenties model
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Licenties extends Model
{


    public $id;

    public $bedrijf;

    public $start;

    public $einde;

    public $betaald;

    public $gratis;

    public $aantal_zeugen;

    public $aantal_vv;

    public $aantal_fok;

    public $gesloten;


    public function initialize()
    {
        //relaties
        $this->belongsTo('bedrijf', 'Bedrijven', 'id', array('foreignKey' => true));
    }


    public function licentieDescription()
    {
        if($this->gratis == 1) return "Gratis licentie";

        $str = '';
        if($this->gesloten == 1) {
            $str = $this->aantal_zeugen . ' zeugen (gesloten)';
        } else {
            if($this->aantal_zeugen > 0) $str .= $this->aantal_zeugen . ' zeugen';
            if($this->aantal_fok > 0) {
                if(! empty($str)) $str .= ', ';
                $str .= $this->aantal_fok . ' fokzeugen';
            }
            if($this->aantal_vv > 0) {
                if(! empty($str)) $str .= ', ';
                $str .= $this->aantal_vv . ' vleesvarkens';
            }
        }
        return $str;
    }


    public function licentieAuteurPrice()
    {
        if($this->gratis == 1) return ['gratis', 'totaal' => 0];
        $start = \DateTime::createFromFormat('Y-m-d', $this->start);
        $einde = \DateTime::createFromFormat('Y-m-d', $this->einde);
        $diff = $start->diff($einde);
        $days = intval($diff->format('%a'))+1;
        $daysThisYear = intval(date('z', mktime(0,0,0,12,31,intval($start->format('Y')))) + 1);

        $price = [];
        $totaal = 0;
        if($this->gesloten == 1) {
            $totaal = $this->aantal_zeugen;
            $price['gesloten'] = $this->aantal_zeugen;
        } else {
            if($this->aantal_zeugen > 0) {
                $price['zeugen'] = $this->aantal_zeugen * 0.7;
                $totaal += $price['zeugen'];
            }
            if($this->aantal_fok > 0) {
                $price['fokzeugen'] = $this->aantal_fok * 0.036;
                $totaal += $price['fokzeugen'];
            }
            if($this->aantal_vv > 0) {
                $price['vleesvarkens'] = $this->aantal_vv * 0.01;
                $totaal += $price['vleesvarkens'];
            }
        }
        $price['totaal'] = ceil($totaal/$daysThisYear*$days);
        return $price;
    }


    public function getSchema()
    {
        return 'medipig';
    }

}
