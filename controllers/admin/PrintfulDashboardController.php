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
class PrintfulDashboardController extends BasePrintfulAdminController
{
    /** @var Printful\PrintfulApi */
    private $api;

    /**
     * PrintfulDashboardController constructor.
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @throws Adapter_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->api = Printful::getService(Printful\PrintfulApi::class);
    }

    /**
     * @throws SmartyException
     */
    public function initContent()
    {
        parent::initContent();

        $this->addCSS(_PS_ADMIN_DIR_ . '/themes/new-theme/public/theme.css');
        $this->addCSS($this->getCssPath('dashboard.css'));

        $this->renderTemplate('dashboard', array(
            'title' => $this->l('Dashboard'),
            'shortcuts' => $this->getShortcuts(),
        ));
    }

    /**
     * Return shortcut array data
     * @return array
     */
    public function getShortcuts()
    {
        $host = Printful::getPrintfulHost();

        return array(
            array(
                'label' => $this->l('Orders'),
                'icon' => 'shopping_cart',
                'link' => $host . 'dashboard/default/orders',
            ),
            array(
                'label' => $this->l('File library'),
                'icon' => 'library_books',
                'link' => $host . 'dashboard/library',
            ),
            array(
                'label' => $this->l('Stores'),
                'icon' => 'store',
                'link' => $host . 'dashboard/store',
            ),
            array(
                'label' => $this->l('Reports'),
                'icon' => 'table_chart',
                'link' => $host . 'dashboard/reports',
            ),
            array(
                'label' => $this->l('My Account'),
                'icon' => 'account_box',
                'link' => $host . 'dashboard/settings/account-settings',
            ),
            array(
                'label' => $this->l('Billing'),
                'icon' => 'attach_money',
                'link' => $host . 'dashboard/billing',
            ),
            array(
                'label' => $this->l('Notifications'),
                'icon' => 'event_note',
                'link' => $host . 'dashboard/notifications',
            ),
        );
    }

    /**
     * Render KPIs from store stats
     * todo: We could probably cache api results..
     * @return string|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function renderKpis()
    {
        $authData = $this->module->getAuthData();
        $stats = $this->api->getStoreStats($authData);
        if (!$stats) { // can't fetch stats
          
            

            $this->warnings[] = str_replace(
                '{link}',
                '<a href="' . Printful\Services\ConnectService::getDisconnectUrl() . '">' . $this->module->l('reconnect your store') . '</a>',
                $this->module->l('Could not fetch store stats from Printful. Try to {link}!')
            );

            return;
        }

        $kpis = array();
        if (is_array($stats->orders_today)) {
            $kpi = $this->getOrderKpi($stats->orders_today);
            $kpi->subtitle = $this->module->l('Today');

            $kpis[] = $kpi->generate();
        }

        if (is_array($stats->orders_last_7_days)) {
            $kpi = $this->getOrderKpi($stats->orders_last_7_days);
            $kpi->subtitle = $this->module->l('Last week');

            $kpis[] = $kpi->generate();
        }

        if (is_array($stats->orders_last_28_days)) {
            $kpi = $this->getOrderKpi($stats->orders_last_28_days);
            $kpi->subtitle = $this->module->l('Last month');

            $kpis[] = $kpi->generate();
        }

        $kpi = new HelperKpi();
        $kpi->id = 'profit';
        $kpi->icon = 'icon-money';
        $kpi->color = 'color1';
        $kpi->title = $this->module->l('Profit');
        $kpi->subtitle = $this->l('Last month');
        $kpi->value = $stats->profit_last_28_days;

        $kpis[] = $kpi->generate();

        $helper = new HelperKpiRow();
        $helper->kpis = $kpis;

        // todo: implement refresh if only we cache results
        $helper->refresh = false;

        return $helper->generate();
    }

    /**
     * @param array $data
     * @return HelperKpi
     */
    protected function getOrderKpi($data)
    {
        $count = isset($data['orders']) ? $data['orders'] : 0;
        $trend = isset($data['trend']) ? $data['trend'] : 'up';

        $kpi = new HelperKpi();
        $kpi->title = $this->module->l('Orders');
        $kpi->icon = $trend === 'up' ? 'icon-arrow-up' : 'icon-arrow-down';
        $kpi->color = $trend === 'up' ? 'color1' : 'color2';
        $kpi->value = $count;

        return $kpi;
    }

    /**
     * Add header icons
     * @throws PrestaShopException
     */
    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['reconnect_printful'] = array(
            'href' => Printful\Services\ConnectService::getDisconnectUrl(),
            'desc' => $this->module->l('Reconnect Printful'),
            'icon' => 'process-icon-retweet icon-retweet',
        );

        parent::initPageHeaderToolbar();
    }
}
