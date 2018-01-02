<?php
namespace ITC\Rapport;


abstract class Weergave
{


    abstract public function getToedieningen(array $toedieningen, $offset, $limit);

    abstract public function getPDFContent(array $toedieningen);

    abstract public function getView();

    abstract public function count(array $toedieningen);


}