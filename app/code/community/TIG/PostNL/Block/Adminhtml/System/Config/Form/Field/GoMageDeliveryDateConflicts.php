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
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_GoMageDeliveryDateConflicts
    extends TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_Hidden
{
    /**
     * Get whether the GoMage LightCheckout delivery date functionality is conflicting with PostNL delivery options.
     *
     * @return int
     */
    protected function _getValue()
    {
        $storeId = $this->_getStoreId();

        $goMageDeliveryDateConflicts = Mage::helper('postnl/deliveryOptions')
                                           ->checkGoMageDeliveryDateConflicts($storeId);

        return (int) $goMageDeliveryDateConflicts;
    }

    /**
     * Get the current store ID based on the request parameters.
     *
     * @return int
     */
    protected function _getStoreId()
    {
        $request = Mage::app()->getRequest();

        if ($request->getParam('store')) {
            $store = $request->getparam('store');
            $storeId = Mage::app()->getStore($store)->getId();
        } elseif ($request->getParam('website')) {
            $website = Mage::getModel('core/website')->load($request->getparam('website'), 'code');
            $store = $website->getDefaultStore();
            $storeId = $store->getId();
        } else {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        return $storeId;
    }
}