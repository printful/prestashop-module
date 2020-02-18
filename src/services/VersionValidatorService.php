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
use Printful;
use Printful\structures\PrintfulPluginVersionCheckData;
use Tools;

/**
 * Class VersionValidatorService
 * @package Printful\services
 */
class VersionValidatorService
{
    const PLUGIN_RELEASE_DATA_URL = 'https://api.github.com/repos/printful/prestashop-module/releases';
    const USER_AGENT = 'Printful';

    /**
     * @param string $version
     * @return PrintfulPluginVersionCheckData|null
     */
    public function validateVersion($version)
    {
        $actualVersionData = $this->getActualVersionData();

        if ($actualVersionData) {
            $actualVersionData->isValidVersion = ($actualVersionData->actualVersion === $version);

            // remember in cache
            $this->storeVersionCheckData($actualVersionData);
        }

        return $actualVersionData;
    }

    /**
     * @return PrintfulPluginVersionCheckData|null
     */
    public function getActualVersionData()
    {
        $currentCheckData = $this->getVersionCheckData();

        // refresh data
        if (!$currentCheckData || $currentCheckData->isExpired()) {
            // Create a stream context - github asks for User-agent
            $context = stream_context_create(array(
                'http' => array(
                    'header'=> 'User-Agent: ' . self::USER_AGENT,
                )
            ));

            $releaseJsonData = Tools::file_get_contents(self::PLUGIN_RELEASE_DATA_URL, false, $context);
            $releaseData = Tools::jsonDecode($releaseJsonData, true);

            // new result with defaults
            $currentCheckData = new PrintfulPluginVersionCheckData();

            $latestRelease = isset($releaseData[0]) ? $releaseData[0] : null;
            if ($latestRelease) {
                $tagName = isset($latestRelease['tag_name']) ? $latestRelease['tag_name'] : null;

                // successful check, override defaults
                $currentCheckData->actualVersion = $tagName;
                $currentCheckData->checkSuccessful = true;
                $currentCheckData->checkedTime = time();
            }
        }

        return $currentCheckData;
    }

    /**
     * Store version check data in configuration
     * @param PrintfulPluginVersionCheckData $data
     */
    protected function storeVersionCheckData(PrintfulPluginVersionCheckData $data)
    {
        $jsonData = Tools::jsonEncode($data->toArray());

        Configuration::set(Printful::CONFIG_PRINTFUL_VERSION_CHECK_DATA, $jsonData);
    }

    /**
     * Get current(cached) version check data
     * @return PrintfulPluginVersionCheckData|null
     */
    protected function getVersionCheckData()
    {
        $checkData = Configuration::get(Printful::CONFIG_PRINTFUL_VERSION_CHECK_DATA);
        $checkDataArr = $checkData ? json_decode($checkData, true) : false;

        return $checkDataArr ? PrintfulPluginVersionCheckData::fromArray($checkDataArr) : null;
    }
}
