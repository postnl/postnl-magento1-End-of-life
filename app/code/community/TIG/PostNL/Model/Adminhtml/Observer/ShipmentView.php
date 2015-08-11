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
 *
 * Observer to edit the shipment view
 */
class TIG_PostNL_Model_Adminhtml_Observer_ShipmentView
{
    /**
     * The block we want to edit.
     */
    const SHIPMENT_VIEW_BLOCK_NAME = 'adminhtml/sales_order_shipment_view';

    /**
     * Observer that adds a print label button to the shipment view page.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event adminhtml_block_html_before
     *
     * @observer postnl_adminhtml_shipmentview
     */
    public function addPrintLabelButton(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         */
        $block = $observer->getBlock();
        $shipmentViewClass = Mage::getConfig()->getBlockClassName(self::SHIPMENT_VIEW_BLOCK_NAME);

        /**
         * @var Mage_Adminhtml_BLock_Sales_Order_Shipment_View $block
         */
        if (!($block instanceof $shipmentViewClass)) {
            return $this;
        }

        $helper = Mage::helper('postnl');

        /**
         * check if the extension is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        /**
         * Check if the current shipment was placed with PostNL.
         *
         * @var Mage_Sales_Model_Order_Shipment $shipment
         */
        $shipment = Mage::registry('current_shipment');
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
            return $this;
        }

        $this->addPostnlButtons($block, $shipment);

        /**
         * Update the back button if the 'come_from_postnl' parameter is set.
         */
        if (Mage::app()->getRequest()->getParam('come_from_postnl')) {
            $comeFrom = Mage::helper('core')->urlDecode(Mage::app()->getRequest()->getParam('come_from_postnl'));
            $comeFromUrl = $block->getUrl($comeFrom);

            $block->updateButton('back', 'onclick', 'setLocation(\'' . $comeFromUrl . '\')');
        }

