<?php
namespace ITC\Forms\Render;

class Check implements Render
{


    public function render($form, $el, $attr = null)
    {
        //https://github.com/phalcon/cphalcon/issues/2890
        echo '<input type="hidden" name="' . $el->getName() . '" value="0">';
        echo '<div class="checkbox checkbox-styled"><label>
				' . $el->render($attr) . ' <span>' . $el->getLabel() . '</span></label>
              </div>';
    }


}