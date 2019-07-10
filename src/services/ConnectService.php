<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\services;

use Configuration;
use InvalidArgumentException;
use PrestaShopDatabaseException;
use PrestaShopException;
use Printful;
use Printful\structures\PrintfulAuthData;
use Printful\structures\PrintfulCredentials;
use WebserviceKeyCore;

/**
 * Class ConnectService
 * @package Printful\services
 */
class ConnectService
{
    const CONNECT_URL = 'dashboard/prestashop/plugin-connect';

    /** @var WebserviceService */
    private $webserviceService;

    /**
     * ConnectService constructor.
     * @param WebserviceService $webserviceService
     */
    public function __construct(WebserviceService $webserviceService)
    {
        $this->webserviceService = $webserviceService;
    }

    /**
     * @param WebserviceKeyCore|null $webService
     * @return PrintfulAuthData
     */
    public function buildAuthData(WebserviceKeyCore $webService = null)
    {
        $authData = new PrintfulAuthData();

        $authData->storeAddress = Printful::getStoreAddress();
        $authData->serviceKey = $webService ? $webService->key : null;

        $authData->identity = Configuration::get(Printful::CONFIG_PRINTFUL_IDENTITY);
        $authData->apiKey = Configuration::get(Printful::CONFIG_PRINTFUL_API_KEY);

        $authData->pluginVersion = Printful::getInstance()->version;

        return $authData;
    }

    /**
     * If we have API key from PF and valid Webservice, consider we are connected
     * @return bool
     */
    public function isConnected()
    {
        $apiKey = Configuration::get(Printful::CONFIG_PRINTFUL_API_KEY);
        $serviceKeyId = Configuration::get(Printful::CONFIG_PRINTFUL_SERVICE_KEY_ID);

        $webService = $this->webserviceService->getWebserviceById($serviceKeyId);

        return $apiKey && $webService;
    }

    /**
     * Builds auth redirect uri
     * @param PrintfulAuthData $authData
     * @return string
     */
    public function buildConnectUrl(PrintfulAuthData $authData)
    {
        $url = Printful::getPrintfulHost() . self::CONNECT_URL;

        $params = array(
            'storeAddress' => $authData->storeAddress,
            'serviceKey' => $authData->serviceKey
        );

        return $url . '?' . http_build_query($params);
    }

    /**
     * Set Printfuls credentials
     * @param PrintfulCredentials $credentials
     */
    public function setPrintfulCredentials(PrintfulCredentials $credentials)
    {
        if (!$credentials->isValid()) {
            throw new InvalidArgumentException('Credentials are not valid');
        }

        // save necessary data to configuration
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_API_KEY, $credentials->apiAccessKey);
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_IDENTITY, $credentials->identity);
    }

    /**
     * Remove selected service key id from configuration
     * so client can re-connect with another key
     */
    public function disconnect()
    {
        Configuration::deleteByName(Printful::CONFIG_PRINTFUL_SERVICE_KEY_ID);
        Configuration::deleteByName(Printful::CONFIG_PRINTFUL_API_KEY);
        Configuration::deleteByName(Printful::CONFIG_PRINTFUL_IDENTITY);
    }
}
