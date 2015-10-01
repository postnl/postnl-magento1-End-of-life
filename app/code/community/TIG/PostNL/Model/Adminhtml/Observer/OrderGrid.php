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
 * Observer to edit the sales > order grid
 *
 * @method TIG_PostNL_Model_Adminhtml_Observer_OrderGrid   setCollection(TIG_PostNL_Model_Resource_Order_Grid_Collection $value)
 * @method TIG_PostNL_Model_Resource_Order_Grid_Collection getCollection()
 * @method TIG_PostNL_Model_Adminhtml_Observer_OrderGrid   setBlock(Mage_Adminhtml_Block_Sales_Order_Grid $value)
 * @method Mage_Adminhtml_Block_Sales_Order_Grid           getBlock()
 */
class TIG_PostNL_Model_Adminhtml_Observer_OrderGrid extends Varien_Object
{
    /**
     * The block we want to edit.
     */
    const ORDER_GRID_BLOCK_NAME = 'adminhtml/sales_order_grid';

    /**
     * variable name for order grid filter.
     */
    const ORDER_GRID_FILTER_VAR_NAME = 'sales_order_gridfilter';

    /**
     * variable name for order grid sorting.
     */
    const ORDER_GRID_SORT_VAR_NAME = 'sales_order_gridsort';

    /**
     * variable name for order grid sorting direction.
     */
    const ORDER_GRID_DIR_VAR_NAME = 'sales_order_griddir';

    /**
     * XML path to show_grid_options setting.
     */
    const XPATH_SHOW_OPTIONS = 'postnl/grid/show_grid_options';

    /**
     * XML path to show_buspakje_options setting.
     */
    const XPATH_SHOW_BUSPAKJE_OPTION = 'postnl/grid/show_buspakje_option';

    /**
     * XML path to buspakje_calculation_mode setting.
     */
    const XPATH_BUSPAKJE_CALCULATION_MODE = 'postnl/delivery_options/buspakje_calculation_mode';

    /**
     * XML path to 'order grid columns' setting
     */
    const XPATH_ORDER_GRID_COLUMNS = 'postnl/grid/order_grid_columns';

    /**
     * Xpath to the 'order_grid_massaction_default' setting.
     */
    const XPATH_ORDER_GRID_MASSACTION_DEFAULT = 'postnl/grid/order_grid_massaction_default';

    /**
     * Edits the sales order grid by adding a mass action to create shipments for selected orders.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event adminhtml_block_html_before
     *
     * @observer postnl_adminhtml_ordergrid
     */
    public function modifyGrid(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         */
        $block = $observer->getBlock();
        $orderGridClass = Mage::getConfig()->getBlockClassName(self::ORDER_GRID_BLOCK_NAME);

        if (!($block instanceof $orderGridClass)) {
            return $this;
        }

        /**
         * check if the extension is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }

        /**
         * @var Mage_Adminhtml_Block_Sales_Order_Grid $block
         * @var Mage_Sales_Model_Resource_Order_Collection $currentCollection
         */
        $currentCollection = $block->getCollection();
        $select = $currentCollection->getSelect()->reset(Zend_Db_Select::WHERE);

        /**
         * replace the collection, as the default collection has a bug preventing it from being reset.
         * Without being able to reset it, we can't edit it. Therefore we are forced to replace it altogether
         *
         * @todo see if this can be avoided in any way
         */
        $collection = Mage::getResourceModel('postnl/order_grid_collection');
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

        $block->setCollection($collection);
        return $this;
    }

