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
class TIG_PostNL_Helper_DeliveryOptions extends TIG_PostNL_Helper_Checkout
{
    /**
     * Xpath to delivery options enabled config setting.
     */
    const XPATH_DELIVERY_OPTIONS_ACTIVE = 'postnl/delivery_options/delivery_options_active';

    /**
     * Xpaths to various possible delivery option settings.
     */
    const XPATH_ENABLE_PAKJEGEMAK               = 'postnl/delivery_options/enable_pakjegemak';
    const XPATH_ENABLE_PAKJEGEMAK_EXPRESS       = 'postnl/delivery_options/enable_pakjegemak_express';
    const XPATH_ENABLE_PAKKETAUTOMAAT_LOCATIONS = 'postnl/delivery_options/enable_pakketautomaat_locations';
    const XPATH_ENABLE_TIMEFRAMES               = 'postnl/delivery_options/enable_timeframes';
    const XPATH_ENABLE_EVENING_TIMEFRAMES       = 'postnl/delivery_options/enable_evening_timeframes';

    /**
     * Xpaths to various business rule settings.
     */
    const XPATH_SHOW_OPTIONS_FOR_LETTER     = 'postnl/delivery_options/show_options_for_letter';
    const XPATH_SHOW_OPTIONS_FOR_BACKORDERS = 'postnl/delivery_options/show_options_for_backorders';
    const XPATH_ALLOW_SUNDAY_SORTING        = 'postnl/delivery_options/allow_sunday_sorting';

    /**
     * Xpaths to extra fee config settings.
     */
    const XPATH_EVENING_TIMEFRAME_FEE  = 'postnl/delivery_options/evening_timeframe_fee';
    const XPATH_PAKJEGEMAK_EXPRESS_FEE = 'postnl/delivery_options/pakjegemak_express_fee';
    /**
     * Xpath for shipping duration setting.
     */
    const XPATH_SHIPPING_DURATION = 'postnl/delivery_options/shipping_duration';

    /**
     * The time (as H * 100 + i) we consider to be the start of the evening.
     */
    const EVENING_TIME = 1900;

    /**
     * @var array
     */
    protected $_validTypes = array(
        'Overdag',
        'Avond',
        'PG',
        'PGE',
        'PA',
    );

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * @return array
     */
    public function getValidTypes()
    {
        return $this->_validTypes;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_quote) {
            return $this->_quote;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $this->_quote = $quote;
        return $quote;
    }

    /**
     * Get the fee charged for evening timeframes.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     *
     * @return float
     */
    public function getEveningFee($formatted = false, $includingTax = true)
    {
        $storeId = Mage::app()->getStore()->getId();

        $eveningFee = (float) Mage::getStoreConfig(self::XPATH_EVENING_TIMEFRAME_FEE, $storeId);

        $price = $this->getPriceWithTax($eveningFee, $includingTax, $formatted);

        return $price;
    }

    /**
     * Get the fee charged for PakjeGemak Express.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     *
     * @return float
     */
    public function getExpressFee($formatted = false, $includingTax = true)
    {
        $storeId = Mage::app()->getStore()->getId();

        $expressFee = (float) Mage::getStoreConfig(self::XPATH_PAKJEGEMAK_EXPRESS_FEE, $storeId);

        $price = $this->getPriceWithTax($expressFee, $includingTax, $formatted);

        return $price;
    }

    /**
     * Get the Shipping date for a specified order date.
     *
     * @param null|string $orderDate
     * @param null|int    $storeId
     *
     * @return bool|string
     */
    public function getShippingDate($orderDate = null, $storeId = null)
    {
        if ($orderDate === null) {
            $orderDate = date('Y-m-d');
        }

        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $shippingDuration = Mage::getStoreConfig(self::XPATH_SHIPPING_DURATION, $storeId);
        $deliveryTime = strtotime("+{$shippingDuration} days", strtotime($orderDate));
        $deliveryDay = date('N', $deliveryTime);

        if ($deliveryDay == 1 && !Mage::helper('postnl/deliveryOptions')->canUseSundaySorting()) {
            $deliveryTime = strtotime('+1 day', $deliveryTime);
        }

        $deliveryDate = date('Y-m-d', $deliveryTime);
        return $deliveryDate;
    }

    /**
     * Gets the shipping duration for the specified quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return int
     *
     * @throws TIG_PostNL_Exception
     */
    public function getShippingDuration(Mage_Sales_Model_Quote $quote)
    {
        $storeId = $quote->getStoreId();

        /**
         * Get the default config duration.
         */
        $configDuration = (int) Mage::getStoreConfig(self::XPATH_SHIPPING_DURATION, $storeId);
        $durationArray  = array($configDuration);

        /**
         * Loop through all products in the quote.
         *
         * @var Mage_Sales_Model_Quote_Item $item
         */
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            /**
             * If the product has a specific shipping duration, add it to the array of durations.
             */
            if ($product->hasPostnlShippingDuration() && $product->getPostnlShippingDuration() !== '') {
                $durationArray[] = (int) $product->getPostnlShippingDuration();
            }
        }

