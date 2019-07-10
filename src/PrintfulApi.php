<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful;

use Printful;
use Printful\exceptions\PrintfulClientException;
use Printful\structures\PrintfulAuthData;
use Printful\structures\StoreStats;

/**
 * Class PrintfulApi
 * @package Printful
 */
class PrintfulApi
{
    /** @var PrintfulClient */
    private $client;

    /**
     * PrintfulApi constructor.
     * @param PrintfulClient $client
     */
    public function __construct(PrintfulClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param PrintfulAuthData $authData
     * @return StoreStats|null
     */
    public function getStoreStats(PrintfulAuthData $authData)
    {
        try {
            $response = $this->client->get(PrintfulClient::ENDPOINT_STATS, array(), $authData);
            $stats = isset($response['store_statistics']) ? $response['store_statistics'] : array();

            return StoreStats::fromArray($stats);
        } catch (PrintfulClientException $exception) {
            $this->logClientException($exception);
        }
    }

    /**
     * Get required permissions for Webservice
     * @return array|null
     */
    public function getRequiredPermissions()
    {
        try {
            $response = $this->client->get(PrintfulClient::ENDPOINT_PERMISSIONS, array());

            return isset($response['data']) ? $response['data'] : array();
        } catch (PrintfulClientException $exception) {
            $this->logClientException($exception);
        }
    }

    /**
     * Send error message to PF for logging
     * @param $message
     */
    public function logErrorMessage($message)
    {
        try {
            $this->client->post(PrintfulClient::ENDPOINT_LOG_ERROR, array(
                'store' => Printful::getStoreAddress(),
                'message' => $message,
            ));
        } catch (\Exception $exception) {
            // well this is awkward.. but what can you do? we can't let this error ruin the day
        };
    }

    /**
     * @param PrintfulAuthData $authData
     * @return array
     */
    public function getStoreOrders(PrintfulAuthData $authData)
    {
        try {
            $response = $this->client->get(PrintfulClient::ENDPOINT_ORDERS, array(), $authData);

            return $response;
        } catch (PrintfulClientException $exception) {
            $this->logClientException($exception);
        }
    }

    /**
     * Send error message for logging
     * @param PrintfulClientException $exception
     */
    protected function logClientException(PrintfulClientException $exception)
    {
        // do not send errors in dev mode, ty
        if (Printful::isDevMode()) {
            die('Client error: ' . $exception->getMessage());
        }

        $this->logErrorMessage($exception->getMessage());
    }
}
