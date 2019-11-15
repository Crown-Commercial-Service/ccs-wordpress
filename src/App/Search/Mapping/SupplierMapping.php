<?php

namespace App\Search\Mapping;

class SupplierMapping extends AbstractMapping
{

    /**
     * The mapping properties
     *
     * @var array
     */
    protected $properties = [
      'id'              => ['type' => 'integer'],
      'salesforce_id'   => ['type' => 'keyword'],
      'name'            => [
        'type'   => 'text',
        'fields' => [
          'raw' => ['type' => 'keyword']
        ]
      ],
      'duns_number'     => ['type' => 'keyword'],
      'trading_name'    => ['type' => 'text'],
      'city'            => ['type' => 'text'],
      'postcode'        => ['type' => 'text'],
      'live_frameworks' => [
        'type'       => 'nested',
        'properties' => [
          'end_date'  => ['type' => 'date'],
          'title'     => ['type' => 'keyword'],
          'rm_number' => ['type' => 'keyword'],
          'status'    => ['type' => 'keyword']
        ]
      ]
    ];

}