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

$fg1->add_field( new acf_fields\wysiwyg('Updates', 'framework_updates', '201902251546a'));

$fg1->add_field( new acf_fields\wysiwyg( 'Description', 'framework_description', '201902041416a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Benefits', 'framework_benefits', '201902041814a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'How to buy', 'framework_how_to_buy', '201902041411a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Information and documents for suppliers', 'framework_info_docs_for_suppliers', '201903211125a', [
    'instructions' => 'Only displayed for DPS Frameworks'
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



$fg3 = ( new fewacf\field_group( 'Keywords', '201902201440a', $location, 30 ));

$fg3->add_field( new acf_fields\textarea( 'Keywords', 'framework_keywords', '201902201448a', [
    'instructions' => 'Optionally enter some keywords (separated by comma\'s) which will be used to help ensure accurate search output (maximum combined length, 3000 characters)',
    'maxlength' => 3000
] ) );

$fg3->register();



$fg4 = ( new fewacf\field_group( 'Upcoming Deal Details', '201903081626a', $location, 30 ));

$fg4->add_field( new acf_fields\wysiwyg( 'Upcoming Deal Details', 'framework_upcoming_deal_details', '201903081627a', [
] ) );

$fg4->register();

$fg5 = ( new fewacf\field_group( 'Guided Match Summary', '201903081628a', $location, 30 ));

$fg5->add_field( new acf_fields\textarea( 'Guided Match Summary', 'framework_gm_summary', '201903081629a', [
    'instructions' => 'This field will be used for guided match summary for this framework and NOT visible at all on the website.',
    'maxlength' => 3000
] ) );

$fg5->register();