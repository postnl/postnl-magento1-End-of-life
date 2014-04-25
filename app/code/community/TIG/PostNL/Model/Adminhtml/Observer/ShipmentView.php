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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
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
     * @return TIG_PostNL_Model_Adminhtml_Observer_ShipmentView
     *
     * @event adminhtml_block_html_before
     *
     * @observer postnl_adminhtml_shipmentview
     */
    public function addPrintLabelButton(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('postnl');

        /**
         * check if the extension is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

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

        /**
         * Check if the current shipment was placed with PostNL
         */
        $shipment = Mage::registry('current_shipment');
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        if (!in_array($shipment->getOrder()->getShippingMethod(), $postnlShippingMethods)) {
            return $this;
        }

        $this->addPostnlButtons($block, $shipment);

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
        $deleteLabelsAllowed      = $helper->checkIsPostnlActionAllowed('delete_labels');
        $resetConfirmAllowed      = $helper->checkIsPostnlActionAllowed(array('reset_confirmation', 'delete_labels'));
        $sendTrackAndTraceAllowed = $helper->checkIsPostnlActionAllowed('send_track_and_trace');

        /**
         * Add a button to print this shipment's shipping labels
         */
        if ($printAllowed) {
            $printShippingLabelUrl = $this->getPrintShippingLabelUrl($shipment->getId());

            $block->addButton('print_shipping_label', array(
                'label'   => $helper->__('PostNL - Print Shipping Label'),
                'onclick' => "printLabel('{$printShippingLabelUrl}')",
                'class'   => 'download',
            ));
        }

        /**
         * Add a button to reset the shipment's confirmation status
         */
        if ($postnlShipment->canResetConfirmation() && $resetConfirmAllowed) {
            $resetConfirmationUrl = $this->getResetConfirmationUrl($shipment->getId());
            $resetWarningMessage = $helper->__(
                'Are you sure that you wish to reset the confirmation status of this shipment? You will need to '
                . 'confirm this shipment with PostNL again before you can send it. This action will remove all barcodes'
                . ' and labels associated with this shipment. You can not undo this action.'
            );

            $block->addButton('reset_confirmation', array(
                'label'   => $helper->__('PostNL - Change Confirmation'),
                'onclick' => "deleteConfirm('"
                    . $resetWarningMessage
                    . "', '"
                    . $resetConfirmationUrl
                    . "')",
                'class'   => 'delete',
            ));
        }

        /**
         * Update the send tracking info button so that it sends our info, instead of the default
         */
        if ($postnlShipment->isConfirmed() && $sendTrackAndTraceAllowed) {
            $resendTrackAndTraceUrl = $this->getResendTrackAndTraceUrl($shipment->getId());

            $block->updateButton('save', 'label', $helper->__('PostNL - Send Tracking Information'));
            $block->updateButton('save', 'onclick',
                "deleteConfirm('"
                . $helper->__('Are you sure you want to send PostNL tracking information to the customer?')
                . "', '" . $resendTrackAndTraceUrl . "')"
            );
        }

        /**
         * Add a button to remove any stored shipping labels for this shipment.
         */
        if ($postnlShipment->hasLabels() && !$postnlShipment->isConfirmed() && $deleteLabelsAllowed) {
            $removeLabelsUrl = $this->getRemoveLabelsUrl($shipment->getId());
            $removeLabelsWarningMessage = $helper->__(
                "Are you sure that you wish to remove this shipment\'s shipping label? You will need to print a new "
                . "shipping label before you can send this shipment."
            );

            $block->addButton('remove_shipping_labels', array(
                'label'   => $helper->__('PostNL - Remove Shipping Label'),
                'onclick' => "deleteConfirm('"
                    . $removeLabelsWarningMessage
                    . "', '"
                    . $removeLabelsUrl
                    . "')",
                'class'   => 'delete',
            ));
        }

        /**
         * Add a button to confirm this shipment.
         */
        if (!$postnlShipment->isConfirmed() && $confirmAllowed) {
            $confirmUrl = $this->getConfirmUrl($shipment->getId());

            $block->addButton('confirm_shipment', array(
                'label'   => $helper->__('PostNL - Confirm Shipment'),
                'onclick' => "setLocation('{$confirmUrl}')",
                'class'   => 'save',
            ));
        }

        return $this;
    }

    /**
     * Get adminhtml url for PostNL print shipping label action
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
}
