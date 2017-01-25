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
class TIG_PostNL_Helper_DeliveryOptions_Fee extends TIG_PostNL_Helper_Data
{
    /**
     * Xpaths to extra fee config settings.
     */
    const XPATH_EVENING_TIMEFRAME_FEE   = 'postnl/delivery_options/evening_timeframe_fee';
    const XPATH_SUNDAY_DELIVERY_FEE     = 'postnl/delivery_options/sunday_delivery_fee';
    const XPATH_SAMEDAY_DELIVERY_FEE    = 'postnl/delivery_options/sameday_delivery_fee';
    const XPATH_PAKJEGEMAK_EXPRESS_FEE  = 'postnl/delivery_options/pakjegemak_express_fee';
    const XPATH_ONLY_STATED_ADDRESS_FEE = 'postnl/delivery_options/stated_address_only_fee';

    /**
     * Fee limit types
     */
    const FEE_LIMIT_MIN = 'min';
    const FEE_LIMIT_MAX = 'max';

    /**
     * Fee types
     */
    const FEE_TYPE_EVENING  = 'Evening';
    const FEE_TYPE_SUNDAY   = 'Sunday';
    const FEE_TYPE_SAMEDAY  = 'Sameday';
    const FEE_TYPE_EXPRESS  = 'Express';

    /**
     * Evening timeframes fee limits
     */
    const EVENING_FEE_MIN = 0;
    const EVENING_FEE_MAX = 2;

    /**
     * PakjeGemak Express fee limits
     */
    const EXPRESS_FEE_MIN = 0;
    const EXPRESS_FEE_MAX = 2;

    /**
     * Sunday delivery fee limits
     */
    const SUNDAY_FEE_MIN = 0;
    const SUNDAY_FEE_MAX = 10;

    /**
     * Same day delivery fee limits
     */
    const SAMEDAY_FEE_MIN = 0;
    const SAMEDAY_FEE_MAX = 25;

    /**
     * Get the fee limit, min or max, for the supplied fee type
     *
     * @param string $feeType
     * @param string $limitType
     *
     * @return int
     */
    public function getFeeLimit($feeType, $limitType = self::FEE_LIMIT_MAX)
    {
        switch ($limitType) {
            case self::FEE_LIMIT_MIN:
                $fee = $this->getMinFeeLimit($feeType);
                break;
            case self::FEE_LIMIT_MAX:
                $fee = $this->getMaxFeeLimit($feeType);
                break;
            default:
                $fee = 0;
        }

        return $fee;
    }

    /**
     * Get the min fee limit
     *
     * @param string $feeType
     *
     * @return int
     */
    public function getMinFeeLimit($feeType)
    {
        switch ($feeType) {
            case self::FEE_TYPE_EVENING:
                $fee = self::EVENING_FEE_MIN;
                break;
            case self::FEE_TYPE_SUNDAY:
                $fee = self::SUNDAY_FEE_MIN;
                break;
            case self::FEE_TYPE_SAMEDAY:
                $fee = self::SAMEDAY_FEE_MIN;
                break;
            case self::FEE_TYPE_EXPRESS:
                $fee = self::EXPRESS_FEE_MIN;
                break;
            default:
                $fee = 0;
        }

        return $fee;
    }

    /**
     * Get the max fee limit
     *
     * @param string $feeType
     *
     * @return int
     */
    public function getMaxFeeLimit($feeType)
    {
        switch ($feeType) {
            case self::FEE_TYPE_EVENING:
                $fee = self::EVENING_FEE_MAX;
                break;
            case self::FEE_TYPE_SUNDAY:
                $fee = self::SUNDAY_FEE_MAX;
                break;
            case self::FEE_TYPE_SAMEDAY:
                $fee = self::SAMEDAY_FEE_MAX;
                break;
            case self::FEE_TYPE_EXPRESS:
                $fee = self::EXPRESS_FEE_MAX;
                break;
            default:
                $fee = 0;
        }

        return $fee;
    }

    /**
     * Get the fee charged for evening time frames.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     */
    public function getEveningFee($formatted = false, $includingTax = true, $convert = true)
    {
        $feeType = self::FEE_TYPE_EVENING;

        return $this->_getFee($feeType, $formatted, $includingTax, $convert);
    }

