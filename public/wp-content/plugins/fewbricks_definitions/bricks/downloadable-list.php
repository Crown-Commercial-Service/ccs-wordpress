<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class downloadable_list
 * @package fewbricks\bricks
 */
class downloadable_list extends project_brick {

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Downloadable Resources List';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields() {

        $this->add_field( (new acf_fields\repeater('Downloadable Resources', 'downloadable_list', '202201091647a', ['button_label' => 'Add Downloadable Resources']))
            ->add_sub_field(new acf_fields\file('Downloadable Resources', 'Downloadable Resources', '202201091649a', [
                'required' => 1
            ]))
        );

    }

}