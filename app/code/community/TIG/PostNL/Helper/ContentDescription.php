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
class TIG_PostNL_Helper_ContentDescription extends Mage_Core_Helper_Abstract
{
    const MAX_STRING_LENGTH = 35;

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return string
     */
    public function get(Mage_Sales_Model_Order_Shipment $shipment)
    {
        return $this->formatDescription($shipment->getAllItems());
    }

    /**
     * @param $items
     *
     * @return string
     */
    protected function formatDescription($items)
    {
        $desc = $this->getProductsListedAsString($items);
        return strlen($desc) > self::MAX_STRING_LENGTH ? substr($desc, 0, self::MAX_STRING_LENGTH - 3).'...' : $desc;
    }

    /**
     * @param $items
     *
     * @return string
     */
    protected function getProductsListedAsString($items)
    {
        $description = '';
        /** @var Mage_Sales_Model_Order_Shipment_Item $item */
        foreach ($items as $item) {
            $description.= ' '. $item->getName(). ',';
        }

        return rtrim(trim($description), ',');
    }
}
