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
class PrintfulHomeController extends BasePrintfulAdminController
{
    /**
     * This controller forwards client to dashboard
     */
    public function initContent()
    {
        Tools::redirectAdmin(Printful::CONTROLLER_DASHBOARD);
    }
}
