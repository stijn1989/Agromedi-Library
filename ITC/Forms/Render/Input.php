<?php
namespace ITC\Forms\Render;

class Input implements Render
{


    public function render($form, $el, $attr = null)
    {
        if($form->hasMessagesFor($el->getName())) {
            echo '<div class="form-group has-error">';
        } else {
            echo '<div class="form-group">';
        }

        if(! empty($el->getLabel())) {
            echo '<label for="' . $el->getName() . '" class="control-label">' . $el->getLabel() . '</label>';
        }

        echo $el->render($attr);

        //error messages?
        if($form->hasMessagesFor($el->getName())) {
            echo '<small class="help-block">';
            foreach($form->getMessagesFor($el->getName()) as $message) {
                echo $message;
            }
            echo '</small>';
        }

        echo '</div>';
    }


}