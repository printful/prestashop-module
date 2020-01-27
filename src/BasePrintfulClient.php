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
        $options = $this->buildRequestOptions($method, $url, $params, $authData);

        $curl = curl_init();

        curl_setopt_array($curl, $options);
        try {
            $response = curl_exec($curl);

            if (!$response) {
                throw new \Exception('cUrl Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
            }

            $result = json_decode($response, true);
            $result = isset($result['result']) ? $result['result'] : null;

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new PrintfulResponseParseException('HTTP JSON response is not parsable', $response);
            }

            curl_close($curl);

            return $result;
        } catch (\Exception $exception) {
            throw $this->handleClientError($exception);
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @param PrintfulAuthData $authData
     * @return array
     */
    protected function buildRequestOptions($method, $url, $params = array(), PrintfulAuthData $authData = null)
    {
        if ($method === self::REQUEST_GET && $params) {
            $url .= '?' . http_build_query($params);
        }

        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $this->getUserAgent(),
            CURLOPT_SSL_VERIFYPEER => !Printful::isDevMode(),
            CURLOPT_HTTPHEADER => $this->buildRequestHeaders($authData),
        );

        if ($method === self::REQUEST_POST) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params;
        };

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
        $headers = array();

        if ($authData) {
            $headers[] = 'Authorization: Basic ' . base64_encode($authData->apiKey);
        }

        return $headers;
    }

    /**
     * @return string
     */
    protected function getUserAgent()
    {
        return BasePrintfulClient::USER_AGENT . ' v' . Printful::getInstance()->version;
    }
}
