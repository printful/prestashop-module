<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

use Printful\services\ConnectService;
use Printful\structures\PrintfulCredentials;

/**
 * Class WebserviceSpecificManagementPrintful
 */
class WebserviceSpecificManagementPrintful implements WebserviceSpecificManagementInterface
{
    const ERROR_BAD_ACTION = 1;
    const ERROR_MALFORMED_XML = 2;
    const ERROR_BAD_REQUEST_PARAMS = 3;

    const ACTION_SET_CREDENTIALS = 'set-credentials';

    /** @var WebserviceOutputBuilder */
    protected $objOutput;
    protected $output;

    /** @var WebserviceRequest */
    protected $wsObject;

    /**
     * @param WebserviceOutputBuilderCore $obj
     * @return $this
     */
    public function setObjectOutput(WebserviceOutputBuilderCore $obj)
    {
        $this->objOutput = $obj;

        return $this;
    }

    /**
     * @return WebserviceOutputBuilder
     */
    public function getObjectOutput()
    {
        return $this->objOutput;
    }

    /**
     * @param WebserviceRequestCore $obj
     * @return $this
     */
    public function setWsObject(WebserviceRequestCore $obj)
    {
        $this->wsObject = $obj;

        return $this;
    }

    /**
     * @return WebserviceRequest
     */
    public function getWsObject()
    {
        return $this->wsObject;
    }

    /**
     * @return void
     * @throws WebserviceException
     */
    public function manage()
    {
        $action = isset($this->wsObject->urlSegment[1]) ? $this->wsObject->urlSegment[1] : null;

        $requestBody = $this->getRequestContent();

        switch ($action) {
            case self::ACTION_SET_CREDENTIALS:
                $this->processSetCredentials($requestBody);
                break;
            default:
                throw new WebserviceException('Unknown Printful resource action: ' . $action, array(self::ERROR_BAD_ACTION, 405));
        }
    }

    /**
     * @param array $requestBody
     * @throws WebserviceException
     */
    protected function processSetCredentials($requestBody)
    {
        $credentialData = isset($requestBody['credentials']) ? $requestBody['credentials'] : null;
        if (!$credentialData) {
            throw new WebserviceException('Missing credentials', array(self::ERROR_BAD_REQUEST_PARAMS, 405));
        }

        try {
            $credentials = PrintfulCredentials::buildFromArray($credentialData);

            /** @var ConnectService $service */
            $service = Printful::getService(ConnectService::class);
            $service->setPrintfulCredentials($credentials);
        } catch (Exception $exception) {
            throw new WebserviceException('Failed to set credentials: ' . $exception->getMessage(), array(self::ERROR_BAD_REQUEST_PARAMS, 405));
        }
    }

    /**
     * @return array
     * @throws WebserviceException
     */
    protected function getRequestContent()
    {
        if (!in_array($this->wsObject->method, array('POST', 'PUT'))) {
            return [];
        }

        $post = Tools::file_get_contents('php://input');

        return $this->parseXml($post);
    }

    /**
     * @param $xmlString
     * @return array
     * @throws WebserviceException
     */
    protected function parseXml($xmlString)
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $result = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (libxml_get_errors()) {
            $errors = var_export(libxml_get_errors(), true);
            libxml_clear_errors();

            throw new WebserviceException('HTTP XML request is not parsable: ' . print_r($errors, true), array(self::ERROR_MALFORMED_XML, 405));
        }

        return json_decode(json_encode((array)$result), true);
    }

    /**
     * This must be return an array with specific values as WebserviceRequest expects.
     *
     * @return array
     */
    public function getContent()
    {
        return $this->objOutput->getObjectRender()->overrideContent($this->output);
    }
}
