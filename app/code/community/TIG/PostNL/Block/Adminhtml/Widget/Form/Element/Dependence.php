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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * Form element dependencies mapper
 * Assumes that one element may depend on other element values.
 * Will toggle as "enabled" only if all elements it depends from toggle as true.
 */
class TIG_PostNL_Block_Adminhtml_Widget_Form_Element_Dependence
    extends Mage_Adminhtml_Block_Widget_Form_Element_Dependence
{
    /**
     * Register field name dependence one from each other by specified values
     *
     * @param string $fieldName
     * @param string $fieldNameFrom
     * @param string|array $refValues
     * @return Mage_Adminhtml_Block_Widget_Form_Element_Dependence
     */
    public function addFieldDependence($fieldName, $fieldNameFrom, $refValues)
    {
        $this->_depends[$fieldName][$fieldNameFrom] = $refValues;
        return $this;
    }

    /**
     * HTML output getter
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_depends) {
            return '';
        }
        return '<script type="text/javascript">'
            . 'var formElementDependenceController = new FormElementDependenceController('
            . $this->_getDependsJson()
            . ($this->_configOptions ? ', ' . Mage::helper('core')->jsonEncode($this->_configOptions) : '')
            . '); </script>';
    }
}
