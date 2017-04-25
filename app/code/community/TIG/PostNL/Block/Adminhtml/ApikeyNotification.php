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
class TIG_PostNL_Block_Adminhtml_ApikeyNotification extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_apikeynotification';

    /**
     * Check to see if the apikey needs te be set.
     *
     * @return boolean
     */
    public function isApikeyRequired()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        /**
         * Return false if extension is not activated.
         */
        $enabled = Mage::getStoreConfigFlag(TIG_PostNL_Helper_Data::XPATH_EXTENSION_MODE, $storeId);
        if ($enabled === false) {
            return false;
        }

        /**
         * Return true if no apikey is set.
         */
        return Mage::getModel('postnl_core/cif')->getApiKey() === false;
    }
}
