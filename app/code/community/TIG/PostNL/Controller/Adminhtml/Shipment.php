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
class TIG_PostNL_Controller_Adminhtml_Shipment extends TIG_PostNL_Controller_Adminhtml_Abstract
{
    /**
     * @var TIG_PostNL_Model_Core_service_Shipment
     */
    protected $_serviceModel;

    /**
     * @return TIG_PostNL_Model_Core_service_Shipment
     */
    public function getServiceModel()
    {
        $serviceModel = $this->_serviceModel;
        if (!$serviceModel) {
            $serviceModel = Mage::getModel('postnl_core/service_shipment');
            $this->setServiceModel($serviceModel);
        }

        return $serviceModel;
    }

    /**
     * @param TIG_PostNL_Model_Core_service_Shipment $serviceModel
     *
     * @return $this
     */
    public function setServiceModel(TIG_PostNL_Model_Core_service_Shipment $serviceModel)
    {
        $this->_serviceModel = $serviceModel;

        return $this;
    }

    /**
     * Get shipment Ids from the request.
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getShipmentIds()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids', array());

        /**
         * Check if a shipment was selected.
         */
        if (!is_array($shipmentIds) || empty($shipmentIds)) {
            throw new TIG_PostNL_Exception(
                $this->__('Please select one or more shipments.'),
                'POSTNL-0013'
            );
        }

