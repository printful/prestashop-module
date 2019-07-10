<?php
/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

/**
 * Class PrintfulDashboardController
 * @property Printful $module
 */
abstract class BasePrintfulAdminController extends ModuleAdminController
{
    /**
     * Does this controller requires connection to Printful
     * @var bool
     */
    protected $requiresConnection = true;

    /** @var Smarty */
    protected $smarty;

    /** @var bool */
    public $bootstrap = true;

    /**
     * BasePrintfulAdminController constructor.
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function __construct()
    {
        parent::__construct();

        $this->smarty = $this->context->smarty;

        if ($this->requiresConnection && !$this->module->isConnected()) {
            Tools::redirectAdmin($this->context->link->getAdminLink(Printful::CONTROLLER_CONNECT));
        }

        $versionData = Printful::validateCurrentVersion();
        if ($versionData && !$versionData->isValidVersion && $versionData->checkSuccessful) {
            $this->warnings[] = str_replace(
                '{here}',
                '<a href="' . Printful::getPluginDownloadUrl() . '" target="_blank">' . $this->l('here') . '</a>',
                $this->l('Your current Printful module is out of date. Download the latest version {here}!')
            );
        }

        $this->addCSS($this->getCssPath('common.css'));
    }

    /**
     * @param $title
     */
    protected function setTitle($title)
    {
        $this->smarty->assign('title', $title);
    }

    /**
     * Renders given template with optional params
     * @param string $templateName
     * @param array $params
     * @throws SmartyException
     */
    protected function renderTemplate($templateName, $params = array())
    {
        $this->smarty->assign($params);

        // "dashboard" => "dashboard.tpl"
        $templateName .= '.tpl';

        $this->content .= $this->smarty->fetch($this->getTemplatePath() . $templateName);

        $this->smarty->assign('content', $this->content);
    }

    /**
     * Return web path for css file
     * @param $filename
     * @return string
     */
    protected function getCssPath($filename)
    {
        return $this->module->getWebPath() . 'views/css/' . $filename;
    }
}
