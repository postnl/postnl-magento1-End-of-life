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
class TIG_PostNL_Controller_Sales extends Mage_Core_Controller_Front_Action
{
    /**
     * @var string
     */
    protected $_errorRedirect = '*/*/noroute';

    /**
     * @return string
     */
    public function getErrorRedirect()
    {
        return $this->_errorRedirect;
    }

    /**
     * View the returns page.
     *
     * @return void
     */
    public function returnsAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $order = Mage::registry('current_order');
        if (!Mage::helper('postnl')->canPrintReturnLabelForOrder($order)) {
            $this->_redirect($this->getErrorRedirect());
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        /**
         * @var Mage_Customer_Block_Account_Navigation $navigationBlock
         */
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }
        $this->renderLayout();
    }

    /**
     * Print the return labels for the current shipment.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function printReturnLabelAction()
    {
        if (!$this->_loadValidPostnlShipment()) {
            $this->_redirect($this->getErrorRedirect());
            return $this;
        }

        /**
         * @var TIG_postNL_Model_Core_Shipment $postnlShipment
         */
        $helper = Mage::helper('postnl');
        $postnlShipment = Mage::registry('current_postnl_shipment');

        try {
            /**
             * Get the labels from CIF.
             */
            $labels = $this->_getReturnLabels($postnlShipment);
            if (false === $labels) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'Unable to retrieve return labels for this shipment.'
                    ),
                    'POSTNL-0202'
                );
            }

            /**
             * Merge the labels and print them.
             */
            $labelModel = Mage::getModel('postnl_core/label');
            $output = $labelModel->setLabelSize('A6')->createPdf($labels);

            $filename = 'PostNL Return Labels-' . date('YmdHis') . '.pdf';

            $this->_prepareDownloadResponse($filename, $output);
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('core/session', $e->getCode(), 'error',
                'An error occurred while retrieving the return labels. Please try again.'
            );

            $this->_redirect('postnl/*/returns', array('order_id' => $postnlShipment->getOrderId()));
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('core/session', null, 'error',
                'An error occurred while retrieving the return labels. Please try again.'
            );

            $this->_redirect('postnl/*/returns', array('order_id' => $postnlShipment->getOrderId()));
            return $this;
        }

        return $this;
    }

    /**
     * Init layout, messages and set active block for customer
     *
     * @return null
     */
    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        /**
         * @var Mage_Customer_block_Account_Navigation $navigationBlock
         */
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive($this->getErrorRedirect());
        }
        $this->renderLayout();
    }

    /**
     * Try to load valid order by order_id and register it.
     *
     * @param int|null $orderId
     *
     * @return boolean
     */
    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('order_id');
        }
        if (!$orderId) {
            $this->_redirect($this->getErrorRedirect());
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            return true;
        } else {
            $this->_redirect($this->getErrorRedirect());
        }
        return false;
    }

    /**
     * Try to load valid PostNL shipment by order_id and register it.
     *
     * @param int|null $shipmentIncrementId
     *
     * @return boolean
     */
    protected function _loadValidPostnlShipment($shipmentIncrementId = null)
    {
        if (null === $shipmentIncrementId) {
            $shipmentIncrementId = (int) $this->getRequest()->getParam('shipment_id');
        }

        if (!$shipmentIncrementId) {
            $this->_forward('noRoute');
            return false;
        }

        $shipmentId = Mage::getResourceModel('postnl/order_shipment')->getShipmentId($shipmentIncrementId);

        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipmentId, 'shipment_id');

        if ($this->_canViewPostnlShipment($postnlShipment)) {
            Mage::register('current_postnl_shipment', $postnlShipment);
            return true;
        } else {
            $this->_redirect($this->getErrorRedirect());
        }
        return false;
    }

    /**
     * Check order view availability
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check PostNL shipment view availability
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return boolean
     */
    protected function _canViewPostnlShipment(TIG_PostNL_Model_Core_Shipment $postnlShipment)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        $order = $postnlShipment->getOrder();

        if ($postnlShipment->getId()
            && $postnlShipment->isConfirmed()
            && $order->getId()
            && $order->getCustomerId()
            && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get all return labels for a shipment.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return TIG_PostNL_Model_Core_Shipment_Label[]|false
     */
    protected function _getReturnLabels($postnlShipment)
    {
        if (!$postnlShipment->canPrintReturnLabels()) {
            return false;
        }

        if ($postnlShipment->hasLabels()) {
            return $postnlShipment->getReturnLabels();
        }

        $postnlShipment = $this->_generateLabels($postnlShipment);

        $labels = $postnlShipment->getReturnLabels();

        if (!$postnlShipment->getReturnLabelsPrinted()) {
            $postnlShipment->setReturnLabelsPrinted(true)
                           ->save();
        }

        return $labels;
    }

    /**
     * Generate shipping labels for this given shipment.
     *
     * @param TIG_PostNL_Model_Core_Shipment  $postnlShipment
     *
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _generateLabels($postnlShipment)
    {
        /**
         * If the shipment does not have a barcode, generate one.
         */
        if (!$postnlShipment->getMainBarcode() && $postnlShipment->canGenerateBarcode()) {
            $postnlShipment->generateBarcodes();
        }

        $printReturnLabel = Mage::helper('postnl/cif')->isReturnsEnabled($postnlShipment->getStoreId());
        if ($printReturnLabel && $postnlShipment->canGenerateReturnBarcode()) {
            $postnlShipment->generateReturnBarcode();
        }

        /**
         * Generate new shipping labels without confirming.
         */
        $postnlShipment->generateLabel()
                       ->save();

        return $postnlShipment;
    }
}