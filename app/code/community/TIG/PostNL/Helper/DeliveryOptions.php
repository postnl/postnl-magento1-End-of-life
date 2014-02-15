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
class TIG_PostNL_Helper_DeliveryOptions extends TIG_PostNL_Helper_Checkout
{
    /**
     * Checks if PakjeGemak is available.
     *
     * @param int|boolean $storeId
     *
     * @return boolean
     */
    public function canUsePakjeGemak($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        return true;
    }

    /**
     * Checks if PakjeGemak Express is available.
     *
     * @param int|boolean $storeId
     *
     * @return boolean
     */
    public function canUsePakjeGemakExpress($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        return true;
    }

    /**
     * Checks if 'pakket automaat' is available.
     *
     * @param int|boolean $storeId
     *
     * @return boolean
     */
    public function canUsePakketAutomaat($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        return true;
    }

    /**
     * Check if PostNL delivery options may be used based on a quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param boolean $sendPing
     *
     * @return boolean
     */
    public function canUseDeliveryOptions(Mage_Sales_Model_Quote $quote, $sendPing = false)
    {
        if (Mage::registry('can_use_delivery_options') !== null) {
            return Mage::registry('can_use_delivery_options');
        }

        $deliveryOptionsEnabled = $this->isDeliveryOptionsEnabled();
        if (!$deliveryOptionsEnabled) {
            Mage::register('can_use_delivery_options', false);
            return false;
        }

        /**
         * PostNL delivery options cannot be used for virtual orders
         */
        if ($quote->isVirtual()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0104',
                    'message' => $this->__('The quote is virtual.'),
                )
            );
            Mage::register('can_use_delivery_options', false);
            return false;
        }

        /**
         * Check if the quote has a valid minimum amount
         */
        if (!$quote->validateMinimumAmount()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0105',
                    'message' => $this->__("The quote's grand total is below the minimum amount required."),
                )
            );
            Mage::register('can_use_delivery_options', false);
            return false;
        }

        /**
         * Check that dutch addresses are allowed
         */
        if (!$this->canUseStandard()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0106',
                    'message' => $this->__('No standard product options are enabled. At least 1 option must be active.'),
                )
            );
            Mage::register('can_use_delivery_options', false);
            return false;
        }

        $storeId = $quote->getStoreId();

        /**
         * Check if PostNL Checkout may be used for 'letter' orders and if not, if the quote could fit in an envelope
         */
        $showCheckoutForLetters = Mage::getStoreConfigFlag(self::XML_PATH_SHOW_CHECKOUT_FOR_LETTER, $storeId);
        if (!$showCheckoutForLetters) {
            $isLetterQuote = $this->quoteIsLetter($quote, $storeId);
            if ($isLetterQuote) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0101',
                        'message' => $this->__("The quote's total weight is below the miniumum required to use PostNL Checkout."),
                    )
                );
                Mage::register('can_use_delivery_options', false);
                return false;
            }
        }

        /**
         * Check if PostNL Checkout may be used for out-og-stock orders and if not, whether the quote has any such products
         */
        $showCheckoutForBackorders = Mage::getStoreConfigFlag(self::XML_PATH_SHOW_CHECKOUT_FOR_BACKORDERS, $storeId);
        if (!$showCheckoutForBackorders) {
            $containsOutOfStockItems = $this->quoteHasOutOfStockItems($quote);
            if ($containsOutOfStockItems) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0102',
                        'message' => $this->__('One or more items in the cart are out of stock.'),
                    )
                );
                Mage::register('can_use_delivery_options', false);
                return false;
            }
        }

        if ($sendPing === true) {
            /**
             * Send a ping request to see if the PostNL Checkout service is available
             */
            try {
                $cif = Mage::getModel('postnl_checkout/cif');
                $result = $cif->ping();
            } catch (Exception $e) {
                $this->logException($e);
                Mage::register('can_use_delivery_options', false);
                return false;
            }

            if ($result !== 'OK') {
                Mage::register('can_use_delivery_options', false);
                return false;
            }
        }

        Mage::register('can_use_delivery_options', true);
        return true;
    }

    /**
     * Check if the module is set to test mode
     *
     * @return boolean
     */
    public function isTestMode($storeId = false)
    {
        if (Mage::registry('delivery_options_test_mode') !== null) {
            return Mage::registry('delivery_options_test_mode');
        }

        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $testModeAllowed = $this->isTestModeAllowed();
        if (!$testModeAllowed) {
            Mage::register('delivery_options_test_mode', false);
            return false;
        }

        $testMode = Mage::getStoreConfigFlag(self::XML_PATH_TEST_MODE, $storeId);

        Mage::register('delivery_options_test_mode', $testMode);
        return $testMode;
    }

    /**
     * Checks if PostNL Checkout is active
     *
     * @param null|int $storeId
     *
     * @return boolean
     */
    public function isDeliveryOptionsEnabled($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        return true;
    }
}
