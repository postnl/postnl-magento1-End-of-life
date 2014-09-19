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
class TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_Tab_StatusHistory extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Class constructor
     *
     * @return TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_Tab_StatusHistory
     *
     * @see Mage_Adminhtml_Block_Widget_Grid::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        /**
         * Set some base variables for this grid
         */
        $this->setEmptyText(Mage::helper('postnl')->__('No status history available.'));
        $this->setId('sales_order_shipment_status_history_grid');
        $this->setDefaultSort('timestamp');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);

        $postnlShipment = Mage::registry('current_postnl_shipment');
        $this->setPostnlShipment($postnlShipment);

        return $this;
    }

    /**
     * Get the basic collection for this grid
     *
     * @return $this
     *
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareCollection
     */
    protected function _prepareCollection()
    {
        $postnlShipmentId = $this->getPostnlShipment()->getId();

        $collection = Mage::getResourceModel('postnl_core/shipment_status_history_collection');
        $collection->addFieldToFilter('parent_id', array('eq' => $postnlShipmentId));

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepares the grid's columns for rendering
     *
     * @return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns
     *
     * @see Mage_Adminhtml_Block_Widget_Grid::addColumn()
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('postnl');

        $this->addColumn('date',
            array(
                'header'      => $helper->__('Date'),
                'index'       => 'timestamp',
                'type'        => 'datetime',
                'align'       => 'left',
                'width'       => '150px',
                'renderer'    => 'adminhtml/widget_grid_column_renderer_date',
                'filter_time' => true,
        ));

        $this->addColumn('timestamp',
            array(
                'header'   => $helper->__('Time'),
                'index'    => 'timestamp',
                'align'    => 'left',
                'width'    => '150px',
                'renderer' => 'postnl_adminhtml/widget_grid_column_renderer_time',
                'filter'   => false,
                'sortable' => false,
        ));

        $this->addColumn('code',
            array(
                'header' => $helper->__('Status Code'),
                'index'  => 'code',
                'width'  => '100px',
        ));

        $this->addColumn('description',
            array(
                'header'   => $helper->__('Description'),
                'index'    => 'description',
                'align'    => 'left',
                'renderer' => 'postnl_adminhtml/widget_grid_column_renderer_translate',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Gets a link to this shipment's MijnPakket page as the grid's header.
     *
     * @return string
     */
    public function getGridHeader()
    {
        $helper = Mage::helper('postnl');

        $postnlShipment = $this->getPostnlShipment();
        $url = $postnlShipment->getBarcodeUrl();

        $urlTitle = $helper->__('Mijnpakket');
        $urlText = $helper->__('View this shipment in mijnpakket');
        $html = "<a href='{$url}' title='{$urlTitle}' target='_blank'>{$urlText}</a>";

        return $html;
    }
}