        return $this;
    }

    /**
     * Add new PostNL buttons to the page.
     *
     * @param Mage_Adminhtml_BLock_Sales_Order_Shipment_View $block
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return $this
     */
    public function addPostnlButtons(Mage_Adminhtml_BLock_Sales_Order_Shipment_View $block,
                                     Mage_Sales_Model_Order_Shipment $shipment)
    {
        $helper = Mage::helper('postnl');

        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipment
         */
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');

        /**
         * Check which actions are allowed.
         */
        $confirmAllowed           = $helper->checkIsPostnlActionAllowed('confirm');
        $printAllowed             = $helper->checkIsPostnlActionAllowed('print_label');
        $printReturnLabelAllowed  = $helper->checkIsPostnlActionAllowed(array('print_label', 'print_return_label'));
        $printPackingSlipAllowed  = $helper->checkIsPostnlActionAllowed(array('print_label', 'print_packing_slip'));
        $deleteLabelsAllowed      = $helper->checkIsPostnlActionAllowed('delete_labels');
        $resetConfirmAllowed      = $helper->checkIsPostnlActionAllowed(array('reset_confirmation', 'delete_labels'));
        $sendTrackAndTraceAllowed = $helper->checkIsPostnlActionAllowed('send_track_and_trace');
        $convertToBuspakjeAllowed = $helper->checkIsPostnlActionAllowed('convert_to_buspakje');
        $convertToPackageAllowed  = $helper->checkIsPostnlActionAllowed('convert_to_package');
        $sendReturnLabelAllowed   = $helper->checkIsPostnlActionAllowed(
            array('print_label', 'print_return_label', 'send_return_label_email')
        );

        /**
         * Add a button to confirm this shipment.
         */
        if (!$postnlShipment->isConfirmed()
            && $postnlShipment->canConfirm()
            && $confirmAllowed
        ) {
            $confirmUrl = $this->getConfirmUrl($shipment->getId());

            $block->addButton(
                'confirm_shipment',
                array(
                    'label'   => $helper->__('PostNL - Confirm Shipment'),
                    'onclick' => "setLocation('{$confirmUrl}')",
                    'class'   => 'save',
                ),
                10
            );
        }

        /**
         * Add a button to print this shipment's shipping labels.
         */
        if ($printAllowed) {
            $printShippingLabelUrl = $this->getPrintShippingLabelUrl($shipment->getId());

            $block->addButton(
                'print_shipping_label',
                array(
                    'label'   => $helper->__('PostNL - Print Shipping Label'),
                    'onclick' => "printLabel('{$printShippingLabelUrl}')",
                    'class'   => 'download',
                ),
                20
            );
        }

        /**
         * Add a button to print this shipment's packing slip.
         */
        if ($printPackingSlipAllowed) {
            $printPackingSlipUrl = $this->getPrintPackingSlipUrl($shipment->getId());

            $block->addButton(
                'print_packing_slip',
                array(
                    'label'   => $helper->__('PostNL - Print Packing Slip'),
                    'onclick' => "printLabel('{$printPackingSlipUrl}')",
                    'class'   => 'download',
                ),
                30
            );
        }

        /**
         * Add a button to print this shipment's return labels.
         */
        if ($printReturnLabelAllowed
            && $postnlShipment->canPrintReturnLabels()
            && Mage::helper('postnl')->isReturnsEnabled($postnlShipment->getStoreId())
        ) {
            $printShippingLabelUrl = $this->getPrintReturnLabelUrl($shipment->getId());

            $block->addButton(
                'print_return_label',
                array(
                    'label'   => $helper->__('PostNL - Print Return Label'),
                    'onclick' => "printLabel('{$printShippingLabelUrl}')",
                    'class'   => 'download',
                ),
                40
            );
        }

        /**
         * Add a button to send the PostNL track & trace email.
         */
        if ($postnlShipment->isConfirmed()) {
            if ($sendTrackAndTraceAllowed) {
                $resendTrackAndTraceUrl = $this->getResendTrackAndTraceUrl($shipment->getId());
                $block->removeButton('save');

                $block->addButton(
                    'send_track_and_trace_email',
                    array(
                        'label'   => $helper->__('PostNL - Send Tracking Information'),
                        'onclick' => "setLocation('{$resendTrackAndTraceUrl}')",
                        'class'   => 'save',
                    ),
                    50
                );
            } else {
                $block->updateButton('save', 'level', 50);
            }
        }

        /**
         * Add the send return label button.
         */
        if ($sendReturnLabelAllowed
            && $postnlShipment->canSendReturnLabelEmail()
            && Mage::helper('postnl')->isReturnsEnabled($postnlShipment->getStoreId())
        ) {
            $sendReturnLabelEmailUrl = $this->getSendReturnLabelEmailUrl($shipment->getId());

            $block->addButton(
                'send_return_label_email',
                array(
                    'label'   => $helper->__('PostNL - Send Return Label Email'),
                    'onclick' => "setLocation('{$sendReturnLabelEmailUrl}')",
                    'class'   => 'save',
                ),
                60
            );
        }

        /**
         * Add a button to convert this shipment to a buspakje shipment.
         */
        if ($postnlShipment->canConvertShipmentToBuspakje()
            && $convertToBuspakjeAllowed
            && (!$postnlShipment->isConfirmed()
                || ($postnlShipment->canResetConfirmation()
                    && $resetConfirmAllowed
                )
            )
        ) {
            $convertToBuspakjeUrl = $this->getConvertToBuspakjeUrl($shipment->getId());
            $convertToBuspakjeMessage = $helper->__(
                'Are you sure you wish to convert this shipment to a letter box parcel? You will need to confirm this' .
                ' shipment with PostNL again before you can send it. This action will remove all barcodes and labels ' .
                'associated with this shipment. You can not undo this action.'
            );

            $block->addButton(
                'convert_to_buspakje',
                array(
                    'label'   => $helper->__('PostNL - Convert to Letter Box Parcel'),
                    'onclick' => "deleteConfirm('"
                        . $convertToBuspakjeMessage
                        . "', '"
                        . $convertToBuspakjeUrl
                        . "')",
                    'class'   => 'btn-reset',
                ),
                70
            );
        }

        /**
         * Add a button to convert this shipment to a package shipment.
         */
        if ($postnlShipment->canConvertShipmentToPackage() && $convertToPackageAllowed
            && (!$postnlShipment->isConfirmed()
                || ($postnlShipment->canResetConfirmation()
                    && $resetConfirmAllowed
                )
            )
        ) {
            $convertToPackageUrl = $this->getConvertToPackageUrl($shipment->getId());
            $convertToPackageMessage = $helper->__(
                'Are you sure you wish to convert this shipment to a package? You will need to confirm this shipment ' .
                'with PostNL again before you can send it. This action will remove all barcodes and labels associated' .
                ' with this shipment. You can not undo this action.'
            );

            $block->addButton(
                'convert_to_package',
                array(
                    'label'   => $helper->__('PostNL - Convert to Package'),
                    'onclick' => "deleteConfirm('"
                        . $convertToPackageMessage
                        . "', '"
                        . $convertToPackageUrl
                        . "')",
                    'class'   => 'btn-reset',
                ),
                80
            );
        }

        /**
         * Add a button to reset the shipment's confirmation status.
         */
        if ($postnlShipment->canResetConfirmation() && $resetConfirmAllowed) {
            $resetConfirmationUrl = $this->getResetConfirmationUrl($shipment->getId());
            $resetWarningMessage = $helper->__(
                'Are you sure that you wish to reset the confirmation status of this shipment? You will need to '
                . 'confirm this shipment with PostNL again before you can send it. This action will remove all barcodes'
                . ' and labels associated with this shipment. You can not undo this action.'
            );

            $block->addButton(
                'reset_confirmation',
                array(
                    'label'   => $helper->__('PostNL - Change Confirmation'),
                    'onclick' => "deleteConfirm('"
                        . $resetWarningMessage
                        . "', '"
                        . $resetConfirmationUrl
                        . "')",
                    'class'   => 'delete',
                ),
                90
            );
        }

        /**
         * Add a button to remove any stored shipping labels for this shipment.
         */
        if (!$postnlShipment->isConfirmed()
            && $postnlShipment->hasLabels()
            && $deleteLabelsAllowed
        ) {
            $removeLabelsUrl            = $this->getRemoveLabelsUrl($shipment->getId());
            $removeLabelsWarningMessage = $helper->__(
                "Are you sure that you wish to remove this shipment\'s shipping label? You will need to print a new "
                . "shipping label before you can send this shipment."
            );

            $block->addButton(
                'remove_shipping_labels',
                array(
                    'label'   => $helper->__('PostNL - Remove Shipping Label'),
                    'onclick' => "deleteConfirm('"
                        . $removeLabelsWarningMessage
                        . "', '"
                        . $removeLabelsUrl
                        . "')",
                    'class'   => 'delete',
                ),
                100
            );
        }

        return $this;
    }

    /**
     * Get adminhtml url for PostNL print shipping label action.
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getPrintShippingLabelUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/printLabel',
            array('shipment_id' => $shipmentId)
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL print return label action.
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getPrintReturnLabelUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/printReturnLabel',
            array('shipment_id' => $shipmentId)
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL print packing slip action.
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getPrintPackingSlipUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/printPackingSlip',
            array('shipment_id' => $shipmentId)
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL reset confirmation action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getResetConfirmationUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/resetConfirmation',
            array('shipment_id' => $shipmentId)
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL remove labels action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getRemoveLabelsUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/removeLabels',
            array('shipment_id' => $shipmentId)
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL re-send track and trace action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getResendTrackAndTraceUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/sendTrackAndTrace',
            array('shipment_id' => $shipmentId)
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL confirm shipment action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getConfirmUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/confirm',
            array(
                'shipment_id'    => $shipmentId,
                'return_to_view' => true,
            )
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL convert_to_buspakje shipment action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getConvertToBuspakjeUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/convertToBuspakje',
            array(
                'shipment_id'    => $shipmentId,
                'return_to_view' => true,
            )
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL convert_to_package shipment action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getConvertToPackageUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/convertToPackage',
            array(
                'shipment_id'    => $shipmentId,
                'return_to_view' => true,
            )
        );

        return $url;
    }

    /**
     * Get adminhtml url for PostNL send_return_label shipment action
     *
     * @param int $shipmentId The ID of the current shipment
     *
     * @return string
     */
    public function getSendReturnLabelEmailUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl_admin/adminhtml_shipment/sendReturnLabelEmail',
            array(
                'shipment_id'    => $shipmentId,
                'return_to_view' => true,
            )
        );

        return $url;
    }
}
