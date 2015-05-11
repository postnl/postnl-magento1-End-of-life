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
class TIG_PostNL_Model_Mijnpakket_Service extends Varien_Object
{
    /**
     * Parse billing data and return an array such as Magento would expect from Onepage Checkout.
     *
     * @param StdClass $profile
     *
     * @return array
     */
    public function parseProfileData($profile)
    {
        $billingData = array(
            'firstname'            => $profile->Voornaam,
            'middlename'           => $profile->Tussenvoegsel,
            'lastname'             => $profile->Achternaam,
            'company'              => $profile->Bedrijf,
            'email'                => $profile->Email,
            'country_id'           => $profile->Land,
            'postcode'             => $profile->Postcode,
            'city'                 => $profile->Plaats,
            'street'               => array(
                0 => $profile->Straat,
                1 => $profile->Huisnummer,
                2 => $profile->HuisnummerExt,
            ),
            'region_id'            => '',
            'region'               => $profile->Regio,
            'telephone'            => $profile->Mobiel,
            'fax'                  => '',
            'customer_password'    => '',
            'confirm_password'     => '',
            'save_in_address_book' => 0,
            'use_for_shipping'     => 1,
        );

        return $billingData;
    }
}