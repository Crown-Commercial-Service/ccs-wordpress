<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


// --- Setting up fields for the supplier custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'whitepaper'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Whitepaper Details', '202001131107a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content'
    ]
]));

$fg1->add_field( new acf_fields\file( 'Whitepaper', 'whitepaper_file', '202001131109a' ) );

$fg1->register();

