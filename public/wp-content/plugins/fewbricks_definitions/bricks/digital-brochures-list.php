<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class digital_brochures_list
 * @package fewbricks\bricks
 */
class digital_brochures_list extends project_brick {

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Digital Brochures List';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields() {

        $this->add_field( (new acf_fields\relationship('Digital Brochures', 'digital_brochures', '202103299533a', [
            'post_type' => 'digital_brochure'
        ])) );

    }

}
