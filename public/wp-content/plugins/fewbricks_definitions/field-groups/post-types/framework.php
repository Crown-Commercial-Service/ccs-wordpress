<?php
use fewbricks\bricks AS bricks;
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;


// --- Setting up fields for the framework custom post type ---

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'framework'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Framework Details', '201902041237a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
        'the_content',
        'excerpt'
    ]
]));


$fg1->add_field( new acf_fields\text( 'Framework ID', 'framework_id', '201902041405a', [
    'instructions' => 'Framework ID from Salesforce',
    'maxlength' => 50,
    'required' => 1,
    'readonly' => 1
] ) );


$fg1->add_field( new acf_fields\wysiwyg( 'Summary', 'framework_summary', '201902181515a', [
    'instructions' => 'A few short sentences - a maximum of 180 characters.'
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Description', 'framework_description', '201902041416a', [
    'instructions' => '',
] ) );

$fg1->add_field( (new acf_fields\repeater('Updates', 'framework_updates', '201902041434a', [
        'button_label' => 'Add Update'
    ]))
        ->add_sub_field( new acf_fields\text( 'Date and title', 'framework_update_title', '201902041813a' ) )
        ->add_sub_field( new acf_fields\wysiwyg( 'Update Description', 'framework_update_description', '201902041813b' ) )
    );

$fg1->add_field( new acf_fields\wysiwyg( 'Benefits', 'framework_benefits', '201902041814a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'How to buy', 'framework_how_to_buy', '201902041411a', [
    'instructions' => '',
] ) );

$fg1->register();



$fg2 = ( new fewacf\field_group( 'Documents', '201902051045a', $location, 20 ));

$fg2->add_field( new acf_fields\wysiwyg( 'Documents - Updates', 'framework_documents_updates', '201902051044a', [
    'instructions' => '',
] ) );

$fg2->add_field( (new acf_fields\repeater('Documents - Downloads', 'framework_documents', '201902051040a', [
    'button_label' => 'Add Document'
]))
    ->add_sub_field( new acf_fields\file( 'Document', 'framework_documents_document', '201902051043a' ) )
);

$fg2->register();
