<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use Printful\services\ConnectService;
use Printful\services\InstallService;
use Printful\services\UninstallService;
use Printful\services\VersionValidatorService;
use Printful\services\WebserviceService;
use Printful\structures\PrintfulAuthData;
use Printful\structures\PrintfulPluginVersionCheckData;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/src/services/web/WebserviceSpecificManagementPrintful.php';
require_once dirname(__FILE__) . '/controllers/admin/PrintfulConnectController.php';

/**
 * Class Printful
 */
class Printful extends Module
{
    const ENV_DEV = 'dev';
    const ENV_PROD = 'prod';

    // active env
    const ENV = self::ENV_PROD;

    // PF Configuration keys
    const CONFIG_PRINTFUL_SERVICE_KEY_ID = 'PRINTFUL_SERVICE_KEY_ID';
    const CONFIG_PRINTFUL_IDENTITY = 'PRINTFUL_IDENTITY';
    const CONFIG_PRINTFUL_API_KEY = 'PRINTFUL_API_KEY';
    const CONFIG_PRINTFUL_PLUGIN_VERSION = 'PRINTFUL_PLUGIN_VERSION';
    const CONFIG_PRINTFUL_SAPI_NAME = 'PRINTFUL_SAPI_NAME';
    const CONFIG_PRINTFUL_VERSION_CHECK_DATA = 'PRINTFUL_VERSION_CHECK_DATA';

    // Common Configuration keys
    const CONFIG_WEBSERVICE = 'PS_WEBSERVICE';
    const CONFIG_WEBSERVICE_CGI_HOST = 'PS_WEBSERVICE_CGI_HOST';

    // Used in uninstall
    const PRINTFUL_CONFIGURATION_KEYS = [
        self::CONFIG_PRINTFUL_SERVICE_KEY_ID,
        self::CONFIG_PRINTFUL_IDENTITY,
        self::CONFIG_PRINTFUL_API_KEY,
        self::CONFIG_PRINTFUL_PLUGIN_VERSION,
        self::CONFIG_PRINTFUL_SAPI_NAME,
        self::CONFIG_PRINTFUL_VERSION_CHECK_DATA,
    ];

    // Controller names
    const CONTROLLER_IMPROVE = 'IMPROVE';
    const CONTROLLER_PRINTFUL = 'PrintfulHome';
    const CONTROLLER_DASHBOARD = 'PrintfulDashboard';
    const CONTROLLER_ORDERS = 'PrintfulOrders';
    const CONTROLLER_CONNECT = 'PrintfulConnect';

    // Printful host
    const PRINTFUL_HOST = 'https://www.printful.com/';
    const PRINTFUL_HOST_DEV = 'http://www.printful.test/';

    // Printful API host
    const PRINTFUL_API_HOST = 'https://api.printful.com';
    const PRINTFUL_API_HOST_DEV = 'http://api.printful.test';

    const PRINTFUL_PLUGIN_PATH = 'dashboard/prestashop/download';

    /**
     * Printful constructor.
     */
    public function __construct()
    {
        $this->name = 'printful';
        $this->tab = 'others';
        $this->version = '1.0.2';
        $this->author = 'Printful';
        $this->need_instance = 1;

        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_,
        );
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Printful: Print-on-demand dropshipping');
        $this->description = $this->l('Use Printful to design and sell your own shirts, hats, bags and more! We will handle inventory, production, and shipping, so you can focus on building your store.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!self::isConnected()) {
            $this->warning = $this->l('Your store is not connected to Printful');
        } else {
            /** @var VersionValidatorService $versionValidator */
            $versionValidator = ServiceLocator::get(VersionValidatorService::class);
            $data = $versionValidator->validateVersion($this->version);
            if ($data && !$data->isValidVersion) {
                $this->warning = $this->l('Your current Printful module is out of date');
            }
        }

        $this->module_key = 'f9dc46e8f45d06a7ee5ad692ff89eb15';
    }

    /**
     * Install module
     * @return bool
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        try {
            /** @var InstallService $installService */
            $installService = ServiceLocator::get(InstallService::class);

            return $installService->install($this);
        } catch (Exception $exception) {
            // notify PF about failed install?
            return false;
        }
    }

    /**
     * Uninstall module
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        try {
            /** @var UninstallService $uninstallService */
            $uninstallService = ServiceLocator::get(UninstallService::class);

            return $uninstallService->uninstall($this);
        } catch (Exception $exception) {
            // notify PF about failed uninstall?
            return false;
        }
    }

    /**
     * Configuration page, currently just redirect to dashboard
     * @throws PrestaShopException
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::CONTROLLER_DASHBOARD));
    }

    /**
     * Add our Webservice resource
     * @return array
     */
    public function hookAddWebserviceResources()
    {
        return self::getCustomWebserviceResources();
    }

    /**
     * Include tab css for icon
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/tab.css');
    }

    /**
     * Return custom Webservice endpoints
     * @return array
     */
    public static function getCustomWebserviceResources()
    {
        return array(
            'printful' => array(
                'description' => 'Printful integration',
                'specific_management' => true,
            ),
        );
    }

    /**
     * Returns auth data
     *
     * @return PrintfulAuthData
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function getAuthData()
    {
        if (!$this->isConnected()) {
            return null;
        }

        /** @var ConnectService $connectService */
        $connectService = ServiceLocator::get(ConnectService::class);
        /** @var WebserviceService $webService */
        $webService = ServiceLocator::get(WebserviceService::class);

        $connectedWebService = $webService->getConnectedWebservice();
        return $connectService->buildAuthData($connectedWebService);
    }

    /**
     * Check if module is connected to Printful
     * @return bool
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function isConnected()
    {
        /** @var ConnectService $service */
        $service = ServiceLocator::get(ConnectService::class);

        return $service->isConnected();
    }

    /**
     * @return PrintfulPluginVersionCheckData|null
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public static function validateCurrentVersion()
    {
        /** @var VersionValidatorService $versionValidator */
        $versionValidator = ServiceLocator::get(VersionValidatorService::class);

        return $versionValidator->validateVersion(Printful::getInstance()->version);
    }

    /**
     * @return string
     */
    public static function getStoreAddress()
    {
        return Tools::getHttpHost(true) . __PS_BASE_URI__;
    }

    /**
     * @return string
     */
    public static function getPrintfulHost()
    {
        return self::isDevMode() ? self::PRINTFUL_HOST_DEV : self::PRINTFUL_HOST;
    }

    /**
     * @return string
     */
    public static function getPluginDownloadUrl()
    {
        return self::getPrintfulHost() . self::PRINTFUL_PLUGIN_PATH;
    }

    /**
     * @return bool
     */
    public static function isDevMode()
    {
        return self::ENV === self::ENV_DEV;
    }

    /**
     * Get Printful module instance
     * @return Printful
     */
    public static function getInstance()
    {
        return Module::getInstanceByName('printful');
    }

    /**
     * Return web path for module
     * @return string
     */
    public function getWebPath()
    {
        return $this->_path;
    }
}
