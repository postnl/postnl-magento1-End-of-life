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
class TIG_PostNL_Model_Core_Api_V2 extends TIG_PostNL_Model_Core_Api
{
    public function createShipments($orderIds = array())
    {
        $resultArray = array();

//        $serviceModel = Mage::getModel('postnl_core/service_shipment');
//        foreach ($orderIds as $orderId) {
//            $serviceModel->resetWarnings();
//            $shipmentId = $serviceModel->createShipment($orderId);
//
//            $resultArray[] = array(
//                'order_id'    => $orderId,
//                'shipment_id' => $shipmentId,
//                'warning'     => $serviceModel->getWarnings()
//            );
//        }

        return array(
            array(
                'order_id' => 1,
                'shipment_id' => 2,
                'warning' => array(
                    array(
                                       'entity_id' => 1,
                                       'code' => null,
                                       'description' => 'test'//$e->getMessage(),
                                   )
                ),
                'error' => array(
                    array(
                                     'entity_id' => 1,
                                     'code' => null,
                                     'description' => 'test'//$e->getMessage(),
                                 )
                ),
            )
        );
        return array(
            array(
                'order_id' => 1,
                'shipment_id' => 2,
                'warning' => array(
                    array(
                        'entity_id' => 1,
                        'code' => 'test',
                        'description' => 'test',
                    ),
                    array(
                        'entity_id' => 2,
                        'code' => 'test2',
                        'description' => 'test2',
                    ),
                ),
            ),
        );

//        $return = array(
//            array(
//                'order_id' => 1,
//                'shipment_id' => 2,
//            ),
//            array(
//                'order_id' => 2,
//                'shipment_id' => 3,
//                'warning' => 'test warning',
//            ),
//        );

        return $resultArray;
    }

    public function fullPostnlFlow($orderIds = array())
    {
        return array('test2');
    }

    public function confirmShipments($shipmentsIds = array())
    {
        return array('test3');
    }

    public function printShippingLabels($shipmentsIds = array())
    {
        return array('test4');
    }

    /**
     * Confirm and print shipping labels for the requested shipments.
     *
     * @param array       $shipmentIds
     * @param string|bool $labelSize
     * @param int|null    $labelStartPosition
     *
     * @return array
     * @throws Mage_Api_Exception
     */
    public function confirmAndPrintShippingLabels($shipmentIds = array(), $labelSize = false,
                                                  $labelStartPosition = null)
    {
        /**
         * Validate if the labelSize parameter has a valid value.
         */
        if (!empty($labelSize) && $labelSize != 'A4' && $labelSize != 'A6') {
            $this->_fault(
                'POSTNL-0226',
                Mage::helper('postnl')->__("Only A4 or A6 are valid values for the 'labelSize' parameter.")
            );
        }

        /**
         * Validate if the labelStartPosition parameter has a valid value.
         */
        if (!is_null($labelStartPosition)
            && (!is_int($labelStartPosition)
                || $labelStartPosition < 1
                || $labelStartPosition > 4
            )
        ) {
            $this->_fault(
                'POSTNL-0227',
                Mage::helper('postnl')->__(
                    "The 'labelStartPosition' parameter must contain an integer value between 1 and 4."
                )
            );
        }

        /**
         * Get service model used for processing this request.
         */
        $serviceModel = Mage::getModel('postnl_core/service_shipment');

        /**
         * Get the shipping labels for each shipment.
         */
        $response = array();
        foreach ($shipmentIds as $shipmentId) {
            /**
             * Reset the warnings so we don't add the warnings generated by the previous shipment.
             */
            $serviceModel->resetWarnings();

            /**
             * Add the resulting data to the response.
             */
            $response[] = $this->_getLabels($serviceModel, $shipmentId, true, $labelSize, $labelStartPosition);
        }

        return $response;
    }

