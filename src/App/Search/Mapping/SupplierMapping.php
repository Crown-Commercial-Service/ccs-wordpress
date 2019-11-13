<?php

namespace App\Search\Mapping;

use Elastica\Mapping;

class SupplierMapping extends Mapping {

    /**
     * @var array
     */
    protected $properties = [
      'id'            => ['type' => 'integer'],
      'salesforce_id' => ['type' => 'keyword'],
      'name'          => [
        'type'   => 'text',
        'fields' => [
          'raw' => ['type' => 'keyword']
        ]
      ],
      'duns_number'   => ['type' => 'keyword'],
      'trading_name'  => ['type' => 'text'],
      'city'       => ['type' => 'text'],
      'postcode'   => ['type' => 'text'],
      'frameworks' => ['type' => 'nested']
    ];

    /**
     * SupplierMapping constructor.
     */
    public function __construct()
    {
        $this->setProperties($this->properties);
    }

}