        /**
         * Sort the array and get it's last item. This will be the highest value.
         */
        natsort($durationArray);
        $shippingDuration = end($durationArray);

        /**
         * Make sure the value is between 1 and 14 days.
         */
        if ($shippingDuration > 14 || $shippingDuration < 1) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Invalid shipping duration: %s. Shipping duration must be between 1 and 14 days.',
                    $shippingDuration
                ),
                'POSTNL-0127'
            );
        }

        return $shippingDuration;
    }

    /**
     * Convert a value to a formatted price.
     *
     * @param float   $price
     * @param boolean $includingTax
     * @param boolean $formatted
     *
     * @return float
     *
     * @see Mage_Checkout_Block_Onepage_Shipping_Method_Available::getShippingPrice()
     */
    public function getPriceWithTax($price, $includingTax, $formatted = false)
    {
        $quote = $this->getQuote();
        $store = $quote->getStore();

        $shippingPrice  = Mage::helper('tax')->getShippingPrice($price, $includingTax, $quote->getShippingAddress());
        $convertedPrice = $store->convertPrice($shippingPrice, $formatted, false);

        return $convertedPrice;
    }

    /**
     * Mark a set of location results with the 'isEvening' parameter. This will allow the google maps api to easily
     * identify which locations may be filtered out later.
     *
     * @param array  $locations    An array of PostNL location objects
     * @param string $deliveryDate The date on which the package should be delivered.
     *
     * @return array
     */
    public function markEveningLocations($locations, $deliveryDate)
    {
        /**
         * Get the day of the week on which the package should be delivered.
         *
         * date('l') returns the full textual representation of the day of the week (Sunday through Saturday).
         */
        $weekDay = date('l', strtotime($deliveryDate));

        foreach ($locations as &$location) {
            /**
             * if we don't have any business hours specified for this date, the location is closed.
             */
            if (!isset($location->OpeningHours->$weekDay->string)) {
                $location->isEvening = false;

                continue;
            }

            /**
             * Check if the location is open in the evening and mark it accordingly.
             */
            $businessHours = $location->OpeningHours->$weekDay->string;
            if ($this->_businessHoursIsEvening($businessHours)) {
                $location->isEvening = true;

                continue;
            }

            $location->isEvening = false;

            continue;
        }

        return $locations;
    }

    /**
     * Check if an array of business hours contains a timespan that is condiered to be in the evening.
     *
     * @param array $businessHours
     *
     * @return bool
     */
    protected function _businessHoursIsEvening($businessHours)
    {
        foreach ($businessHours as $businessHour) {
            if ($this->_isEvening($businessHour)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a specified opening time is considered to be in the evening. Opening times must be formatted as
     * H:i-H:i. The second part of the time (the closing time) will be compared to the self::EVENING_TIME constant to
     * find out if it's in the evening.
     *
     * @param $time
     *
     * @return bool
     */
    protected function _isEvening($time)
    {
        $timeParts = explode('-', $time);

        if (!isset($timeParts[1])) {
            return false;
        }

        $closingTime = str_replace(':', '', $timeParts[1]);

        if ($closingTime >= self::EVENING_TIME) {
            return true;
        }

        return false;
    }

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

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_PAKJEGEMAK, $storeId);
        if (!$enabled) {
            return false;
        }

        $canUsePakjeGemak = parent::canUsePakjeGemak($storeId);

        return $canUsePakjeGemak;
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

        if (!$this->canUsePakjeGemak($storeId)) {
            return false;
        }

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_PAKJEGEMAK_EXPRESS, $storeId);
        if (!$enabled) {
            return false;
        }

        $pgeOptions = Mage::getModel('postnl_core/system_config_source_pakjeGemakProductOptions')
                          ->getAvailablePgeOptions($storeId);

        if (empty($pgeOptions)) {
            return false;
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

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_PAKKETAUTOMAAT_LOCATIONS, $storeId);
        if (!$enabled) {
            return false;
        }

        $pakketautomaatOptions = Mage::getModel('postnl_core/system_config_source_pakketautomaatProductOptions')
                                     ->getAvailableOptions($storeId);

        if (empty($pakketautomaatOptions)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if timeframes are available.
     *
     * @param int|boolean $storeId
     *
     * @return boolean
     */
    public function canUseTimeframes($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_TIMEFRAMES, $storeId);

        return $enabled;
    }

    /**
     * Checks if evening timeframes are available.
     *
     * @param int|boolean $storeId
     *
     * @return boolean
     */
    public function canUseEveningTimeframes($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!$this->canUseTimeframes($storeId)) {
            return false;
        }

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_EVENING_TIMEFRAMES, $storeId);
        if (!$enabled) {
            return false;
        }

        $eveningOptions = Mage::getModel('postnl_core/system_config_source_standardProductOptions')
                              ->getAvailableAvondOptions($storeId);

        if (empty($eveningOptions)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if sunday sorting is allowed.
     *
     * @param bool $storeId
     *
     * @return bool
     */
    public function canUseSundaySorting($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $allowed = Mage::getStoreConfigFlag(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);

        return $allowed;
    }

    /**
     * Check if PostNL delivery options may be used based on a quote.
     *
     * @param Mage_Sales_Model_Quote|boolean $quote
     * @param boolean $checkCountry
     *
     * @return boolean
     */
    public function canUseDeliveryOptions($quote = false, $checkCountry = true)
    {
        $registryKey = 'can_use_delivery_options';
        if ($quote) {
            $registryKey .= '_quote_id_' . $quote->getId();
        }

        if ($checkCountry) {
            $registryKey .= '_check_country';
        }

        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        Mage::unregister('postnl_enabled_delivery_options_errors');

        $deliveryOptionsEnabled = $this->isDeliveryOptionsEnabled();
        if (!$deliveryOptionsEnabled) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * PostNL delivery options cannot be used for virtual orders
         */
        if ($quote && $quote->isVirtual()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0104',
                    'message' => $this->__('The quote is virtual.'),
                )
            );
            Mage::register('postnl_enabled_delivery_options_errors', $errors);
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check if the quote has a valid minimum amount
         */
        if ($quote && !$quote->validateMinimumAmount()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0105',
                    'message' => $this->__("The quote's grand total is below the minimum amount required."),
                )
            );
            Mage::register('postnl_enabled_delivery_options_errors', $errors);
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check that dutch addresses are allowed
         */
        if (!$this->canUseStandard()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0106',
                    'message' => $this->__(
                        'No standard product options are enabled. At least 1 option must be active.'
                    ),
                )
            );
            Mage::register('postnl_enabled_delivery_options_errors', $errors);
            Mage::register($registryKey, false);
            return false;
        }

        if ($quote && $checkCountry) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress->getCountry() != 'NL') {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0132',
                        'message' => $this->__(
                            'PostNL delivery options are only available for Dutch shipping addresses.'
                        ),
                    )
                );
                Mage::register('postnl_enabled_delivery_options_errors', $errors);
                Mage::register($registryKey, false);
                return false;
            }
        }

        /**
         * If we have no quote, we have no further checks to perform.
         */
        if (!$quote) {
            Mage::register($registryKey, true);
            return true;
        }

        $storeId = $quote->getStoreId();

        /**
         * Check if PostNL delivery options may be used for 'letter' orders and if not, if the quote could fit in an
         * envelope.
         */
        $showDeliveryOptionsForLetters = Mage::getStoreConfigFlag(self::XPATH_SHOW_OPTIONS_FOR_LETTER, $storeId);
        if (!$showDeliveryOptionsForLetters) {
            $isLetterQuote = $this->quoteIsLetter($quote, $storeId);
            if ($isLetterQuote) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0150',
                        'message' => $this->__(
                            "The quote's total weight is below the miniumum required to use PostNL delivery options."
                        ),
                    )
                );
                Mage::register('postnl_enabled_delivery_options_errors', $errors);
                Mage::register($registryKey, false);
                return false;
            }
        }

        /**
         * Check if PostNL delivery options may be used for out-og-stock orders and if not, whether the quote has any
         * such products.
         */
        $showDeliveryOptionsForBackorders = Mage::getStoreConfigFlag(self::XPATH_SHOW_OPTIONS_FOR_BACKORDERS, $storeId);
        if (!$showDeliveryOptionsForBackorders) {
            $containsOutOfStockItems = $this->quoteHasOutOfStockItems($quote);
            if ($containsOutOfStockItems) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0102',
                        'message' => $this->__('One or more items in the cart are out of stock.'),
                    )
                );
                Mage::register('postnl_enabled_delivery_options_errors', $errors);
                Mage::register($registryKey, false);
                return false;
            }
        }

        Mage::register($registryKey, true);
        return true;
    }

    /**
     * Check if the module is set to test mode
     *
     * @param bool $storeId
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

        $testMode = Mage::getStoreConfigFlag(self::XML_PATH_TEST_MODE, $storeId);

        Mage::register('delivery_options_test_mode', $testMode);
        return $testMode;
    }

    /**
     * Checks if PostNL delivery options are enabled.
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

        if (Mage::registry('postnl_enabled_delivery_options_errors')) {
            Mage::unregister('postnl_enabled_delivery_options_errors');
        }

        $isPostnlEnabled = $this->isEnabled($storeId, false, $this->isTestMode());
        if ($isPostnlEnabled === false) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0107',
                    'message' => $this->__('You have not yet enabled the PostNL extension.'),
                )
            );
            Mage::register('postnl_enabled_delivery_options_errors', $errors);
            return false;
        }

        $isDeliveryOptionsActive = $this->isDeliveryOptionsActive($storeId);
        if (!$isDeliveryOptionsActive) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0133',
                    'message' => $this->__('You have not yet enabled PostNL delivery options.'),
                )
            );
            Mage::register('postnl_enabled_delivery_options_errors', $errors);
            return false;
        }

        return true;
    }

    /**
     * Checks if PostNL delivery options is active.
     *
     * @param null|int $storeId
     *
     * @return boolean
     */
    public function isDeliveryOptionsActive($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $isActive = Mage::getStoreConfigFlag(self::XPATH_DELIVERY_OPTIONS_ACTIVE, $storeId);

        return $isActive;
    }
}
