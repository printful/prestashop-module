<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful;

use InvalidArgumentException;
use Printful;
use Printful\exceptions\PrintfulClientException;
use Printful\structures\PrintfulAuthData;

/**
 * Class PrintfulClient
 * @package Printful
 */
class PrintfulClient extends BasePrintfulClient
{
    const ENDPOINT_STATS = '/store/statistics';
    const ENDPOINT_LOG_ERROR = '/prestashop/log-error';
    const ENDPOINT_PERMISSIONS = '/prestashop/get-required-permissions';
    const ENDPOINT_ORDERS = '/orders';

    const ENDPOINTS = array(
        self::ENDPOINT_STATS,
        self::ENDPOINT_LOG_ERROR,
        self::ENDPOINT_PERMISSIONS,
        self::ENDPOINT_ORDERS,
    );

    /**
     * Make a GET request to given endpoint
     * @param string $endpoint
     * @param array $params
     * @param PrintfulAuthData $authData
     * @return array
     * @throws PrintfulClientException
     */
    public function get($endpoint, $params = array(), PrintfulAuthData $authData = null)
    {
        if (!in_array($endpoint, self::ENDPOINTS)) {
            throw new InvalidArgumentException('Incorrect endpoint given: ' . $endpoint);
        }

        $url = $this->buildRequestUrl($endpoint);

        return $this->makeRequest(BasePrintfulClient::REQUEST_GET, $url, $params, $authData);
    }

    /**
     * Make a POST request to given endpoint
     * @param $endpoint
     * @param array $params
     * @param PrintfulAuthData|null $authData
     * @return array
     * @throws PrintfulClientException
     */
    public function post($endpoint, $params = array(), PrintfulAuthData $authData = null)
    {
        if (!in_array($endpoint, self::ENDPOINTS)) {
            throw new InvalidArgumentException('Incorrect endpoint given');
        }

        $url = $this->buildRequestUrl($endpoint);

        return $this->makeRequest(BasePrintfulClient::REQUEST_POST, $url, $params, $authData);
    }

    /**
     * @param $endpoint
     * @return string
     */
    protected function buildRequestUrl($endpoint)
    {
        $base = Printful::isDevMode() ? Printful::PRINTFUL_API_HOST_DEV : Printful::PRINTFUL_API_HOST;

        return $base . $endpoint;
    }
}
