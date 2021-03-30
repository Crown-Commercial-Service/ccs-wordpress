<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


// --- Setting up fields for the digital brochure custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'digital_brochure'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Digital Brochure Details', '202103293819a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content'
    ]
]));

$fg1->add_field( new acf_fields\text( 'Digital Brochure URL', 'digital_brochure_url', '202103301985a', [
    'instructions' => 'URL to be displayed on confirmation page'
] ) );

$fg1->add_field( new acf_fields\text( 'Digital Brochure URL Text', 'digital_brochure_url_text', '202103301654a' ) );

$fg1->add_field( new acf_fields\file( 'Digital Brochure File', 'digital_brochure_file', '202103291846a' ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Digital Brochure Form Introduction', 'form_introduction', '202103297695a', [
    'instructions' => 'Optional text to display above the form when requesting access to the Digital Brochure'
] ) );

$fg1->add_field( new acf_fields\text( 'Link text', 'link_text', '202103294610a', [
    'instructions' => 'Optionally add link text to display underneath the Digital Brochure when listing it.'
] ) );

$fg1->add_field( new acf_fields\text( 'Description', 'description', '202103290739a', [
    'instructions' => 'This hidden description is sent to Salesforce when the form is submitted.'
] ) );

$fg1->add_field( new acf_fields\text( 'Campaign code', 'campaign_code', '202103292266a', [
    'instructions' => 'An optional campaign code which will be sent to Salesforce on submission.'
] ) );

$fg1->register();

