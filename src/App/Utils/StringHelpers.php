<?php

namespace App\Utils;

/**
 * Class StringHelpers
 * @package App\Utils
 */
class StringHelpers
{

    /**
     * Convert a string into a URL safe slug
     *
     * E.g. convert: My name is Earl
     * into: my-name-is-earl
     *
     * @param $string
     * @return string
     */
    public static function slugify($string): string
    {
        // Filter
        $string = mb_strtolower($string, 'UTF-8');
        $string = strip_tags($string);
        $string = preg_replace('/\s/', '-', $string);
        $string = preg_replace('/[-]+/', '-', $string);

        // Sanitise
        $string = filter_var($string, FILTER_SANITIZE_URL);

        // Replace anything that isn't a unicode letter, number or dash -
        $string = preg_replace('/[^\p{L}\p{N}-]+/', '', $string);

        return $string;
    }
}
