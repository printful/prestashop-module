<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\helpers;

use Configuration;
use Printful;

/**
 * Class SystemHelper
 */
class SystemHelper
{
    /**
     * Check if server is running in cgi mode
     * @return bool
     */
    public static function runningInCgi()
    {
        return strpos(php_sapi_name(), 'cgi') !== false;
    }

    /**
     * Validate Server configuration and return error messages
     * Copied from AdminWebserviceControllerCore
     * @return array
     */
    public static function getSystemWarnings()
    {
        $warnings = [];

        $module = Printful::getInstance();
        
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === false) {
            $warnings[] = $module->l('To avoid operating problems, please use an Apache server.');
            if (function_exists('apache_get_modules')) {
                $apache_modules = apache_get_modules();
                if (!in_array('mod_auth_basic', $apache_modules)) {
                    $warnings[] = $module->l('Please activate the \'mod_auth_basic\' Apache module to allow authentication of PrestaShop\'s webservice.');
                }
                if (!in_array('mod_rewrite', $apache_modules)) {
                    $warnings[] = $module->l('Please activate the \'mod_rewrite\' Apache module to allow the PrestaShop webservice.');
                }
            } else {
                $warnings[] = $module->l('We could not check to see if basic authentication and rewrite extensions have been activated. Please manually check if they\'ve been activated in order to use the PrestaShop webservice.');
            }
        }

        if (!extension_loaded('SimpleXML')) {
            $warnings[] = $module->l('Please activate the \'SimpleXML\' PHP extension to allow testing of PrestaShop\'s webservice.');
        }

        if (!configuration::get('PS_SSL_ENABLED')) {
            $warnings[] = $module->l('It is preferable to use SSL (https:) for webservice calls, as it avoids the "man in the middle" type security issues.');
        }
        
        return $warnings;
    }
}
