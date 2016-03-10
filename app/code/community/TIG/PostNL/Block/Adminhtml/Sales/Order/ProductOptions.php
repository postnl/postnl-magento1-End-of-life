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
 *
 * @method boolean                                               hasExtraCoverProductOptions()
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_ProductOptions setExtraCoverProductOptions(array $value)
 * @method boolean                                               hasGlobalpackProductOption()
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_ProductOptions setGlobalpackProductOption(array $value)
 */
class TIG_PostNL_Block_Adminhtml_Sales_Order_ProductOptions extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_sales_order_productoptions';

    /**
     * Get available product options
     *
     * @return array
     */
    public function getExtraCoverProductOptions()
    {
        if ($this->hasExtraCoverProductOptions()) {
            return $this->_getData('extra_cover_product_options');
        }

        /** @var TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions $sourceModel */
        $sourceModel = Mage::getModel('postnl_core/system_config_source_allProductOptions');
        $productOptions = $sourceModel->getExtraCoverOptions(true);

        $this->setExtraCoverProductOptions($productOptions);
        return $productOptions;
    }

    /**
     * Get available GlobalPack product option
     *
     * @return string
     */
    public function getGlobalPackProductOption()
    {
        if ($this->hasGlobalpackProductOption()) {
            return $this->_getData('globalpack_product_option');
        }

        /** @var TIG_PostNL_Model_Core_System_Config_Source_GlobalProductOptions $sourceModel */
        $sourceModel = Mage::getModel('postnl_core/system_config_source_globalProductOptions');
        $globalPackProductOption = $sourceModel->getAvailableOptions();

        if (empty($globalPackProductOption)) {
            return '';
        }

        $optionValue = current($globalPackProductOption)['value'];
        $this->setGlobalpackProductOption($optionValue);
        return $optionValue;
    }

    /**
     * Gets an array of shipment types for use with GlobalPack shipments
     *
     * @return array
     */
    public function getShipmentTypes()
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $shipmentTypes = $helper->getShipmentTypes();

        return $shipmentTypes;
    }

    /**
     * Check if the PostNL module is enabled before rendering
     *
     * @return string|parent::_toHtml()
     *
     * @see Mage_Adminhtml_Block_Abstract::_toHtml()
     */
    protected function _toHtml()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if (!$helper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
