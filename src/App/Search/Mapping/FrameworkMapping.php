<?php

namespace App\Search\Mapping;

class FrameworkMapping extends AbstractMapping
{

    /**
     * The mapping properties
     *
     * @var array
     */
    protected $properties = [
      'id'            => ['type' => 'integer'],
      'salesforce_id' => ['type' => 'keyword'],
      'rm_number'     => [
        'type'      => 'text',
        'fielddata' => 'true',
        'fields'    => [
          'raw' => ['type' => 'keyword']
        ]
      ],
      'rm_number_numerical' => ['type' => 'keyword'],
      'type'          => ['type' => 'keyword'],
      'description'   => ['type' => 'text', 'analyzer' => 'english_analyzer'],
      'benefits'      => ['type' => 'text', 'analyzer' => 'english_analyzer'],
      'how_to_buy'    => ['type' => 'text', 'analyzer' => 'english_analyzer'],
      'summary'       => ['type' => 'text', 'analyzer' => 'english_analyzer'],
      'keywords'      => ['type' => 'text', 'analyzer' => 'english_analyzer'],
      'title'         => [
        'type'     => 'text',
        'analyzer' => 'english_analyzer',
        'fields'   => [
          'raw' => ['type' => 'keyword']
        ]
      ],
      'start_date'       => ['type' => 'date'],
      'end_date'         => ['type' => 'date'],
      'terms'            => ['type' => 'keyword'],
      'pillar'           => ['type' => 'keyword'],
      'category'         => ['type' => 'keyword'],
      'status'           => ['type' => 'keyword'],
      'published_status' => ['type' => 'keyword'],
      'lots'             => [
        'type'       => 'nested',
        'properties' => [
          'title'       => ['type' => 'keyword'],
          'description' => ['type' => 'text'],
        ]
      ]
    ];
}
