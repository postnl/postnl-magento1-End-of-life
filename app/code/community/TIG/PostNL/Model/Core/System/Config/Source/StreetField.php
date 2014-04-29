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

class TIG_PostNL_Model_Core_System_Config_Source_StreetField
{
    /**
     * XML path to community edition address lines configuration option
     */
    const XML_PATH_COMMUNITY_STREET_LINES = 'customer/address/street_lines';

    /**
     * @var null|array The resulting product option array
     */
    protected $_options = null;

    /**
     * Source model for street line settings
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options !== null) {
            return $this->_options;
        }

        if (Mage::helper('postnl')->isEnterprise()) {
            $array = $this->_getEnterpriseOptions();

            $this->_options = $array;
            return $array;
        }

        $array = $this->_getCommunityOptions();

        $this->_options = $array;
        return $array;
    }

    /**
     * Gets options for community edition shops
     *
     * @return array
     */
    protected function _getCommunityOptions()
    {
        $request = Mage::app()->getRequest();
        $helper = Mage::helper('postnl');

        /**
         * Get the allowed number of address lines based on the current scope
         */
        if ($request->getParam('store')) {
            $lineCount = Mage::getStoreConfig(self::XML_PATH_COMMUNITY_STREET_LINES, $request->getParam('store'));
        } elseif ($request->getParam('website')) {
            $website = Mage::getModel('core/website')->load($request->getParam('website'), 'code');
            $lineCount = $website->getConfig(self::XML_PATH_COMMUNITY_STREET_LINES, $website->getId());
        } else {
            $lineCount = Mage::getStoreConfig(self::XML_PATH_COMMUNITY_STREET_LINES, Mage_Core_Model_App::ADMIN_STORE_ID);
        }

        /**
         * Build the option array
         */
        $array = array();
        for ($n = 1; $n <= $lineCount; $n++) {
            $array[] = array(
                'value' => $n,
                'label' => $helper->__('Street line #%s', $n),
            );
        }

        return $array;
    }

    /**
     * Gets options for enterprise edition shops
     *
     * @return array
     */
    protected function _getEnterpriseOptions()
    {
        $helper = Mage::helper('postnl');
        $lineCount = Mage::helper('customer/address')->getStreetLines();

        /**
         * Build the option array
         */
        $array = array();
        for ($n = 1; $n <= $lineCount; $n++) {
            $array[] = array(
                'value' => $n,
                'label' => $helper->__('Street line #%s', $n),
            );
        }

        return $array;
    }
}