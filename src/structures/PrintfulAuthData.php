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
 * Class PrintfulAuthData
 *
 * Auth data used to communicate with Printful API
 * @package Printful\structures
 */
class PrintfulAuthData
{
    /** @var string */
    public $storeAddress;

    /** @var string */
    public $serviceKey;

    /**
     * Identity on PF side
     * @var string
     */
    public $identity;

    /**
     * Api key on PF side
     * @var string
     */
    public $apiKey;

    /**
     * Current plugin verion
     * @var string
     */
    public $pluginVersion;
}
