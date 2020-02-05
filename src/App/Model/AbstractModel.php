<?php

namespace App\Model;

use Nayjest\StrCaseConverter\Str;

abstract class AbstractModel implements ModelInterface
{

    /**
     * Model constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        if (empty($data)) {
            return;
        }

        // Set data to the model
        foreach ($data as $key => $value) {
            $method = 'set' . Str::toCamelCase($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
}
