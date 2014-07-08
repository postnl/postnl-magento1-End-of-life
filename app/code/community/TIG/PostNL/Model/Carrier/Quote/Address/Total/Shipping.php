<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
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
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();

        /**
         * If the shipping method is not PostNL, load the regular shipping total model to process the total. We can't
         * just return the parent, because another extension might rewrite the regular total model. This way the rewrite
         * is only ignored for PostNL shipments at a slight cost to performance (roughly 0.0005s).
         */
        if (!in_array($method, $postnlShippingMethods)) {
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

        /**
         * @var Mage_Sales_Model_Quote_Address_Rate $rate
         */
        foreach ($address->getAllShippingRates() as $rate) {
            if ($rate->getCode() != $method) {
                continue;
            }

            $price = $rate->getPrice();

            $postnlOrder = Mage::getModel('postnl_core/order');

            $postnlOrder->load($address->getQuoteId(), 'quote_id');

            if ($postnlOrder->getId() && $postnlOrder->getIsActive()) {
                $type = $postnlOrder->getType();
            } else {
                $amountPrice = $address->getQuote()->getStore()->convertPrice($rate->getPrice(), false);
                $this->_setAmount($amountPrice);
                $this->_setBaseAmount($price);
                $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                $address->setShippingDescription(trim($shippingDescription, ' -'));

                break;
            }

            $includingTax = false;
            if (Mage::getSingleton('tax/config')->shippingPriceIncludesTax()) {
                $includingTax = true;
            }

            $fee = 0;
            if ($type == 'PGE') {
                $fee = Mage::helper('postnl/deliveryOptions')->getExpressFee(false, $includingTax, false);
            } elseif ($type == 'Avond' ) {
                $fee = Mage::helper('postnl/deliveryOptions')->getEveningFee(false, $includingTax, false);
            }

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
