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
class TIG_PostNL_AddressValidationController extends Mage_Core_Controller_Front_Action
{
    /**
     * Validates and enriches a postcode/housenumber combination. This will result in the address's city and streetname if valid.
     *
     * @return TIG_PostNL_AddressValidationController
     *
     * @todo add check to see if response is valid
     */
    public function postcodeCheckAction()
    {
        /**
         * Get the address data from the $_POST superglobal
         */
        $address = $this->getRequest()->getPost();
        if (!$address
            || !isset($address['postcode'])
            || !isset($address['housenumber'])
        ) {
            $this->getResponse()
                 ->setBody('missing data');

            return $this;
        }

        $postcode = $address['postcode'];
        $housenumber = $address['housenumber'];

        /**
         * Load the Cendris webservice and perform an getAdresxpressPostcode request
         */
        $webservice = Mage::getModel('postnl_addressvalidation/webservices');

        try {
            $result = $webservice->getAdresxpressPostcode($postcode, $housenumber);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        /**
         * @todo add check to see if result is valid
         */

        /**
         * Get the city and streetname from the response
         */
        $city = $result->woonplaats;
        $streetname = $result->straatnaam;

        /**
         * If either the city or streetname is empty we have an invalid response
         */
        if (empty($city) || empty($streetname)) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        /**
         * Add the resulting city and streetname to an array and JSON encode it
         */
        $responseArray = array(
            'city'       => $city,
            'streetname' => $streetname,
        );

        $response = Mage::helper('core')->jsonEncode($responseArray);

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json')
             ->setBody($response);

        return $this;
    }
}
