<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

// --- Setting components on default page template ---

$location = [
    [
        [
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'post'
        ],
        [
            'param'    => 'post_type',
            'operator' => '!=',
            'value'    => 'page'
        ]
    ]
];



$fg7 = ( new fewacf\field_group( 'Lead Text', '201903111530a', $location, 5, [
    'position' => 'acf_after_title',
    'names_of_items_to_hide_on_screen' => [
    ]
] ));

$fg7->add_field( new acf_fields\text( 'Lead Text', 'post_lead_text', '201903111530b', [
    'instructions' => 'Optionally enter some lead text for the page (maximum length, 200 characters)',
    'maxlength' => 200
] ) );

$fg7->register();


$fg9 = ( new fewacf\field_group( 'Author Name', '202201261530a', $location, 6, [
    'position' => 'acf_after_title',
    'names_of_items_to_hide_on_screen' => [
    ]
] ));

$fg9->add_field( new acf_fields\text( 'Author Name', 'author_name_text', '202201261530b', [
    'instructions' => 'Optionally enter author name:',
] ) );

$fg9->register();
