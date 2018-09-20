<?php
namespace ITC\Rapport;


/**
 * Toedieningen worden gegroepeerd individueel of per groep.
 * Datum = datum start - einde
 * Dieren = doelgroep of gegroepeerde zeugnrs
 * Medicatie = naam medicatie
 * Dosis = Som van alle dosissen samen (over alle dagen heen)
 * Wachtijd = in dagen de wachttijd
 * Reden/Opmerking
 *
 * Dit is het officiele rapport dat FAVV nodig heeft.
 *
 * @package ITC\Rapport
 */
class Minimalistic extends Weergave
{


    /**
     * Aantal dagen dat twee toedieningen (zelfde medicatie) maximum tussen mogen hebben om gezien te worden als één behandeling
     *
     * @var int
     */
    private static $max_verschil = 2;

    private $cachePath;

    private $filterStr;

    private $count = 0;


    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
    }


    public function setFilterStr($filterStr)
    {
        $this->filterStr = implode('::', $filterStr);
    }


    public function hasCache()
    {
        $hash = md5($this->filterStr);
        $file = $this->cachePath . '/' . $hash . '.min.cache';
        return file_exists($file);
    }


    public function getCache()
    {
        return unserialize(file_get_contents($this->cachePath . '/' . md5($this->filterStr) . '.min.cache'));
    }


    private function array_keys_regex($regex, &$array)
    {
        $keys = array_keys($array);
        $matches = preg_grep($regex, $keys);
        $return = [];
        foreach($matches as $match) {
            $return[$match] = $array[$match];
        }

        return $return;
    }


    public function getToedieningen(array $toedieningen, $offset, $limit)
    {
        $createCache = true;
        if($this->hasCache()) {
            $cache = $this->getCache();
            if($cache['c'] == count($toedieningen)) {
                $createCache = false;
                $data = $cache['toedieningen'];
            }
        }

        if($createCache) {
            $max_verschil = self::$max_verschil;
            $indexes = [];
            $data = [
                'rows' => []
            ];

            foreach ($toedieningen as $t) {
                $toevoegen = true;
                $datum = \DateTime::createFromFormat('Y-m-d', $t['datum']);

                if (!empty($t['dierId'])) { //individueel
                    //key maken
                    $key = 'd_' . $t['medicatieId'] . '_' . md5($t['reden']);
                } else {
                    //key maken
                    $key = 'g_' . $t['doelgroepId'] . '_' . $t['medicatieId'] . '_' . md5($t['reden']);
                }

                $regex = "~".$key."_\\d~";
                $rows = $this->array_keys_regex($regex, $data['rows']);
                if (count($rows) > 0) {
                    //de reeds bestaande toedieningen overlopen in $data voor zeug/groep en medicatie
                    //als het verschil met de laatste tot datum en deze toediening datum niet groter is dan 2 dagen, tot datum overschrijven
                    foreach ($rows as $idx => $row) {
                        if ($toevoegen) {
                            $di = $datum->diff($row['tot'], true);
                            if (intval($di->format('%a')) <= $max_verschil) {
                                if(! empty($t['dierId']) && ! in_array($t['dierNr'], $data['rows'][$idx]['doelgroep'])) $data['rows'][$idx]['doelgroep'][] = $t['dierNr'];
                                $data['rows'][$idx]['tot'] = $datum;
                                $data['rows'][$idx]['dosis'] += \ITC\Util::decimalToInt($t['dosis']);
                                $toevoegen = false; //vlag afzetten
                            }
                        }
                    }
                }

                if ($toevoegen) { //bestaat nog niet of er is teveel dagen tussen toedieningen, nieuwe row toevoegen
                    if(!array_key_exists($key, $indexes)) {
                        $indexes[$key] = 0;
                    }
                    $idx = $indexes[$key]++;
                    $key = $key . '_' . $idx;
                    $data['rows'][$key] = [
                        'van' => $datum,
                        'tot' => $datum,
                        'medicatie' => $t['medicatie'],
                        'dosis' => \ITC\Util::decimalToInt($t['dosis']),
                        'eenheid' => $t['eenheid'],
                        'reden' => $t['reden'],
                        'wachttijd' => $t['wachttijd'],
                        'doelgroep' => ((! empty($t['dierId'])) ? [$t['dierNr']] : $t['doelgroep'])
                    ];
                }
            }

            //write cache
            $cache = ['c' => count($toedieningen), 'toedieningen' => $data];
            file_put_contents($this->cachePath . '/' . md5($this->filterStr) . '.min.cache', serialize($cache));
        }

        $this->count = count($data['rows']);

        if($offset == 0 && $limit == 0) {
            return $data['rows'];
        } else {
            return array_slice($data['rows'], $offset, $limit);
        }
    }


    public function getPDFContent(array $toedieningen)
    {
        $html = '<table border="1" cellpadding="2" width="100%" style="font-size: 10px;"><thead><tr style="font-weight: bold;"><th width="10%">Datum</th><th width="15%">Medicatie</th><th width="35%">Doelgroep</th><th width="10%">Dosis</th><th width="10%">Wachttijd</th><th width="20%">Reden</th></tr></thead><tbody>';

        //eerst individueel
        foreach($toedieningen as $idx => $row) {
            $eenheid = ($row['eenheid'] == \Medicaties::EENHEID_CC) ? 'ml' : 'gr';
            $html .= '<tr nobr="true">';
            $di = $row['van']->diff($row['tot'], true);
            if(intval($di->format('%a')) == 0) {
                $html .= '<td width="10%">' . $row['van']->format('d/m/Y') . '</td>';
            } else {
                $html .= '<td width="10%">' . $row['van']->format('d/m/Y') . ' - <br>' . $row['tot']->format('d/m/Y') . '</td>';
            }
            $html .= '<td width="15%">' . $row['medicatie'] . '</td>';
            if(substr($idx, 0, 1) == 'd') $html .= '<td style="font-size: 6px;" width="35%">' . implode(',', $row['doelgroep']) . '</td>';
            else $html .= '<td width="35%">' . $row['doelgroep'] . '</td>';
            $html .= '<td width="10%">' . $row['dosis'] . $eenheid . '</td>';
            $html .= '<td width="10%">' . $row['wachttijd'] . 'd</td>';
            $html .= '<td width="20%">' . $row['reden'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<p><em>d = dagen</em></p>';

        return $html;
    }


    public function getView()
    {
        return 'rapport/weergave2';
    }


    public function count(array $toedieningen)
    {
        return $this->count;
    }
}