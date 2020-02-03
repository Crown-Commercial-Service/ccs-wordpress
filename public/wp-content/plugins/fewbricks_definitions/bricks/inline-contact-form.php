<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class inline_contact_form
 * @package fewbricks\bricks
 */
class inline_contact_form extends project_brick
{

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Inline Contact Form';

    /**
     * Set all the fields for the brick.
     */
    public function set_fields()
    {
        $this->add_field(new acf_fields\true_false('Show contact form?', 'show_contact_form', '202002031250a'));


        $this->add_field(new acf_fields\text('Form heading', 'form_heading', '202002031251a', [
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));
        $this->add_field(new acf_fields\text('Form sub-heading', 'form_sub_heading', '202002031251b', [
            'conditional_logic' => [
                [
                    [
                        'field' => '202002031250a',
                        'operator' => '==',
                        'value' => '1'
                    ]
                ]
            ],
        ]));
    }

    /**
     * This function will be used in the frontend when displaying the brick.
     * It will be called by the parents class function get_html(). See that function
     * for info on what data you have at your disposal.
     * @return array
     */
    protected function get_brick_html()
    {
        // Use apply-filter on WYSIWYG fields
        $data = [
        ];

        return $data;
    }

}
