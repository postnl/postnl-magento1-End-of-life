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
class TIG_PostNL_Model_Carrier_Quote_Address_Total_Shipping
    extends Mage_Sales_Model_Quote_Address_Total_Shipping
{
    /**
     * Collect totals information about shipping
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        Mage_Sales_Model_Quote_Address_Total_Abstract::collect($address);

        $address->setWeight(0);
        $address->setFreeMethodWeight(0);
        $this->_setAmount(0)
            ->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $method = $address->getShippingMethod();

        /**
         * If the shipping method is not PostNL, load the regular shipping total model to process the total. We can't
         * just return the parent, because another extension might rewrite the regular total model. This way the rewrite
         * is only ignored for PostNL shipments at a slight cost to performance (roughly 0.0005s).
         */
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($method)) {
            return Mage::getModel('sales/quote_address_total_shipping')->collect($address);
        }

        $freeAddress= $address->getFreeShipping();

        $addressWeight    = $address->getWeight();
        $freeMethodWeight = $address->getFreeMethodWeight();

        $addressQty = 0;

        /**
         * @var Mage_Sales_Model_Quote_Item $item
         * @var Mage_Sales_Model_Quote_Item $child
         */
        foreach ($items as $item) {
            /**
             * Skip if this item is virtual
             */
            if ($item->getProduct()->isVirtual()) {
                continue;
            }

            /**
             * Children weight we calculate for parent
             */
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if ($child->getProduct()->isVirtual()) {
                        continue;
                    }
                    $addressQty += $child->getTotalQty();

                    if (!$item->getProduct()->getWeightType()) {
                        $itemWeight = $child->getWeight();
                        $itemQty    = $child->getTotalQty();
                        $rowWeight  = $itemWeight*$itemQty;
                        $addressWeight += $rowWeight;
                        if ($freeAddress || $child->getFreeShipping()===true) {
                            $rowWeight = 0;
                        } elseif (is_numeric($child->getFreeShipping())) {
                            $freeQty = $child->getFreeShipping();
                            if ($itemQty>$freeQty) {
                                $rowWeight = $itemWeight*($itemQty-$freeQty);
                            }
                            else {
                                $rowWeight = 0;
                            }
                        }
                        $freeMethodWeight += $rowWeight;
                        $item->setRowWeight($rowWeight);
                    }
                }
                if ($item->getProduct()->getWeightType()) {
                    $itemWeight = $item->getWeight();
                    $rowWeight  = $itemWeight*$item->getQty();
                    $addressWeight+= $rowWeight;
                    if ($freeAddress || $item->getFreeShipping()===true) {
                        $rowWeight = 0;
                    } elseif (is_numeric($item->getFreeShipping())) {
                        $freeQty = $item->getFreeShipping();
                        if ($item->getQty()>$freeQty) {
                            $rowWeight = $itemWeight*($item->getQty()-$freeQty);
                        }
                        else {
                            $rowWeight = 0;
                        }
                    }
                    $freeMethodWeight+= $rowWeight;
                    $item->setRowWeight($rowWeight);
                }
            }
            else {
                if (!$item->getProduct()->isVirtual()) {
                    $addressQty += $item->getQty();
                }
                $itemWeight = $item->getWeight();
                $rowWeight  = $itemWeight*$item->getQty();
                $addressWeight+= $rowWeight;
                if ($freeAddress || $item->getFreeShipping()===true) {
                    $rowWeight = 0;
                } elseif (is_numeric($item->getFreeShipping())) {
                    $freeQty = $item->getFreeShipping();
                    if ($item->getQty()>$freeQty) {
                        $rowWeight = $itemWeight*($item->getQty()-$freeQty);
                    }
                    else {
                        $rowWeight = 0;
                    }
                }
                $freeMethodWeight+= $rowWeight;
                $item->setRowWeight($rowWeight);
            }
        }

        if (isset($addressQty)) {
            $address->setItemQty($addressQty);
        }

        $address->setWeight($addressWeight);
        $address->setFreeMethodWeight($freeMethodWeight);

        $address->collectShippingRates();

        $this->_setAmount(0)
            ->_setBaseAmount(0);

        if (!$method) {
            return $this;
        }

        $postnlOrder = Mage::getModel('postnl_core/order');
        $postnlOrder->load($address->getQuoteId(), 'quote_id');

        $type = false;
        if ($postnlOrder->getId() && $postnlOrder->getIsActive()) {
            $type = $postnlOrder->getType();
        }

        /**
         * @var Mage_Sales_Model_Quote_Address_Rate $rate
         */
        foreach ($address->getAllShippingRates() as $rate) {
            if ($rate->getCode() != $method) {
                continue;
            }

            $price = $rate->getPrice();

            $includingTax = false;
            if (Mage::getSingleton('tax/config')->shippingPriceIncludesTax()) {
                $includingTax = true;
            }

            $fee = 0;
            if ($type == 'PGE') {
                $fee = Mage::helper('postnl/deliveryOptions')->getExpressFee(false, $includingTax, false);
            } elseif ($type == 'Avond' ) {
                $fee = Mage::helper('postnl/deliveryOptions')->getEveningFee(false, $includingTax, false);
            } elseif ($type == TIG_PostNL_Helper_DeliveryOptions_Fee::FEE_TYPE_SUNDAY ) {
                $fee = Mage::helper('postnl/deliveryOptions_fee')->getSundayFee(false, $includingTax, false);
            }

            $fee += Mage::helper('postnl/deliveryOptions')->getOptionsFee($postnlOrder, false, $includingTax, false);

            $price += $fee;

            $amountPrice = $address->getQuote()->getStore()->convertPrice($price, false);

            $this->_setAmount($amountPrice);
            $this->_setBaseAmount($price);

            $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();

            $address->setShippingDescription(trim($shippingDescription, ' -'));
            break;
        }

        return $this;
    }
}
