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

    /** @var AuthMigrationService */
    private $authMigrationService;

    /**
     * ConnectService constructor.
     * @param WebserviceService $webserviceService
     */
    public function __construct(
        WebserviceService $webserviceService,
        AuthMigrationService $authMigrationService
    ) {
        $this->webserviceService = $webserviceService;
        $this->authMigrationService = $authMigrationService;
    }

    /**
     * @param WebserviceKeyCore|null $webService
     * @return PrintfulAuthData
     */
    public function buildAuthData(WebserviceKeyCore $webService = null)
    {
        $legacyToken = Configuration::get(Printful::CONFIG_PRINTFUL_API_KEY);

        if ($this->shouldMigrateAuth()) {
            try {
                $this->authMigrationService->migrate($legacyToken);
            } catch (\Throwable $throwable) {
                // failed migration should not be a fatal error
            }
        }

        $authData = new PrintfulAuthData();

        $authData->storeAddress = Printful::getStoreAddress();
        $authData->serviceKey = $webService ? $webService->key : null;

        $authData->identity = Configuration::get(Printful::CONFIG_PRINTFUL_IDENTITY);

        $authData->apiKey = $legacyToken;
        $oauthKey = Configuration::get(Printful::CONFIG_PRINTFUL_OAUTH_KEY);
        if ($oauthKey) {
            $authData->apiKey = $oauthKey;
            $authData->isOauth = true;
        }

        $authData->pluginVersion = Printful::getInstance()->version;

        return $authData;
    }

    public function shouldMigrateAuth()
    {
        return $this->hasLegacyAccess() && !$this->hasOAuthAccess();
    }

    public function hasLegacyAccess()
    {
        return (bool)Configuration::get(Printful::CONFIG_PRINTFUL_API_KEY);
    }

    public function hasOAuthAccess()
    {
        return (bool)Configuration::get(Printful::CONFIG_PRINTFUL_OAUTH_KEY);
    }

    /**
     * If we have API key from PF and valid Webservice, consider we are connected
     * @return bool
     */
    public function isConnected()
    {
        $apiKey = Configuration::get(Printful::CONFIG_PRINTFUL_API_KEY);
        $oAuthKey = Configuration::get(Printful::CONFIG_PRINTFUL_OAUTH_KEY);
        $serviceKeyId = Configuration::get(Printful::CONFIG_PRINTFUL_SERVICE_KEY_ID);
        $webService = $this->webserviceService->getWebserviceById($serviceKeyId);

        return ($apiKey || $oAuthKey) && $webService;
    }

    /**
     * @return string
     */
    public function buildReturnUrl()
    {
        $adminLink = (new \LinkCore())->getAdminLink(Printful::CONTROLLER_CONNECT_RETURN, true);

        return _PS_BASE_URL_ . '/' . basename(_PS_ADMIN_DIR_) . '/' . $adminLink;
    }

    /**
     * Builds auth redirect uri
     * @param PrintfulAuthData $authData
     * @param string $returnUrl
     * @return string
     */
    public function buildConnectUrl(PrintfulAuthData $authData, $returnUrl = null)
    {
        $url = Printful::getPrintfulHost() . self::CONNECT_URL;

        $params = array(
            'storeAddress' => $authData->storeAddress,
            'serviceKey' => $authData->serviceKey,
            'version' => Printful::getInstance()->version,
        );

        if ($returnUrl) {
            $params['returnUrl'] = $returnUrl;
        }

        return $url . '?' . http_build_query($params);
    }

    /**
     * Return disconnect url
     * @return string
     */
    public static function getDisconnectUrl()
    {
        $link = new \Link();

        return Printful::isOlderPSVersion()
            ? $link->getAdminLink(Printful::CONTROLLER_CONNECT) . '&action=disconnect'
            : $link->getAdminLink(Printful::CONTROLLER_CONNECT, true, array(), array('action' => 'disconnect'));
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
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_OAUTH_KEY, $credentials->apiAccessKey);
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_IDENTITY, $credentials->identity);
    }

    /**
     *  Process Connect flow returned data
     *
     * @param PrintfulConnectReturnData $data
     */
    public function processConnectReturnData(PrintfulConnectReturnData $data)
    {
        // save necessary data to configuration
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_API_KEY, $data->apiAccessKey);
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_IDENTITY, $data->identity);
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
