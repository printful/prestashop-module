<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\structures;

use Exception;

/**
 * Class PrintfulClientExceptionPayload
 * @package Printful\structures
 */
class PrintfulClientExceptionPayload
{
    /** @var Exception */
    public $originalException;
}
