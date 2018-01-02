<?php
namespace ITC\Forms\Render;

class Horizontal implements Render
{


    public function render($form, $el, $attr = null)
    {
        if($form->hasMessagesFor($el->getName())) {
            echo '<div class="form-group has-error">';
        } else {
            echo '<div class="form-group">';
        }

        if(! empty($el->getLabel())) {
            echo '<label for="' . $el->getName() . '" class="control-label col-sm-2">' . $el->getLabel() . '</label>';
        }

        echo '<div class="col-sm-6">';
        echo $el->render($attr);
        echo '</div>';

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