<?php
namespace ITC\Forms\Render;

class Radio implements Render
{


    public function render($form, $el, $attr = null)
    {
        echo '<label class="radio-styled radio-inline">
				' . $el->render($attr) . ' <span>' . $el->getLabel() . '</span>
              </label>';
    }


}