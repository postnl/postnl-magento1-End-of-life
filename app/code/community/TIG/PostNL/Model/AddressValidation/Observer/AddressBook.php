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
 * @method boolean                                                 hasAddressBlockClass()
 * @method TIG_PostNL_Model_AddressValidation_Observer_AddressBook setAddressBlockClass(string $value)
 */
class TIG_PostNL_Model_AddressValidation_Observer_AddressBook extends Varien_Object
{
    /**
     * The block class that we want to edit
     */
    const ADDRESS_COMMUNITY_BLOCK_NAME  = 'customer/address_edit';

    /**
     * Current environment for the postcode check
     */
    const POSTCODECHECK_ENV = 'addressbook';

    /**
     * Gets the classname for the addressbook block that we want to edit.
     *
     * @return string
     */
    public function getAddressBlockClass()
    {
        if ($this->hasAddressBlockClass()) {
            return $this->_getData('address_block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::ADDRESS_COMMUNITY_BLOCK_NAME);

        $this->setAddressBlockClass($blockClass);
        return $blockClass;
    }

    /**
     * Alters the template of the onepage checkout billing address block if the postcode check functionality is active.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return TIG_PostNL_Model_AddressValidation_Observer_AddressBook
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer checkout_onepage_billing_postcodecheck
     *
     */
    public function addressBookPostcodeCheck(Varien_Event_Observer $observer)
    {
        /**
         * Check if the extension is active
         */
        if (!Mage::helper('postnl/addressValidation')->isPostcodeCheckEnabled(null, self::POSTCODECHECK_ENV)) {
            return $this;
        }

        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         *
         * @var Mage_Customer_Block_Address_Edit $block
         */
        $block = $observer->getBlock();
        $blockClass = $this->getAddressBlockClass();

        if (get_class($block) !== $blockClass) {
            return $this;
        }

        $block->setTemplate('TIG/PostNL/address_validation/customer/address/edit.phtml');

        return $this;
    }
}