    /**
     * Adds additional joins to the collection that will be used by newly added columns.
     *
     * Resulting query:
     * SELECT `main_table`.*,
     *     `order`.`shipping_method`,
     *     `payment`.`method` AS `payment_method`,
     *     `shipping_address`.`country_id`,
     *     `postnl_order`.`is_pakje_gemak`,
     *     `postnl_order`.`is_pakketautomaat`,
     *     `postnl_order`.`type` AS `delivery_option_type`,
     *     `postnl_order`.`confirm_date`,
     *     group_concat(
     *         `postnl_shipment`.`confirm_status`
     *         ORDER BY `postnl_shipment`.`created_at` DESC
     *         SEPARATOR ","
     *     ) AS `confirm_status`,
     *     group_concat(
     *         `postnl_shipment`.`shipping_phase`
     *         ORDER BY `postnl_shipment`.`created_at` DESC
     *         SEPARATOR ","
     *     ) AS `shipping_phase`,
     *     group_concat(
     *         `postnl_shipment`.`shipment_type`
     *         ORDER BY `postnl_shipment`.`created_at` DESC
     *         SEPARATOR ","
     *     ) AS `shipment_type`,
     *     group_concat(
     *         `postnl_shipment`.`product_code`
     *         ORDER BY `postnl_shipment`.`created_at` DESC
     *         SEPARATOR ","
     *     ) AS `product_code`,
     *     IF(
     *         `postnl_shipment`.`confirm_date`,
     *         `postnl_shipment`.`confirm_date`,
     *         `postnl_order`.`confirm_date`
     *     ) AS `confirm_date`
     * FROM `sales_flat_order_grid` AS `main_table`
     * INNER JOIN `sales_flat_order` AS `order`
     *     ON `main_table`.`entity_id`=`order`.`entity_id`
     * LEFT JOIN `sales_flat_order_payment` AS `payment`
     *     ON `main_table`.`entity_id`=`payment`.`parent_id`
     * LEFT JOIN `sales_flat_order_address` AS `shipping_address`
     *     ON `main_table`.`entity_id`=`shipping_address`.`parent_id`
     *     AND `shipping_address`.`address_type`='shipping'
     * LEFT JOIN `tig_postnl_order` AS `postnl_order`
     *     ON `main_table`.`entity_id`=`postnl_order`.`order_id`
     * LEFT JOIN `tig_postnl_shipment` AS `postnl_shipment`
     *     ON `main_table`.`entity_id`=`postnl_shipment`.`order_id`
     * GROUP BY `main_table`.`entity_id`
     * ORDER BY created_at DESC
     * LIMIT 20
     *
     * @param TIG_PostNL_Model_Resource_Order_Grid_Collection $collection
     *
     * @return $this
     */
    protected function _joinCollection($collection)
    {
        $resource = Mage::getSingleton('core/resource');

        /**
         * If the order has any PostNl shipments, we can use their confirm_date. Otherwise we can check the confirm_date
         * stored by the tig_postnl_order table.
         */
        $collection->addExpressionFieldToSelect(
            'confirm_date',
            'IF({{shipment_confirm_date}}, {{shipment_confirm_date}}, {{order_confirm_date}})',
            array(
                'shipment_confirm_date' => '`postnl_shipment`.`confirm_date`',
                'order_confirm_date'    => '`postnl_order`.`confirm_date`',
            )
        );

        $select = $collection->getSelect();

        /**
         * Join sales_flat_order table.
         */
        $select->joinInner(
            array('postnl_join_order' => $resource->getTableName('sales/order')),
            '`main_table`.`entity_id`=`postnl_join_order`.`entity_id`',
            array(
                'shipping_method' => 'postnl_join_order.shipping_method',
            )
        );

        /**
         * Join sales_flat_order_payment table.
         */
        $select->joinLeft(
            array('postnl_join_payment' => $resource->getTableName('sales/order_payment')),
            '`main_table`.`entity_id`=`postnl_join_payment`.`parent_id`',
            array(
                'payment_method' => 'postnl_join_payment.method',
            )
        );

        /**
         * Join sales_flat_order_address table.
         */
        $select->joinLeft(
            array('postnl_join_shipping_address' => $resource->getTableName('sales/order_address')),
            "`main_table`.`entity_id`=`postnl_join_shipping_address`.`parent_id` AND" .
            " `postnl_join_shipping_address`.`address_type`='shipping'",
            array(
                'country_id' => 'postnl_join_shipping_address.country_id',
            )
        );

        /**
         * Join tig_postnl_order table.
         */
        $select->joinLeft(
            array('postnl_order' => $resource->getTableName('postnl_core/order')),
            '`main_table`.`entity_id`=`postnl_order`.`order_id`',
            array(
                'is_pakje_gemak'       => 'postnl_order.is_pakje_gemak',
                'is_pakketautomaat'    => 'postnl_order.is_pakketautomaat',
                'delivery_option_type' => 'postnl_order.type',
                'options'              => 'postnl_order.options',
            )
        );

        /**
         * Join tig_postnl_shipment table.
         */
        $select->joinLeft(
            array('postnl_shipment' => $resource->getTableName('postnl_core/shipment')),
            '`main_table`.`entity_id`=`postnl_shipment`.`order_id`',
            array(
                'confirm_status' => new Zend_Db_Expr(
                    'group_concat(`postnl_shipment`.`confirm_status` ORDER BY `postnl_shipment`.`created_at` DESC ' .
                    'SEPARATOR ",")'
                ),
                'shipping_phase' => new Zend_Db_Expr(
                    'group_concat(`postnl_shipment`.`shipping_phase` ORDER BY `postnl_shipment`.`created_at` DESC ' .
                    'SEPARATOR ",")'
                ),
                'shipment_type' => new Zend_Db_Expr(
                    'group_concat(`postnl_shipment`.`shipment_type` ORDER BY `postnl_shipment`.`created_at` DESC ' .
                    'SEPARATOR ",")'
                ),
                'product_code' => new Zend_Db_Expr(
                    'group_concat(`postnl_shipment`.`product_code` ORDER BY `postnl_shipment`.`created_at` DESC ' .
                    'SEPARATOR ",")'
                ),
            )
        );

        /**
         * Group the results by the ID column.
         */
        $select->group('main_table.entity_id');

        return $this;
    }

