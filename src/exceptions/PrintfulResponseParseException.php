<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\exceptions;

use Throwable;

/**
 * Class PrintfulResponseParseException
 * @package Printful\exceptions
 */
class PrintfulResponseParseException extends BasePrintfulException
{
    /** @var string */
    private $response;

    /**
     * PrintfulResponseParseException constructor.
     * @param string $response
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $response = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
