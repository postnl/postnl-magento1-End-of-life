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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_DeliveryOptions_System_Config_Source_Themes extends Varien_Object
{
    /**
     * Returns a list of supported themes.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $themes = array();

        $config = Mage::getConfig()->getNode('tig/delivery_options/themes');
        if (!$config || !$config->hasChildren()) {
            return $themes;
        }

        /**
         * @var Varien_Simplexml_Element $child
         */
        foreach ($config->children() as $name => $child) {
            $child = $this->_translateConfig($child);

            /** @noinspection PhpUndefinedFieldInspection */
            $themes[] = array(
                'label' => (string) $child->label,
                'value' => $name,
            );
        }

        return $themes;
    }

    /**
     * Translate a config element based on the 'translate' and 'module'attributes.
     *
     * @param Varien_Simplexml_Element $config
     *
     * @return Varien_Simplexml_Element
     */
    protected function _translateConfig(Varien_Simplexml_Element $config)
    {
        $translate = $config->getAttribute('translate');
        if (!$translate) {
            return $config;
        }

        $module = $config->getAttribute('module');
        if ($module) {
            $helper = Mage::helper($module);
        } else {
            $helper = Mage::helper('core');
        }

        $translate = explode(' ', $translate);

        /**
         * @var Varien_Simplexml_Element $value
         */
        foreach ($config->children() as $name => $value) {
            if ($value->children()) {
                continue;
            }

            if (!in_array($name, $translate)) {
                continue;
            }

            $translatedValue = $helper->__((string) $value);
            $config->$name = $translatedValue;
        }

        return $config;
    }
}