    /**
     * Modifies existing columns to prevent issues with the new collections.
     *
     * @param Mage_Adminhtml_Block_Sales_Order_Grid $block
     *
     * @return $this
     */
    protected function _modifyColumns($block)
    {
        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $incrementIdColumn
         */
        $incrementIdColumn = $block->getColumn('real_order_id');
        if ($incrementIdColumn) {
            $incrementIdColumn->setFilterIndex('main_table.increment_id');
        }

        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $massactionColumn
         */
        $massactionColumn = $block->getColumn('massaction');
        if ($massactionColumn) {
            $massactionColumn->setFilterIndex('main_table.entity_id');
        }

        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $statusColumn
         */
        $statusColumn = $block->getColumn('status');
        if ($statusColumn) {
            $statusColumn->setFilterIndex('main_table.status');
        }

        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $createdAtColumn
         */
        $createdAtColumn = $block->getColumn('created_at');
        if ($createdAtColumn) {
            $createdAtColumn->setFilterIndex('main_table.created_at');
        }

        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $baseGrandTotalColumn
         */
        $baseGrandTotalColumn = $block->getColumn('base_grand_total');
        if ($baseGrandTotalColumn) {
            $baseGrandTotalColumn->setFilterIndex('main_table.base_grand_total');
        }

        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $grandTotalColumn
         */
        $grandTotalColumn = $block->getColumn('grand_total');
        if ($grandTotalColumn) {
            $grandTotalColumn->setFilterIndex('main_table.grand_total');
        }

        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $storeIdColumn
         */
        $storeIdColumn = $block->getColumn('store_id');
        if ($storeIdColumn) {
            $storeIdColumn->setFilterIndex('main_table.store_id');
        }

        return $this;
    }