    /**
     * Get the fee charged for sunday delivery.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     */
    public function getSundayFee($formatted = false, $includingTax = true, $convert = true)
    {
        $feeType = self::FEE_TYPE_SUNDAY;

        return $this->_getFee($feeType, $formatted, $includingTax, $convert);
    }

    /**
     * Get the fee charged for same day delivery.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     */
    public function getSameDayFee($formatted = false, $includingTax = true, $convert = true)
    {
        $feeType = self::FEE_TYPE_SAMEDAY;

        return $this->_getFee($feeType, $formatted, $includingTax, $convert);
    }

    /**
     * Get the fee charged for PakjeGemak Express.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     */
    public function getExpressFee($formatted = false, $includingTax = true, $convert = true)
    {
        $feeType = self::FEE_TYPE_EXPRESS;

        return $this->_getFee($feeType, $formatted, $includingTax, $convert);
    }

    /**
     * Get the fee for the supplied type
     *
     * @param string  $feeType
     * @param boolean $formatted
     * @param bool    $includingTax
     * @param bool    $convert
     *
     * @return float|int|string
     */
    protected function _getFee($feeType, $formatted = false, $includingTax = true, $convert = true)
    {
        $registryKey = $this->_getFeeRegistryKey($feeType);

        if ($includingTax) {
            $registryKey .= '_incl';
        }

        if (Mage::registry($registryKey) !== null) {
            $price = Mage::registry($registryKey);
        } else {
            $storeId = Mage::app()->getStore()->getId();
            $xpath = $this->_getFeeConfigXpath($feeType);
            $fee = (float) Mage::getStoreConfig($xpath, $storeId);

            $price = $this->getPriceWithTax($fee, $includingTax, false, false);

            if ($price > $this->getFeeLimit($feeType)) {
                $price = 0;
            }

            Mage::register($registryKey, $price);
        }

        if ($convert) {
            $quote = $this->getQuote();
            $store = $quote->getStore();

            $price = $store->convertPrice($price, false, false);
        }

        if ($formatted) {
            $price = Mage::app()->getStore()->formatPrice($price, false);
        }

        return $price;
    }

    /**
     * @param string $feeType
     *
     * @return string
     */
    protected function _getFeeRegistryKey($feeType)
    {
        switch ($feeType) {
            case self::FEE_TYPE_EVENING:
                $registryKey = 'postnl_evening_fee';
                break;
            case self::FEE_TYPE_SUNDAY:
                $registryKey = 'postnl_sunday_fee';
                break;
            case self::FEE_TYPE_SAMEDAY:
                $registryKey = 'postnl_sameday_fee';
                break;
            case self::FEE_TYPE_EXPRESS:
                $registryKey = 'postnl_express_fee';
                break;
            default:
                throw new InvalidArgumentException("Invalid feeType supplied.");
        }

        return $registryKey;
    }

    /**
     * @param string $feeType
     *
     * @return string
     */
    protected function _getFeeConfigXpath($feeType)
    {
        switch ($feeType) {
            case self::FEE_TYPE_EVENING:
                $xpath = self::XPATH_EVENING_TIMEFRAME_FEE;
                break;
            case self::FEE_TYPE_SUNDAY:
                $xpath = self::XPATH_SUNDAY_DELIVERY_FEE;
                break;
            case self::FEE_TYPE_SAMEDAY:
                $xpath = self::XPATH_SAMEDAY_DELIVERY_FEE;
                break;
            case self::FEE_TYPE_EXPRESS:
                $xpath = self::XPATH_PAKJEGEMAK_EXPRESS_FEE;
                break;
            default:
                throw new InvalidArgumentException("Invalid feeType supplied.");
        }

        return $xpath;
    }

