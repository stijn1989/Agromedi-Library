<?php
namespace ITC\PDF;
require_once  dirname(__FILE__) . '/../../TCPDF/tcpdf.php';

class Rapport extends \TCPDF
{


    /**
     * @var \Bedrijven
     */
    public $bedrijf;


    public function Header()
    {
        $dt = new \DateTime();
        $this->Cell(40, 20, $this->bedrijf->naam . ' (' . $this->bedrijf->beslagnr . ')', 0, false, 'L', 0, '', 0, false, 'C', 'B');
        //$this->Cell(0, 20, $dt->format('d/m/Y') , 0, false, 'R', 0, '', 0, false, 'C', 'B');
    }


    public function Footer()
    {
        $this->setY(-15);
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }


}