    /**
     * Adds additional columns to the grid
     *
     * @param Mage_Adminhtml_Block_Sales_Order_Grid $block
     *
     * @return $this
     */
    protected function _addColumns($block)
    {
        $helper = Mage::helper('postnl');

        $countryIdColumnAttributes = array(
            'header'                    => $helper->__('Shipment type'),
            'align'                     => 'left',
            'index'                     => 'country_id',
            'type'                      => 'options',
            'renderer'                  => 'postnl_adminhtml/widget_grid_column_renderer_orderType',
            'width'                     => '140px',
            'filter_condition_callback' => array($this, '_filterShipmentType'),
            'sortable'                  => false,
            'options'                   => array(
                'nl'                  => $helper->__('Domestic'),
                'pakje_gemak'         => $helper->__('Post Office'),
                'eu'                  => $helper->__('EPS'),
                'global'              => $helper->__('GlobalPack'),
                'pakketautomaat'      => $helper->__('Parcel Dispenser'),
                'avond'               => $helper->__('Evening Delivery'),
                'pakje_gemak_express' => $helper->__('Early Pickup'),
            ),
        );

        $showOrderColumns = Mage::getStoreConfig(self::XPATH_ORDER_GRID_COLUMNS, Mage_Core_Model_App::ADMIN_STORE_ID);
        $showOrderColumns = explode(',', $showOrderColumns);

        /**
         * If we don't need to display the shipment type column, hide it. We'll still need it for some javascript
         * functionality
         */
        if (!in_array('shipment_type', $showOrderColumns)) {
            $countryIdColumnAttributes['column_css_class'] = 'no-display';
            $countryIdColumnAttributes['header_css_class'] = 'no-display';
            $countryIdColumnAttributes['display'] = 'none';
        }

        $block->addColumnAfter(
            'country_id',
            $countryIdColumnAttributes,
            'shipping_name'
        );

        /**
         * Add the confirm date column.
         */
        $after = 'country_id';
        if (in_array('confirm_date', $showOrderColumns)) {
            $block->addColumnAfter(
                'confirm_date',
                array(
                    'type'                      => 'date',
                    'header'                    => $helper->__('Send date'),
                    'index'                     => 'confirm_date',
                    'filter'                    => 'postnl_adminhtml/widget_grid_column_filter_confirmDate',
                    'filter_condition_callback' => array($this, '_filterConfirmDate'),
                    'renderer'                  => 'postnl_adminhtml/widget_grid_column_renderer_orderConfirmDate',
                    'width'                     => '150px',
                    'frame_callback'            => array($this, 'decorateConfirmDate'),
                ),
                $after
            );

            $after = 'confirm_date';
        }

        /**
         * Add the confirm status column.
         */
        if (in_array('confirm_status', $showOrderColumns)) {
            $block->addColumnAfter(
                'confirm_status',
                array(
                    'header'         => $helper->__('Confirm Status'),
                    'type'           => 'text',
                    'index'          => 'confirm_status',
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_orderConfirmStatus',
                    'frame_callback' => array($this, 'decorateConfirmStatus'),
                    'sortable'       => false,
                    'filter'         => false,
                ),
                $after
            );

            $after = 'confirm_status';
        }

        /**
         * Add the shipping phase column.
         */
        if (in_array('shipping_phase', $showOrderColumns)) {
            $block->addColumnAfter(
                'shipping_phase',
                array(
                    'header'         => $helper->__('Shipping Phase'),
                    'align'          => 'left',
                    'index'          => 'shipping_phase',
                    'type'           => 'text',
                    'renderer'       => 'postnl_adminhtml/widget_grid_column_renderer_shippingPhase',
                    'frame_callback' => array($this, 'decorateShippingPhase'),
                    'sortable'       => false,
                    'filter'         => false,
                ),
                $after
            );
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

        $class = $this->_getConfirmDateClass($value, $row, $column);

        $origValue = $row->getData($column->getIndex());

        $formattedDate = Mage::helper('core')->formatDate($origValue, 'full', false);

        $html = "<span class='{$class}' title='{$formattedDate}'><span>{$value}</span></span>";
        return $html;
    }

    /**
     * Gets class name for the confirmDate column of the current row.
     *
     * @param string|null                             $value
     * @param Mage_Sales_Model_Order                  $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return string
     */
    protected function _getConfirmDateClass($value, $row, $column)
    {
        if (!$value) {
            return '';
        }

        $origValue = $row->getData($column->getIndex());
        $dateModel = Mage::getModel('core/date');
        $now       = new DateTime($dateModel->gmtDate(), new DateTimeZone('UTC'));

        if (!$origValue) {
            $helper = Mage::helper('postnl/deliveryOptions');
            $shippingDuration = $helper->getOrderShippingDuration($row);
            $deliveryDate = $helper->getDeliveryDate(
                $row->getCreatedAt(),
                $row->getStoreId(),
                false,
                true,
                true,
                $shippingDuration
            );
            $origDate = new DateTime($deliveryDate, new DateTimeZone('UTC'));
            $origDate = $origDate->sub(new DateInterval('P1D'));
        } else {
            $origDate = new DateTime($origValue, new DateTimeZone('UTC'));
        }

        /**
         * @var $postnlShipmentClass TIG_PostNL_Model_Core_Shipment
         */
        $interval            = $now->diff($origDate);
        $isConfirmed         = $this->_isRowConfirmed($row);
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        if ($isConfirmed ||
            ($row->getData('confirm_status') == $postnlShipmentClass::CONFIRM_STATUS_BUSPAKJE
                && $interval->d >= 1
                && $interval->invert
            )
        ) {
            return 'grid-severity-notice';
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
     * Checks if the row has been fully confirmed.
     *
     * @param Mage_Sales_Model_Order_Shipment $row
     *
     * @return boolean
     */
    protected function _isRowConfirmed($row)
    {
        $confirmStatus = $row->getConfirmStatus();

        /**
         * @var $postnlShipmentClass TIG_PostNL_Model_Core_Shipment
         */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $statusses = explode(',', $confirmStatus);
        foreach ($statusses as $status) {
            if ($status != $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED) {
                return false;
            }
        }

        return true;
    }

    /**
     * Decorates the confirm_status column
     *
     * @param string | null $values
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean $isExport
     *
     * @return string
     */
    public function decorateConfirmStatus($values, $row, $column, $isExport)
    {
        if ($isExport) {
            return $values;
        }

        if (is_null($values) || $values === '') {
            return '';
        }

        $origValues = $row->getData($column->getIndex());
        if (!$origValues) {
            $html = '<span class="grid-severity-minor"><span>' . $values . '</span></span>';
            return $html;
        }

        $html      = '';
        $statusses = explode(',', $origValues);
        $values    = explode(',', $values);

        foreach ($statusses as $key => $status) {
            $html .= $this->_decorateConfirmStatus($status, $values[$key]);
        }

        return $html;
    }

    /**
     * Decorate a single confirm status value.
     *
     * @param string $status
     * @param string $value
     *
     * @return string
     */
    protected function _decorateConfirmStatus($status, $value)
    {
        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipmentClass
         */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        switch ($status) {
            case $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED:
                $class = 'grid-severity-notice';
                break;
            case $postnlShipmentClass::CONFIRM_STATUS_UNCONFIRMED: //no break
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

        $html = '<span class="'.$class.'"><span>'.$value.'</span></span>';
        return $html;
    }

    /**
     * Decorates the shipping_phase column
     *
     * @param string|null                             $values
     * @param Mage_Sales_Model_Order_Shipment         $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean                                 $isExport
     *
     * @return string
     */
    public function decorateShippingPhase($values, $row, $column, $isExport)
    {
        if ($isExport) {
            return $values;
        }

        $html           = '';
        $shippingPhases = explode(',', $row->getData($column->getIndex()));
        $values         = explode(',', $values);

        foreach ($shippingPhases as $key => $phase) {
            $html .= $this->_decorateShippingPhase($phase, $values[$key]);
        }

        return $html;
    }

    /**
     * Decorate a single shipping phase and corresponding value.
     *
     * @param string|int $phase
     * @param string     $value
     *
     * @return string
     */
    protected function _decorateShippingPhase($phase, $value)
    {
        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipmentClass
         */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        switch ($phase) {
            case null: //rows with no value (non-PostNL shipments) or unconfirmed shipments.
                $class = '';
                break;
            case $postnlShipmentClass::SHIPPING_PHASE_DELIVERED:
                $class = 'grid-severity-notice';
                break;
            case $postnlShipmentClass::SHIPPING_PHASE_SORTING: //no break;
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

        $html = '<span class="' . $class . '"><span>' . $value . '</span></span>';
        return $html;
    }

    /**
     * Adds a massaction to confirm the order and print the shipping labels
     *
     * @param Mage_Adminhtml_Block_Sales_Order_Grid $block
     *
     * @return $this
     */
    protected function _addMassaction($block)
    {
        $helper = Mage::helper('postnl');

        /**
         * Make sure the admin is allowed to ship orders and add the mass action.
         */
        if ($helper->checkIsPostnlActionAllowed('create_shipment')) {
            $createShipmentMassActionData = $this->_getCreateShipmentMassAction();

            /**
             * Add the massaction.
             */
            $block->getMassactionBlock()
                  ->addItem(
                      'postnl_create_shipments',
                      $createShipmentMassActionData
                  );
        }

        /**
         * Make sure the admin is allowed to ship orders, print labels and confirm shipments. If so, add the
         * massaction.
         */
        if ($helper->checkIsPostnlActionAllowed(
                array(
                    'create_shipment',
                    'confirm',
                    'print_label',
                )
            )
        ) {
            $fullPostnlFlowMassActionData = $this->_getFullPostnlFlowMassAction();

            /**
             * Add the massaction.
             */
            $block->getMassactionBlock()
                  ->addItem(
                      'postnl_create_shipment_print_label_and_confirm',
                      $fullPostnlFlowMassActionData
                  );
        }

        /**
         * Make sure the admin is allowed to ship orders, print labels, print packing slips and confirm shipments. If
         * so, add the massaction.
         */
        if ($helper->checkIsPostnlActionAllowed(
            array(
                'create_shipment',
                'confirm',
                'print_label',
                'print_packing_slips',
            )
        )
        ) {
            $fullPostnlFlowPackingSlipMassActionData = $this->_getFullPostnlFlowPackingSlipMassAction();

            /**
             * Add the massaction.
             */
            $block->getMassactionBlock()
                  ->addItem(
                      'postnl_create_shipment_print_packing_slip_and_confirm',
                      $fullPostnlFlowPackingSlipMassActionData
                  );
        }

        /**
         * Make sure the admin is allowed to print packing slips and add the mass action.
         */
        if ($helper->checkIsPostnlActionAllowed('print_packing_slips')) {
            $printPackingSlipMassActionData = $this->_getCreatePackingSlipMassAction();

            /**
             * Add the massaction.
             */
            $block->getMassactionBlock()
                  ->addItem(
                      'postnl_print_packing_slip',
                      $printPackingSlipMassActionData
                  );
        }

        return $this;
    }

    /**
     * Gets mass action data for the createShipments mass action.
     *
     * @return array
     */
    protected function _getCreateShipmentMassAction()
    {
        $helper = Mage::helper('postnl');

        /**
         * Build an array of options for the massaction item.
         */
        $massActionData = array(
            'label'=> $helper->__('PostNL - Create Shipments'),
            'url'  => Mage::helper('adminhtml')->getUrl('postnl_admin/adminhtml_shipment/massCreateShipments'),
        );

        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $defaultMassAction = Mage::getStoreConfig(self::XPATH_ORDER_GRID_MASSACTION_DEFAULT, $storeId);
        if ($defaultMassAction == 'postnl_create_shipments') {
            $massActionData['selected'] = true;
        }

        $showOptions = Mage::getStoreConfig(self::XPATH_SHOW_OPTIONS, $storeId);

        if ($showOptions) {
            $optionLabel = $helper->__('Product options');
            $options     = $this->_getProductOptions();

            /**
             * Add another dropdown containing the possible product options.
             */
            $config = array(
                'postnl_use_default' => array(
                    'name'    => 'product_options[use_default]',
                    'type'    => 'postnl_checkbox',
                    'label'   => $helper->__('Use default option'),
                    'value'   => 1,
                    'checked' => 'checked',
                ),
            );

            $buspakjeCalculationMode = Mage::getStoreConfig(self::XPATH_BUSPAKJE_CALCULATION_MODE, $storeId);
            $showBuspakjeOptions = Mage::getStoreConfigFlag(self::XPATH_SHOW_BUSPAKJE_OPTION, $storeId);
            if ($helper->canUseBuspakje()
                && $buspakjeCalculationMode == 'manual'
                && $showBuspakjeOptions
                && !empty($options['postnl_buspakje_options'])
            ) {
                $config['postnl_is_buspakje'] = array(
                    'name'    => 'product_options[is_buspakje]',
                    'type'    => 'postnl_checkbox',
                    'label'   => $helper->__('Is letter box parcel'),
                    'value'   => 1,
                );
            }

            if (!empty($options['postnl_domestic_options'])) {
                $config['postnl_domestic_options'] = array(
                    'name'   => 'product_options[domestic_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_domestic_options'],
                );
            }

            if (!empty($options['postnl_avond_options'])) {
                $config['postnl_avond_options'] = array(
                    'name'   => 'product_options[avond_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_avond_options'],
                );
            }

            if (!empty($options['postnl_pg_options'])) {
                $config['postnl_pg_options'] = array(
                    'name'   => 'product_options[pg_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_pg_options'],
                );
            }

            if (!empty($options['postnl_pge_options'])) {
                $config['postnl_pge_options'] = array(
                    'name'   => 'product_options[pge_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_pge_options'],
                );
            }

            if (!empty($options['postnl_eps_options'])) {
                $config['postnl_eps_options'] = array(
                    'name'   => 'product_options[eps_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_eps_options'],
                );
            }

            if (!empty($options['postnl_globalpack_options'])) {
                $config['postnl_globalpack_options'] = array(
                    'name'   => 'product_options[globalpack_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_globalpack_options'],
                );
            }

            if (!empty($options['postnl_domestic_cod_options'])) {
                $config['postnl_domestic_cod_options'] = array(
                    'name'   => 'product_options[domestic_cod_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_domestic_cod_options'],
                );
            }

