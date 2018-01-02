<?php
use Phalcon\Mvc\Model;

/**
 * Berichten model
 *
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class Berichten extends Model
{


    public $id;

    public $bedrijf;

    public $type;

    public $bericht;

    public $tag;

    public $datum;

    public $gelezen;


    public function initialize()
    {
        //relaties
        $this->belongsTo('bedrijf', 'Bedrijven', 'id', array('foreignKey' => true));
    }


    public function getSchema()
    {
        return 'medipig';
    }


    public function findUnread()
    {
        $bedrijf = $this->getDI()->get('session')->get('auth.bedrijf')['bedrijf'];
        return $this->find([
            'conditions' => "bedrijf = ?1 AND (gelezen IS NULL OR gelezen = '0000-00-00')",
            'bind' => [1 => $bedrijf],
            'order' => 'datum ASC'
        ]);
    }


}
