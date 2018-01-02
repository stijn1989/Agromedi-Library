<?php
use Phalcon\Mvc\Model;

/**
 * Doelgroepen model.
 *
 * Een doelgroep is een (sub)groep van dieren. Standaard zijn dit:
 *  - Vleesvarkens
 *  - Zeugen
 *  - Biggen
 *  - Gespeend
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Doelgroepen extends Model
{


    public $id;

    public $doelgroep;

    public $route;

    public $controle;

    public $groep;

    public $individueel;

    public $schemas;

    public $rapport;


    public function getSchema()
    {
        return 'medipig';
    }


    public function heeftGroepWachttijd(\DateTime $dt = null)
    {
        if($dt == null) $dt = new \DateTime();
        $b = \Phalcon\Di::getDefault()->get('session')->get('auth.bedrijf');
        $schema = $b['schema'];
        $dtm = \ITC\Util::PHP2MySQLDate($dt);
        $id = $this->id;
        $factor = Bedrijven::findFirst($b['bedrijf'])->labels->factor;

        //label factor
        if($factor > 1) { //wachttijd de laatste maand maal $factor doen. Enkel als factor groter is dan 1, want dit is een lastige case query
            $wachttijdSelect = "(case when datediff('$dtm', t.datum) <= 30 then w.wachttijd*$factor else w.wachttijd end)";
        } else {
            $wachttijdSelect = 'w.wachttijd';
        }

        $sql = "SELECT t.id, t.datum, t.medicatie, m.naam, t.reden, t.afdeling, DATEDIFF(DATE_ADD(t.datum, INTERVAL $wachttijdSelect DAY), '$dtm') AS dagen_verschil " .
            "FROM $schema.toedieningen AS t " .
            "INNER JOIN $schema.voorraad_toedieningen as vt ON(vt.toediening = t.id) " .
            "INNER JOIN medipig.medicaties AS m ON(m.id = t.medicatie) " .
            "INNER JOIN medipig.wachttijden AS w ON(t.medicatie = w.medicatie) " .
            "WHERE t.dier IS NULL AND t.doelgroep=$id AND w.doelgroep=$id AND DATEDIFF(DATE_ADD(t.datum, INTERVAL $wachttijdSelect DAY), '$dtm') > 0 AND t.datum <= '$dtm' " .
            "ORDER BY dagen_verschil DESC";

        $data = [];
        foreach($this->getReadConnection()->fetchAll($sql) as $row) {
            $row['datum'] = \ITC\Model::convertToHumanDate($row['datum']);
            $key = md5($row['medicatie'] . $row['reden'] . $row['afdeling']);
            if(array_key_exists($key, $data)) {
                if($data[$key]['dagen_verschil'] < $row['dagen_verschil']) {
                    $data[$key] = $row;
                }
            } else {
                $data[$key] = $row;
            }
        }

        return $data;
    }


    public function heeftVoederWachttijd(\DateTime $dt = null)
    {
        if($dt == null) $dt = new \DateTime();
        $b = \Phalcon\Di::getDefault()->get('session')->get('auth.bedrijf');
        $schema = $b['schema'];
        $dtm = \ITC\Util::PHP2MySQLDate($dt);
        $id = $this->id;
        $factor = Bedrijven::findFirst($b['bedrijf'])->labels->factor;

        $sql = "SELECT v.id, v.datum_in_silo, v.datum_leeg_silo, m.medicatie as medicatie, s.naam as silo, w.wachttijd as wachttijd, s.doelgroep as doelgroep, m.type as type_m " .
            "FROM $schema.medicatie_voeder AS v " .
            "INNER JOIN $schema.silos AS s ON(v.silo = s.id) " .
            "INNER JOIN medipig.voeder_medicaties AS m ON(m.id = v.medicatie) " .
            "INNER JOIN medipig.wachttijden AS w ON(v.medicatie = w.voeder_medicatie) " .
            "WHERE s.doelgroep=$id AND w.doelgroep=$id AND v.datum_in_silo <= '$dtm'";

        $dtMinOneMonth = clone $dt;
        $dtMinOneMonth->sub(new \DateInterval('P1M'));

        $data = [];
        $nsaid_ab = [];
        foreach($this->getReadConnection()->fetchAll($sql) as $row) {
            if(!empty($row['datum_leeg_silo']) && $row['datum_leeg_silo'] != '0000-00-00') {
                //datum verschil bereken sinds leeg en $dt, rekening houdend met $factor
                $dtLeeg = \DateTime::createFromFormat('Y-m-d', $row['datum_leeg_silo']);
                if($dtLeeg >= $dtMinOneMonth) {
                    $days = intval($row['wachttijd']) * $factor; //enkel de laatste maand wachtijd met factor vermeerderen
                } else {
                    $days = intval($row['wachttijd']);
                }
                $dtLeeg->add(new \DateInterval('P' . $days . 'D'));
                if($dtLeeg > $dt) {
                    $data['v_' . $row['id']] = ['silo' => $row['silo'], 'medicatie' => $row['medicatie'], 'leeg' => \ITC\Model::convertToHumanDate($row['datum_leeg_silo']), 'wachttijd' => intval($dt->diff($dtLeeg, true)->format('%a'))];
                }

                //combinatie nsaid + ab in dezelfde silo met dezelfde geblazen datum heeft een wachttijd van 28 dagen (don't ask why, just some idiots from Brussels)
                //TODO is 28 correct of moet dit ook *$factor gedaan worden indien de laatste 30 dagen?
                if($row['type_m'] == VoederMedicaties::TYPE_ANTIBIOTICA || $row['type_m'] == VoederMedicaties::TYPE_NSAID) {
                    $key = md5($row['datum_in_silo'] . $row['silo']);
                    if(array_key_exists($key, $nsaid_ab)) {
                        if(is_array($nsaid_ab[$key])) {
                            //$nsaid_ab[$key] bevat de eerste row id van de medicatie in het voeder van AB of NSAID
                            //eerst overlopen of die wachttijd 28 dagen is.
                            $dtLeeg = \DateTime::createFromFormat('Y-m-d', $nsaid_ab[$key]['leeg']);
                            $days_x = 28;
                            if ($nsaid_ab[$key]['wachttijd'] > 28) $days_x = $nsaid_ab[$key]['wachttijd'];
                            $dtLeeg->add(new \DateInterval('P' . $days_x . 'D'));
                            if ($dtLeeg > $dt) {
                                $data['v_' . $nsaid_ab[$key]['id']] = ['silo' => $nsaid_ab[$key]['silo'], 'medicatie' => $nsaid_ab[$key]['medicatie'], 'leeg' => \ITC\Model::convertToHumanDate($nsaid_ab[$key]['leeg']), 'wachttijd' => intval($dt->diff($dtLeeg, true)->format('%a'))];
                            }

                            $nsaid_ab[$key] = false; //eerste row is gecheckt, de andere vormen combinatie met de eerste row.
                        }

                        //controleer of $days van current row < 28, dan opnieuw berekenen van de wachttijd met 28 dagen
                        if($days < 28) {
                            $days = 28;
                            $dtLeeg = \DateTime::createFromFormat('Y-m-d', $row['datum_leeg_silo']);
                            $dtLeeg->add(new \DateInterval('P' . $days . 'D'));
                            if($dtLeeg > $dt) {
                                $data['v_' . $row['id']] = ['silo' => $row['silo'], 'medicatie' => $row['medicatie'], 'leeg' => \ITC\Model::convertToHumanDate($row['datum_leeg_silo']), 'wachttijd' => intval($dt->diff($dtLeeg, true)->format('%a'))];
                            }
                        }
                    } else {
                        $nsaid_ab[$key] = ['id' => $row['id'], 'silo' => $row['silo'], 'medicatie' => $row['medicatie'], 'leeg' => $row['datum_leeg_silo'], 'wachttijd' => $days];
                    }
                }
            } else {
                //silo is nog niet leeg gemarkeerd, dus er zit nog medicatie in.
                $data['v_' . $row['id']] = ['silo' => $row['silo'], 'medicatie' => $row['medicatie'], 'in' => \ITC\Model::convertToHumanDate($row['datum_in_silo'])];
            }
        }

        return $data;
    }


    public static function getBedrijfDoelgroepen()
    {
        $session = \Phalcon\Di::getDefault()->get('session');
        $result = [];
        if($session->has('auth.bedrijf')) {
            foreach($session->get('auth.bedrijf')['doelgroepen'] as $d) {
                $result[] = $d;
            }
        }
        return $result;
    }


}
