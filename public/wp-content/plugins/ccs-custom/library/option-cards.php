<?php

/**
 * Add meta box to view page in front-end site
 *
 */
add_action("add_meta_boxes", function(){
    add_meta_box("ccs-option-cards", "Option Cards", function() {

        echo <<<EOD

        <input type="checkbox" id="check-option-cards" name="check-option-cards" value="Option cards" checked>
        <label for="check-option-cards">Include option cards?</label><br>
        
        EOD;
    }); 
});

