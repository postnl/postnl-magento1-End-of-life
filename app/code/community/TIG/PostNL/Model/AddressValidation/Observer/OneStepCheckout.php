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
 *
 * @method boolean                                                     hasAddressBlockClass()
 * @method TIG_PostNL_Model_AddressValidation_Observer_OneStepCheckout setAddressBlockClass(string $value)
 */
class TIG_PostNL_Model_AddressValidation_Observer_OneStepCheckout extends Varien_Object
{
    /**
     * The block class that we want to edit
     */
    const ADDRESS_BLOCK_NAME = 'onestepcheckout/fields';

    /**
     * Block aliases used by OneStepCheckout.
     *
     * We use these to determine whether the block is for the billing or shipping address.
     */
    const BILLING_ADDRESS_BLOCK_ALIAS  = 'billing_address';
    const SHIPPING_ADDRESS_BLOCK_ALIAS = 'shipping_address';

    /**
     * Current environment for the postcode check
     */
    const POSTCODECHECK_ENV = 'checkout';

    /**
     * Gets the classname for the address block that we want to edit
     *
     * @return string
     */
    public function getAddressBlockClass()
    {
        if ($this->hasAddressBlockClass()) {
            return $this->_getData('address_block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::ADDRESS_BLOCK_NAME);

        $this->setAddressBlockClass($blockClass);
        return $blockClass;
    }

    /**
     * Alters the template of the OneStepCheckout address block if the postcode check functionality is active.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return TIG_PostNL_Model_AddressValidation_Observer_OneStepCheckout
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer customer_address_edit_postcodecheck
     */
    public function addressPostcodeCheck(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         *
         * @var Idev_OneStepCheckout_Block_Fields $block
         */
        $block = $observer->getBlock();
        $blockClass = $this->getAddressBlockClass();

        if (get_class($block) !== $blockClass) {
            return $this;
        }

        if (!$block->getChild('postnl_billing_postcodecheck') && !$block->getChild('postnl_shipping_postcodecheck')) {
            return $this;
        }

        /**
         * Check if the extension is active
         */
        if (!Mage::helper('postnl/addressValidation')->isPostcodeCheckEnabled(null, self::POSTCODECHECK_ENV)) {
            return $this;
        }

        /**
         * Get the blocks alias and alter it's template based on this
         */
        $blockAlias = $block->getBlockAlias();
        switch ($blockAlias) {
            case self::BILLING_ADDRESS_BLOCK_ALIAS:
                $block->setTemplate('TIG/PostNL/address_validation/onestepcheckout/billing_fields.phtml');
                break;
            case self::SHIPPING_ADDRESS_BLOCK_ALIAS:
                $block->setTemplate('TIG/PostNL/address_validation/onestepcheckout/shipping_fields.phtml');
                break;
            //no default
        }

        return $this;
    }
}
