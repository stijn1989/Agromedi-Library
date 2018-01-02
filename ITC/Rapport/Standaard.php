<?php
namespace ITC\Rapport;


/**
 * De standaard weergave van een rapport is iedere toediening lijn in de toediening tabel tonen.
 *
 * @package ITC\Rapport
 */
class Standaard extends Weergave
{

    public function getToedieningen(array $toedieningen, $offset, $limit)
    {
        if($offset == 0 && $limit == 0) {
            return $toedieningen;
        } else {
            return array_slice($toedieningen, $offset, $limit);
        }
    }

    public function getPDFContent(array $toedieningen)
    {
        $html = '<table border="1" cellpadding="3"><thead><tr style="font-weight: bold;"><th>Datum</th><th>Zeug</th><th>Medicatie (lot)</th><th>Referentie</th><th>Wachttijd</th><th>Reden</th></tr></thead><tbody>';
        foreach($toedieningen as $t) {
            $html .= '<tr>';
            $html .= '<td>' . \DateTime::createFromFormat('Y-m-d',$t['datum'])->format('d/m/Y') . '</td>';
            $html .= '<td>' . (empty($t['zeug']) ? 'N/A' : $t['zeug']) . '</td>';
            $html .= '<td>' . $t['medicatie'] . ' (' . $t['lotnr'] . ')</td>';
            $html .= '<td>' . $t['referentie'] . '</td>';
            $html .= '<td>' . $t['wachttijd'] . 'd</td>';
            $html .= '<td>' . $t['reden'] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    public function getView()
    {
        return 'rapport/weergave0';
    }

    public function count(array $toedieningen)
    {
        return count($toedieningen);
    }
}