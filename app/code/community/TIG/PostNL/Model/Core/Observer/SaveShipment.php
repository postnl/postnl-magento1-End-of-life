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
class TIG_PostNL_Model_Core_Observer_SaveShipment
{
    /**
     * Registers a chosen product option
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event controller_action_predispatch_adminhtml_sales_order_shipment_save
     *
     * @observer postnl_core_shipment_save
     */
    public function registerProductOption(Varien_Event_Observer $observer)
    {
        /**
         * Check if the PostNL module is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }

        /**
         * retrieve and register the chosen option, if any.
         *
         * @var Mage_Core_Controller_Varien_Front $controller
         */
        $controller = $observer->getControllerAction();
        $productOption = $controller->getRequest()->getParam('postnl');

        if ($productOption && isset($productOption['product_option'])) {
            Mage::register('postnl_product_option', $productOption['product_option']);
            unset($productOption['product_option']);
        }

        if ($productOption && !empty($productOption)) {
            Mage::register('postnl_additional_options', $productOption);
        }

        return $this;
    }
}