        return $shipmentIds;
    }

    /**
     * Get order Ids from the request.
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getOrderIds()
    {
        $orderIds = $this->getRequest()->getParam('order_ids', array());

        /**
         * Check if an order was selected.
         */
        if (!is_array($orderIds) || empty($orderIds)) {
            throw new TIG_PostNL_Exception(
                $this->__('Please select one or more orders.'),
                'POSTNL-0011'
            );
        }

        return $orderIds;
    }

    /**
     * Get the output of printing labels for an array of shipments.
     *
     * @param TIG_PostNL_Model_Core_Shipment[] $shipments
     *
     * @return string|false
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getMassLabelsOutput($shipments)
    {
        return $this->getServiceModel()->getMassLabelsOutput($shipments);
    }

    /**
     * Get the output of printing packing slips for an array of shipments.
     *
     * @param TIG_PostNL_Model_Core_Shipment[] $shipments
     *
     * @return bool|string
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _getMassPackingSlipsOutput($shipments)
    {
        return $this->getServiceModel()->getMassPackingSlipsOutput($shipments);
    }

    /**
     * Get all return labels for a shipment.
     *
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return TIG_PostNL_Model_Core_Shipment_Label[]|false
     */
    protected function _getReturnLabels($shipment)
    {
        return $this->getServiceModel()->getReturnLabels($shipment);
    }

    /**
     * Retrieves the shipping label for a given shipment ID.
     *
     * If the shipment has a stored label, it is returned. Otherwise a new one is generated.
     *
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     * @param boolean                                                        $confirm Optional parameter to also
     *                                                                                confirm the shipment
     * @param boolean|null                                                   $includeReturnLabels
     *
     * @return TIG_PostNL_Model_Core_Shipment_Label[]
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getLabels($shipment, $confirm = false, $includeReturnLabels = null)
    {
        return $this->getServiceModel()->getLabels($shipment, $confirm, $includeReturnLabels);
    }

    /**
     * Confirms the shipment without printing labels.
     *
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _confirmShipment($shipment)
    {
        return $this->getServiceModel()->confirmShipment($shipment);
    }

    /**
     * Gets the postnl shipment associated with a shipment
     *
     * @param int $shipmentId
     *
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _getPostnlShipment($shipmentId)
    {
        return $this->getServiceModel()->getPostnlShipment($shipmentId);
    }

    /**
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return $this
     * @throws Exception
     * @throws TIG_PostNL_Exception
     * @throws TIG_PostNL_Model_Core_Cif_Exception
     */
    protected function _updateShippingStatus(TIG_PostNL_Model_Core_Shipment $postnlShipment)
    {
        return $this->getServiceModel()->updateShippingStatus($postnlShipment);
    }

    /**
     * Load an array of shipments based on an array of shipmentIds and check if they're shipped using PostNL
     *
     * @param array|int $shipmentIds
     * @param boolean   $loadPostnlShipments Flag that determines whether the shipments will be loaded as
     *                                       Mage_Sales_Model_Shipment or TIG_PostNL_Model_Core_Shipment objects.
     * @param boolean   $throwException Flag whether an exception should be thrown when loading the shipment fails.
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _loadAndCheckShipments($shipmentIds, $loadPostnlShipments = false, $throwException = true)
    {
        return $this->getServiceModel()->loadAndCheckShipments($shipmentIds, $loadPostnlShipments, $throwException);
    }

    /**
     * Load a shipment based on a shipment ID.
     *
     * @param int     $shipmentId
     * @param boolean $loadPostnlShipments
     *
     * @return boolean|Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment
     */
    protected function _loadShipment($shipmentId, $loadPostnlShipments)
    {
        return $this->getServiceModel()->loadShipment($shipmentId, $loadPostnlShipments);
    }

    /**
     * Output the specified string as a pdf.
     *
     * @param string $filename
     * @param string $output
     *
     * @return $this
     * @throws Zend_Controller_Response_Exception
     */
    protected function _preparePdfResponse($filename, $output)
    {
        $this->getResponse()
             ->setHttpResponseCode(200)
             ->setHeader('Pragma', 'public', true)
             ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate', true)
             ->setHeader('Content-type', 'application/pdf', true)
             ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
             ->setHeader('Last-Modified', date('r'))
             ->setBody($output);

        return $this;
    }

    /**
     * Checks if any warnings were received while processing the shipments and/or orders. If any warnings are found they
     * are added to the adminhtml session as a notice.
     *
     * @return $this
     */
    protected function _checkForWarnings()
    {
        /**
         * Check if any warnings were registered
         */
        $cifWarnings = Mage::registry('postnl_cif_warnings');

        if (is_array($cifWarnings) && !empty($cifWarnings)) {
            $this->_addWarningMessages($cifWarnings, $this->__('PostNL replied with the following warnings:'));
        }

        $warnings = $this->getServiceModel()->getWarnings();

        if (!empty($warnings)) {
            $this->_addWarningMessages(
                $warnings,
                $this->__('The following shipments or orders could not be processed:')
            );
        }

        return $this;
    }

    /**
     * Add an array of warning messages to the adminhtml session.
     *
     * @param        $warnings
     * @param string $headerText
     *
     * @return $this
     * @throws TIG_PostNL_Exception
     */
    protected function _addWarningMessages($warnings, $headerText = '')
    {
        $helper = Mage::helper('postnl');

        /**
         * Create a warning message to display to the merchant.
         */
        $warningMessage = $headerText;
        $warningMessage .= '<ul class="postnl-warning">';

        /**
         * Add each warning to the message.
         */
        foreach ($warnings as $warning) {
            /**
             * Warnings must have a description.
             */
            if (empty($warning['description'])) {
                continue;
            }

            /**
             * Codes are optional for warnings, but must be present in the array. If no code is found in the warning we
             * add an empty one.
             */
            if (!isset($warning['code'])) {
                continue;
            }

            /**
             * Translate the individual parts of the message.
             */
            $descriptionMessages = explode(PHP_EOL, $warning['description']);
            $description = array();
            foreach ($descriptionMessages as $descriptionMessage) {
                if (empty($descriptionMessage)) {
                    continue;
                }

                $description[] = $this->__($descriptionMessage);
            }

            /**
             * If the code is empty, replace it with a null value.
             */
            $code = $warning['code'];
            if (empty($code)) {
                $code = null;
            }

            /**
             * Get the formatted warning message.
             */
            $warningText = $helper->getSessionMessage(
                $code,
                'warning',
                implode(' ', $description)
            );

            /**
             * Prepend the warning's entity ID if present.
             */
            if (!empty($warning['entity_id'])) {
                $warningText = $warning['entity_id'] . ': ' . $warningText;
            }

            /**
             * Build the message proper.
             */
            $warningMessage .= '<li>' . $warningText . '</li>';
        }

        $warningMessage .= '</ul>';

        /**
         * Add the warnings to the session.
         */
        Mage::helper('postnl')->addSessionMessage('adminhtml/session', null, 'notice',
            $warningMessage
        );

        return $this;
    }
}