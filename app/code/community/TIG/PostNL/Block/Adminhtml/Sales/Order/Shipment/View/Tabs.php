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
class TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructor for the tabs container
     *
     * @return \TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_Tabs
    @see Mage_Adminhtml_Block_Widget_Tabs::__construct()
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_shipment_view_tabs');
        $this->setDestElementId('sales_order_shipment_view');
        $this->setTitle(Mage::helper('sales')->__('Shipment View'));
    }

    /**
     * Add the main tabs to the page. Layout XML may be used to add more if desired
     *
     * @return Mage_Adminhtml_Block_Widget_Tabs::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        /**
         * Add the 'information' tab. this contains all default features of the shipment view page and is selected by
         * default.
         */
        $this->addTab(
            'shipment_info',
            array(
                'label'     => Mage::helper('sales')->__('Information'),
                'content'   => $this->getLayout()
                                    ->getBlock('form')
                                    ->toHtml(),
            )
        );

        /**
         * Load a PosTNL shipment corresponding to the current Magento shipment.
         *
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipment
         */
        $shipment = Mage::registry('current_shipment');
        $postnlShipment = Mage::getModel('postnl_core/shipment')->loadByShipment($shipment);

        /**
         * Only show the status history tab if a postnl shipment entity was found for the current shipment and the
         * current admin user is allowed to view the complete shipment status history. If the shipment uses a custom
         * barcode we also can't show the status history, because custom barcodes can't be tracked.
         */
        $historyAllowed = Mage::helper('postnl')->checkIsPostnlActionAllowed('view_complete_status');
        if ($historyAllowed && $postnlShipment->getId() && !$postnlShipment->hasCustomBarcode()) {
            /**
             * Add the status history tab. This is added by PostNL
             */
            $this->addTab(
                'shipment_status_history',
                array(
                    'label'     => Mage::helper('postnl')->__('Shipping event history'),
                    'url'       => $this->getUrl(
                                       'postnl_admin/adminhtml_shipment/statusHistory',
                                       array(
                                           '_current'    => true,
                                           'shipment_id' => $shipment->getId(),
                                       )
                                   ),
                    'class'     => 'ajax',
                )
            );
        }

        return parent::_prepareLayout();
    }
}
