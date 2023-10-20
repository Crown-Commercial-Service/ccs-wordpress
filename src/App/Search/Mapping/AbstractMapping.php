<?php

namespace App\Search\Mapping;

use Elastica\Mapping;

abstract class AbstractMapping extends Mapping
{
    /**
     * The mapping properties
     *
     * @var array
     */
    protected $properties = [];

    /**
     * The constructor sets the defined properties when booted.
     */
    public function __construct()
    {
        $this->setProperties($this->properties);
    }
}
