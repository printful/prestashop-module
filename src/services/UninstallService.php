<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\services;

use Cache;
use Configuration;
use Module;
use Printful;
use Tab;

/**
 * Class UninstallService
 * @package Printful\services
 */
class UninstallService
{
    /**
     * Process plugin uninstall routines.
     * DO NOT remove configuration data, as client might want to re-connect to existing store
     * @param Printful $module
     * @return bool
     * @throws \PrestaShopException
     */
    public function uninstall(Printful $module)
    {
        // remove tabs
        foreach (Tab::getCollectionFromModule($module->name) as $tab) {
            $tab->delete();
        }

        // remove all Printful configuration
        foreach (Printful::PRINTFUL_CONFIGURATION_KEYS as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }
}
