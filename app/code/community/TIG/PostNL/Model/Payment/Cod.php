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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Payment_Cod extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Xpath to PostNL COD settings. N.B. the last part is missing.
     */
    const XPATH_COD_SETTINGS = 'postnl/cod';

    /**
     * Xpath to the PostNL COD fee setting.
     */
    const XPATH_COD_FEE = 'payment/postnl_cod/fee';

    /**
     * Xpath to the 'allow_for_buspakje' configuration setting.
     */
    const XPATH_ALLOW_FOR_BUSPAKJE = 'payment/postnl_cod/allow_for_buspakje';

    /**
     * This payment method's unique code.
     *
     * @var string
     */
    protected $_code = 'postnl_cod';

    /**
     * Cash On Delivery form block path.
     *
     * @var string
     */
    protected $_formBlockType = 'postnl_payment/form_cod';

    /**
     * Cash on delivery info block path.
     *
     * @var string
     */
    protected $_infoBlockType = 'postnl_payment/info';

    /**
     * Payment Method features.
     *
     * @var boolean
     */
    protected $_isGateway                   = false;

    /**
     * @var boolean
     */
    protected $_canOrder                    = false;

    /**
     * @var boolean
     */
    protected $_canAuthorize                = true;

    /**
     * @var boolean
     */
    protected $_canCapture                  = false;

    /**
     * @var boolean
     */
    protected $_canCapturePartial           = false;

    /**
     * @var boolean
     */
    protected $_canCaptureOnce              = false;

    /**
     * @var boolean
     */
    protected $_canRefund                   = false;

    /**
     * @var boolean
     */
    protected $_canRefundInvoicePartial     = false;

    /**
     * @var boolean
     */
    protected $_canVoid                     = false;

    /**
     * @var boolean
     */
    protected $_canUseInternal              = true;

    /**
     * @var boolean
     */
    protected $_canUseCheckout              = true;

    /**
     * @var boolean
     */
    protected $_canUseForMultishipping      = false;

    /**
     * @var boolean
     */
    protected $_isInitializeNeeded          = false;

    /**
     * @var boolean
     */
    protected $_canFetchTransactionInfo     = false;

    /**
     * @var boolean
     */
    protected $_canReviewPayment            = false;

    /**
     * @var boolean
     */
    protected $_canCreateBillingAgreement   = false;

    /**
     * @var boolean
     */
    protected $_canManageRecurringProfiles  = false;

    /**
     * @var array
     */
    protected $_helpers                     = array();

    /**
     * @var array
     */
    protected $_models                      = array();

    /**
     * @var boolean
     */

    /**
     * Get instructions text from config.
     *
     * @return string
     */
    public function getInstructions()
    {
        $instructions = trim($this->getConfigData('instructions'));

        return $instructions;
    }

    /**
     * Checks whether PostNL COD is available.
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        /** @var TIG_PostNL_Helper_Payment $helper */
        $helper = $this->getHelper('postnl/payment');

        /**
         * Make sure the quote is available.
         */
        if (is_null($quote)) {
            $helper->log(
                $helper->__('PostNL COD is not available, because the quote is empty.')
            );
            return false;
        }

        /**
         * Check if this payment method is active.
         */
        if (!(bool)$this->getConfigData('active', $quote->getStoreId())) {
            return false;
        }

        /**
         * COD is not available for virtual shipments.
         */
        if ($quote->isVirtual()) {
            $helper->log(
                $helper->__('PostNL COD is not available, because the order is virtual.')
            );
            return false;
        }

        /**
         * COD is not available for Food shipments.
         */
        if ($helper->quoteIsFood($quote)) {
            return false;
        }

        /**
         * If COD is only available for PostNL shipping methods, we need to check if the shipping method is PostNL.
         */
        if (!(bool) $this->getConfigData('allow_for_non_postnl', $quote->getStoreId())) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

            /**
             * If the shipping method is not set, we won't check it.
             */
            if ($shippingMethod === null) {
                return false;
            }

            /** @var TIG_PostNL_Helper_Carrier $carrierHelper */
            $carrierHelper = $this->getHelper('postnl/carrier');
            if (!$carrierHelper->isPostnlShippingMethod($shippingMethod)) {
                $helper->log(
                    $helper->__('PostNL COD is not available, because the chosen shipping method is not PostNL.')
                );
                return false;
            }
        }

        /**
         * Make sure all required fields are entered.
         */
        $codSettings = Mage::getStoreConfig(self::XPATH_COD_SETTINGS, Mage::app()->getStore()->getId());

        if (empty($codSettings['account_name'])
            || empty($codSettings['iban'])
            || empty($codSettings['bic'])
        ) {
            $helper->log(
                $helper->__('PostNL COD is not available, because required fields are missing.')
            );
            return false;
        }

        /**
         * Check that the shipping address isn't a P.O. box. Unfortunately we can only check this by checking if the
         * street name contains the word 'postbus' (dutch for P.O. box).
         */
        $shippingAddress = $quote->getShippingAddress();
        $fullStreet = $shippingAddress->getStreetFull();
        if (stripos($fullStreet, 'postbus') !== false) {
            $helper->log(
                $helper->__('PostNL COD is not available, because the shipping address is a P.O. box.')
            );
            return false;
        }

        /**
         * Check that the destination country is allowed.
         */
        if (!$this->canUseForCountry($shippingAddress->getCountry())) {
            $helper->log(
                $helper->__('PostNL COD is not available, because the shipping destination country is not allowed.')
            );
            return false;
        }

        /**
         * Check if the delivery type is not a Sunday Delivery, since COD is not available for Sunday delivery
         */
        /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
        $postnlOrder = $this->getModel('postnl_core/order')->load($quote->getId(), 'quote_id');
        if ($postnlOrder->getType() == 'Sunday') {
            $helper->log(
                $helper->__('PostNL Cod is not available, because COD is not allowed in combination with Sunday Delivery.')
            );
            return false;
        }

        /**
         * Check if COD is available in combination with Buspakje.
         */
        if (!$this->canShowForBuspakje()) {
            $helper->log(
                $helper->__('PostNL Cod is not for Buspakje shipments.')
            );
            return false;
        }

        /**
         * Finally, perform Magento's own checks.
         */
        $parentIsAvailable = parent::isAvailable($quote);
        if (!$parentIsAvailable) {
            $helper->log(
                $helper->__("PostNL COD is not available, because the base isAvailable() check returned 'false'")
            );
        }

        return $parentIsAvailable;
    }

    /**
     * Check whether payment method is applicable to quote.
     * Purposed to allow use in controllers some logic that was implemented in blocks only before.
     *
     * Overloaded to replace the CHECK_USE_FOR_COUNTRY check with the shipping address.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int|null               $checksBitMask
     *
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        if ($checksBitMask & self::CHECK_USE_FOR_COUNTRY) {
            if (!$this->canUseForCountry($quote->getShippingAddress()->getCountry())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
            if (!$this->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_CHECKOUT) {
            if (!$this->canUseCheckout()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_MULTISHIPPING) {
            if (!$this->canUseForMultishipping()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_INTERNAL) {
            if (!$this->canUseInternal()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ORDER_TOTAL_MIN_MAX) {
            $total = $quote->getBaseGrandTotal();
            $minTotal = $this->getConfigData('min_order_total');
            $maxTotal = $this->getConfigData('max_order_total');
            if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_RECURRING_PROFILES) {
            if (!$this->canManageRecurringProfiles() && $quote->hasRecurringItems()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ZERO_TOTAL) {
            $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
            if ($total < 0.0001 && $this->getCode() != 'free'
                && !($this->canManageRecurringProfiles() && $quote->hasRecurringItems())
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the payment method title.
     *
     * @return string
     */
    public function getTitle()
    {
        $title = parent::getTitle();

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if ($helper->isAdmin()) {
            /** @var Mage_Adminhtml_Model_Session_Quote $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session_quote');
            if ($adminSession && $adminSession->getStore() !== null) {
                $store = $adminSession->getStore();
            } else {
                $store = Mage::app()->getStore();
            }
        } else {
            /** @var Mage_Checkout_Model_Session $checkoutSession */
            $checkoutSession = Mage::getSingleton('checkout/session');
            if ($checkoutSession && $checkoutSession->getQuote()) {
                $store = $checkoutSession->getQuote()->getStore();
            } else {
                $store = Mage::app()->getStore();
            }
        }

        /**
         * Get the fee from the config and convert and format it according to the chosen currency and locale.
         */
        $fee = Mage::getStoreConfig(self::XPATH_COD_FEE, $store);
        $fee = $store->convertPrice($fee, true, false);

        /**
         * Replace any parameters in the title with the fee.
         */
        $title = sprintf($title, $fee);
        return $title;
    }

    /**
     * Check if the PostNL COD payment method may be shown for letter box parcel orders.
     *
     * @return boolean
     */
    protected function canShowForBuspakje()
    {
        /**
         * Check the configuration setting.
         */
        $showForBuspakje = Mage::getStoreConfigFlag(self::XPATH_ALLOW_FOR_BUSPAKJE, Mage::app()->getStore()->getId());
        if ($showForBuspakje) {
            return true;
        }

        /**
         * Check if the buspakje calculation mode is set to automatic.
         */
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $calculationMode = $helper->getBuspakjeCalculationMode();
        if ($calculationMode != 'automatic') {
            return true;
        }

        /**
         * Check if the current quote fits as a letter box parcel.
         */
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        if (!$helper->fitsAsBuspakje($quote->getAllItems())) {
            return true;
        }

        return false;
    }

    /**
     * @param $helper
     *
     * @return Mage_Core_Helper_Data
     */
    protected function getHelper($helper)
    {
        if (!array_key_exists($helper, $this->_helpers)) {
            $this->_helpers[$helper] = Mage::helper($helper);
        }

        return $this->_helpers[$helper];
    }

    /**
     * @param $model
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function getModel($model)
    {
        if (!array_key_exists($model, $this->_models)) {
            $this->_models[$model] = Mage::getModel($model);
        }

        return $this->_models[$model];
    }
}
