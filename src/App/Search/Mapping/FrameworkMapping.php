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
      'title'          => [
        'type'   => 'text',
        'fields' => [
          'raw' => ['type' => 'keyword']
        ]
      ]
    ];

}