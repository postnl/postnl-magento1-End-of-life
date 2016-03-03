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
 * @method boolean                                             hasBillingAddressBlockClass()
 * @method TIG_PostNL_Model_AddressValidation_Observer_Onepage setBillingAddressBlockClass(string $value)
 * @method boolean                                             hasShippingAddressBlockClass()
 * @method TIG_PostNL_Model_AddressValidation_Observer_Onepage setShippingAddressBlockClass(string $value)
 */
class TIG_PostNL_Model_AddressValidation_Observer_Onepage extends Varien_Object
{
    /**
     * The block classes that we want to edit
     */
    const BILLING_ADDRESS_BLOCK_NAME = 'checkout/onepage_billing';
    const SHIPPING_ADDRESS_BLOCK_NAME = 'checkout/onepage_shipping';

    /**
     * Current environment for the postcode check
     */
    const POSTCODECHECK_ENV = 'checkout';

    /**
     * Gets the classname for the onepage checkout billing address block that we want to alter
     *
     * @return string
     */
    public function getBillingAddressBlockClass()
    {
        if ($this->hasBillingAddressBlockClass()) {
            return $this->_getData('billing_address_block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::BILLING_ADDRESS_BLOCK_NAME);

        $this->setBillingAddressBlockClass($blockClass);
        return $blockClass;
    }

    /**
     * Gets the classname for the onepage checkout shipping address block that we want to alter
     *
     * @return string
     */
    public function getShippingAddressBlockClass()
    {
        if ($this->hasShippingAddressBlockClass()) {
            return $this->_getData('shipping_address_block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::SHIPPING_ADDRESS_BLOCK_NAME);

        $this->setShippingAddressBlockClass($blockClass);
        return $blockClass;
    }

    /**
     * Alters the template of the onepage checkout billing address block if the postcode check functionality is active.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer checkout_onepage_billing_postcodecheck
     *
     */
    public function billingAddressPostcodeCheck(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         *
         * @var Mage_Checkout_Block_Onepage_Billing $block
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $block      = $observer->getBlock();
        $blockClass = $this->getBillingAddressBlockClass();

        if (get_class($block) !== $blockClass) {
            return $this;
        }

        if (!$block->getChild('postnl_billing_postcodecheck')) {
            return $this;
        }

        /**
         * Check if the extension is active.
         */
        /** @var TIG_PostNL_Helper_AddressValidation $helper */
        $helper = Mage::helper('postnl/addressValidation');
        if (!$helper->isPostcodeCheckEnabled(null, self::POSTCODECHECK_ENV)) {
            return $this;
        }

        $block->setTemplate('TIG/PostNL/address_validation/checkout/onepage/billing.phtml');

        return $this;
    }

    /**
     * Alters the template of the onepage checkout shipping address block if the postcode check functionality is active.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer checkout_onepage_shipping_postcodecheck
     *
     */
    public function shippingAddressPostcodeCheck(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         *
         * @var Mage_Checkout_Block_Onepage_Shipping $block
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $block      = $observer->getBlock();
        $blockClass = $this->getShippingAddressBlockClass();

        if (get_class($block) !== $blockClass) {
            return $this;
        }

        if (!$block->getChild('postnl_shipping_postcodecheck')) {
            return $this;
        }

        /**
         * Check if the extension is active
         */
        /** @var TIG_PostNL_Helper_AddressValidation $helper */
        $helper = Mage::helper('postnl/addressValidation');
        if (!$helper->isPostcodeCheckEnabled(null, self::POSTCODECHECK_ENV)) {
            return $this;
        }

        $block->setTemplate('TIG/PostNL/address_validation/checkout/onepage/shipping.phtml');

        return $this;
    }
}
