<?php
namespace ITC\Forms;

abstract class Form extends \Phalcon\Forms\Form
{


    public function render($name, $attributes = null)
    {
        $el = $this->get($name);
        if($el == null) return;

        echo $this->createRender($el)->render($this, $el, $attributes);
    }


    public abstract function createRender($el);


}