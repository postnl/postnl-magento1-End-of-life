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
class TIG_PostNL_Model_DeliveryOptions_Cif extends TIG_PostNL_Model_Core_Cif
{
    /**
     * Delivery option codes.
     */
    const PAKJEGEMAK_DELIVERY_OPTION         = 'PG';
    const PAKJEGEMAK_EXPRESS_DELIVERY_OPTION = 'PGE';
    const PAKKETAUTOMAAT_DELIVERY_OPTION     = 'PA';

    /**
     * Check if the module is set to test mode
     *
     * @see TIG_PostNL_Helper_Checkout::isTestMode()
     *
     * @return boolean
     */
    public function isTestMode($storeId = false)
    {
        $testMode = Mage::helper('postnl/cif')->isTestMode($storeId);

        return $testMode;
    }

    /**
     * Gets the delivery date based on the shop's cut-off time.
     *
     * @param string $postcode
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function getDeliveryDate($postcode)
    {
        if (empty($postcode)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for GetDeliveryDay request.'),
                'POSTNL-0115'
            );
        }

        $soapParams = array();

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'deliverydate',
            'GetDeliveryDate',
            $soapParams
        );

        if (!is_object($response)
            || !isset($response->Checkout)
            || !is_object($response->Checkout)
            || !isset($response->Checkout->OrderToken)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid GetDeliveryDate response: %s', "\n" . var_export($response, true)),
                'POSTNL-0116'
            );
        }

        return $response;
    }

    /**
     * Get evening timeframes for the specified postcode and delivery window.
     *
     * @param array $data
     *
     * @return StdClass
     *
     * @throws TIG_PostNL_Exception
     */
    public function getDeliveryTimeframes($data)
    {
        if (empty($data)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for GetEveningTimeframes request.'),
                'POSTNL-0117'
            );
        }

        $startDate = $data['deliveryDate'];
        /**
         * @todo base this off of a new config setting
         */
        $endDate = date('d-m-Y', strtotime('+6 days', strtotime($startDate)));

        $soapParams = array(
            'Timeframe' => array(
                'PostalCode'  => $data['postcode'],
                'HouseNumber' => $data['housenumber'],
                'StartDate'   => $startDate,
                'EndDate'     => $endDate,
            ),
            'Message' => $this->_getMessage('')
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'timeframe',
            'GetDeliveryTimeframes',
            $soapParams
        );

        if (!isset($response->Timeframes)
            || !isset($response->Timeframes->Timeframe)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid response for GetEveningTimeframes request: %s', $response),
                'POSTNL-0122'
            );
        }

        return $response->Timeframes->Timeframe;
    }

    /**
     * Gets nearby post office locations. This service can be based off of a postcode or a set of coordinates. Results may
     * include PakjeGemak, PakjeGemak Express or pakket automaat locations based on the configuration of the extension.
     *
     * @param string $postcode
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function getNearestLocations($data)
    {
        if (empty($data)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for GetEveningTimeframes request.'),
                'POSTNL-0117'
            );
        }

        $location = $this->_getLocation($data);
        $message  = $this->_getMessage('');

        $soapParams = array(
            'Location' => $location,
            'Message'  => $message,
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'location',
            'GetNearestLocations',
            $soapParams
        );

        if (!isset($response->GetLocationsResult)
            || !isset($response->GetLocationsResult->ResponseLocation)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid response for GetNearestLocations request: %s', $response),
                'POSTNL-0123'
            );
        }

        return $response->GetLocationsResult->ResponseLocation;
    }

    /**
     * Gets the location SOAP parameter array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _getLocation($data)
    {
        /**
         * Start building the location array by adding the available delivery options.
         */
        $location = array(
            'DeliveryOptions' => $this->_getDeliveryOptions(),
        );

        /**
         * Next we add the desired delivery date. If none is specified, we set it to tomorrow.
         */
        if (isset($data['deliveryDate'])) {
            $location['DeliveryDate'] = $data['deliveryDate'];
        } else {
            $tomorrow = strtotime('tomorrow', Mage::getModel('core/date')->timestamp());
            $location['DeliveryDate'] = date('d-m-Y', $tomorrow);
        }

        /**
         * If an opening time was specified, add that as well.
         */
        if (isset($data['openingTime'])) {
            $location['OpeningTime'] = $data['openingTime'];
        }

        /**
         * Add the postcode if available.
         */
        if (isset($data['postcode'])) {
            $location['Postalcode'] = $data['postcode'];
        }

        /**
         * Add coordinates if both a latitude and longitude are available.
         */
        if (isset($data['lat']) && isset($data['long'])) {
            $location['Coordinates'] = array(
                'Latitude'  => $data['lat'],
                'Longitude' => $data['long'],
            );
        }

        return $location;
    }

    /**
     * Gets an array of allowed delivery options.
     *
     * @return array
     */
    protected function _getDeliveryOptions()
    {
        $deliveryOptions = array();

        $helper = Mage::helper('postnl/deliveryOptions');
        if ($helper->canUsePakjeGemak()) {
            $deliveryOptions[] = self::PAKJEGEMAK_DELIVERY_OPTION;
        }

        if ($helper->canUsePakjeGemakExpress()) {
            $deliveryOptions[] = self::PAKJEGEMAK_EXPRESS_DELIVERY_OPTION;
        }

        if ($helper->canUsePakketAutomaat()) {
            $deliveryOptions[] = self::PAKKETAUTOMAAT_DELIVERY_OPTION;
        }

        return $deliveryOptions;
    }
}
