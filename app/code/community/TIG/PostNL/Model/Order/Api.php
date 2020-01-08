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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Order_Api extends Mage_Sales_Model_Order_Api
{
    /**
     * Retrieve full order information
     *
     * @param string $orderIncrementId
     * @return array
     */
    public function info($orderIncrementId)
    {
        $result = parent::info($orderIncrementId);

        /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($result['order_id'], 'order_id');

        if (!$postnlOrder->getId()) {
            return $result;
        }

        if ($postnlOrder->hasPakjeGemakAddress())  {
            $pakjeGemakAddress = $postnlOrder->getPakjeGemakAddress()->getData();
            $pakjeGemakAddressId = array_shift($pakjeGemakAddress);
            $pakjeGemakAddress['address_id'] = $pakjeGemakAddressId;

            if ($postnlOrder->hasPgLocationCode()) {
                $pakjeGemakAddress['location_code'] = $postnlOrder->getPgLocationCode();
            }

            if ($postnlOrder->hasPgRetailNetworkId()) {
                $pakjeGemakAddress['retail_network_id'] = $postnlOrder->getPgRetailNetworkId();
            }

            $result['pakjegemak_address_id'] = $pakjeGemakAddressId;
            $result['pakjegemak_address'] = $pakjeGemakAddress;
        }

        if ($type = $postnlOrder->getType()) {
            $result['postnl_type'] = $type;
        }

        if ($postnlOrder->hasDeliveryDate()) {
            $result['postnl_delivery_date'] = $postnlOrder->getDeliveryDate();
        }

        if ($postnlOrder->hasExpectedDeliveryTimeStart()) {
            $result['postnl_expected_delivery_time_start'] = $postnlOrder->getExpectedDeliveryTimeStart();
        }

        if ($postnlOrder->hasExpectedDeliveryTimeEnd()) {
            $result['postnl_expected_delivery_time_end'] = $postnlOrder->getExpectedDeliveryTimeEnd();
        }

        return $result;
    }
}