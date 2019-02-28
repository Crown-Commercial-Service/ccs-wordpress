<?php

namespace App\Model;

use Nayjest\StrCaseConverter\Str;

abstract class AbstractModel implements ModelInterface {

    /**
     * Model constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $key => $value)
        {
            $this->{'set' . Str::toCamelCase($key)}($value);
        }
    }

}