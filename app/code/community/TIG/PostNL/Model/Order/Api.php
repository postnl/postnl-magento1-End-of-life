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

        $pakjeGemakAddress = $postnlOrder->getPakjeGemakAddress();
        if (!$pakjeGemakAddress) {
            return $result;
        }

        $pakjeGemakAddress = $pakjeGemakAddress->getData();
        $result['pakjegemak_address_id'] = array_shift($pakjeGemakAddress);

        $pakjeGemakAddress['address_id'] = $result['pakjegemak_address_id'];
        $result['pakjegemak_address'] = $pakjeGemakAddress;

        return $result;
    }
}