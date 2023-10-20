<?php

namespace App\Traits;

use App\Utils\YamlLoader;

trait SalesforceMappingTrait
{
    /**
     * An array of mappings for model properties to salesforce properties
     * Key is model
     * Value is salesforce
     *
     * @var array
     */
    protected $mappings = [];

    /**
     * Uses the field mappings to set the correct Salesforce property to the right property on the model through it's setter.
     *
     * @param $data
     * @throws \ReflectionException
     */
    public function setMappedFields($data)
    {
        $className = (new \ReflectionClass($this))->getShortName();

        if (empty($this->mappings)) {
            $this->mappings = YamlLoader::loadMappings($className);
        }

        foreach ($this->mappings['properties'] as $classProperty => $salesforceProperty) {
            $method = "set" . ucfirst($classProperty);
            if (isset($data->$salesforceProperty)) {
                $this->$method($data->$salesforceProperty);
            }
        }
    }
}
