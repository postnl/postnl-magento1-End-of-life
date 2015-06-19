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
 * Observer to edit the sales > shipments grid
 *
 * @method TIG_PostNL_Model_Adminhtml_Observer_ShipmentGrid    setCollection(Mage_Sales_Model_Resource_Order_Shipment_Collection $value)
 * @method Mage_Sales_Model_Resource_Order_Shipment_Collection getCollection()
 * @method TIG_PostNL_Model_Adminhtml_Observer_ShipmentGrid    setBlock(Mage_Adminhtml_Block_Sales_Shipment_Grid $value)
 * @method Mage_Adminhtml_Block_Sales_Shipment_Grid            getBlock()
 * @method TIG_PostNL_Model_Adminhtml_Observer_ShipmentGrid    setLabelSize(string $value)
 * @method boolean                                             hasLabelSize()
 */
class TIG_PostNL_Model_Adminhtml_Observer_ShipmentGrid extends Varien_Object
{
    /**
     * The block we want to edit
     */
    const SHIPMENT_GRID_CLASS_NAME       = 'adminhtml/sales_shipment_grid';
    const POSTNL_RETURNS_GRID_CLASS_NAME = 'postnl_adminhtml/sales_returns_grid';

    /**
     * variable name for shipment grid filter
     */
    const SHIPMENT_GRID_FILTER_VAR_NAME = 'sales_shipment_gridfilter';

    /**
     * variable name for shipment grid sorting
     */
    const SHIPMENT_GRID_SORT_VAR_NAME = 'sales_shipment_gridsort';

    /**
     * variable name for shipment grid sorting direction
     */
    const SHIPMENT_GRID_DIR_VAR_NAME = 'sales_shipment_griddir';

    /**
     * XML path to 'shipping grid columns' setting
     */
    const XPATH_SHIPPING_GRID_COLUMNS = 'postnl/grid/shipping_grid_columns';

    /**
     * XML path to default selected mass action setting
     */
    const XPATH_SHIPPING_GRID_MASSACTION_DEFAULT = 'postnl/grid/shipping_grid_massaction_default';

    /**
     * Xpath to label size setting.
     */
    const XPATH_LABEL_SIZE = 'postnl/cif_labels_and_confirming/label_size';

    /**
     * XML path to show_buspakje_options setting.
     */
    const XPATH_SHOW_BUSPAKJE_OPTION = 'postnl/grid/show_buspakje_option';

    /**
     * XML path to buspakje_calculation_mode setting.
     */
    const XPATH_BUSPAKJE_CALCULATION_MODE = 'postnl/delivery_options/buspakje_calculation_mode';

    /**
     * Gets an array of optional columns to display
     *
     * @return array
     */
    public function getOptionalColumnsToDisplay()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $columnsToDisplay = Mage::getStoreConfig(self::XPATH_SHIPPING_GRID_COLUMNS, $storeId);

        $columnsToDisplay = explode(',', $columnsToDisplay);

