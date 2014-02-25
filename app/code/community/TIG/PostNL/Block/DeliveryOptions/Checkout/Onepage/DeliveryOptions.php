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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_DeliveryOptions_Checkout_Onepage_DeliveryOptions extends Mage_Core_Block_Template
{
    /**
     * Xpaths to extra fee config settings.
     */
    const XPATH_EVENING_TIMEFRAME_FEE  = 'postnl/delivery_options/evening_timeframe_fee';
    const XPATH_PAKJEGEMAK_EXPRESS_FEE = 'postnl/delivery_options/pakjegemak_express_fee';

    /**
     * Make sure we have a delivery date as this is required for all further requests.
     *
     * @return Mage_Core_Block_Template::_construct()
     */
    protected function _construct()
    {
        $shippingAddress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
        $this->setShippingAddress($shippingAddress);

        $postcode = $shippingAddress->getPostcode();

        $this->setPostcode($postcode);

        $deliveryDate = $this->_getDeliveryDate($postcode);
        $this->setDeliveryDate($deliveryDate);

        return parent::_construct();
    }

    /**
     * Get the currenct store's base currency.
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        if ($this->hasBaseCurrency()) {
            return $this->getData('base_currency');
        }

        $baseCurrency  = Mage::app()->getStore()->getBaseCurrencyCode();

        $this->setBaseCurrency($baseCurrency);
        return $baseCurrency;
    }

    /**
     * Get the fee charged for evening timeframes.
     *
     * @return float
     */
    public function getEveningFee()
    {
        $storeId = Mage::app()->getStore()->getId();

        $eveningFee = (float) Mage::getStoreConfig(self::XPATH_EVENING_TIMEFRAME_FEE, $storeId);
        return $eveningFee;
    }

    /**
     * Get the fee charged for PakjeGemak Express.
     *
     * @return float
     */
    public function getExpressFee()
    {
        $storeId = Mage::app()->getStore()->getId();

        $expressFee = (float) Mage::getStoreConfig(self::XPATH_PAKJEGEMAK_EXPRESS_FEE, $storeId);
        return $expressFee;
    }

    /**
     * Get either the evening or the express fee as a currency value.
     *
     * @param string $type
     *
     * @return string
     */
    public function getFeeText($type)
    {
        switch ($type) {
            case 'evening':
                $fee = $this->getEveningFee();
                break;
            case 'express':
                $fee = $this->getExpressFee();
                break;
            default:
                return '';
        }

        /**
         * If no fee is entered or an invalid value was entered, return an empty string.
         */
        if (!$fee || $fee > 2 || $fee < 0) {
            return '';
        }

        $baseCurrency = $this->getBaseCurrency();
        $currencyModel = Mage::app()->getLocale()->currency($baseCurrency);

        $feeText = $currencyModel->toCurrency($fee);

        return $feeText;
    }

    /**
     * Checks whether PakjeGemak locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakjeGemak()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakjeGemak = Mage::helper('postnl/deliveryOptions')->canUsePakjeGemak();
        return $canUsePakjeGemak;
    }

    /**
     * Checks whether PakjeGemak Express locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakjeGemakExpress()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakjeGemakExpress = Mage::helper('postnl/deliveryOptions')->canUsePakjeGemakExpress();
        return $canUsePakjeGemakExpress;
    }

    /**
     * Checks whether Pakket Automaat locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakketAutomaat()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakketAutomaat = Mage::helper('postnl/deliveryOptions')->canUsePakketAutomaat();
        return $canUsePakketAutomaat;
    }

    /**
     * Checks whether timeframes are allowed.
     *
     * @return boolean
     */
    public function canUseTimeframes()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakketAutomaat = Mage::helper('postnl/deliveryOptions')->canUseTimeframes();
        return $canUsePakketAutomaat;
    }

    /**
     * Checks whether evening timeframes are allowed.
     *
     * @return boolean
     */
    public function canUseEveningTimeframes()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakketAutomaat = Mage::helper('postnl/deliveryOptions')->canUseEveningTimeframes();
        return $canUsePakketAutomaat;
    }

    /**
     * get the first possible delivery date from PostNL.
     *
     * @param string $postcode
     *
     * @return string
     */
    protected function _getDeliveryDate($postcode)
    {
        $storeId = Mage::app()->getStore()->getId();

        $cif = Mage::getModel('postnl_deliveryoptions/cif');
        $response = $cif->setStoreId(Mage::app()->getStore()->getId())
                        ->getDeliveryDate($postcode);

        return $response;
    }
}

