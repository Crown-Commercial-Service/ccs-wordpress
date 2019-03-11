<?php

namespace DeliciousBrains\WP_Offload_Media\Gcp\Rize\UriTemplate\Operator;

use DeliciousBrains\WP_Offload_Media\Gcp\Rize\UriTemplate\Node;
use DeliciousBrains\WP_Offload_Media\Gcp\Rize\UriTemplate\Parser;
/**
 * | 1   |    {/list}    /red,green,blue                  | {$value}*(?:,{$value}+)*
 * | 2   |    {/list*}   /red/green/blue                  | {$value}+(?:{$sep}{$value}+)*
 * | 3   |    {/keys}    /semi,%3B,dot,.,comma,%2C        | /(\w+,?)+
 * | 4   |    {/keys*}   /semi=%3B/dot=./comma=%2C        | /(?:\w+=\w+/?)*
 */
class UnNamed extends \DeliciousBrains\WP_Offload_Media\Gcp\Rize\UriTemplate\Operator\Abstraction
{
    public function toRegex(\DeliciousBrains\WP_Offload_Media\Gcp\Rize\UriTemplate\Parser $parser, \DeliciousBrains\WP_Offload_Media\Gcp\Rize\UriTemplate\Node\Variable $var)
    {
        $regex = null;
        $value = $this->getRegex();
        $options = $var->options;
        if ($options['modifier']) {
            switch ($options['modifier']) {
                case '*':
                    // 2 | 4
                    $regex = "{$value}+(?:{$this->sep}{$value}+)*";
                    break;
                case ':':
                    $regex = $value . '{0,' . $options['value'] . '}';
                    break;
                case '%':
                    throw new \Exception("% (array) modifier only works with Named type operators e.g. ;,?,&");
                default:
                    throw new \Exception("Unknown modifier `{$options['modifier']}`");
            }
        } else {
            // 1, 3
            $regex = "{$value}*(?:,{$value}+)*";
        }
        return $regex;
    }
}
