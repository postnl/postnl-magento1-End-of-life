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

use \TIG_PostNL_Model_Core_Shipment as PostNLShipment;

class TIG_PostNL_Helper_ProductCode extends TIG_PostNL_Helper_Base
{
    /**
     * @param TIG_PostNL_Model_Core_Order    $postnlOrder
     * @param                                $storeId
     * @param                                $shipmentType
     *
     * @param TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return mixed
     */
    public function getDefault(
        $storeId,
        $shipmentType,
        TIG_PostNL_Model_Core_Order $postnlOrder = null,
        TIG_PostNL_Model_Core_Shipment $shipment = null
    ) {
        $orderInfo = $this->getOrderInfo($postnlOrder);

        $xpath = false;
        switch ($shipmentType) {
            case PostNLShipment::SHIPMENT_TYPE_DOMESTIC_COD:
                $xpath = PostNLShipment::XPATH_DEFAULT_STANDARD_COD_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_AVOND:
                if ($postnlOrder && $postnlOrder->hasOptions()) {
                    $xpath = $this->_getDefaultProductCodeXpathByOptions($postnlOrder);
                }

                if ($this->isBelgiumShipment($orderInfo)) {
                    $xpath = PostNLShipment::XPATH_DEFAULT_EVENING_BE_PRODUCT_OPTION;
                }

                if (!$xpath) {
                    $xpath = PostNLShipment::XPATH_DEFAULT_EVENING_PRODUCT_OPTION;
                }
                break;
            case PostNLShipment::SHIPMENT_TYPE_AVOND_COD:
                $xpath = PostNLShipment::XPATH_DEFAULT_EVENING_COD_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_PG:
                if ($this->isBelgiumShipment($orderInfo)) {
                    if ($this->getHelper()->getDomesticCountry() == 'BE') {
                        $xpath = PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_BE_BE_PRODUCT_OPTION;
                    } else {
                        if ($this->getHelper()->canUsePakjegemakBeNotInsured($storeId)) {
                            $xpath = PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_BE_NOT_INSURED_PRODUCT_OPTION;
                        } else {
                            $xpath = PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_NL_BE_PRODUCT_OPTION;
                        }
                    }
                } else {
                    $xpath = PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION;
                }
                break;
            case PostNLShipment::SHIPMENT_TYPE_PG_COD:
                $xpath = PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_COD_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_PGE:
                $xpath = PostNLShipment::XPATH_DEFAULT_PGE_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_PGE_COD:
                $xpath = PostNLShipment::XPATH_DEFAULT_PGE_COD_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_PA:
                $xpath = PostNLShipment::XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_EPS:
                if ($this->getHelper()->canUseEpsBEOnlyOption($storeId)
                    && $this->isBelgiumShipment($orderInfo)
                ) {
                    $xpath = PostNLShipment::XPATH_DEFAULT_EU_BE_PRODUCT_OPTION;
                } else {
                    $xpath = PostNLShipment::XPATH_DEFAULT_EU_PRODUCT_OPTION;
                }
                break;
            case PostNLShipment::SHIPMENT_TYPE_GLOBALPACK:
                $xpath = PostNLShipment::XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_BUSPAKJE:
                $xpath = PostNLShipment::XPATH_DEFAULT_BUSPAKJE_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_SUNDAY:
                $xpath = PostNLShipment::XPATH_DEFAULT_SUNDAY_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_SAMEDAY:
                $xpath = PostNLShipment::XPATH_DEFAULT_SAMEDAY_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_FOOD:
                $xpath = PostNLShipment::XPATH_DEFAULT_FOOD_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_COOLED:
                $xpath = PostNLShipment::XPATH_DEFAULT_COOLED_PRODUCT_OPTION;
                break;
            case PostNLShipment::SHIPMENT_TYPE_AGECHECK:
                if ($postnlOrder->isPakjeGemak()) {
                    $xpath = PostNLShipment::XPATH_DEFAULT_AGECHECK_PICKUP_PRODUCT_OPTION;
                } else {
                    $xpath = PostNLShipment::XPATH_DEFAULT_AGECHECK_DELIVERY_PRODUCT_OPTION;
                }
                break;
            case PostNLShipment::SHIPMENT_TYPE_BIRTHDAYCHECK:
                if ($postnlOrder->isPakjeGemak()) {
                    $xpath = PostNLShipment::XPATH_DEFAULT_BIRTHDAYCHECK_PICKUP_PRODUCT_OPTION;
                } else {
                    $xpath = PostNLShipment::XPATH_DEFAULT_BIRTHDAYCHECK_DELIVERY_PRODUCT_OPTION;
                }
                break;
            case PostNLShipment::SHIPMENT_TYPE_IDCHECK:
                if ($postnlOrder->isPakjeGemak()) {
                    $xpath = PostNLShipment::XPATH_DEFAULT_IDCHECK_PICKUP_PRODUCT_OPTION;
                } else {
                    $xpath = PostNLShipment::XPATH_DEFAULT_IDCHECK_DELIVERY_PRODUCT_OPTION;
                }
                break;
            case PostNLShipment::SHIPMENT_TYPE_EXTRAATHOME:
                $xpath = PostNLShipment::XPATH_DEFAULT_EXTRA_AT_HOME_PRODUCT_OPTION;
                break;

            //no default
        }

        /**
         * If the shipment is not EU or global, it's dutch (AKA a 'standard' shipment).
         */
        if (!$xpath && $postnlOrder && $postnlOrder->hasOptions()) {
            $xpath = $this->_getDefaultProductCodeXpathByOptions($postnlOrder);
        }

        /**
         * Dutch shipments may use an alternative default option when the shipment's base grand total exceeds a
         * specified amount.
         */
        $useAlternativeDefault = Mage::getStoreConfig(PostNLShipment::XPATH_USE_ALTERNATIVE_DEFAULT, $storeId);
        if (!$xpath && $useAlternativeDefault) {
            /**
             * Alternative default option usage is enabled.
             */
            $maxShipmentAmount = Mage::getStoreConfig(PostNLShipment::XPATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT, $storeId);
            $shipmentAmount = $shipment !== null ? $shipment->getShipmentBaseGrandTotal() : $orderInfo->getBaseGrandTotal();
            if ($shipmentAmount > $maxShipmentAmount) {
                /**
                 * The shipment's base grand total exceeds the specified amount: use the alternative default.
                 */
                $xpath = PostNLShipment::XPATH_ALTERNATIVE_DEFAULT_OPTION;
            }
        }

        /**
         * If we still don't have an xpath, the shipment is a regular domestic shipment.
         */
        if (!$xpath) {
            $helper = $this->getHelper('deliveryOptions');

            if ($helper->getDomesticCountry() == 'NL') {
                $xpath = PostNLShipment::XPATH_DEFAULT_STANDARD_PRODUCT_OPTION;
            }

            $shippingAddress = $orderInfo->getShippingAddress();
            if (!$xpath &&
                $shippingAddress->getCountryId() == 'NL' &&
                $helper->canUseDutchProducts()
            ) {
                $xpath = PostNLShipment::XPATH_DEFAULT_STANDARD_PRODUCT_OPTION_NETHERLANDS;
            }
        }

        /**
         * If xpath is still empty, fall back to the default product option
         */
        if (!$xpath) {
            $xpath = PostNLShipment::XPATH_DEFAULT_STANDARD_PRODUCT_OPTION;
        }

        /**
         * Get the product code configured to the xpath.
         */
        $productCode = Mage::getStoreConfig($xpath, $storeId);

        // If the country doesn't exist in either Priority EPS or Priority RoW list, fall back to regular shipment
        $pepsProducts = array_keys(
            Mage::getSingleton('postnl_core/system_config_source_allProductOptions')->getPepsOptions(true)
        );
        
        if (in_array($productCode, $pepsProducts)
            && !$this->getHelper('cif')->countryAvailableInPepsLists($shipment->getShippingAddress()->getCountryId())
        ) {
            $productCode = $this->getNonPriorityProductcode($shipmentType);
        }

        return $productCode;
    }

