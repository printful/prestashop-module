<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\services;

use Module;
use PrestaShopBundle\Entity\Repository\TabRepository;
use Printful;
use Tools;
use WebserviceKeyCore;
use Tab;
use Language;
use Configuration;
use WebserviceKey;

/**
 * Class InstallService
 * @package Printful\services
 */
class InstallService
{
    /**
     * @param Printful $module
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function install(Printful $module)
    {
        // install tab
        foreach ($this->getTabsToInstall($module) as $tab) {
            $this->installTab($module, $tab);
        }

        // register Printful Webservice endpoint
        $module->registerHook('addWebserviceResources');
        $module->registerHook('displayBackOfficeHeader');

        // remember plugin version
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_PLUGIN_VERSION, Printful::getInstance()->version);

        // remember sapi name for easier debugging
        Configuration::updateValue(Printful::CONFIG_PRINTFUL_SAPI_NAME, php_sapi_name());

        // forget version check data if there is one
        Configuration::deleteByName(Printful::CONFIG_PRINTFUL_VERSION_CHECK_DATA);

        return true;
    }

    /**
     * @param Printful $module
     * @param array $tabData
     * @return int
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function installTab(Printful $module, $tabData)
    {
        $className = isset($tabData['className']) ? $tabData['className'] : null;
        $parent = isset($tabData['parent']) ? $tabData['parent'] : null;
        $tabName = isset($tabData['tabName']) ? $tabData['tabName'] : null;

        if (!$className || !$parent) {
            return 0;
        }

        $tabRepositoryId = 'prestashop.core.admin.tab.repository';
        $tabRepository = $module->get($tabRepositoryId);

        $tab = new Tab();
        $tab->id_parent = $tabRepository->findOneIdByClassName($parent);
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        $tab->class_name = $className;
        $tab->module = $module->name;
        $tab->active = 1;

        return $tab->add();
    }

    /**
     * Get tabs to install
     * @param Printful $module
     * @return array
     */
    public function getTabsToInstall(Printful $module)
    {
        return array(
            array(
                'parent' => Printful::CONTROLLER_IMPROVE,
                'tabName' => Tools::ucfirst($module->name),
                'className' => Printful::CONTROLLER_PRINTFUL,
                'icon' => 'local_shipping',
            ),
            array(
                'parent' => Printful::CONTROLLER_PRINTFUL,
                'tabName' => $module->l('Dashboard'),
                'className' => Printful::CONTROLLER_DASHBOARD,
            ),
            array(
                'parent' => Printful::CONTROLLER_PRINTFUL,
                'tabName' => $module->l('Orders'),
                'className' => Printful::CONTROLLER_ORDERS,
            ),
        );
    }
}