    /**
     * Get the current Track & Trace URL for the requested shipments as well as their barcode.
     *
     * @param array $shipmentIds
     *
     * @return array
     */
    public function getTrackAndTraceUrls($shipmentIds = array())
    {
        $helper       = Mage::helper('postnl');

        /**
         * Get service model used for processing this request.
         */
        $serviceModel = Mage::getModel('postnl_core/service_shipment');

        /**
         * Update the shipping status for each requested shipment.
         */
        $response = array();
        foreach ($shipmentIds as $shipmentId) {
            /**
             * Reset the warnings so we don't add the warnings generated by the previous shipment.
             */
            $serviceModel->resetWarnings();

            /**
             * Get the PostNL Shipment for the current Shipment ID.
             */
            $postnlShipment = $serviceModel->getPostnlShipment($shipmentId);

            /**
             * If the PostNL shipment does not exist, return an error.
             */
            if (!$postnlShipment || !is_object($postnlShipment) || !$postnlShipment->getId()) {
                $response = array(
                    'order_id'            => null,
                    'shipment_id'         => $shipmentId,
                    'track_and_trace_url' => null,
                    'main_barcode'        => null,
                    'warning'             => null,
                    'error'               => array(
                        array(
                            'entity_id'   => $shipmentId,
                            'code'        => null,
                            'description' => $helper->__('No PostNL Shipment found for shipment ID #%s', $shipmentId)
                        ),
                    ),
                );

                return $response;
            }

            /**
             * Add the resulting data to the response.
             */
            $response[] = array(
                'order_id'            => $postnlShipment->getOrderId(),
                'shipment_id'         => $postnlShipment->getShipmentId(),
                'track_and_trace_url' => $postnlShipment->getBarcodeUrl(),
                'main_barcode'        => $postnlShipment->getMainBarcode(),
            );

            /**
             * Add any warnings that may have occurred.
             */
            if ($serviceModel->hasWarnings()) {
                $response['warning'] = $serviceModel->getWarnings();
            }
        }

        return $response;
    }

    /**
     * @param array $shipmentIds
     *
     * @return array
     *
     * @throws Exception
     * @throws TIG_PostNL_Exception
     * @throws TIG_PostNL_Model_Core_Cif_Exception
     */
    public function getStatusInfo($shipmentIds = array())
    {
        /**
         * Get service model used for processing this request.
         */
        $serviceModel = Mage::getModel('postnl_core/service_shipment');

        /**
         * Update the shipping status for each requested shipment.
         */
        $response = array();
        foreach ($shipmentIds as $shipmentId) {
            /**
             * Reset the warnings so we don't add the warnings generated by the previous shipment.
             */
            $serviceModel->resetWarnings();

            /**
             * Add the resulting data to the response.
             */
            $response[] = $this->_getStatusInfo($serviceModel, $shipmentId);
        }

        Mage::log(var_export($response, true), null, 'soap.log', true);
        return $response;
    }

    /**
     * Get the shipping labels for the requested shipments.
     *
     * @param TIG_PostNL_Model_Core_Service_Shipment $serviceModel
     * @param int                                    $shipmentId
     * @param bool                                   $confirm
     * @param bool|string                            $labelSize
     * @param int                                    $labelStartPosition
     *
     * @return array
     */
    protected function _getLabels(TIG_PostNL_Model_Core_Service_Shipment $serviceModel, $shipmentId, $confirm = false,
                                  $labelSize = false, $labelStartPosition = 0)
    {
        $helper = Mage::helper('postnl');

        /**
         * Get the PostNL shipment for this shipment ID.
         */
        $postnlShipment = $serviceModel->getPostnlShipment($shipmentId);

        /**
         * If the PostNL shipment does not exist, return an error.
         */
        if (!$postnlShipment || !is_object($postnlShipment) || !$postnlShipment->getId()) {
            $response = array(
                'order_id'    => null,
                'shipment_id' => $shipmentId,
                'label'       => null,
                'warning'     => null,
                'error'       => array(
                    array(
                        'entity_id'   => $shipmentId,
                        'code'        => 'POSTNL-0225',
                        'description' => $helper->__('No PostNL Shipment found for shipment ID #%s.', $shipmentId)
                    ),
                ),
            );

            return $response;
        }

        /**
         * Form the base response array.
         */
        $response = array(
            'order_id'    => $postnlShipment->getOrderId(),
            'shipment_id' => $postnlShipment->getShipmentId(),
            'label'       => null,
        );

        $errors = array();
        try {
            /**
             * Check whether we should also get the return labels based on the shop's configuration.
             */
            $printReturnLabels = Mage::helper('postnl')->canPrintReturnLabelsWithShippingLabels(
                $postnlShipment->getStoreId()
            );

            /**
             * Get the actual labels.
             */
            $labels = $serviceModel->getLabels($postnlShipment, $confirm, $printReturnLabels);

            /**
             * Get the label model which will convert the base64_encoded pdf strings to a single, merged pdf.
             */
            $labelModel = Mage::getModel('postnl_core/label')
                              ->setLabelSize($labelSize)
                              ->setLabelCounter($labelStartPosition);

            /**
             * Create the merged pdf.
             */
            $label = $labelModel->createPdf($labels);

            /**
             * Base64_encode the merged pdf and add it to the response array.
             */
            $response['label'] = base64_encode($label);
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);

            $code = $e->getCode();
            if (empty($code)) {
                $code = null;
            }

            $errors[] = array(
                'entity_id'   => $shipmentId,
                'code'        => $code,
                'description' => $e->getMessage(),
            );
        } catch (Exception $e) {
            $helper->logException($e);
            $errors[] = array(
                'entity_id'   => $shipmentId,
                'code'        => null,
                'description' => $e->getMessage(),
            );
        }

