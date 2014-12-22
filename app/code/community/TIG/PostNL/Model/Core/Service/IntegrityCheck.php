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
class TIG_PostNL_Model_Core_Service_IntegrityCheck
{
    /**
     * Error codes that may be detected by the integrity check.
     */
    const ERROR_MISSING_MAGENTO_ORDER    = 'POSTNL-0214';
    const ERROR_MISSING_MAGENTO_QUOTE    = 'POSTNL-0215';
    const ERROR_MISSING_MAGENTO_SHIPMENT = 'POSTNL-0216';
    const ERROR_QUOTE_MISMATCH           = 'POSTNL-0217';
    const ERROR_ORDER_MISMATCH           = 'POSTNL-0218';

    /**
     * @var array
     */
    protected $_errors = array();

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param array $errors
     *
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->_errors = $errors;

        return $this;
    }

    /**
     * @param string $type
     * @param array  $error
     *
     * @return $this
     */
    public function addError($type, array $error)
    {
        $errors = $this->getErrors();
        $errors[$type][] = $error;

        $this->setErrors($errors);
        return $this;
    }

    /**
     * Check the integrity of the PostNL tables. This is done to prevent the deletion of orders, quotes, shipments etc.
     * from causing errors.
     *
     * @return array
     */
    public function integrityCheck()
    {
        $this->validatePostnlOrderIntegrity();
        $this->validatePostnlShipmentIntegrity();

        $errors = $this->getErrors();

        return $errors;
    }

    /**
     * Check the integrity of PostNL orders.
     *
     * @return $this
     */
    public function validatePostnlOrderIntegrity()
    {
        $resource = Mage::getSingleton('core/resource');

        $postnlOrderCollection = Mage::getResourceModel('postnl_core/order_collection')
                                     ->addFieldToSelect(array('entity_id', 'quote_id', 'order_id'));

        $select = $postnlOrderCollection->getSelect();

        /**
         * Join sales_flat_order table.
         */
        $select->joinLeft(
            array('order' => $resource->getTableName('sales/order')),
            '`main_table`.`order_id`=`order`.`entity_id`',
            array(
                'magento_order_id' => 'order.entity_id',
            )
        );

        /**
         * Join sales_flat_quote table.
         */
        $select->joinLeft(
            array('quote' => $resource->getTableName('sales/quote')),
            '`main_table`.`quote_id`=`quote`.`entity_id`',
            array(
                'magento_quote_id' => 'quote.entity_id',
            )
        );

        foreach ($postnlOrderCollection as $postnlOrder) {
            if ($postnlOrder->hasOrderId()) {
                $this->_validateOrderId($postnlOrder);
            }

            if ($postnlOrder->hasQuoteId()) {
                $this->_validateQuoteId($postnlOrder);
            }
        }

        return $this;
    }

    /**
     * Check the integrity of PostNL shipments.
     *
     * @return $this
     */
    public function validatePostnlShipmentIntegrity()
    {
        $resource = Mage::getSingleton('core/resource');

        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection')
                                     ->addFieldToSelect(array('entity_id', 'shipment_id', 'order_id'));

        $select = $postnlShipmentCollection->getSelect();

        /**
         * Join sales_flat_shipment table.
         */
        $select->joinLeft(
            array('shipment' => $resource->getTableName('sales/shipment')),
            '`main_table`.`shipment_id`=`shipment`.`entity_id`',
            array(
                'magento_shipment_id' => 'shipment.entity_id',
            )
        );

        /**
         * Join sales_flat_order table.
         */
        $select->joinLeft(
            array('order' => $resource->getTableName('sales/order')),
            '`main_table`.`order_id`=`order`.`entity_id`',
            array(
                'magento_order_id' => 'order.entity_id',
            )
        );

        foreach ($postnlShipmentCollection as $postnlShipment) {
            if ($postnlShipment->hasOrderId()) {
                $this->_validateOrderId($postnlShipment);
            }

            if ($postnlShipment->hasShipmentId()) {
                $this->_validateShipmentId($postnlShipment);
            }
        }

        return $this;
    }

    /**
     * Validate the order ID of a PostNL order or shipment.
     *
     * @param TIG_PostNL_Model_Core_Order|TIG_PostNL_Model_Core_Shipment $postnlObject
     *
     * @return bool
     */
    protected function _validateOrderId($postnlObject)
    {
        if (!$postnlObject->getData('magento_order_id')) {
            if ($postnlObject instanceof TIG_PostNL_Model_Core_Order) {
                $type = 'postnl_core/order';
            } else {
                $type = 'postnl_core/shipment';
            }

            $this->addError(
                $type,
                array(
                    'id'         => $postnlObject->getId(),
                    'error_code' => self::ERROR_MISSING_MAGENTO_ORDER
                )
            );
            return false;
        }

        if (
            ($postnlObject instanceof TIG_PostNL_Model_Core_Order)
            && $postnlObject->hasQuoteId()
        ) {
            $orderId = $postnlObject->getOrderId();
            $quoteId = $postnlObject->getQuoteId();
            $orderQuoteId = Mage::getResourceSingleton('postnl_core/order')->getOrderQuoteId($orderId);

            if ($quoteId != $orderQuoteId) {
                $this->addError(
                    'postnl_core/order',
                    array(
                        'id'         => $postnlObject->getId(),
                        'error_code' => self::ERROR_QUOTE_MISMATCH
                    )
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Validate the quote ID of a PostNL order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     *
     * @return bool
     */
    protected function _validateQuoteId(TIG_PostNL_Model_Core_Order $postnlOrder)
    {
        if (!$postnlOrder->getData('magento_quote_id')) {
            $this->addError(
                'postnl_core/order',
                array(
                    'id'         => $postnlOrder->getId(),
                    'error_code' => self::ERROR_MISSING_MAGENTO_QUOTE
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Validate the shipment ID of a PostNL shipment.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return bool
     */
    protected function _validateShipmentId(TIG_PostNL_Model_Core_Shipment $postnlShipment)
    {
        if (!$postnlShipment->getData('magento_shipment_id')) {
            $this->addError(
                'postnl_core/shipment',
                array(
                    'id'         => $postnlShipment->getId(),
                    'error_code' => self::ERROR_MISSING_MAGENTO_SHIPMENT
                )
            );
            return false;
        }

        $shipmentId = $postnlShipment->getShipmentId();
        $orderId    = $postnlShipment->getOrderId();
        $shipmentOrderId = Mage::getResourceSingleton('postnl_core/shipment')->getShipmentOrderId($shipmentId);

        if ($orderId != $shipmentOrderId) {
            $this->addError(
                'postnl_core/shipment',
                array(
                    'id'         => $postnlShipment->getId(),
                    'error_code' => self::ERROR_ORDER_MISMATCH
                )
            );
            return false;
        }

        return true;
    }
}