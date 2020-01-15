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
            'value' => 'webinar'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Webinar Details', '202001131703a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content'
    ]
]));

$fg1->add_field( new acf_fields\date_time_picker( 'Webinar Date', 'webinar_date', '202001131704a' ) );

$fg1->add_field( new acf_fields\oembed('Webinar Video', 'webinar_video', '202001150013a'));

$fg1->register();

