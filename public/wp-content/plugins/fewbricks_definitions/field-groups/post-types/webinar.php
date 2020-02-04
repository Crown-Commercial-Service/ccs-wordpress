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

$fg1->add_field( new acf_fields\date_time_picker( 'Webinar Date', 'webinar_date', '202001131704a', [
    'return_format' => 'd-m-Y g:i a'
] ) );

$fg1->add_field( new acf_fields\oembed('Webinar Video', 'webinar_video', '202001150013a'));

$fg1->add_field( new acf_fields\text( 'Link text', 'link_text', '202001282130a', [
    'instructions' => 'Optionally add link text to display underneath the Webinar when listing it.'
] ) );

$fg1->add_field( new acf_fields\text( 'Campaign code', 'campaign_code', '202002041115b', [
    'instructions' => 'An optional campaign code which will be sent to Salesforce on submission.'
] ) );

$fg1->register();

