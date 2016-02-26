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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Helper_Parcelware extends TIG_PostNL_Helper_Data
{
    /**
     * XML path to auto confirm setting
     */
    const XPATH_AUTO_CONFIRM = 'postnl/parcelware_export/auto_confirm';

    /**
     * XML path to the active/inactive setting
     */
    const XPATH_ACTIVE = 'postnl/parcelware_export/active';

    /**
     * XML path to the customer code setting.
     */
    const XPATH_CUSTOMER_CODE = 'postnl/cif/customer_code';

    /**
     * AutoConfirmEnabled flag
     *
     * @var boolean|null $_autoConfirmEnabled
     */
    protected $_autoConfirmEnabled = null;

    /**
     * Gets the autoConfirmEnabled flag
     *
     * @return boolean|null
     */
    public function getAutoConfirmEnabled()
    {
        return $this->_autoConfirmEnabled;
    }

    /**
     * Sets the autoConfirmEnabled flag
     *
     * @param boolean $autoConfirmEnabled
     *
     * @return $this
     */
    public function setAutoConfirmEnabled($autoConfirmEnabled)
    {
        $this->_autoConfirmEnabled = $autoConfirmEnabled;

        return $this;
    }

    /**
     * Splits a barcode into its component parts.
     *
     * @param string   $barcode
     * @param int|bool $storeId
     *
     * @return array
     */
    public function splitBarcode($barcode, $storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $type = substr($barcode, 0, 2);

        $customerCode = (string) Mage::getStoreConfig(self::XPATH_CUSTOMER_CODE, $storeId);

        $number = substr($barcode, 2  + strlen($customerCode));

        $barcodeComponents = array(
            'type'   => $type,
            'range'  => $customerCode,
            'number' => $number,
        );

        return $barcodeComponents;
    }

    /**
     * Check to see if Parcelware export functionality is enabled.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function isParcelwareExportEnabled($storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $active = Mage::getStoreConfigFlag(self::XPATH_ACTIVE, $storeId);

        return $active;
    }

    /**
     * Checks if auto confirm is enabled
     *
     * @return boolean
     */
    public function isAutoConfirmEnabled()
    {
        if ($this->getAutoConfirmEnabled() !== null) {
            return $this->getAutoConfirmEnabled();
        }

        $autoConfirmEnabled = Mage::getStoreConfigFlag(self::XPATH_AUTO_CONFIRM, Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->setAutoConfirmEnabled($autoConfirmEnabled);
        return $autoConfirmEnabled;
    }
}
