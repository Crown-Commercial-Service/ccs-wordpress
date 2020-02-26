<?php

namespace App\Utils;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlLoader
 * @package App\Utils
 */
class YamlLoader
{
    /**
     * Load the YAML config file and convert it to a mappings array
     * @param string $fileName
     * @return mixed
     */
    public static function loadMappings(string $fileName): ?array
    {
        return Yaml::parseFile(__DIR__ . '/../Config/Mappings/' . $fileName . '.yaml');
    }
}
