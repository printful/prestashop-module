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
 * Class PrintfulPluginVersionCheckData
 * @package Printful\structures
 */
class PrintfulPluginVersionCheckData
{
    const DATA_LIFESPAN = 28800; // 8h

    /**
     * Version validation result
     * @var bool
     */
    public $isValidVersion = false;

    /**
     * Version checked successfully
     * @var bool
     */
    public $checkSuccessful = false;

    /**
     * Version number in release data
     * @var string
     */
    public $actualVersion;

    /**
     * Timestamp when version was checked
     * @var int
     */
    public $checkedTime = 0;

    /**
     * Is data expired
     * @return bool
     */
    public function isExpired()
    {
        return $this->checkedTime + self::DATA_LIFESPAN < time();
    }

    /**
     * Convert array to instance
     * @param array $arrayData
     * @return PrintfulPluginVersionCheckData
     */
    public static function fromArray($arrayData)
    {
        $data = new PrintfulPluginVersionCheckData();

        $data->isValidVersion = isset($arrayData['isValidVersion']) ? (bool)$arrayData['isValidVersion'] : false;
        $data->actualVersion = isset($arrayData['actualVersion']) ? $arrayData['actualVersion'] : null;
        $data->checkedTime = isset($arrayData['checkedTime']) ? $arrayData['checkedTime'] : 0;
        $data->checkSuccessful = isset($arrayData['checkSuccessful']) ? $arrayData['checkSuccessful'] : 0;

        return $data;
    }

    /**
     * Convert instance to array for saving
     * @return array
     */
    public function toArray()
    {
        return array(
            'isValidVersion' => $this->isValidVersion,
            'actualVersion' => $this->actualVersion,
            'checkedTime' => $this->checkedTime,
            'checkSuccessful' => $this->checkSuccessful,
        );
    }
}
