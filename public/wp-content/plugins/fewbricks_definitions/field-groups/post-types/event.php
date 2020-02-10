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
            'value' => 'event'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Event Details', '202002061432a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
    ]
]));


$fg1->add_field( new acf_fields\image( 'Event image', 'image', '202002061421a', [
    'instructions' => 'Also used for the thumbnail',
] ) );

$fg1->add_field( new acf_fields\wysiwyg( 'Description', 'description', '202002061420a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\text( 'CTA Label', 'cta_label', '202002061456a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\text( 'CTA Destination', 'cta_destination', '202002061456b', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\date_time_picker( 'Event start date (and time)', 'start_datetime', '202002061422a', [
    'instructions' => 'The start date and time for the event',
    'display_format' => 'd-m-Y g:i a',
    'return_format' => 'd-m-Y g:i a',
] ) );

$fg1->add_field( new acf_fields\date_time_picker( 'Event end date (and time)', 'end_datetime', '202002061423a', [
    'instructions' => 'The end date and time for the event',
    'display_format' => 'd-m-Y g:i a',
    'return_format' => 'd-m-Y g:i a',
] ) );

$fg1->add_field( new acf_fields\textarea( 'Event location', 'location', '202002061424a', [
    'instructions' => '',
] ) );

$fg1->add_field( new acf_fields\text( 'Secondary CTA Label', 'secondary_cta_label', '202002061517a', [
    'instructions' => 'A secondary CTA to display beneath the event location (appears in the sidebar on large screens)',
] ) );

$fg1->add_field( new acf_fields\text( 'CTA Destination', 'secondary_cta_destination', '202002061517b', [
    'instructions' => 'A secondary CTA to display beneath the event location (appears in the sidebar on large screens)',
] ) );

$fg1->register();

/*
description content (formattable, no limit, able to add images to the content)

event image (to be used for thumbnail too)

CTA with label and defined destination (if required)

event date / date range (required)

event start and finish time (required)

event location (free text, if required)
=
right hand box CTA with label and defined destination (if required)

Category tags (enable multiple tags)

Sector tags (enable multiple tags)

Audience tag (customer / supplier event)

Then they should be stored with the event in Wordpress
*/
