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
class TIG_PostNL_Helper_Carrier extends TIG_PostNL_Helper_Data
{
    /**
     * Shipping carrier code used by PostNL.
     */
    const POSTNL_CARRIER = 'postnl';

    /**
     * PostNL shipping methods.
     */
    const POSTNL_FLATRATE_METHOD  = 'flatrate';
    const POSTNL_TABLERATE_METHOD = 'tablerate';
    const POSTNL_MATRIX_METHOD    = 'matrixrate';

    /**
     * Localised track and trace base URL's.
     */
    const POSTNL_TRACK_AND_TRACE_BASE_URL_XPATH  = 'postnl/cif/track_and_trace_base_url';

    /**
     * XML path to rate type setting.
     */
    const XPATH_RATE_TYPE = 'carriers/postnl/rate_type';

    /**
     * Xpath to the 'postnl_shipping_methods' setting.
     */
    const XPATH_POSTNL_SHIPPING_METHODS = 'postnl/advanced/postnl_shipping_methods';

    /**
     * Default destination street. This is defined here, rather tha n in the address model for backwards compatibility.
     */
    const DEFAULT_DEST_STREET = '-1';

    /**
     * Array of possible PostNL shipping methods.
     *
     * @var array
     */
    protected $_postnlShippingMethods;

    /**
     * Array of shipping methods that have already been checked for whether they're PostNL.
     *
     * @var array
     */
    protected $_matchedMethods = array();

    /**
     * Gets an array of possible PostNL shipping methods.
     *
     * @return array
     */
    public function getPostnlShippingMethods()
    {
        if ($this->_postnlShippingMethods) {
            return $this->_postnlShippingMethods;
        }

        $cache = $this->getCache();
        if ($cache && $cache->hasPostnlShippingMethods()) {
            $shippingMethods = $cache->getPostnlShippingMethods();

            $this->setPostnlShippingMethods($shippingMethods);
            return $shippingMethods;
        }

        $shippingMethods = Mage::getStoreConfig(self::XPATH_POSTNL_SHIPPING_METHODS, Mage::app()->getStore()->getId());
        $shippingMethods = explode(',', $shippingMethods);

        if ($cache) {
            $cache->setPostnlShippingMethods($shippingMethods);
        }

        $this->setPostnlShippingMethods($shippingMethods);
        return $shippingMethods;
    }

    /**
     * @param array $postnlShippingMethods
     *
     * @return $this
     */
    public function setPostnlShippingMethods($postnlShippingMethods)
    {
        $this->_postnlShippingMethods = $postnlShippingMethods;

        return $this;
    }

    /**
     * @return array
     */
    public function getMatchedMethods()
    {
        $matchedMethods = $this->_matchedMethods;
        if (!empty($matchedMethods)) {
            return $matchedMethods;
        }

        $cache = $this->getCache();
        if ($cache && $cache->hasMatchedPostnlShippingMethods()) {
            $this->setMatchedMethods(
                $cache->getMatchedPostnlShippingMethods()
            );
        }

        return $this->_matchedMethods;
    }

    /**
     * @param array $matchedMethods
     *
     * @return $this
     */
    public function setMatchedMethods($matchedMethods)
    {
        $this->_matchedMethods = $matchedMethods;

        $cache = $this->getCache();
        if ($cache) {
            $cache->setMatchedPostnlShippingMethods($matchedMethods);
        }

        return $this;
    }

    /**
     * Adds a matched method to the matched methods array.
     *
     * @param string  $method
     * @param boolean $value
     *
     * @return $this
     */
    public function addMatchedMethod($method, $value)
    {
        $matchedMethods = $this->getMatchedMethods();
        $matchedMethods[$method] = $value;

        $this->setMatchedMethods($matchedMethods);
        return $this;
    }