    /**
     * @param $shipmentType
     *
     * @return int
     */
    private function getNonPriorityProductcode($shipmentType)
    {
        if ($shipmentType == 'globalpack') {
            return "4945";
        }

        return "4952";
    }

    /**
     * Gets the xpath for the default product option by saved PostNL Order options. Currently only the
     * 'only_stated_address' option is supported, but this may be expanded in future releases.
     *
     * If multiple options are applicable, the first applicable option is applied.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     *
     * @return bool|string
     */
    protected function _getDefaultProductCodeXpathByOptions(TIG_PostNL_Model_Core_Order $postnlOrder)
    {
        /**
         * If this shipment has no PostNL order or that order doesn't have any options, do nothing.
         */
        if (!$postnlOrder || !$postnlOrder->hasOptions()) {
            return false;
        }

        /**
         * If the options are empty, do nothing.
         */
        $options = $postnlOrder->getOptions();
        if (empty($options)) {
            return false;
        }

        /**
         * Unserialize the options and check loop through them.
         */
        foreach ($options as $option => $value) {
            /**
             * If the option has no true value, move on to the next option.
             */
            if (!$value) {
                continue;
            }

            switch ($option) {
                case 'only_stated_address':
                    return PostNLShipment::XPATH_DEFAULT_STATED_ADDRESS_ONLY_OPTION;
                //no default
            }
        }

        return false;
    }

    /**
     * Check if the shipping destination of this shipment is Belgium.
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $order
     *
     * @return bool
     */
    protected function isBelgiumShipment($order)
    {
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            return false;
        }

        if ($shippingAddress->getCountryId() == 'BE') {
            return true;
        }

        return false;
    }

    /**
     * @param bool $postnlOrder
     *
     * @return Mage_Sales_Model_Order|Mage_Sales_Model_Quote|null
     */
    protected function getOrderInfo($postnlOrder = false)
    {
        $orderInfo = null;
        if ($postnlOrder) {
            /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
            $orderInfo = $postnlOrder->getOrder();
        }

        if ($orderInfo === null) {
            $orderInfo = Mage::getModel('checkout/cart')->getQuote();
        }

        return $orderInfo;
    }
}
