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
 * Class StoreStats
 * @package Printful\structures
 */
class StoreStats
{
    /** @var array */
    public $orders_today;

    /** @var array */
    public $orders_last_7_days;

    /** @var array */
    public $orders_last_28_days;

    /** @var float */
    public $profit_last_28_days;

    /** @var float */
    public $profit_trend_last_28_days;

    /** @var string */
    public $currency;

    /**
     * @param array $data
     * @return StoreStats
     */
    public static function fromArray($data)
    {
        $stats = new StoreStats();

        if (!is_array($data)) {
            return $stats;
        }

        foreach ($data as $key => $value) {
            if (property_exists($stats, $key)) {
                $stats->$key = $value;
            }
        }

        return $stats;
    }
}
