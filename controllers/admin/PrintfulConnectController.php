<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

require_once("BasePrintfulAdminController.php");

/**
 * Class PrintfulDashboardController
 * @property Printful $module
 */
class PrintfulConnectController extends BasePrintfulAdminController
{
    const ACTION_DISCONNECT = 'disconnect';
    const ACTION_STATUS_CHECK = 'checkConnectionStatus';

    const ERROR_NO_PERMISSIONS = 'no-permissions';

    /** @var Printful\services\ConnectService */
    private $connectService;

    /** @var Printful\services\WebserviceService */
    private $webserviceService;

    /** @var bool */
    protected $requiresConnection = false;

    /**
     * PrintfulDashboardController constructor.
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function __construct()
    {
        parent::__construct();

        // set dependencies
        $this->connectService = Printful::getService(Printful\services\ConnectService::class);
        $this->webserviceService = Printful::getService(Printful\services\WebserviceService::class);
    }

    /**
     * AJAX endpoint for checking if PrestaShop is connected to Printful
     */
    public function ajaxProcessCheckConnectionStatus()
    {
        echo Tools::jsonEncode(array('status' => $this->connectService->isConnected()));
        die;
    }

    /**
     * Catch POST request and redirect to connect page
     *
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        if (Tools::getValue('action') === self::ACTION_STATUS_CHECK) {
            return $this->ajaxProcessCheckConnectionStatus();
        }

        if (Tools::getIsset('printful_connect')) {
            // get selected service key id
            $webServiceKeyId = Tools::getValue('webservice_id');

            $webService = null;
            if ($webServiceKeyId) {
                $webService = $this->webserviceService->getWebserviceById($webServiceKeyId);
            }

            // enable webservice if it's turned off
            $this->webserviceService->enableWebservice();

            // turn on CGI setting if necessary
            if (Printful\helpers\SystemHelper::runningInCgi()) {
                $this->webserviceService->setCgiMode(true);
            }

            // use selected or find one from configuration.
            $webService = $webService ? $webService : $this->webserviceService->getConnectedWebservice();

            // if no key in configuration, create new
            $webService = $webService ? $webService : $this->webserviceService->createNewWebservice();

            // set/renew required permissions
            try {
                $this->webserviceService->renewPermissions($webService);
            } catch (Printful\exceptions\PrintfulFailedLoadPermissions $exception) {
                // if we created new service key, delete it
                if (!$webServiceKeyId) {
                    $webService->delete();
                }

                $params = array('connectError' => self::ERROR_NO_PERMISSIONS);
                $redirectUrl = $this->context->link->getAdminLink(Printful::CONTROLLER_CONNECT, true, array(), $params);

                Tools::redirect($redirectUrl);
            }

            // register chosen webservice
            $this->webserviceService->registerWebservice($webService);

            $authData = $this->connectService->buildAuthData($webService);

            $returnUrl = Printful::isOlderPSVersion()
                ? $this->connectService->buildReturnUrl()
                : null;

            $redirectUrl = $this->connectService->buildConnectUrl($authData, $returnUrl);

            Tools::redirect($redirectUrl);
        }

        $action = Tools::getValue('action');
        if ($action === self::ACTION_DISCONNECT) {
            $this->connectService->disconnect();

            $redirectUrl = $this->context->link->getAdminLink(Printful::CONTROLLER_CONNECT);
            Tools::redirectAdmin($redirectUrl);
        }

        $connectError = Tools::getValue('connectError');
        if ($connectError) {
            $errorMessage = $this->getErrorMessage($connectError);
            if ($errorMessage) {
                $this->errors[] = $errorMessage;
            }
        }

        $errorMessage = Tools::getValue('errorMessage');
        if ($errorMessage) {
            $this->errors[] = $errorMessage;
        }

        $isConnected = Tools::getValue('connected');
        if ((bool)$isConnected) {
            $this->informations[] = $this->module->l('You have successfully connected to Printful');
        }

        return true;
    }

    /**
     * Output connect page
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        $this->addCSS(_PS_ADMIN_DIR_ . '/themes/new-theme/public/theme.css');
        $this->addJS($this->module->getPathUri() . 'views/js/connect.js');

        $smarty = $this->context->smarty;
        $isConnected = $this->connectService->isConnected();

        // Am i already connected? Redirect back to dashboard
        if ($isConnected) {
            Tools::redirectAdmin($this->context->link->getAdminLink(Printful::CONTROLLER_DASHBOARD));
        }

        $webserviceOptions = array('0' => $this->module->l('Create new...'));
        foreach ($this->webserviceService->getAllWebservices() as $key) {
            $webserviceOptions[$key->id] = $key->description . ' (' . $key->key . ')';
        }

        if (Printful\helpers\SystemHelper::runningInCgi()) {
            $this->informations[] = $this->module->l('It looks like your server is running in CGI mode. We will turn on the `Enable CGI mode for PHP` setting during the connection process.');
        }
        $this->warnings = array_merge($this->warnings, Printful\helpers\SystemHelper::getSystemWarnings());

        $smarty->assign(array(
            'content' => $this->content,
            'webserviceOptions' => $webserviceOptions,
            'runningInCgi' => Printful\helpers\SystemHelper::runningInCgi(),
            'action' => $this->context->link->getAdminLink(Printful::CONTROLLER_CONNECT) . '&printful_connect=1',
            'statusCheckUrl' => $this->context->link->getAdminLink(Printful::CONTROLLER_CONNECT),
            'statusCheckController' => Printful::CONTROLLER_CONNECT,
            'statusCheckAction' => self::ACTION_STATUS_CHECK,
            'oldPSVersion' => Printful::isOlderPSVersion(),
            'logoPath' => $this->module->getPathUri() . 'views/img/printful-connect.svg',
        ));

        $this->content = $smarty->fetch($this->getTemplatePath() . 'connect.tpl');

        $this->context->smarty->assign(array(
            'content' => $this->content,
            'title' => $this->module->l('Connect to Printful'),
        ));
    }

    /**
     * @param string $error
     * @return string|null
     */
    protected function getErrorMessage($error)
    {
        if ($error === self::ERROR_NO_PERMISSIONS) {
            return $this->module->l('Connecting to Printful failed. (CODE: {code})', array('{code}' => self::ERROR_NO_PERMISSIONS));
        }

        return null;
    }
}
