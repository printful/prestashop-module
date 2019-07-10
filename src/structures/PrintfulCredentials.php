<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\structures;

/**
 * Class PrintfulCredentials
 * @package Printful\structures
 */
class PrintfulCredentials
{
    /** @var string */
    public $identity;

    /** @var string */
    public $apiAccessKey;

    /**
     * Create instance from array data
     *
     * @param array $array
     * @return PrintfulCredentials
     */
    public static function buildFromArray($array)
    {
        $data = new PrintfulCredentials();

        foreach ($array as $key => $value) {
            if (property_exists($data, $key)) {
                $data->$key = $value;
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->identity && $this->apiAccessKey;
    }
}