    /**
     * Alias for getCurrentPostnlShippingMethod()
     *
     * @return string
     *
     * @see TIG_PostNL_Helper_Carrier::getCurrentPostnlShippingMethod()
     *
     * @deprecated
     */
    public function getPostnlShippingMethod()
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return $this->getCurrentPostnlShippingMethod();
    }

    /**
     * Returns the PostNL shipping method
     *
     * @param null $storeId
     *
     * @throws TIG_PostNL_Exception
     * @return string
     */
    public function getCurrentPostnlShippingMethod($storeId = null)
    {
        if (Mage::registry('current_postnl_shipping_method') !== null) {
            return Mage::registry('current_postnl_shipping_method');
        }

        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $rateType = Mage::getStoreConfig(self::XPATH_RATE_TYPE, $storeId);

        $carrier = self::POSTNL_CARRIER;
        switch ($rateType) {
            case 'flat':
                $shippingMethod = $carrier . '_' . self::POSTNL_FLATRATE_METHOD;
                break;
            case 'table':
                $shippingMethod = $carrier . '_' . self::POSTNL_TABLERATE_METHOD;
                break;
            case 'matrix':
                $shippingMethod = $carrier . '_' . self::POSTNL_MATRIX_METHOD;
                break;
            default:
                throw new TIG_PostNL_Exception(
                    $this->__('Invalid rate type requested: %s', $rateType),
                    'POSTNL-0036'
                );
        }

        Mage::register('current_postnl_shipping_method', $shippingMethod);
        return $shippingMethod;
    }

    /**
     * Get a shipping rate for a parcel only.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Shipping_Model_Rate_Result|false
     */
    public function getParcelShippingRate(Mage_Sales_Model_Quote $quote)
    {
        $registryKey = 'postnl_parcel_shipping_rate_quote_id_' . $quote->getId();
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress) {
            Mage::register($registryKey, false);
            return false;
        }

        $store = $quote->getStore();

        /** @var $request Mage_Shipping_Model_Rate_Request */
        $request = Mage::getModel('shipping/rate_request');
        $request->setAllItems($shippingAddress->getAllItems());
        $request->setDestCountryId($shippingAddress->getCountryId());
        $request->setDestRegionId($shippingAddress->getRegionId());
        $request->setDestRegionCode($shippingAddress->getRegionCode());

        /**
         * Need to call getStreet with -1 to get data in string instead of array.
         */
        $request->setDestStreet($shippingAddress->getStreet(self::DEFAULT_DEST_STREET));
        $request->setDestCity($shippingAddress->getCity());
        $request->setDestPostcode($shippingAddress->getPostcode());
        $request->setPackageValue($shippingAddress->getBaseSubtotal());
        $packageValueWithDiscount = $shippingAddress->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageValueWithDiscount);
        $request->setPackageWeight($shippingAddress->getWeight());
        $request->setPackageQty($shippingAddress->getItemQty());

        /**
         * Need for shipping methods that use insurance based on price of physical products.
         */
        $packagePhysicalValue = $shippingAddress->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        $request->setFreeMethodWeight($shippingAddress->getFreeMethodWeight());

        $request->setStoreId($store->getId());
        $request->setWebsiteId($store->getWebsiteId());
        $request->setFreeShipping($shippingAddress->getFreeShipping());
        /**
         * Currencies need to convert in free shipping.
         */
        $request->setBaseCurrency($store->getBaseCurrency());
        $request->setPackageCurrency($store->getCurrentCurrency());
        $request->setLimitCarrier($shippingAddress->getLimitCarrier());

        $request->setBaseSubtotalInclTax(
            $shippingAddress->getBaseSubtotalInclTax() + $shippingAddress->getBaseExtraTaxAmount()
        );
        $request->setParcelType('regular');

        $rawResult = Mage::getResourceModel('postnl_carrier/matrixrate')->getRate($request);
        if (!$rawResult) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Convert the raw result from the database to a shipping rate result object.
         */
        $carrier = Mage::getModel('postnl_carrier/postnl');
        $result  = Mage::getModel('shipping/rate_result');
        $method  = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier('postnl');
        $method->setCarrierTitle($carrier->getConfigData('title'));

        $method->setMethod('flatrate');
        $method->setMethodTitle($carrier->getConfigData('name'));

        $method->setPrice($rawResult['price']);
        $method->setCost(0);

        $result->append($method);

        Mage::register($registryKey, $result);
        return $result;
    }

    /**
     * Checks if a specified shipping method is a PostNL shipping method.
     *
     * @param $shippingMethod
     *
     * @return bool
     */
    public function isPostnlShippingMethod($shippingMethod)
    {
        /**
         * Check if we've matched this shipping method before.
         */
        $matchedMethods = $this->getMatchedMethods();
        if (isset($matchedMethods[$shippingMethod])) {
            return $matchedMethods[$shippingMethod];
        }

        /**
         * Check if the shipping method exists in the configured array of supported methods.
         */
        $postnlShippingMethods = $this->getPostnlShippingMethods();
        if (in_array($shippingMethod, $postnlShippingMethods)) {
            $this->addMatchedMethod($shippingMethod, true);
            return true;
        }

        /**
         * Some shipping methods add suffixes to the method code.
         */
        foreach ($postnlShippingMethods as $postnlShippingMethod) {
            $regex = "/^({$postnlShippingMethod})(_?\d*)$/";

            if (preg_match($regex, $shippingMethod) === 1) {
                $this->addMatchedMethod($shippingMethod, true);
                return true;
            }
        }

        $this->addMatchedMethod($shippingMethod, false);
        return false;
    }

    /**
     * Constructs a PostNL track & trace url based on a barcode and the destination of the package (country and
     * zipcode).
     *
     * @param string              $barcode
     * @param array|Varien_Object $destination An array or object containing the shipment's destination data.
     * @param boolean|string      $lang        This parameter is no longer used as of v1.4.1.
     * @param boolean             $business
     *
     * @return string
     */
    public function getBarcodeUrl($barcode, $destination, $lang = null, $business = false)
    {
        /**
         * Set first L (language) parameter
         */
        $lang = strtoupper($lang);

        $allowedLanguages = array (
            'NL', 'DE', 'EN', 'FR', 'ED', 'IT', 'CN'
        );
        if (!in_array($lang, $allowedLanguages)) {
            $lang = 'EN';
        }
        $langParameter = 'L=' . $lang;

        /**
         * Set second B (barcode) parameter
         */
        if (!empty($barcode)) {
            $barcodeParameter = '&B=' . $barcode;
        } else {
            throw new InvalidArgumentException('Barcode can not be empty.');
        }

        /**
         * Set third (postcode) and fourth (destination) parameter
         */
        $countryCode = null;
        $postcode    = null;
        if (is_array($destination)) {
            if (!isset($destination['countryCode'])) {
                throw new InvalidArgumentException('Destination must contain a country code.');
            }
            $countryCode = $destination['countryCode'];
            $postcode    = $destination['postcode'];
        } elseif (is_object($destination) && $destination instanceof Varien_Object) {
            if (!$destination->getCountry()) {
                throw new InvalidArgumentException('Destination must contain a country code.');
            }
            $countryCode = $destination->getCountry();
            $postcode    = str_replace(' ', '', $destination->getPostcode());
        } else {
            throw new InvalidArgumentException('Destination must be an array or an instance of Varien_Object.');
        }

        $postcodeParameter    = '&P=' . $postcode;
        $destinationParameter = '&D=' . $countryCode;

        /**
         * Set last and fifth (Consumer or Business) parameter
         */
        $businessParameter  = '&T=C';
        if ($business) {
            $businessParameter  = '&T=B';
        }

        /**
         * Get track & trace URL
         */
        $barcodeUrl = Mage::getStoreConfig(self::POSTNL_TRACK_AND_TRACE_BASE_URL_XPATH)
            . $langParameter
            . $barcodeParameter
            . $postcodeParameter
            . $destinationParameter
            . $businessParameter;

        return $barcodeUrl;
    }
}
