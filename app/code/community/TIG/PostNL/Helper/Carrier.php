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
class TIG_PostNL_Helper_Carrier extends TIG_PostNL_Helper_Data
{
    /**
     * Shipping carrier code used by PostNL
     */
    const POSTNL_CARRIER = 'postnl';

    /**
     * PostNL shipping methods
     */
    const POSTNL_FLATRATE_METHOD  = 'flatrate';
    const POSTNL_TABLERATE_METHOD = 'tablerate';

    /**
     * Localised track and trace base URL's
     */
    const POSTNL_TRACK_AND_TRACE_NL_BASE_URL  = 'https://mijnpakket.postnl.nl/Inbox/Search?';
    const POSTNL_TRACK_AND_TRACE_GB_BASE_URL  = 'http://parcels-uk.tntpost.com/mytrackandtrace/trackandtrace.aspx?';
    const POSTNL_TRACK_AND_TRACE_DE_BASE_URL  = 'http://parcels-de.tntpost.com/de/mytrackandtrace/TrackAndTrace.aspx?';
    const POSTNL_TRACK_AND_TRACE_FR_BASE_URL  = 'http://parcels-fr.tntpost.com/fr/mytrackandtrace/TrackAndTrace.aspx?';
    const POSTNL_TRACK_AND_TRACE_INT_BASE_URL = 'http://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?';

    /**
     * XML path to rate type setting
     */
    const XML_PATH_RATE_TYPE = 'carriers/postnl/rate_type';

    /**
     * Array of possible PostNL shipping methods
     *
     * @var array
     */
    protected $_postnlShippingMethods = array(
        'postnl_postnl',    //deprecated
        'postnl_flatrate',
        'postnl_tablerate',
    );

    /**
     * Gets an array of possible PostNL shipping methods
     *
     * @return array
     */
    public function getPostnlShippingMethods()
    {
        $shippingMethods = $this->_postnlShippingMethods;
        return $shippingMethods;
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

        $rateType = Mage::getStoreConfig(self::XML_PATH_RATE_TYPE, $storeId);

        $carrier = self::POSTNL_CARRIER;
        switch ($rateType) {
            case 'flat':
                $shippingMethod = $carrier . '_' . self::POSTNL_FLATRATE_METHOD;
                break;
            case 'table':
                $shippingMethod = $carrier . '_' . self::POSTNL_TABLERATE_METHOD;
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
     * Constructs a PostNL track & trace url based on a barcode and the destination of the package (country and zipcode)
     *
     * @param string $barcode
     * @param mixed $destination An array or object containing the shipment's destination data
     * @param boolean | string $lang
     * @param boolean $forceNl
     *
     * @return string
     */
    public function getBarcodeUrl($barcode, $destination = false, $lang = false, $forceNl = false)
    {
        $countryCode = null;
        $postcode    = null;
        if (is_array($destination)) {
            $countryCode = $destination['countryCode'];
            $postcode    = $destination['postcode'];
        }

        if (is_object($destination) && $destination instanceof Varien_Object) {
            $countryCode = $destination->getCountry();
            $postcode    = $destination->getPostcode();
        }

        /**
         * Get the dutch track & trace URL for dutch shipments or for the admin
         */
        if ($forceNl
            || (!empty($countryCode)
                && $countryCode == 'NL'
            )
        ) {
            $barcodeUrl = self::POSTNL_TRACK_AND_TRACE_NL_BASE_URL
                        . '&b=' . $barcode;
            /**
             * For dutch shipments add the postcode. For international shipments add an 'international' flag
             */
            if (!empty($postcode)
                && !empty($countryCode)
                && $countryCode == 'NL'
            ) {
                $barcodeUrl .= '&p=' . $postcode;
            } else {
                $barcodeUrl .= '&i=true';
            }

            return $barcodeUrl;
        }

        /**
         * Get localized track & trace URLs for UK, DE and FR shipments
         */
        if (isset($countryCode)
            && ($countryCode == 'UK'
                || $countryCode == 'GB'
            )
        ) {
            $barcodeUrl = self::POSTNL_TRACK_AND_TRACE_GB_BASE_URL
                        . '&B=' . $barcode
                        . '&D=GB'
                        . '&lang=en';

            return $barcodeUrl;
        }

        if (isset($countryCode) && $countryCode == 'DE') {
            $barcodeUrl = self::POSTNL_TRACK_AND_TRACE_DE_BASE_URL
                        . '&B=' . $barcode
                        . '&D=DE'
                        . '&lang=de';

            return $barcodeUrl;
        }

        if (isset($countryCode) && $countryCode == 'FR') {
            $barcodeUrl = self::POSTNL_TRACK_AND_TRACE_FR_BASE_URL
                        . '&B=' . $barcode
                        . '&D=FR'
                        . '&lang=fr';

            return $barcodeUrl;
        }

        /**
         * Get a general track & trace URL for all other destinations
         */
        $barcodeUrl = self::POSTNL_TRACK_AND_TRACE_INT_BASE_URL
                    . '&B=' . $barcode
                    . '&I=true';

        if ($lang) {
            $barcodeUrl .= '&lang=' . strtolower($lang);
        }

        if ($countryCode) {
            $barcodeUrl .= '&D=' . $countryCode;
        }

        return $barcodeUrl;
    }
}
