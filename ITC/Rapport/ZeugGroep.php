<?php
namespace ITC\Rapport;


/**
 * De toedieningen worden per zeug/doelgroep gegroepeerd en getoond.
 * Zo krijg je medicaties die aan een zeug over meerdere dagen gegeven zijn, op één lijn.
 * Zelfde voor regumate, etc.
 *
 * Dit is het officiele rapport dat FAVV nodig heeft.
 *
 * @package ITC\Rapport
 */
class ZeugGroep extends Weergave
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
        $file = $this->cachePath . '/' . $hash . '.cache';
        return file_exists($file);
    }


    public function getCache()
    {
        return unserialize(file_get_contents($this->cachePath . '/' . md5($this->filterStr) . '.cache'));
    }


    private function array_keys_regex($regex, $array)
    {
        $keys = array_keys($array);
        $matches = preg_grep($regex, $keys);
        $return = [];
        foreach($matches as $match) {
            $return[$match] = $array[$match];
        }

        return $return;
    }


    public static function getInfoFromKey($key, array $info)
    {
        $arr = explode('_', $key);
        return ['isDier' => ($arr[0] == 'd'), 'dierId' => $arr[1], 'medId' => $arr[2], 'info' => $info[$arr[0]][$arr[1]]];
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
                'info' => ['d' => [], 'g' => []],
                'rows' => []
            ];


            foreach ($toedieningen as $t) {
                $toevoegen = true;
                $datum = \DateTime::createFromFormat('Y-m-d', $t['datum']);

                if (!empty($t['dierId'])) { //individueel
                    //staat de zeug al in data?
                    if (!array_key_exists($t['dierId'], $data['info']['d'])) {
                        $data['info']['d'][$t['dierId']] = ['dier' => $t['dierNr'], 'opgeruimd' => $t['opgeruimd']];
                    }

                    //key maken
                    $key = 'd_' . $t['dierId'] . '_' . $t['medicatieId'];
                } else {
                    //staat de doelgroep al in data?
                    if (!array_key_exists($t['doelgroepId'], $data['info']['g'])) {
                        $data['info']['g'][$t['doelgroepId']] = ['doelgroep' => $t['doelgroep']];
                    }

                    //key maken
                    $key = 'g_' . $t['doelgroepId'] . '_' . $t['medicatieId'] . '_' . md5($t['reden']) . '_' . md5($t['afdeling']);
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
                                $data['rows'][$idx]['tot'] = $datum;
                                $data['rows'][$idx]['lotnrs'] =
                                    array_unique($data['rows'][$idx]['lotnrs'] + array_map('trim', explode(',', $t['lotnr'])));
                                $data['rows'][$idx]['referenties'] =
                                    array_unique($data['rows'][$idx]['referenties'] + array_map('trim', explode(',', $t['referentie'])));
                                $data['rows'][$idx]['dosis'][] = \ITC\Util::decimalToInt($t['dosis']);
                                if (!empty($t['reden'])) $data['rows'][$idx]['reden'] = $t['reden'];
                                if (!empty($t['behandelingNaam'])) $data['rows'][$idx]['behandeling'] = $t['behandelingNaam'];
                                if (!empty($t['begeleidingscontract'])) $data['rows'][$idx]['behandeling'] .= ' (begeleidingscontract: ' . $t['begeleidingscontract'] . ')';
                                if (!empty($t['afdeling'])) $data['rows'][$idx]['afdeling'] = $t['afdeling'];
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
                        'lotnrs' => array_unique(array_map('trim', explode(',', $t['lotnr']))),
                        'referenties' => array_unique(array_map('trim', explode(',', $t['referentie']))),
                        'dosis' => [\ITC\Util::decimalToInt($t['dosis'])],
                        'eenheid' => $t['eenheid'],
                        'reden' => $t['reden'],
                        'behandeling' => $t['behandelingNaam'] . (!empty($t['begeleidingscontract']) ? ' (begeleidingscontract: ' . $t['begeleidingscontract'] . ')' : ''),
                        'afdeling' => $t['afdeling'],
                        'wachttijd' => $t['wachttijd']
                    ];
                }
            }

            //write cache
            $cache = ['c' => count($toedieningen), 'toedieningen' => $data];
            file_put_contents($this->cachePath . '/' . md5($this->filterStr) . '.cache', serialize($cache));
        }

        $this->count = count($data['rows']);

        if($offset == 0 && $limit == 0) {
            return ['info' => $data['info'], 'rows' => $data['rows']];
        } else {
            return ['info' => $data['info'], 'rows' => array_slice($data['rows'], $offset, $limit)];
        }
    }


    public function getPDFContent(array $toedieningen)
    {
        $html = '<table border="1" cellpadding="3"><thead><tr style="font-weight: bold;"><th>Datum van</th><th>Datum ltste</th><th>Zeug/Groep</th><th>Medicatie (lot)</th><th>Referentie</th><th>Wachttijd</th><th>Dosis</th><th>Reden</th></tr></thead><tbody>';

        //eerst individueel
        foreach($toedieningen['rows'] as $idx => $row) {
            $info = self::getInfoFromKey($idx, $toedieningen['info']);
            $eenheid = ($row['eenheid'] == \Medicaties::EENHEID_CC) ? 'ml' : 'gram';
            $html .= '<tr>';
            $html .= '<td>' . $row['van']->format('d/m/Y') . '</td>';
            $html .= '<td>' . $row['tot']->format('d/m/Y') . '</td>';
            if($info['isDier']) $html .= '<td>' . $info['info']['dier'] . '</td>';
            else $html .= '<td>' . $info['info']['doelgroep'] . '</td>';
            $html .= '<td>' . $row['medicatie'] . ' (' . implode(',', $row['lotnrs']) . ')</td>';
            $html .= '<td>' . implode(',', $row['referenties']) . '</td>';
            $html .= '<td>' . $row['wachttijd'] . 'd</td>';
            $html .= '<td>' . implode($eenheid. ', ', $row['dosis']) . $eenheid . '</td>';
            $html .= '<td>' . $row['reden'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '<p><em>d = dagen</em></p>';

        return $html;
    }


    public function getView()
    {
        return 'rapport/weergave1';
    }


    public function count(array $toedieningen)
    {
        return $this->count;
    }
}