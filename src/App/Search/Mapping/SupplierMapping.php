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
      'id'                        => ['type' => 'integer'],
      'salesforce_id'             => ['type' => 'keyword'],
      'name'                      => [
        'type'     => 'text',
        'analyzer' => 'english_analyzer',
        'fields'   => [
          'raw' => ['type' => 'keyword']
        ]
      ],
      'encoded_name'              => ['type' => 'text'],
      'duns_number'               => ['type' => 'keyword'],
      'trading_name'              => ['type' => 'text'],
      'alternative_trading_names' => ['type' => 'text'],
      'city'                      => ['type' => 'text'],
      'postcode'                  => ['type' => 'text'],
      'live_frameworks'           => [
        'type'       => 'nested',
        'properties' => [
          'end_date'            => ['type' => 'date'],
          'title'               => ['type' => 'keyword'],
          'rm_number'           => ['type' => 'text', 'fielddata' => 'true'],
          'rm_number_numerical' => ['type' => 'keyword'],
          'status'              => ['type' => 'keyword'],
          'lot_ids'             => ['type' => 'keyword'],
        ]
      ]
    ];

}
