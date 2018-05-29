<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_GoogleNotification extends TIG_PostNL_Block_Adminhtml_Template
{
    protected $_eventPrefix = 'postnl_adminhtml_googlenotification';

    /**
     * Returns true if the api key is required.
     * @return bool
     */
    public function isGoogleApiKeyRequired()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        /**
         * Return false if extension is not activated.
         */
        $enabled = Mage::getStoreConfigFlag(TIG_PostNL_Helper_Data::XPATH_EXTENSION_MODE, $storeId);
        if ($enabled === false) {
            return false;
        }

        $mapsEnabled = Mage::getStoreConfigFlag(TIG_PostNL_Helper_DeliveryOptions::XPATH_GOOGLE_MAPS_ACTIVE, $storeId);
        if ($mapsEnabled === false) {
            return false;
        }

        $apiKey = Mage::getStoreConfig(
            TIG_PostNL_Helper_DeliveryOptions::XPATH_GOOGLE_MAPS_API_KEY,
            Mage::app()->getStore()->getId()
        );

        if (!$apiKey) {
            return true;
        }

        /** @var Mage_Core_Helper_Data $helper */
        $helper = Mage::helper('core');
        $apiKey = trim($helper->decrypt($apiKey));

        if (empty($apiKey) || $apiKey == '') {
            return true;
        }

        return false;
    }
}
