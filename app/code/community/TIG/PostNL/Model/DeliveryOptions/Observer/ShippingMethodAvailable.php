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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_DeliveryOptions_Observer_ShippingMethodAvailable extends Varien_Object
{
    /**
     * The block class that we want to edit.
     */
    const BLOCK_NAME = 'checkout/onepage_shipping_method_available';

    /**
     * Gets the classname for the block that we want to alter.
     *
     * @return string
     */
    public function getBlockClass()
    {
        if ($this->hasData('block_class')) {
            return $this->getData('block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::BLOCK_NAME);

        $this->setData('block_class', $blockClass);
        return $blockClass;
    }

    /**
     * Alters the template of the onepage checkout shipping method available block so that we can display our delivery
     * options.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return TIG_PostNL_Model_DeliveryOptions_Observer_Onepage
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer checkout_onepage_billing_postcodecheck
     *
     */
    public function addDeliveryOptions(Varien_Event_Observer $observer)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $canUseDeliveryOptions = Mage::helper('postnl/deliveryOptions')->canUseDeliveryOptions($quote);
        if (!$canUseDeliveryOptions) {
            return $this;
        }

        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block
         *
         * @var Mage_Core_Block_Abstract $block
         */
        $block = $observer->getBlock();
        $blockClass = $this->getBlockClass();

        if (get_class($block) !== $blockClass) {
            return $this;
        }

        /**
         * Get the template for the current module.
         */
        $template = 'TIG/PostNL/delivery_options/onepage/available.phtml';
        if (Mage::app()->getRequest()->getModuleName() == 'onestepcheckout') {
            $template = 'TIG/PostNL/delivery_options/onestepcheckout/available.phtml';
        }

        /**
         * @var Mage_Checkout_Block_Onepage_Shipping_Method_Available $block
         */
        $block->setTemplate($template);

        return $this;
    }
}