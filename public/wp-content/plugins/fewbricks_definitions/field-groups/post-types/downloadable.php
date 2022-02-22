<?php

use fewbricks\bricks as bricks;
use fewbricks\acf as fewacf;
use fewbricks\acf\fields as acf_fields;


// --- Setting up fields for the downloadable resources custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'downloadable'
        ]
    ]
];

$fg1 = (new fewacf\field_group('Downloadable Resources Details', '202203293819a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content'
    ]
]));

$fg1->add_field(new acf_fields\text('Downloadable Resources URL', 'downloadable_resources_url', '202203301985a', [
    'instructions' => 'URL to be displayed on confirmation page'
]));

$fg1->add_field(new acf_fields\text('Downloadable Resources URL Text', 'downloadable_resources_url_text', '202203301654a'));

$fg1->add_field(new acf_fields\file('Downloadable Resources File', 'downloadable_resources_file', '202203291846a'));

$fg1->add_field(new acf_fields\wysiwyg('Downloadable Resources Form Introduction', 'form_introduction', '202203297695a', [
    'instructions' => 'Optional text to display above the form when requesting access to the Downloadable Resources'
]));

$fg1->add_field(new acf_fields\text('Link text', 'link_text', '202203294610a', [
    'instructions' => 'Optionally add link text to display underneath the Downloadable Resources when listing it.'
]));

$fg1->add_field(new acf_fields\text('Document Type', 'document_type', '202209021654a', [
    'default_value' => 'Downloadable Resources',
    'instructions' => 'The text to be displayed at the top of the form. Note default is Downloadable Resources'
]));

$fg1->add_field(new acf_fields\text('Description', 'description', '202203290739a', [
    'instructions' => 'This hidden description is sent to Salesforce when the form is submitted.'
]));

$fg1->add_field(new acf_fields\text('Campaign code', 'campaign_code', '202203292266a', [
    'instructions' => 'An optional campaign code which will be sent to Salesforce on submission.'
]));

$fg1->register();
