<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once("BasePrintfulAdminController.php");

use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use Printful\services\ConnectService;
use Printful\structures\PrintfulConnectReturnData;

/**
 * Class PrintfulDashboardController
 * @property Printful $module
 */
class PrintfulConnectReturnController extends BasePrintfulAdminController
{
    const CONNECT_STATUS_SUCCESS = 'success';
    const CONNECT_STATUS_ERROR = 'error';

    /** @var ConnectService */
    private $connectService;

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
        $this->connectService = ServiceLocator::get(ConnectService::class);
    }

    /**
     * Process Connect flow return routines
     * @return bool|ObjectModel|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $returnData = PrintfulConnectReturnData::buildFromArray(Tools::getAllValues());
        $link = $this->context->link;

        if ($returnData->status === self::CONNECT_STATUS_SUCCESS) {
            $this->connectService->processConnectReturnData($returnData);

            $params = array('connected' => 1);
            $dashboardLink = $link->getAdminLink(Printful::CONTROLLER_DASHBOARD, true, array(), $params);
            Tools::redirectAdmin($dashboardLink);
        }

        // could not connect, return to connect page
        $params = array('errorMessage' => $returnData->errorMessage);
        $connectLink = $link->getAdminLink(Printful::CONTROLLER_CONNECT, true, array(), $params);
        Tools::redirectAdmin($connectLink);
    }
}
