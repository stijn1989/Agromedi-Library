<?php
namespace ITC;


/**
 * Breadcrumb helper klasse. Via de add methode kan je crumbs toevoegen.
 * De addActive methode voegt een element toe zonder link.
 * De elements() methode geeft de elementen terug.
 *
 * Zie ook plugins/BreadcrumbPlugin
 *
 * @package ITC
 */
class Breadcrumb
{


    /**
     * De broodkruimels
     *
     * @var array
     */
    private $elements = [];


    /**
     * Voegt een breadcrumb toe aan de lijst.
     *
     * @param $link     De link naar de breadcrumb
     * @param $caption  De tekst van de breadcrumb
     * @return Breadcrumb
     */
    public function add($link, $caption)
    {
        $add = true;
        foreach($this->elements as $el) {
            if($el['caption'] == $caption && $el['link'] == $link) $add = false;
        }
        if($add) {
            $this->elements[] = ['link' => $link, 'caption' => $caption];
        }
        return $this;
    }


    /**
     * Voegt een actieve breadcrumb toe aan de lijst.
     *
     * @param $caption  De tekst van de breadcrumb
     * @return Breadcrumb
     */
    public function addActive($caption)
    {
        $add = true;
        foreach($this->elements as $el) {
            if($el['caption'] == $caption) $add = false;
        }
        if($add) {
            $this->elements[] = ['caption' => $caption];
        }
        return $this;
    }


    /**
     * Geeft de elementen terug.
     *
     * @return array
     */
    public function elements()
    {
        return $this->elements;
    }


}