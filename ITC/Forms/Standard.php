<?php
namespace ITC\Forms;

class Standard extends Form
{

    public function createRender($el)
    {
        $cls = get_class($el);

        if(strpos($cls, 'Radio')) {
            return new \ITC\Forms\Render\Radio();
        } elseif(strpos($cls, 'Check')) {
            return new \ITC\Forms\Render\Check();
        } else {
            return new \ITC\Forms\Render\Input();
        }
    }

}