        return $columnsToDisplay;
    }

    /**
     * Get the configured label size.
     *
     * @return string
     */
    public function getLabelSize()
    {
        if ($this->hasLabelSize()) {
            return $this->_getData('label_size');
        }

        $labelSize = Mage::getStoreConfig(self::XPATH_LABEL_SIZE, Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->setLabelSize($labelSize);
        return $labelSize;
    }

    /**
     * Observer that adds columns to the grid and allows these to be sorted and filtered properly
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event adminhtml_block_html_before
     *
     * @observer postnl_adminhtml_shipmentgrid
     *
     * @todo see if replacing the collection can be avoided
     */
    public function modifyGrid(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block
         */
        $block = $observer->getBlock();
        $shipmentGridClass = Mage::getConfig()->getBlockClassName(self::SHIPMENT_GRID_CLASS_NAME);
        $postnlReturnsGridClass = Mage::getConfig()->getBlockClassName(self::POSTNL_RETURNS_GRID_CLASS_NAME);

        if (!($block instanceof $shipmentGridClass) || ($block instanceof $postnlReturnsGridClass)) {
            return $this;
        }

        /**
         * check if the extension is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }

        /**
         * @var Mage_Adminhtml_Block_Sales_Shipment_Grid $block
         * @var Mage_Sales_Model_Resource_Order_Shipment_Collection $currentCollection
         */
        $currentCollection = $block->getCollection();
        $select = $currentCollection->getSelect()->reset(Zend_Db_Select::WHERE);

        /**
         * replace the collection as the default collection has a bug preventing it from being reset.
         * Without being able to reset it, we can't edit it. Therefore we are forced to replace it altogether.
         */
        $collection = Mage::getResourceModel('postnl/order_shipment_grid_collection');
        $collection->setSelect($select)
                   ->setPageSize($currentCollection->getPageSize())
                   ->setCurPage($currentCollection->getCurPage());

        $this->setCollection($collection);
        $this->setBlock($block);

        $this->_joinCollection($collection);
        $this->_modifyColumns($block);
        $this->_addColumns($block);
        $this->_applySortAndFilter();
        $this->_addMassaction($block);

        $block->setCollection($this->getCollection());
        return $this;
    }

    /**
     * Adds additional joins to the collection that will be used by newly added columns.
     *
     * Resulting query:
     * SELECT `main_table`.*,
     *     IF(
     *         `pakjegemak_address`.`parent_id`,
     *         `pakjegemak_address`.`country_id`,
     *         `shipping_address`.`country_id`
     *     ) AS `country_id`,
     *     IF(
     *         `pakjegemak_address`.`parent_id`,
     *         `pakjegemak_address`.`postcode`,
     *         `shipping_address`.`postcode`
     *     ) AS `postcode`,
     *     `order`.`shipping_method`,
     *     `order`.`shipping_description`,
     *     `postnl_shipment`.`confirm_date`,
     *     `postnl_shipment`.`main_barcode`,
     *     `postnl_shipment`.`confirm_status`,
     *     `postnl_shipment`.`labels_printed`,
     *     `postnl_shipment`.`shipping_phase`,
     *     `postnl_shipment`.`parcel_count`,
     *     `postnl_shipment`.`is_parcelware_exported`,
     *     `postnl_shipment`.`product_code`,
     *     `postnl_shipment`.`extra_cover_amount`,
     *     `postnl_shipment`.`return_labels_printed`,
     *     `postnl_shipment`.`return_phase`,
     *     `postnl_order`.`is_pakje_gemak`,
     *     `postnl_order`.`delivery_date`,
     *     `postnl_order`.`is_pakketautomaat`,
     *     `postnl_order`.`type` AS `delivery_option_type`
     * FROM `sales_flat_shipment_grid` AS `main_table`
     * INNER JOIN `sales_flat_order` AS `order`
     *     ON `main_table`.`order_id`=`order`.`entity_id`
     * LEFT JOIN `sales_flat_order_address` AS `shipping_address`
     *     ON `main_table`.`order_id`=`shipping_address`.`parent_id`
     *     AND `shipping_address`.`address_type`='shipping'
     * LEFT JOIN `sales_flat_order_address` AS `pakjegemak_address`
     *     ON `main_table`.`order_id`=`pakjegemak_address`.`parent_id`
     *     AND `pakjegemak_address`.`address_type`='pakje_gemak'
     * LEFT JOIN `tig_postnl_shipment` AS `postnl_shipment`
     *     ON `main_table`.`entity_id`=`postnl_shipment`.`shipment_id`
     * LEFT JOIN `tig_postnl_order` AS `postnl_order`
     *     ON `main_table`.`order_id`=`postnl_order`.`order_id`
     * ORDER BY created_at DESC
     * LIMIT 20
     *
     * @param TIG_PostNL_Model_Resource_Order_Shipment_Grid_Collection $collection
     *
     * @return $this
     */
    protected function _joinCollection($collection)
    {
        $resource = Mage::getSingleton('core/resource');

        $select = $collection->getSelect();

        /**
         * Join sales_flat_order table.
         */
        $select->joinInner(
            array('postnl_join_order' => $resource->getTableName('sales/order')),
            '`main_table`.`order_id`=`postnl_join_order`.`entity_id`',
            array(
                'shipping_method'      => 'postnl_join_order.shipping_method',
                'shipping_description' => 'postnl_join_order.shipping_description',
            )
        );

        /**
         * Join sales_flat_order_address table.
         */
        $select->joinLeft(
            array('postnl_join_shipping_address' => $resource->getTableName('sales/order_address')),
            "`main_table`.`order_id`=`postnl_join_shipping_address`.`parent_id` AND " .
            "`postnl_join_shipping_address`.`address_type`='shipping'",
            array(
                'postcode'   => 'postnl_join_shipping_address.postcode',
                'country_id' => 'postnl_join_shipping_address.country_id',
            )
        );

        /**
         * Join tig_postnl_shipment table.
         */
        $select->joinLeft(
            array('postnl_shipment' => $resource->getTableName('postnl_core/shipment')),
            '`main_table`.`entity_id`=`postnl_shipment`.`shipment_id`',
            array(
                'confirm_date'           => 'postnl_shipment.confirm_date',
                'main_barcode'           => 'postnl_shipment.main_barcode',
                'confirm_status'         => 'postnl_shipment.confirm_status',
                'labels_printed'         => 'postnl_shipment.labels_printed',
                'return_labels_printed'  => 'postnl_shipment.return_labels_printed',
                'shipping_phase'         => 'postnl_shipment.shipping_phase',
                'return_phase'           => 'postnl_shipment.return_phase',
                'parcel_count'           => 'postnl_shipment.parcel_count',
                'is_parcelware_exported' => 'postnl_shipment.is_parcelware_exported',
                'product_code'           => 'postnl_shipment.product_code',
                'extra_cover_amount'     => 'postnl_shipment.extra_cover_amount',
                'shipment_type'          => 'postnl_shipment.shipment_type',
            )
        );

        /**
         * Join tig_postnl_order table.
         */
        $select->joinLeft(
            array('postnl_order' => $resource->getTableName('postnl_core/order')),
            '`main_table`.`order_id`=`postnl_order`.`order_id`',
            array(
                'delivery_date' => 'postnl_order.delivery_date',
            )
        );

        return $this;
    }

    /**
     * Modifies existing columns to prevent issues with the new collections
     *
     * @param Mage_Adminhtml_Block_Sales_Shipment_Grid $block
     *
     * @return $this
     */
    protected function _modifyColumns($block)
    {
        $incrementIdColumn = $block->getColumn('increment_id');
        $incrementIdColumn->setFilterIndex('main_table.increment_id');

        $createdAtColumn = $block->getColumn('created_at');
        $createdAtColumn->setFilterIndex('main_table.created_at');

        $massactionColumn = $block->getColumn('massaction');
        $massactionColumn->setFilterIndex('main_table.entity_id');

        return $this;
    }

    /**
     * Adds additional columns to the grid
     *
     * @param Mage_Adminhtml_Block_Sales_Shipment_Grid $block
     *
     * @return $this
     */
    protected function _addColumns($block)
    {
        $helper = Mage::helper('postnl');

        /**
         * Get an array of which optional columns should be shown
         */
        $columnsToDisplay = $this->getOptionalColumnsToDisplay();

        /**
         * This variable is the column ID of each column that the next column will follow.
         * By changing this variable after each column is added we guarantee the correct
         * column order will be followed regardless of which optional columns are shown
         */
        $after = 'total_qty';
        if (in_array('parcel_count', $columnsToDisplay)) {
            $block->addColumnAfter(
                'parcel_count',
                array(
                    'header'       => $helper->__('Number of Parcels'),
                    'index'        => 'parcel_count',
                    'width'        => '100px',
                    'type'         => 'number',
                    'filter_index' => 'postnl_shipment.parcel_count',
                ),
                $after
            );

            $after = 'parcel_count';
        }

        if (in_array('shipping_description', $columnsToDisplay)) {
            $block->addColumnAfter(
                'shipping_description',
                array(
                    'header'           => $helper->__('Shipping Method'),
                    'align'            => 'left',
                    'index'            => 'shipping_description',
                    'renderer'         => 'postnl_adminhtml/widget_grid_column_renderer_shippingDescription',
                    'column_css_class' => 'nobr',
                    'options'          => Mage::getModel('postnl_core/system_config_source_allProductOptions')
                                              ->getAvailableOptions(true),
                ),
                $after
            );

            $after = 'shipping_description';
        }

        if (in_array('shipment_type', $columnsToDisplay)) {
            $block->addColumnAfter(
                'shipment_type',
                array(
                    'header'                    => $helper->__('Shipment type'),
                    'align'                     => 'left',
                    'index'                     => 'shipment_type',
                    'type'                      => 'options',
                    'renderer'                  => 'postnl_adminhtml/widget_grid_column_renderer_shipmentType',
                    'width'                     => '75px',
                    'sortable'                  => false,
                    'options'                   => array(
                        'nl'                  => $helper->__('Domestic'),
                        'pakje_gemak'         => $helper->__('PakjeGemak'),
                        'eu'                  => $helper->__('EPS'),
                        'global'              => $helper->__('GlobalPack'),
                        'pakketautomaat'      => $helper->__('Parcel Dispenser'),
                        'avond'               => $helper->__('Evening Delivery'),
                        'pakje_gemak_express' => $helper->__('Early Pickup'),
                        'buspakje'            => $helper->__('Letter Box Parcel'),
                    ),
                ),
                $after
            );

            $after = 'shipment_type';
        }

        if (in_array('product_code', $columnsToDisplay)) {
            $block->addColumnAfter(
                'product_code',
                array(
                    'header'           => $helper->__('Shipping Product'),
                    'align'            => 'left',
                    'index'            => 'product_code',
                    'type'             => 'options',
                    'filter_index'     => 'postnl_shipment.product_code',
                    'column_css_class' => 'nobr',
                    'options'          => Mage::getModel('postnl_core/system_config_source_allProductOptions')
                                              ->getAvailableOptions(true),
                ),
                $after
            );

            $after = 'product_code';
        }

        if (in_array('extra_cover_amount', $columnsToDisplay)) {
            $block->addColumnAfter(
                'extra_cover_amount',
                array(
                    'header'           => $helper->__('Extra Cover'),
                    'align'            => 'left',
                    'index'            => 'extra_cover_amount',
                    'type'             => 'currency',
                    'currency_code'    => Mage::app()->getStore()->getBaseCurrencyCode(), //returns the base currency
                                                                                          //code for the admin store
                ),
                $after
            );

            $after = 'extra_cover_amount';
        }

        if (in_array('confirm_date', $columnsToDisplay)) {
            $block->addColumnAfter(
                'confirm_date',
                array(
                    'type'           => 'date',
                    'header'         => $helper->__('Send Date'),
                    'index'          => 'confirm_date',
                    'filter'         => 'postnl_adminhtml/widget_grid_column_filter_confirmDate',
                    'filter_index'   => 'postnl_shipment.confirm_date',
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_confirmDate',
                    'width'          => '150px',
                    'frame_callback' => array($this, 'decorateConfirmDate'),
                ),
                $after
            );

            $after = 'confirm_date';
        }

        if (in_array('delivery_date', $columnsToDisplay)) {
            $block->addColumnAfter(
                'delivery_date',
                array(
                    'type'         => 'date',
                    'header'       => $helper->__('Delivery Date'),
                    'index'        => 'delivery_date',
                    'filter_index' => 'postnl_order.delivery_date',
                    'renderer'     => 'postnl_adminhtml/widget_grid_column_renderer_deliveryDate',
                    'width'        => '100px',
                    'filter'       => false,
                ),
                $after
            );

            $after = 'delivery_date';
        }

        if (in_array('confirm_status', $columnsToDisplay)) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $postnlShipmentClass
             */
            $postnlShipmentClass = Mage::app()->getConfig()->getModelClassName('postnl_core/shipment');

            $block->addColumnAfter(
                'confirm_status',
                array(
                    'header'         => $helper->__('Confirm Status'),
                    'type'           => 'options',
                    'index'          => 'confirm_status',
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_confirmStatus',
                    'frame_callback' => array($this, 'decorateConfirmStatus'),
                    'options'        => array(
                        $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED       => $helper->__('Confirmed'),
                        $postnlShipmentClass::CONFIRM_STATUS_UNCONFIRMED     => $helper->__('Unconfirmed'),
                        $postnlShipmentClass::CONFIRM_STATUS_CONFIRM_EXPIRED => $helper->__('Confirmation Expired'),
                    ),
                ),
                $after
            );

            $after = 'confirm_status';
        }

        if (in_array('labels_printed', $columnsToDisplay)) {
            $block->addColumnAfter(
                'labels_printed',
                array(
                    'header'         => $helper->__('Labels Printed'),
                    'type'           => 'options',
                    'index'          => 'labels_printed',
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_yesNo',
                    'frame_callback' => array($this, 'decorateYesNo'),
                    'options'        => array(
                        1 => $helper->__('Yes'),
                        0 => $helper->__('No'),
                    ),
                ),
                $after
            );

            $after = 'labels_printed';
        }

        if (in_array('return_labels_printed', $columnsToDisplay)) {
            $block->addColumnAfter(
                'return_labels_printed',
                array(
                    'header'         => $helper->__('Return Labels Printed'),
                    'type'           => 'options',
                    'index'          => 'return_labels_printed',
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_yesNo',
                    'frame_callback' => array($this, 'decorateYesNo'),
                    'options'        => array(
                        1 => $helper->__('Yes'),
                        0 => $helper->__('No'),
                    ),
                ),
                $after
            );

            $after = 'return_labels_printed';
        }

        if (in_array('is_parcelware_exported', $columnsToDisplay)) {
            $block->addColumnAfter(
                'is_parcelware_exported',
                array(
                    'header'         => $helper->__('Exported to Parcelware'),
                    'align'          => 'left',
                    'type'           => 'options',
                    'index'          => 'is_parcelware_exported',
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_yesNo',
                    'frame_callback' => array($this, 'decorateYesNo'),
                    'options'        => array(
                        1 => $helper->__('Yes'),
                        0 => $helper->__('No'),
                    ),
                ),
                $after
            );

            $after = 'is_parcelware_exported';
        }

        if (in_array('barcode', $columnsToDisplay)) {
            $block->addColumnAfter(
                'barcode',
                array(
                    'header'   => $helper->__('Track & Trace'),
                    'align'    => 'left',
                    'index'    => 'main_barcode',
                    'renderer' => 'postnl_adminhtml/widget_grid_column_renderer_barcode',
                ),
                $after
            );

            $after = 'barcode';
        }

        if (in_array('shipping_phase', $columnsToDisplay)) {
            $block->addColumnAfter(
                'shipping_phase',
                array(
                    'header'         => $helper->__('Shipping Phase'),
                    'align'          => 'left',
                    'index'          => 'shipping_phase',
                    'type'           => 'options',
                    'options'        => Mage::helper('postnl/cif')->getShippingPhases(),
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_shippingPhase',
                    'frame_callback' => array($this, 'decorateShippingPhase'),
                ),
                $after
            );

            $after = 'shipping_phase';
        }

        if (in_array('return_phase', $columnsToDisplay)) {
            $block->addColumnAfter(
                'return_phase',
                array(
                    'header'         => $helper->__('Return Phase'),
                    'align'          => 'left',
                    'index'          => 'return_phase',
                    'type'           => 'options',
                    'options'        => Mage::helper('postnl/cif')->getShippingPhases(),
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_shippingPhase',
                    'frame_callback' => array($this, 'decorateShippingPhase'),
                ),
                $after
            );

            $after = 'return_phase'; //Defined in case of future additions to the grid.
        }

        $actionColumn = $block->getColumn('action');
        if ($actionColumn) {
            $actions = $actionColumn->getActions();

            if ($helper->checkIsPostnlActionAllowed('print_label')) {
                $actions[] = array(
                    'caption'   => $helper->__('Print label'),
                    'style'     => 'cursor:pointer;',
                    'is_postnl' => true, //custom flag for renderer
                    'code'      => 'postnl_print_label',
                );
            }

            if ($helper->checkIsPostnlActionAllowed('confirm')) {
                $actions[] = array(
                    'caption'   => $helper->__('Confirm'),
                    'url'       => array('base' => 'postnl_admin/adminhtml_shipment/confirm'),
                    'field'     => 'shipment_id',
                    'is_postnl' => true, //custom flag for renderer
                    'code'      => 'postnl_confirm',
                );
            }

            $actionColumn->setActions($actions)
                         ->setWidth('150px')
                         ->setData('renderer', 'postnl_adminhtml/widget_grid_column_renderer_action');
        }

        $block->sortColumnsByOrder();

        return $this;
    }

    /**
     * Decorates the confirm_sate column
     *
     * @param string|null                             $value
     * @param Mage_Sales_Model_Order_Shipment         $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean                                 $isExport
     *
     * @return string
     */
    public function decorateConfirmDate($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return $value;
        }

        $class = $this->_getConfirmDateClass($row, $column);

        if (!empty($class) && empty($value)) {
            $class = '';
        }

        $origValue = $row->getData($column->getIndex());

        $formattedDate = Mage::helper('core')->formatDate($origValue, 'full', false);

        $html = "<span class='{$class}' title='{$formattedDate}'><span>{$value}</span></span>";
        return $html;
    }

    /**
     * Gets classname for the confirmDate column of the current row.
     *
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return string
     */
    protected function _getConfirmDateClass($row, $column)
    {

        $origValue = $row->getData($column->getIndex());

        if (!$origValue) {
            return '';
        }

        $dateModel = Mage::getModel('core/date');

        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipmentClass
         */
        $origDate            = new DateTime($origValue, new DateTimeZone('UTC'));
        $now                 = new DateTime($dateModel->gmtDate(), new DateTimeZone('UTC'));
        $interval            = $now->diff($origDate);
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        if ($row->getData('confirm_status') == $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED
            || ($row->getData('confirm_status') == $postnlShipmentClass::CONFIRM_STATUS_BUSPAKJE
                && $interval->d >= 1
                && $interval->invert
            )
        ) {
            return 'grid-severity-notice';
        }

        if ($row->getData('confirm_status') == $postnlShipmentClass::CONFIRM_STATUS_CONFIRM_EXPIRED) {
            return 'grid-severity-critical';
        }

        if ($interval->d == 0) {
            return 'grid-severity-major';
        }

        if ($interval->d >= 1 && $interval->invert) {
            return 'grid-severity-critical';
        }

        return 'grid-severity-minor';
    }

    /**
     * Decorates the confirm_status column
     *
     * @param string|null $value
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean $isExport
     *
     * @return string
     */
    public function decorateConfirmStatus($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return $value;
        }

        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipmentClass
         */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        switch ($row->getData($column->getIndex())) {
            case null: //rows with no value (non-PostNL shipments)
                $class = '';
                break;
            case $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED:
                $class = 'grid-severity-notice';
                break;
            case $postnlShipmentClass::CONFIRM_STATUS_UNCONFIRMED:
            case $postnlShipmentClass::CONFIRM_STATUS_CONFIRM_EXPIRED:
                $class = 'grid-severity-critical';
                break;
            case $postnlShipmentClass::CONFIRM_STATUS_BUSPAKJE:
                $class = 'grid-severity-notice no-display';
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
     * Decorates the labels_printed column
     *
     * @param string|null $value
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean $isExport
     *
     * @return string
     */
    public function decorateYesNo($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return $value;
        }

        switch ($row->getData($column->getIndex())) {
            case null: //rows with no value (non-PostNL shipments)
                $class = '';
                break;
            case 0:
                $class = 'grid-severity-critical';
                break;
            case 1:
                $class = 'grid-severity-notice';
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
     * Adds a massaction to confirm the order and print the shipping labels
     *
     * @param Mage_Adminhtml_Block_Sales_Shipment_Grid $block
     *
     * @return $this
     *
     * @todo optimize by placing acl checks before mass action generation
     */
    protected function _addMassaction($block)
    {
        $helper = Mage::helper('postnl/parcelware');
        $adminhtmlHelper = Mage::helper('adminhtml');

        $massactionBlock = $block->getMassactionBlock();

        /**
         * Build all the mass action option arrays
         */
        $printAndConfirmOptions = array(
            'label' => $helper->__('PostNL - Print shipping labels & confirm shipment'),
            'url'   => $adminhtmlHelper->getUrl('postnl_admin/adminhtml_shipment/massPrintLabelsAndConfirm'),
        );

        $printPackingSlipsAndConfirmOptions = array(
            'label' => $helper->__('PostNL - Print packing slips & confirm shipment'),
            'url'   => $adminhtmlHelper->getUrl('postnl_admin/adminhtml_shipment/massPrintPackingSlipsAndConfirm'),
        );

        $printOptions = array(
            'label' => $helper->__('PostNL - Print shipping labels'),
            'url'   => $adminhtmlHelper->getUrl('postnl_admin/adminhtml_shipment/massPrintLabels'),
        );

        $packingSlipOptions = array(
            'label' => $helper->__('PostNL - Print packing slips'),
            'url'   => $adminhtmlHelper->getUrl('postnl_admin/adminhtml_shipment/massPrintPackingslips'),
        );

        $confirmOptions = array(
            'label' => $helper->__('PostNL - Confirm shipments'),
            'url'   => $adminhtmlHelper->getUrl('postnl_admin/adminhtml_shipment/massConfirm'),
        );

        $parcelWareOptions = array(
            'label' => $helper->__('PostNL - Create Parcelware export'),
            'url'   => $adminhtmlHelper->getUrl('postnl_admin/adminhtml_shipment/massCreateParcelwareExport')
        );

        $updateShippingStatusOptions = array(
            'label' => $helper->__('PostNL - Update shipping status'),
            'url'   => $adminhtmlHelper->getUrl('postnl_admin/adminhtml_shipment/massUpdateShippingStatus')
        );

        /**
         * Add an additional option to the 'label printing' mass actions if the configured label size is A4.
         */
        if ($this->getLabelSize() == 'A4') {
            /**
             * Get the additional options block for 'label printing' mass actions.
             */
            $printAdditional = Mage::app()
                                   ->getLayout()
                                   ->createBlock(
                                       'postnl_adminhtml/widget_grid_massaction_item_additional_labelStartPos'
                                   );

            $printAdditional->setData(
                array(
                    'name'   => 'print_start_pos',
                    'label'  => $helper->__('Choose printing start position'),
                )
            );

            /**
             * Add the additional option block.
             */
            $printAndConfirmOptions['additional'] = $printAdditional;
            $printOptions['additional']           = $printAdditional;
        }

        /**
         * Check which mass action should be selected by default
         */
        $defaultSelectedOption = Mage::getStoreConfig(
            self::XPATH_SHIPPING_GRID_MASSACTION_DEFAULT,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        /**
         * Add the additional 'selected' parameter to the chosen mass action
         */
        switch ($defaultSelectedOption) {
            case 'postnl_print_labels_and_confirm':
                $printAndConfirmOptions['selected'] = true;
                break;
            case 'postnl_print_packing_slips_and_confirm':
                $printPackingSlipsAndConfirmOptions['selected'] = true;
                break;
            case 'postnl_print_labels':
                $printOptions['selected'] = true;
                break;
            case 'postnl_print_packing_slips':
                $packingSlipOptions['selected'] = true;
                break;
            case 'postnl_confirm_shipments':
                $confirmOptions['selected'] = true;
                break;
            case 'postnl_parcelware_export':
                $parcelWareOptions['selected'] = true;
                break;
            case 'postnl_update_status':
                $updateShippingStatusOptions['selected'] = true;
                break;
            // no default
        }

        $printAllowed       = $helper->checkIsPostnlActionAllowed('print_label');
        $packingSlipAllowed = $helper->checkIsPostnlActionAllowed('print_packing_slips');
        $confirmAllowed     = $helper->checkIsPostnlActionAllowed('confirm');
        $exportAllowed      = $helper->checkIsPostnlActionAllowed('create_parcelware_export');

        /**
         * Add the mass actions to the grid if the current admin user is allowed to use them.
         */
        if ($printAllowed && $confirmAllowed) {
            $massactionBlock->addItem(
                'postnl_print_labels_and_confirm',
                $printAndConfirmOptions
            );

            if ($packingSlipAllowed) {
                $massactionBlock->addItem(
                    'postnl_print_packing_slips_and_confirm',
                    $printPackingSlipsAndConfirmOptions
                );
            }
        }

        if ($printAllowed) {
            $massactionBlock->addItem(
                'postnl_print_labels',
                $printOptions
            );
        }

        if ($printAllowed && $packingSlipAllowed) {
            $massactionBlock->addItem(
                'postnl_print_packing_slips',
                $packingSlipOptions
            );
        }

        if ($confirmAllowed) {
            $massactionBlock->addItem(
                'postnl_confirm_shipments',
                $confirmOptions
            );
        }

        $parcelwareExportEnabled = $helper->isParcelwareExportEnabled();
        if ($parcelwareExportEnabled && $exportAllowed) {
            $massactionBlock->addItem(
                'postnl_parcelware_export',
                $parcelWareOptions
            );
        }

        $massactionBlock->addItem(
            'postnl_update_status',
            $updateShippingStatusOptions
        );

        return $this;
    }

    /**
     * Applies sorting and filtering to the collection
     *
     * @return $this
     */
    protected function _applySortAndFilter()
    {
        $session = Mage::getSingleton('adminhtml/session');

        $filter = $session->getData(self::SHIPMENT_GRID_FILTER_VAR_NAME);
        $filter = Mage::helper('adminhtml')->prepareFilterString($filter);

        if ($filter) {
            $this->_filterCollection($filter);
        }

        $sort = $session->getData(self::SHIPMENT_GRID_SORT_VAR_NAME);

        if ($sort) {
            $dir = $session->getData(self::SHIPMENT_GRID_DIR_VAR_NAME);

            $this->_sortCollection($sort, $dir);
        }

        return $this;
    }

    /**
     * Adds new filters to the collection if these filters are based on columns added by this observer
     *
     * @param array $filter Array of filters to be added
     *
     * @return $this
     */
    protected function _filterCollection($filter)
    {
        $block = $this->getBlock();

        foreach ($filter as $columnName => $value) {
            $column = $block->getColumn($columnName);

            if (!$column) {
                continue;
            }

            $column->getFilter()->setValue($value);
            $this->_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Based on Mage_Adminhtml_Block_Widget_Grid::_addColumnFilterToCollection()
     *
     * Adds a filter condition to the collection for a specified column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if (!$this->getCollection()) {
            return $this;
        }

        if ($column->getFilterConditionCallback()) {
            call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);

            return $this;
        }

        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $cond = $column->getFilter()->getCondition();
        if ($field && isset($cond)) {
            $this->getCollection()->addFieldToFilter($field , $cond);
        }

        return $this;
    }

    /**
     * Sorts the collection by a specified column in a specified direction
     *
     * @param string $sort The column that the collection is sorted by
     * @param string $dir The direction that is used to sort the collection
     *
     * @return $this
     */
    protected function _sortCollection($sort, $dir)
    {
        $block = $this->getBlock();
        /** @var Mage_Adminhtml_Block_Widget_Grid_Column $column */
        $column = $block->getColumn($sort);
        if (!$column) {
            return $this;
        }

        $column->setDir($dir);
        $this->_setCollectionOrder($column);

        return $this;
    }

    /**
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if (!$collection) {
            return $this;
        }

        $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();

        $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        return $this;
    }
}
