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
            'value'    => 'page'
        ]
    ]
];



$fg5 = ( new fewacf\field_group( 'Lead Text', '201903111200a', $location, 5, [
    'position' => 'acf_after_title',
    'names_of_items_to_hide_on_screen' => [
    ]
] ));

$fg5->add_field( new acf_fields\text( 'Lead Text', 'page_lead_text', '201903111104b', [
    'instructions' => 'Optionally enter some lead text for the page (maximum length, 200 characters)',
    'maxlength' => 200
] ) );

$fg5->register();



$fg6 = ( new fewacf\field_group('Accordion', '201903111208a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
    ]
]) );

$fg6->add_field(( new acf_fields\repeater('Accordion', 'page_accordion', '201903111209a') )
    ->add_sub_field( new acf_fields\text( 'Title', 'page_accordion_item_title', '201903111210a' ) )
    ->add_sub_field( new acf_fields\wysiwyg( 'Content', 'page_accordion_item_content', '201903111210b' ) )
);

$fg6->register();



$fg8 = ( new fewacf\field_group( 'Full width content', '201903201810a', $location, 5, [
    'names_of_items_to_hide_on_screen' => [
    ]
] ));

$fg8->add_field( new acf_fields\wysiwyg( 'Full width content', 'full_width_content', '201903201810b', [
    'instructions' => 'Optionally enter some content to display at the full-width of the page template',
] ) );

$fg8->register();
