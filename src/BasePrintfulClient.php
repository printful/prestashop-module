<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful;

use Exception;
use GuzzleHttp\Client;
use Printful;
use Printful\exceptions\PrintfulClientException;
use Printful\exceptions\PrintfulResponseParseException;
use Printful\structures\PrintfulAuthData;
use Printful\structures\PrintfulClientExceptionPayload;

/**
 * Class BasePrintfulClient
 * @package Printful
 */
abstract class BasePrintfulClient
{
    const USER_AGENT = 'Printful Prestashop plugin';

    const REQUEST_GET = 'get';
    const REQUEST_POST = 'post';
    const REQUEST_DELETE = 'delete';

    /** @var Client */
    protected $guzzle;

    /**
     * PrintfulClient constructor.
     */
    public function __construct()
    {
        // setting up Guzzle client
        $config = array(
            'timeout' => 90,
        );

        $this->guzzle = new Client($config);

        if (Printful::isDevMode()) {
            $this->guzzle->setDefaultOption('verify', false);
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @param PrintfulAuthData|null $authData
     * @return array
     * @throws PrintfulClientException
     */
    protected function makeRequest($method, $url, $params = array(), PrintfulAuthData $authData = null)
    {
        $options = $this->buildRequestOptions($method, $params, $authData);
        try {
            switch ($method) {
                case self::REQUEST_GET:
                    $response = $this->guzzle->get($url, $options);
                    break;
                case self::REQUEST_POST:
                    $response = $this->guzzle->post($url, $options);
                    break;
                default:
                    throw new \InvalidArgumentException('Bad method given: ' . $method);
            }

            $contents = $response->getBody()->getContents();
            $result = json_decode($contents, true);
            $result = isset($result['result']) ? $result['result'] : null;

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new PrintfulResponseParseException('HTTP JSON response is not parsable', $response);
            }
        } catch (Exception $exception) {
            throw $this->handleClientError($exception);
        }

        return $result;
    }

    /**
     * @param string $method
     * @param array $params
     * @param PrintfulAuthData $authData
     * @return array
     */
    protected function buildRequestOptions($method, $params = array(), PrintfulAuthData $authData = null)
    {
        $options = array(
            'headers' => $this->buildRequestHeaders($authData),
        );

        if (!$params) {
            return $options;
        }

        if ($method === self::REQUEST_GET) {
            $options['query'] = $params;
        } elseif ($method === self::REQUEST_POST) {
            $options['body'] = json_encode($params);
        }

        return $options;
    }

    /**
     * Handle Client error - convert everything to PrintfulClientException
     * @param Exception $exception
     * @return PrintfulClientException
     */
    protected function handleClientError(Exception $exception)
    {
        $payload = new PrintfulClientExceptionPayload();
        $payload->originalException = $exception;

        $errorMessage = $exception->getMessage();
        $errorCode = $exception->getCode();

        $exception = new PrintfulClientException($errorMessage, $errorCode);
        $exception->payload = $payload;

        return $exception;
    }

    /**
     * Build request header array
     * @param PrintfulAuthData|null $authData
     * @return array
     */
    protected function buildRequestHeaders(PrintfulAuthData $authData = null)
    {
        $printful = Printful::getInstance();

        $headers = array(
            'User-Agent' => BasePrintfulClient::USER_AGENT . ' v' . $printful->version,
        );

        if ($authData) {
            $headers['Authorization'] = 'Basic ' . base64_encode($authData->apiKey);
        }

        return $headers;
    }
}
