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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Core_System_Config_Source_ShippingMethods
{
    /**
     * Return an option array of carriers and shipping methods. If $isActiveOnlyFlag is set to true, only active
     * carriers and their methods will be returned. {@inheritdoc}
     *
     * @param boolean $isMultiSelect
     * @param boolean $isActiveOnlyFlag
     *
     * @return array
     */
    public function toOptionArray($isMultiSelect = false, $isActiveOnlyFlag = false)
    {
        $methods = array();

        /**
         * @var Mage_Shipping_Model_Carrier_Abstract $carrierModel
         */
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if ($isActiveOnlyFlag && !$carrierModel->isActive()) {
                continue;
            }

            try {
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
                continue;
            }

            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = array(
                'label'   => $carrierTitle,
                'value' => array(),
            );

            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $methods[$carrierCode]['value'][] = array(
                    'value' => $carrierCode . '_' . $methodCode,
                    'label' => '[' . $carrierCode . '] ' . $methodTitle,
                );
            }
        }

        return $methods;
    }

    /**
     * Get all options as a flat array.
     *
     * @param bool $isActiveOnlyFlag
     *
     * @return array
     */
    public function toArray($isActiveOnlyFlag = false)
    {
        $methods = array();

        /**
         * @var Mage_Shipping_Model_Carrier_Abstract $carrierModel
         */
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if ($isActiveOnlyFlag && !$carrierModel->isActive()) {
                continue;
            }

            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }

            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $methods[] = $carrierCode . '_' . $methodCode;
            }
        }

        return $methods;
    }
}