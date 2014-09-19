<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Core_JsTranslate extends TIG_PostNL_Block_Core_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_core_jstemplate';

    /**
     * Translate file name
     */
    const JAVASCRIPT_TRANSLATE_CONFIG_FILENAME = 'jstranslator.xml';

    /**
     * The template file used by this block.
     *
     * @var string app/design/frontend/base/default/template/TIG/PostNL/page/html/js_translate.phtml
     */
    protected $_template = 'TIG/PostNL/core/page/html/js_translate.phtml';

    /**
     * Array of JS translations
     *
     * @var array|null
     */
    protected $_translateData = null;

    /**
     * Translate config
     *
     * @var Varien_Simplexml_Config|null
     */
    protected $_config = null;

    /**
     * @param array|null $translateData
     *
     * @return $this
     */
    public function setTranslateData($translateData)
    {
        $this->_translateData = $translateData;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTranslateData()
    {
        return $this->_translateData;
    }

    /**
     * @param null|Varien_Simplexml_Config $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->_config = $config;

        return $this;
    }

    /**
     * @return null|Varien_Simplexml_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Get JS translations for PostNL JS files.
     *
     * @return string
     */
    public function getPostnlTranslateData()
    {
        $messages = $this->_getTranslateData();

        $json = Mage::helper('core')->jsonEncode($messages);
        return $json;
    }

    /**
     * Get JS translations from jstranslate.xml files. These files are only supported since Magento 1.7, so we made
     * this block to provide forwards compatibility to Magento 1.6.
     *
     * Because Magento 1.6 doesn't have a cache entry for these xml files, we don't use caching.
     *
     * @return array
     */
    protected function _getTranslateData()
    {
        if ($this->getTranslateData() !== null) {
            return $this->getTranslateData();
        }

        $translateData = array();
        $messages = $this->_getXmlConfig()->getXpath('*/message');

        if (empty($messages)) {
            $this->setTranslateData($translateData);

            return $translateData;
        }

        $helper = Mage::helper('postnl');

        /**
         * @var Varien_Simplexml_Element $message
         */
        foreach ($messages as $message) {
            $messageText = (string) $message;
            $module = $message->getParent()->getAttribute("module");

            /**
             * We only want to parse PostNL's translations.
             */
            if (!$module || $module != 'postnl') {
                continue;
            }
            $translateData[$messageText] = $helper->__($messageText);
        }

        foreach ($translateData as $key => $value) {
            if ($key == $value) {
                unset($translateData[$key]);
            }
        }

        $this->setTranslateData($translateData);

        return $translateData;
    }

    /**
     * Load config from files and try to cache it
     *
     * @return Varien_Simplexml_Config
     */
    protected function _getXmlConfig()
    {
        if ($this->getConfig() !== null)
            return $this->getConfig();

        $xmlConfig = new Varien_Simplexml_Config();
        $xmlConfig->loadString('<?xml version="1.0"?><jstranslator></jstranslator>');
        Mage::getConfig()->loadModulesConfiguration(self::JAVASCRIPT_TRANSLATE_CONFIG_FILENAME, $xmlConfig);

        $this->setConfig($xmlConfig);

        return $xmlConfig;
    }

    /**
     * Check if the current Magento version is below 1.7 or 1.12 for Magento community and enterprise, respectively. If
     * so, render the template. If not, return an empty string.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $helper = Mage::helper('postnl');
        if (!$helper->isEnterprise() && version_compare(Mage::getVersion(), '1.7.0.0', '>=')) {
            return '';
        }

        if ($helper->isEnterprise() && version_compare(Mage::getVersion(), '1.12.0.0', '>=')) {
            return '';
        }

        return parent::_toHtml();
    }
}