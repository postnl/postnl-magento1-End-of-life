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
 * @method boolean                                      hasLoginBlockClass()
 * @method TIG_PostNL_Model_Mijnpakket_Observer_Onepage setLoginBlockClass(string $value)
 * @method boolean                                      hasSuccessBlockClass()
 * @method TIG_PostNL_Model_Mijnpakket_Observer_Onepage setSuccessBlockClass(string $value)
 */
class TIG_PostNL_Model_Mijnpakket_Observer_Onepage extends Varien_Object
{
    /**
     * The block class that we want to edit.
     */
    const SUCCESS_BLOCK_NAME = 'checkout/onepage_success';

    /**
     * Xpath to the MijnPakket notification setting.
     */
    const XPATH_MIJNPAKKET_NOTIFICATION = 'postnl/delivery_options/mijnpakket_notification';

    /**
     * The new template.
     */
    const ACCOUNT_NOTIFICATION_TEMPLATE = 'TIG/PostNL/mijnpakket/onepage/success.phtml';

    /**
     * Gets the classname for the checkout success block that we want to alter.
     *
     * @return string
     */
    public function getSucessBlockClass()
    {
        if ($this->hasSuccessBlockClass()) {
            return $this->_getData('success_block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::SUCCESS_BLOCK_NAME);

        $this->setSuccessBlockClass($blockClass);
        return $blockClass;
    }

    /**
     * Replace the onepage checkout success template.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer checkout_onepage_mijnpakket_success
     */
    public function addAccountNotification(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         *
         * @var Mage_Core_Block_Abstract $block
         */
        $block      = $observer->getBlock();
        $blockClass = $this->getSucessBlockClass();

        if (!$block || !is_object($block) || get_class($block) != $blockClass) {
            return $this;
        }

        if (!Mage::helper('postnl/deliveryOptions')->canUseDeliveryOptions()) {
            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();
        $canShowNotification = Mage::getStoreConfigFlag(self::XPATH_MIJNPAKKET_NOTIFICATION, $storeId);

        if (!$canShowNotification) {
            return $this;
        }

        /**
         * @var Mage_Checkout_Block_Onepage_Success $block
         */
        $block->setTemplate(self::ACCOUNT_NOTIFICATION_TEMPLATE);

        return $this;
    }
}