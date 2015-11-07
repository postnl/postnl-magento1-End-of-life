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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
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
        $helper = Mage::helper('postnl/payment');

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
         * COD is not available for virtual shipments.
         */
        if ($quote->isVirtual()) {
            $helper->log(
                $helper->__('PostNL COD is not available, because the order is virtual.')
            );
            return false;
        }

        /**
         * If COD is only available for PostNL shipping methods, we need to check if the shipping method is PostNL.
         */
        if (!(bool) $this->getConfigData('allow_for_non_postnl', $quote->getStoreId())) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
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
        $postnlOrder = Mage::getModel('postnl_core/order')->load($quote->getId(), 'quote_id');
        if ($postnlOrder->getType() == 'Sunday') {
            $helper->log(
                $helper->__('PostNL Cod is not available, because COD is not allowed in combination with Sunday Delivery.')
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
     * Overloaded to expand the CHECK_USE_FOR_COUNTRY check with the shipping address.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int|null               $checksBitMask
     *
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        if ($checksBitMask & self::CHECK_USE_FOR_COUNTRY) {
            if (!$this->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                return false;
            }
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

        if (Mage::helper('postnl')->isAdmin()) {
            $adminSession = Mage::getSingleton('adminhtml/session_quote');
            if ($adminSession && $adminSession->getStore() !== null) {
                $store = $adminSession->getStore();
            } else {
                $store = Mage::app()->getStore();
            }
        } else {
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
}