            if (!empty($options['postnl_avond_cod_options'])) {
                $config['postnl_avond_cod_options'] = array(
                    'name'   => 'product_options[avond_cod_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_avond_cod_options'],
                );
            }

            if (!empty($options['postnl_pg_cod_options'])) {
                $config['postnl_pg_cod_options'] = array(
                    'name'   => 'product_options[pg_cod_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_pg_cod_options'],
                );
            }

            if (!empty($options['postnl_pge_cod_options'])) {
                $config['postnl_pge_cod_options'] = array(
                    'name'   => 'product_options[pge_cod_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_pge_cod_options'],
                );
            }

            if (!empty($options['postnl_pa_options'])) {
                $config['postnl_pa_options'] = array(
                    'name'   => 'product_options[pa_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_pa_options'],
                );
            }

            if (!empty($options['postnl_buspakje_options'])) {
                $config['postnl_buspakje_options'] = array(
                    'name'   => 'product_options[buspakje_options]',
                    'type'   => 'select',
                    'label'  => $optionLabel,
                    'values' => $options['postnl_buspakje_options'],
                );
            }

            /**
             * @var TIG_PostNL_Block_Adminhtml_Widget_Grid_Massaction_Item_Additional_ProductOptions $block
             */
            $block = Mage::app()
                         ->getLayout()
                         ->createBlock('postnl_adminhtml/widget_grid_massaction_item_additional_productOptions');

            $massActionData['additional'] = $block->createFromConfiguration($config);
        }

        return $massActionData;
    }

    /**
     * @return array
     */
    protected function _getProductOptions()
    {
        $optionsModel = Mage::getModel('postnl_core/system_config_source_allProductOptions');
        $options = array(
            'postnl_domestic_options' => $optionsModel->getOptions(
                array(
                    'group' => 'standard_options',
                    'isCod' => false,
                ),
                false,
                true
            ),
            'postnl_avond_options' => $optionsModel->getOptions(
                array(
                    'group' => 'standard_options',
                    'isCod' => false,
                ),
                false,
                true
            ),
            'postnl_pg_options' => $optionsModel->getOptions(
                array(
                    'group'   => 'standard_options',
                    'isCod'   => false,
                    'isAvond' => true,
                ),
                false,
                true
            ),
            'postnl_pge_options' => $optionsModel->getOptions(
                array(
                    'group' => 'pakjegemak_options',
                    'isCod' => false,
                    'isPge' => true,
                ),
                false,
                true
            ),
            'postnl_eps_options' => $optionsModel->getOptions(
                array(
                    'group' => 'eu_options',
                ),
                false,
                true
            ),
            'postnl_globalpack_options' => $optionsModel->getOptions(
                array(
                    'group' => 'global_options',
                ),
                false,
                true
            ),
            'postnl_domestic_cod_options' => $optionsModel->getOptions(
                array(
                    'group' => 'standard_options',
                    'isCod' => true,
                ),
                false,
                true
            ),
            'postnl_avond_cod_options' => $optionsModel->getOptions(
                array(
                    'group'   => 'standard_options',
                    'isCod'   => true,
                    'isAvond' => true,
                ),
                false,
                true
            ),
            'postnl_pg_cod_options' => $optionsModel->getOptions(
                array(
                    'group' => 'pakjegemak_options',
                    'isCod' => true,
                ),
                false,
                true
            ),
            'postnl_pge_cod_options' => $optionsModel->getOptions(
                array(
                    'group' => 'pakjegemak_options',
                    'isCod' => true,
                    'isPge' => true,
                ),
                false,
                true
            ),
            'postnl_pa_options' => $optionsModel->getOptions(
                array(
                    'group' => 'pakketautomaat_options',
                ),
                false,
                true
            ),
            'postnl_buspakje_options' => $optionsModel->getOptions(
                array(
                    'group' => 'buspakje_options',
                ),
                false,
                true
            )
        );

        return $options;
    }

    /**
     * Gets mass action data for the full PostNL flow mass action.
     *
     * @return array
     */
    protected function _getFullPostnlFlowMassAction()
    {
        $helper = Mage::helper('postnl');

        /**
         * Build an array of options for the massaction item.
         */
        $massActionData = array(
            'label' => $helper->__('PostNL - Create shipments, print labels and confirm'),
            'url'   => Mage::helper('adminhtml')->getUrl('postnl_admin/adminhtml_shipment/massFullPostnlFlow'),
        );

        $defaultMassAction = Mage::getStoreConfig(
            self::XPATH_ORDER_GRID_MASSACTION_DEFAULT,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        if ($defaultMassAction == 'postnl_create_shipment_print_label_and_confirm') {
            $massActionData['selected'] = true;
        }

        return $massActionData;
    }

    /**
     * Gets mass action data for the full PostNL flow mass action with packing slip.
     *
     * @return array
     */
    protected function _getFullPostnlFlowPackingSlipMassAction()
    {
        $helper = Mage::helper('postnl');

        /**
         * Build an array of options for the massaction item.
         */
        $massActionData = array(
            'label' => $helper->__('PostNL - Create shipments, print packing slips and confirm'),
            'url'   => Mage::helper('adminhtml')->getUrl(
                'postnl_admin/adminhtml_shipment/massFullPostnlFlowWithPackingSlip'
            ),
        );

        $defaultMassAction = Mage::getStoreConfig(
            self::XPATH_ORDER_GRID_MASSACTION_DEFAULT,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        if ($defaultMassAction == 'postnl_create_shipment_print_packing_slip_and_confirm') {
            $massActionData['selected'] = true;
        }

        return $massActionData;
    }

    /**
     * Gets mass action data for the printPackingSlips mass action.
     *
     * @return array
     */
    protected function _getCreatePackingSlipMassAction()
    {
        $helper = Mage::helper('postnl');

        /**
         * Build an array of options for the massaction item.
         */
        $massActionData = array(
            'label' => $helper->__('PostNL - Print packing slips'),
            'url'   => Mage::helper('adminhtml')->getUrl('postnl_admin/adminhtml_shipment/massPrintPackingslips'),
        );

        $defaultMassAction = Mage::getStoreConfig(
            self::XPATH_ORDER_GRID_MASSACTION_DEFAULT,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        if ($defaultMassAction == 'postnl_print_packing_slip') {
            $massActionData['selected'] = true;
        }

        return $massActionData;
    }

    /**
     * Applies sorting and filtering to the collection
     *
     * @return $this
     */
    protected function _applySortAndFilter()
    {
        $session = Mage::getSingleton('adminhtml/session');

        $filter = $session->getData(self::ORDER_GRID_FILTER_VAR_NAME);
        $filter = Mage::helper('adminhtml')->prepareFilterString($filter);

        if ($filter) {
            $this->_filterCollection($filter);
        }

        $sort = $session->getData(self::ORDER_GRID_SORT_VAR_NAME);

        if ($sort) {
            $dir = $session->getData(self::ORDER_GRID_DIR_VAR_NAME);

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
     * Filters the collection by the 'shipment_type' column. Th column has 3 options: domestic, EPS and GlobalPack.
     *
     * @param TIG_PostNL_Model_Resource_Order_Grid_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column         $column
     *
     * @return $this
     */
    protected function _filterShipmentType($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();
        $filterCond = $cond['eq'];

        /**
         * First filter out all non-postnl orders.
         */
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        $postnlShippingMethodsRegex = '';
        foreach ($postnlShippingMethods as $method) {
            if ($postnlShippingMethodsRegex) {
                $postnlShippingMethodsRegex .= '|';
            } else {
                $postnlShippingMethodsRegex .= '^';
            }

            $postnlShippingMethodsRegex .= "({$method})(_{0,1}[0-9]*)";
        }

        $postnlShippingMethodsRegex .= '$';
        $collection->addFieldToFilter(
            'postnl_join_order.shipping_method',
            array(
                'regexp' => $postnlShippingMethodsRegex
            )
        );

        /**
         * If the filter condition is PakjeGemak Express, filter out all non-PakjeGemak Express orders
         */
        if ($filterCond == 'pakje_gemak_express') {
            $collection->addFieldToFilter('postnl_order.type', array('eq' => 'PGE'));

            return $this;
        }

        /**
         * If the filter condition is evening delivery, filter out all other orders
         */
        if ($filterCond == 'avond') {
            $collection->addFieldToFilter('postnl_order.type', array('eq' => 'Avond'));

            return $this;
        }

        /**
         * If the filter condition is PakjeGemak, filter out all non-PakjeGemak orders
         */
        if ($filterCond == 'pakje_gemak') {
            $collection->addFieldToFilter('postnl_order.is_pakje_gemak', array('eq' => 1));
            $collection->addFieldToFilter('postnl_order.type', array(array('eq' => 'PG'), array('null' => true)));

            return $this;
        }

        /**
         * If the filter condition is Pakket Automaat, filter out all non-Pakket Automaat orders
         */
        if ($filterCond == 'pakketautomaat') {
            $collection->addFieldToFilter('postnl_order.is_pakketautomaat', array('eq' => 1));
            $collection->addFieldToFilter(
                'postnl_order.type',
                array(
                    array('eq'   => 'PA'),
                    array('null' => true)
                )
            );

            return $this;
        }

        /**
         * If the filter condition is NL, filter out all orders not being shipped to the Netherlands. PakjeGemak,
         * PakjeGemak Express, evening delivery and pakketautomaat shipments are also shipped to the Netherlands so we
         * need to explicitly filter those as well.
         */
        if ($filterCond == 'nl') {
            $collection->addFieldToFilter('country_id', $cond);
            $collection->addFieldToFilter(
                       'postnl_order.type',
                       array(
                           array('eq'   => 'Overdag'),
                           array('null' => true)
                       )
            );
            $collection->addFieldToFilter(
                       'postnl_order.is_pakje_gemak',
                       array(
                           array('eq'   => 0),
                           array('null' => true)
                       )
            );
            $collection->addFieldToFilter(
                       'postnl_order.is_pakketautomaat',
                       array(
                           array('eq'   => 0),
                           array('null' => true)
                       )
            );

            return $this;
        }

        /**
         * If the filter condition is EU, filter out all orders not being shipped to the EU and those being shipped to
         * the Netherlands
         */
        $euCountries = Mage::helper('postnl/cif')->getEuCountries();
        if ($filterCond == 'eu') {
            $collection->addFieldToFilter('country_id', array('neq' => 'NL'));
            $collection->addFieldToFilter('country_id', array('in', $euCountries));

            return $this;
        }

        /**
         * Lastly, filter out all orders who are being shipped to the Netherlands or other EU countries
         */
        $collection->addFieldToFilter('country_id', array('neq' => 'NL'));
        $collection->addFieldToFilter('country_id', array('nin' => $euCountries));

        return $this;
    }

    /**
     * Filter the order grid's confirm date field. This field may represent either the postnl_order's confirm_date
     * column or the postnl_shipment's confirm_date column.
     *
     * @param TIG_PostNL_Model_Resource_Order_Grid_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column         $column
     *
     * @return $this
     */
    protected function _filterConfirmDate($collection, $column)
    {
        $filter = $column->getFilter();
        if (!$filter) {
            return $this;
        }

        $cond = $filter->getCondition();
        if (!$cond) {
            return $this;
        }

        $field = "IF(`postnl_shipment`.`confirm_date`, `postnl_shipment`.`confirm_date`, "
               . "`postnl_order`.`confirm_date`)";

        $collection->addFieldToFilter($field , $cond);

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

        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        if ($column->getFilterConditionCallback()) {
            call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);

            return $this;
        }

        $cond = $column->getFilter()->getCondition();
        if ($field && isset($cond)) {
            /**
             * @var TIG_PostNL_Model_Resource_Order_Grid_Collection $collection
             */
            $collection = $this->getCollection();
            $collection->addFieldToFilter($field , $cond);
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
