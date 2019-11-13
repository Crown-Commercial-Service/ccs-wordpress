<?php

namespace App\Search\Mapping;

use Elastica\Mapping;

class SupplierMapping extends Mapping {

    /**
     * @var array
     */
    protected $properties = [
      'id'            => ['type' => 'integer'],
      'salesforce_id' => ['type' => 'text'],
      'name'          => ['type' => 'text'],
      'duns_number'   => ['type' => 'text'],
      'trading_name'  => ['type' => 'text'],
    ];

    /**
     * SupplierMapping constructor.
     */
    public function __construct()
    {
        $this->setProperties($this->properties);
    }

}