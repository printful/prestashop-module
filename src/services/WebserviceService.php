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
use Db;
use PrestaShopDatabaseException;
use Printful;
use Printful\exceptions\PrintfulFailedLoadPermissions;
use Printful\PrintfulApi;
use Tools;
use WebserviceKey;
use WebserviceKeyCore;

/**
 * Class WebserviceService
 * @package Printful\services
 */
class WebserviceService
{
    const WEBSERVICE_KEY_LENGTH = 32;

    /** @var PrintfulApi */
    private $api;

    /**
     * WebserviceService constructor.
     * @param PrintfulApi $api
     */
    public function __construct(PrintfulApi $api)
    {
        $this->api = $api;
    }

    /**
     * Return connected Webservice
     *
     * @return WebserviceKeyCore|null
     * @throws PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getConnectedWebservice()
    {
        $keyId = Configuration::get(Printful::CONFIG_PRINTFUL_SERVICE_KEY_ID);
        if (!$keyId) {
            return null;
        }

        return $this->getWebserviceById($keyId);
    }

    /**
     * todo: make/find repo for this
     * @param $id
     * @return WebserviceKeyCore|null
     * @throws PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getWebserviceById($id)
    {
        $webService = new WebserviceKeyCore($id);

        if (!$webService || !$this->isValidWebservice($webService)) {
            return null;
        }

        return $webService;
    }

    /**
     * @param WebserviceKeyCore $webService
     * @return false|string|null
     */
    public function isValidWebservice(WebserviceKeyCore $webService)
    {
        return WebserviceKey::keyExists($webService->key);
    }

    /**
     * Create new web service key
     * @return WebserviceKeyCore
     * @throws PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createNewWebservice()
    {
        // create webservice
        $webService = $this->generateWebservice();

        // save webservice
        $webService->add();

        return $webService;
    }

    /**
     * Register Webservice id for further reference
     * @param WebserviceKeyCore $key
     */
    public function registerWebservice(WebserviceKeyCore $key)
    {
        // store id in configuration for later reference
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_SERVICE_KEY_ID, $key->id);
    }

    /**
     * @return WebserviceKeyCore
     * @throws PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function generateWebservice()
    {
        $webService = new WebserviceKeyCore;

        do {
            $key = Tools::substr(str_shuffle(md5(microtime())), 0, self::WEBSERVICE_KEY_LENGTH);
        } while (WebserviceKey::keyExists($key)); // make me unique

        $webService->key = $key;
        $webService->description = 'Service key for integration with Printful';

        return $webService;
    }

    /**
     * @param WebserviceKeyCore $webService
     * @return bool
     * @throws PrintfulFailedLoadPermissions
     */
    public function renewPermissions(WebserviceKeyCore $webService)
    {
        if (!$this->isValidWebservice($webService)) {
            return false;
        }

        $permissions = $this->api->getRequiredPermissions();
        if (!$permissions) {
            throw new PrintfulFailedLoadPermissions();
        }

        $this->updateWebServicePermissions($webService, $permissions);

        return true;
    }

    /**
     * Set necessary permissions for Webservice
     * @param WebserviceKeyCore $webService
     * @param array $permissions
     * @return null
     */
    public function updateWebServicePermissions(WebserviceKeyCore $webService, $permissions)
    {
        // if we do not have permissions, do not remove existing..
        if (!$permissions) {
            return null;
        }

        $permissionArr = array();
        foreach ($permissions as $resource => $methods) {
            // keys are request types - GET, POST, DELETE...
            $permissionArr[$resource] = array_flip(array_map('strtoupper', $methods));
        }

        $fullPermissions = array(
            'GET' => true,
            'PUT' => true,
            'POST' => true,
            'DELETE' => true,
            'HEAD' => true,
        );

        // add full permissions for custom endpoints
        foreach (Printful::getCustomWebserviceResources() as $name => $data) {
            $permissionArr[$name] = $fullPermissions;
        }

        WebserviceKey::setPermissionForAccount($webService->id, $permissionArr);
    }

    /**
     * Enable Prestashop Webservice
     */
    public function enableWebservice()
    {
        Configuration::updateValue(Printful::CONFIG_WEBSERVICE, true);
    }

    /**
     * Enable CGI mode for PrestaShop webservice
     * @param bool $status
     */
    public function setCgiMode($status)
    {
        Configuration::updateValue(Printful::CONFIG_WEBSERVICE_CGI_HOST, (bool)$status);
    }

    /**
     * Fetch all Webservices from db.
     *
     * todo: Find/Create repository for this
     * @return WebserviceKeyCore[]
     * @throws PrestaShopDatabaseException
     */
    public function getAllWebservices()
    {
        $sql = 'SELECT id_webservice_account FROM ' . _DB_PREFIX_ . 'webservice_account';
        $list = Db::getInstance()->executeS($sql);

        return array_map(function ($item) {
            return new WebserviceKeyCore($item['id_webservice_account']);
        }, $list);
    }
}