    /**
     * Get the fee for PakjeGemak locations. This is only applicable to buspakje orders.
     *
     * @param float   $currentRate
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float|int
     */
    public function getPakjeGemakFee($currentRate, $formatted = false, $includingTax = true, $convert = true)
    {
        $registryKey = 'postnl_pakje_gemak_fee';

        if ($includingTax) {
            $registryKey .= '_incl';
        }

        if (Mage::registry($registryKey) !== null) {
            $price = Mage::registry($registryKey);
        } else {
            /** @var TIG_PostNL_Helper_Carrier $carierHelper */
            $carierHelper = Mage::helper('postnl/carrier');
            $pakjeGemakShippingRates = $carierHelper->getParcelShippingRate($this->getQuote());
            if (!$pakjeGemakShippingRates) {
                return 0;
            }

            $pakjeGemakShippingRate = $pakjeGemakShippingRates->getCheapestRate();
            /** @noinspection PhpUndefinedMethodInspection */
            $pakjeGemakShippingRate = $pakjeGemakShippingRate->getPrice();

            $difference = $pakjeGemakShippingRate - $currentRate;

            $price = $this->getPriceWithTax($difference, $includingTax, false, false);

            Mage::register($registryKey, $price);
        }

        if ($convert) {
            $store = $this->getQuote()->getStore();

            $price = $store->convertPrice($price, false, false);
        }


        if ($formatted) {
            $price = Mage::app()->getStore()->formatPrice($price, false);
        }

        return $price;
    }

    /**
     * Get the fee charged for possible options saved to the PostNL order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     * @param bool                        $formatted
     * @param bool                        $includingTax
     * @param bool                        $convert
     *
     * @return float|int
     */
    public function getOptionsFee(TIG_PostNL_Model_Core_Order $postnlOrder, $formatted = false, $includingTax = true,
        $convert = true)
    {
        if (!$postnlOrder->hasOptions()) {
            return 0;
        }

        $options = $postnlOrder->getOptions();
        if (empty($options)) {
            return 0;
        }

        $storeId = Mage::app()->getStore()->getId();

        /**
         * For upgradability reasons this is a switch, rather than an if statement.
         */
        $fee = 0;
        foreach ($options as $option => $value) {
            if (!$value) {
                continue;
            }

            switch ($option) {
                case 'only_stated_address':
                    $fee += (float) Mage::getStoreConfig(self::XPATH_ONLY_STATED_ADDRESS_FEE, $storeId);
                    break;
                //no default
            }
        }

        $price = $this->getPriceWithTax($fee, $includingTax, false, false);

        if ($convert) {
            $quote = $this->getQuote();
            $store = $quote->getStore();

            $price = $store->convertPrice($price, false, false);
        }

        if ($formatted) {
            $price = Mage::app()->getStore()->formatPrice($price, false);
        }

        return $price;
    }

    /**
     * Gets the configured fee for a specified option.
     *
     * @param string $option
     * @param bool  $formatted
     * @param bool  $includingTax
     * @param bool  $convert
     *
     * @return float|int
     */
    public function getOptionFee($option, $formatted = false, $includingTax = true, $convert = true)
    {
        $storeId = Mage::app()->getStore()->getId();

        /**
         * For upgradability reasons this is a switch, rather than an if statement.
         */
        $fee = 0;
        switch ($option) {
            case 'only_stated_address':
                $fee = (float) Mage::getStoreConfig(self::XPATH_ONLY_STATED_ADDRESS_FEE, $storeId);
                break;
            //no default
        }

        $price = $this->getPriceWithTax($fee, $includingTax, false, false);

        if ($price > 2) {
            $price = 0;
        }

        if ($convert) {
            $quote = $this->getQuote();
            $store = $quote->getStore();

            $price = $store->convertPrice($price, false, false);
        }

        if ($formatted) {
            $price = Mage::app()->getStore()->formatPrice($price, false);
        }

        return $price;
    }

    /**
     * Convert a value to a formatted price.
     *
     * @param float   $price
     * @param boolean $includingTax
     * @param boolean $formatted
     * @param boolean $convert
     *
     * @return float
     *
     * @see Mage_Checkout_Block_Onepage_Shipping_Method_Available::getShippingPrice()
     */
    public function getPriceWithTax($price, $includingTax, $formatted = false, $convert = true)
    {
        $quote = $this->getQuote();
        $store = $quote->getStore();

        /** @var Mage_Tax_Helper_Data $helper */
        $helper = Mage::helper('tax');
        $shippingPrice  = $helper->getShippingPrice($price, $includingTax, $quote->getShippingAddress());

        if ($convert) {
            $shippingPrice = $store->convertPrice($shippingPrice, $formatted, false);
        }

        return $shippingPrice;
    }

}
