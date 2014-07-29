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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_Sales_Items_Column_Qty extends Mage_Adminhtml_Block_Sales_Items_Column_Qty
{
    /**
     * The original template used by Magento.
     */
    const DEFAULT_TEMPLATE = 'adminhtml/sales_items_renderer_default';

    /**
     * Gets the maximum qty allowed for buspakje.
     *
     * @return int|string
     */
    public function getMaxQtyForBuspakje()
    {
        /**
         * @var Mage_Sales_Model_Order_Item $item
         */
        $item = $this->getItem();
        $maxQty = Mage::getResourceModel('catalog/product')->getAttributeRawValue(
            $item->getProductId(),
            'postnl_max_qty_for_buspakje',
            $item->getStoreId()
        );

        return $maxQty;
    }

    /**
     * Before rendering the template, check that the PostNl extension is active. If not, use the default Magento
     * template.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('postnl')->isEnabled($this->getItem()->getStoreId())) {
            $this->setTemplate(self::DEFAULT_TEMPLATE);
        }

        return parent::_toHtml();
    }
}