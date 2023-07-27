<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

namespace Printful\services;

use Printful;
use Printful\PrintfulApi;
use Printful\structures\PrintfulAuthData;
use Configuration;

class AuthMigrationService
{
    /** @var PrintfulApi */
    private $api;

    public function __construct(PrintfulApi $api)
    {
        $this->api = $api;
    }

    public function migrate(string $legacyToken)
    {
        $authData = new PrintfulAuthData();
        $authData->apiKey = $legacyToken;

        $oauthToken = $this->api->getOAuthTokens($authData);

        Configuration::updateValue(Printful::CONFIG_PRINTFUL_OAUTH_KEY, $oauthToken);

        $authData->isOauth = true;
        $authData->apiKey = $oauthToken;

        $this->api->confirmMigration($authData);
    }
}