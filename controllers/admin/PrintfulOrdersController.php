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
 * Class PrintfulOrdersController
 */
class PrintfulOrdersController extends BasePrintfulAdminController
{
    /** @var Printful\PrintfulApi */
    private $api;

    /**
     * PrintfulOrdersController constructor.
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function __construct()
    {
        parent::__construct();

        $this->api = Printful::getService(Printful\PrintfulApi::class);
    }

    /**
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @throws SmartyException
     */
    public function initContent()
    {
        parent::initContent();

        $this->addCSS(_PS_ADMIN_DIR_ . '/themes/new-theme/public/theme.css');
        $this->addCSS($this->getCssPath('orders.css'));

        $authData = $this->module->getAuthData();
        $orders = $this->api->getStoreOrders($authData);
        if (is_null($orders)) {
            $this->warnings[] = $this->trans('Failed to load orders');
        }

        $this->renderTemplate('orders', array(
            'title' => $this->module->l('Orders'),
            'orders' => $this->preProcessOrders($orders),
        ));
    }

    /**
     * Process orders before output
     * @param array $orders
     * @return array
     */
    protected function preProcessOrders($orders)
    {
        if (is_null($orders)) {
            return array();
        }

        // todo read from config
        $dateFormat = 'd.m.Y H:i:s';

        return array_map(function ($order) use ($dateFormat) {
            $order['created'] = date($dateFormat, $order['created']);
            $order['link'] = Printful::getPrintfulHost() . 'dashboard?order_id=' . $order['id'];
            $order['status'] = Tools::ucfirst($order['status']);

            return $order;
        }, $orders);
    }
}
