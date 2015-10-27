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
class TIG_PostNL_Block_Adminhtml_Sales_Returns_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
    /**
     * Initialization
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('confirm_date');
        $this->setId('postnl_returns_grid');
    }

    /**
     * Prepare and set collection of grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        $resource = Mage::getSingleton('core/resource');

        $select = $collection->getSelect();

        /**
         * Join sales_flat_order table.
         */
        $select->joinInner(
            array('order' => $resource->getTableName('sales/order')),
            '`main_table`.`order_id`=`order`.`entity_id`',
            array(
                'shipping_method' => 'order.shipping_method',
            )
        );

        /**
         * Join sales_flat_order_address table.
         */
        $select->joinLeft(
            array('shipping_address' => $resource->getTableName('sales/order_address')),
            "`main_table`.`order_id`=`shipping_address`.`parent_id` AND `shipping_address`.`address_type`='shipping'",
            array(
                'postcode'   => 'shipping_address.postcode',
                'country_id' => 'shipping_address.country_id',
            )
        );

        /**
         * Join tig_postnl_shipment table.
         */
        $select->joinLeft(
            array('postnl_shipment' => $resource->getTableName('postnl_core/shipment')),
            '`main_table`.`entity_id`=`postnl_shipment`.`shipment_id`',
            array(
                'confirm_date'          => 'postnl_shipment.confirm_date',
                'main_barcode'          => 'postnl_shipment.main_barcode',
                'confirm_status'        => 'postnl_shipment.confirm_status',
                'return_labels_printed' => 'postnl_shipment.return_labels_printed',
                'return_phase'          => 'postnl_shipment.return_phase',
                'shipment_type'         => 'postnl_shipment.shipment_type',
            )
        );

        /** @var $postnlShipmentModelClass TIG_PostNL_Model_Core_Shipment */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus     = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;
        $colloNotFoundStatus = $postnlShipmentModelClass::SHIPPING_PHASE_NOT_APPLICABLE;

        $collection->addFieldToFilter(
                       'return_labels_printed',
                       array('eq' => 1)
                   )
                   ->addFieldToFilter(
                       'confirm_status',
                       array('eq' => $confirmedStatus)
                   )
                   ->addFieldToFilter(
                       'return_phase',
                       array(
                           'neq'     => $colloNotFoundStatus,
                           'notnull' => true,
                       )
                   )
                   ->addFieldToFilter(
                       'shipment_id',
                       array(
                           'notnull' => true
                       )
                   );

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Prepare and add columns to grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $salesHelper = Mage::helper('sales');
        $postnlHelper = Mage::helper('postnl/cif');

        $this->addColumn(
            'increment_id',
            array(
                'header'    => $salesHelper->__('Shipment #'),
                'index'     => 'increment_id',
                'type'      => 'text',
            )
        );

        $this->addColumn(
            'order_increment_id',
            array(
                'header'    => $salesHelper->__('Order #'),
                'index'     => 'order_increment_id',
                'type'      => 'text',
            )
        );

        $this->addColumn(
            'order_created_at',
            array(
                'header'    => $salesHelper->__('Order Date'),
                'index'     => 'order_created_at',
                'type'      => 'datetime',
            )
        );

        $this->addColumn(
            'shipping_name',
            array(
                'header' => $salesHelper->__('Ship to Name'),
                'index' => 'shipping_name',
            )
        );

        $this->addColumn(
            'shipment_type',
            array(
                'header'                    => $postnlHelper->__('Shipment type'),
                'align'                     => 'left',
                'index'                     => 'shipment_type',
                'type'                      => 'options',
                'renderer'                  => 'postnl_adminhtml/widget_grid_column_renderer_shipmentType',
                'width'                     => '75px',
                'sortable'                  => false,
                'options'                   => array(
                    'nl'                  => $postnlHelper->__('Domestic'),
                    'pakje_gemak'         => $postnlHelper->__('PakjeGemak'),
                    'eu'                  => $postnlHelper->__('EPS'),
                    'global'              => $postnlHelper->__('GlobalPack'),
                    'pakketautomaat'      => $postnlHelper->__('Parcel Dispenser'),
                    'avond'               => $postnlHelper->__('Evening Delivery'),
                    'pakje_gemak_express' => $postnlHelper->__('Early Pickup'),
                    'buspakje'            => $postnlHelper->__('Letter Box Parcel'),
                ),
            )
        );

        $this->addColumn(
            'confirm_date',
            array(
                'type'           => 'date',
                'header'         => $postnlHelper->__('Send Date'),
                'index'          => 'confirm_date',
                'filter_index'   => 'postnl_shipment.confirm_date',
            )
        );

        $this->addColumn(
            'barcode',
            array(
                'header'       => $postnlHelper->__('Track & Trace'),
                'align'        => 'left',
                'index'        => 'main_barcode',
                'renderer'     => 'postnl_adminhtml/widget_grid_column_renderer_barcode',
            )
        );

        $this->addColumn(
            'return_phase',
            array(
                'header'         => $postnlHelper->__('Return Phase'),
                'align'          => 'left',
                'index'          => 'return_phase',
                'type'           => 'options',
                'options'        => $postnlHelper->getShippingPhases(),
                'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_shippingPhase',
                'frame_callback' => array($this, 'decorateShippingPhase'),
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'    => $salesHelper->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $salesHelper->__('View'),
                        'url'     => array('base'=>'adminhtml/sales_shipment/view'),
                        'field'   => 'shipment_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
                'renderer'  => 'postnl_adminhtml/widget_grid_column_renderer_returnView',
            )
        );

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

    /**
     * Decorates the shipping_phase column
     *
     * @param string | null $value
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean $isExport
     *
     * @return string
     */
    public function decorateShippingPhase($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return $value;
        }

        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipmentClass
         */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        switch ($row->getData($column->getIndex())) {
            case null: //rows with no value (non-PostNL shipments) or unconfirmed shipments.
                $class = '';
                break;
            case $postnlShipmentClass::SHIPPING_PHASE_DELIVERED:
                $class = 'grid-severity-notice';
                break;
            case $postnlShipmentClass::SHIPPING_PHASE_SORTING:      //no break;
            case $postnlShipmentClass::SHIPPING_PHASE_DISTRIBUTION: //no break;
            case $postnlShipmentClass::SHIPPING_PHASE_COLLECTION:
                $class = 'grid-severity-minor';
                break;
            case $postnlShipmentClass::SHIPPING_PHASE_NOT_APPLICABLE:
                $class = 'grid-severity-critical';
                break;
            default:
                $class = '';
                break;
        }

        if (!empty($class) && empty($value)) {
            $class = '';
        }

        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    /**
     * Prepare and set options for massaction
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Get url for row
     *
     * @param Mage_Sales_Model_Order_Shipment $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/shipment')) {
            return false;
        }

        return $this->getUrl('adminhtml/sales_shipment/view',
            array(
                'shipment_id' => $row->getId(),
                'come_from_postnl'   => Mage::helper('core')->urlEncode('adminhtml/postnlAdminhtml_returns')
            )
        );
    }
}