<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\exceptions;

use Printful\structures\PrintfulClientExceptionPayload;

/**
 * Class PrintfulClientException
 *
 * This exception is used to wrap all exceptions within PrintfulClient
 *
 * @package Printful\exceptions
 */
class PrintfulClientException extends BasePrintfulException
{
    /** @var PrintfulClientExceptionPayload */
    public $payload;
}