        /**
         * Add any warnings that may have occurred.
         */
        if ($serviceModel->hasWarnings()) {
            $response['warning'] = $serviceModel->getWarnings();
        }

        /**
         * Add any errors that may have occurred.
         */
        if (!empty($errors)) {
            $response['error'] = $errors;
        }

        return $response;
    }

    /**
     * Get shipping status info for the requested shipment.
     *
     * @param TIG_PostNL_Model_Core_Service_Shipment $serviceModel
     * @param int                                    $shipmentId
     *
     * @return array
     */
    protected function _getStatusInfo(TIG_PostNL_Model_Core_Service_Shipment $serviceModel, $shipmentId)
    {
        $helper = Mage::helper('postnl');

        /**
         * Get the PostNL shipment for this shipment ID.
         */
        $postnlShipment = $serviceModel->getPostnlShipment($shipmentId);

        /**
         * If the PostNL shipment does not exist, return an error.
         */
        if (!$postnlShipment || !is_object($postnlShipment) || !$postnlShipment->getId()) {
            $response = array(
                'order_id'       => null,
                'shipment_id'    => $shipmentId,
                'shipping_phase' => null,
                'return_phase'   => null,
                'warning'        => null,
                'error'          => array(
                    array(
                        'entity_id'   => $shipmentId,
                        'code'        => 'POSTNL-0225',
                        'description' => $helper->__('No PostNL Shipment found for shipment ID #%s.', $shipmentId)
                    ),
                ),
            );

            return $response;
        }

        $errors = array();
        try {
            /**
             * Request a shipping status update.
             */
            $postnlShipment->updateShippingStatus();
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);

            $code = $e->getCode();
            if (empty($code)) {
                $code = null;
            }

            /**
             * If the error is a 'collo not found'-error add it as a warning (a recurring error that does not
             * necessarily indicate that anything is wrong). Add it as a true error otherwise.
             */
            if ($code != TIG_PostNL_Model_Core_Cif_Abstract::SHIPMENT_NOT_FOUND_ERROR_NUMBER) {
                $serviceModel->addWarning(
                    array(
                        'entity_id'   => $shipmentId,
                        'code'        => $code,
                        'description' => $e->getMessage(),
                    )
                );
            } else {
                $errors[] = array(
                    'entity_id'   => $shipmentId,
                    'code'        => $code,
                    'description' => $e->getMessage(),
                );
            }
        } catch (Exception $e) {
            $helper->logException($e);
            $errors[] = array(
                'entity_id'   => $shipmentId,
                'code'        => null,
                'description' => $e->getMessage(),
            );
        }

        /**
         * Return the shipment's data.
         */
        $response = array(
            'order_id'       => $postnlShipment->getOrderId(),
            'shipment_id'    => $postnlShipment->getShipmentId(),
            'shipping_phase' => $postnlShipment->getShippingPhase(),
            'return_phase'   => $postnlShipment->getReturnPhase(),
            'warning'        => $serviceModel->getWarnings(),
        );

        /**
         * Add any warnings that may have occurred.
         */
        if ($serviceModel->hasWarnings()) {
            $response['warning'] = $serviceModel->getWarnings();
        }

        /**
         * Add any errors that may have occurred.
         */
        if (!empty($errors)) {
            $response['error'] = $errors;
        }

        return $response;
    }
}