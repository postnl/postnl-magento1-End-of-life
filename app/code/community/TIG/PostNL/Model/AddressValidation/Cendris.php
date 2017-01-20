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
class TIG_PostNL_Model_AddressValidation_Cendris extends TIG_PostNL_Model_AddressValidation_Cendris_Abstract
{
    /**
     * Xpaths to cendris username and password.
     */
    const XPATH_USERNAME = 'postnl/cendris/username';
    const XPATH_PASSWORD = 'postnl/cendris/password';

    /**
     * Validates and enriches the postcode and housenumber with a city and streetname
     *
     * @param string $postcode
     * @param string $housenumber
     *
     * @return StdClass
     */
    public function getAdresxpressPostcode($postcode, $housenumber)
    {
        $username = $this->_getUsername();
        $password = $this->_getPassword();

        $soapParams = array(
            'gebruikersnaam' => $username,
            'wachtwoord'     => $password,
            'postcode'       => $postcode,
            'huisnummer'     => $housenumber,
        );

        $result = $this->call('getAdresxpressPostcode', $soapParams);

        if (is_array($result)) {
            $result = current($result);
        }

        return $result;
    }

    /**
     * Get the Cendris username.
     *
     * @return string
     */
    protected function _getUsername()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $username = Mage::getStoreConfig(self::XPATH_USERNAME, $storeId);

        return $username;
    }

    /**
     * Get the Cendris password.
     *
     * TIG exception notice: The unencrypted form is used in line with the available communication options with the
     * Cendris API at the moment of writing this. PostNL is aware of this and may change this at any time in the
     * upcoming versions of their API.
     *
     * @return string
     */
    protected function _getPassword()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $password = Mage::getStoreConfig(self::XPATH_PASSWORD, $storeId);

        return $password;
    }
}
