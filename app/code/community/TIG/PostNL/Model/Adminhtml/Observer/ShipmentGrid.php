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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
 
/**
 * Observer to edit the sales > shipments grid
 */
class TIG_PostNL_Model_Adminhtml_Observer_ShipmentGrid extends Varien_Object
{
    /**
     * The block we want to edit
     */
    const SHIPMENT_GRID_BLOCK_NAME = 'adminhtml/sales_shipment_grid';
    
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
     * array of column indices added by this module
     * 
     * @var array
     */
    protected $_postnlColumns = array(
        'shipping_description',
        'confirm_date',
        'barcode',
    );
    
    /**
     * Get the array of postnl columns
     * 
     * @return array
     */
    public function getPostnlColumns()
    {
        return $this->_postnlColumns;
    }
    
    /**
     * Observer that adds columns to the grid and allows these to be sorted and filtered properly
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
     * 
     * @event adminhtml_block_html_before
     * 
     * @observer postnl_adminhtml_shipmentgrid
     * 
     * @todo see if $collection->clear() can be avoided
     */
    public function modifyGrid(Varien_Event_Observer $observer)
    {
        //check if the extension is active
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }
        
        /**
         * Checks if the current block is the one we want to edit.
         * 
         * Unfortunately there is no unique event for this block
         */
        $block = $observer->getBlock();
        $shipmentGridClass = Mage::getConfig()->getBlockClassName(self::SHIPMENT_GRID_BLOCK_NAME);
       
        if (get_class($block) !== $shipmentGridClass) {
            return $this;
        }
        
        $collection = $block->getCollection();
        /**
         * reset the collection as it has previously been loaded and we still need to edit it
         * 
         * TODO check if there is no way to avoid having to do this as it causes a decent perfromance hit
         */
        $collection->clear(); 
        
        $this->setCollection($collection);
        $this->setBlock($block);
        
        $this->_addColumns($block);
        $this->_addMassaction($block);
        $this->_joinCollection($collection);
        $this->_applySortAndFilter($collection);
        
        return $this;
    }
    
    /**
     * Adds additional columns to the grid
     * 
     * @param Mage_Adminhtml_Block_Sales_Shipment_Grid $block
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
     */
    protected function _addColumns($block)
    {
        $helper = Mage::helper('postnl');
        
        $block->addColumnAfter(
            'shipping_description',
            array(
                'header'    => $helper->__('Shipping Method'),
                'align'     => 'left',
                'index'     => 'shipping_description',
            ),
            'total_qty'
        );
        
        $block->addColumnAfter(
            'confirm_date',
            array(
                'type'     => 'date',
                'header'   => $helper->__('Confirm Date'),
                'align'    => 'left',
                'index'    => 'confirm_date',
                'renderer' => 'postnl_adminhtml/widget_grid_column_renderer_confirmDate',
            ),
            'shipping_description'
        );
        
        $block->addColumnAfter(
            'barcode',
            array(
                'header'   => $helper->__('Track & Trace'),
                'align'    => 'left',
                'index'    => 'barcode',
                'renderer' => 'postnl_adminhtml/widget_grid_column_renderer_barcode',
            ),
            'confirm_date'
        );
        
        $actionColumn = $block->getColumn('action');
        $actions = $actionColumn->getActions();
        
        $actions[] = array(
            'caption'   => $helper->__('Print label'),
            'url'       => array('base' => 'postnl/adminhtml_shipment/printLabel'),
            'field'     => 'shipment_id',
            'is_postnl' => true, //custom flag for renderer
        );
        
        $actions[] = array(
            'caption'   => $helper->__('Confirm'),
            'url'       => array('base' => 'postnl/adminhtml_shipment/confirm'),
            'field'     => 'shipment_id',
            'is_postnl' => true, //custom flag for renderer
        );
        
        $actionColumn->setActions($actions)
                     ->unsWidth()
                     ->setData('renderer', 'postnl_adminhtml/widget_grid_column_renderer_action');
        
        $block->sortColumnsByOrder();
        
        return $this;
    }

    /**
     * Adds a massaction to confirm the order and print the shipping labels
     * 
     * @param Mage_Adminhtml_Block_Sales_Shipment_Grid $block
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
     */
    protected function _addMassaction($block)
    {
        $massactionBlock = $block->getMassactionBlock();
        
        $massactionBlock->addItem(
            'postnl_print_labels_and_confirm',
            array(
                'label'=> Mage::helper('postnl')->__('PostNL - Print shipping labels & confirm shipment'),
                'url'  => Mage::helper('adminhtml')->getUrl('postnl/adminhtml_shipment/massPrintLabelsAndConfirm'),
            )
        );
        
        $massactionBlock->addItem(
            'postnl_print_labels',
            array(
                'label'=> Mage::helper('postnl')->__('PostNL - Print shipping labels'),
                'url'  => Mage::helper('adminhtml')->getUrl('postnl/adminhtml_shipment/massPrintLabels'),
            )
        );
        
        // /**
         // * get the default print_shipping_label massaction then remove it
         // */
        // $printShippingLabel = $block->getMassactionBlock()->getItem('print_shipping_label');
        // $massactionBlock->removeItem('print_shipping_label');
//         
        // /**
         // * Change the mass action's url so it triggers our own massaction, rather than the default and add it again to the block
         // */
        // $printShippingLabel->setUrl(Mage::helper('adminhtml')->getUrl('postnl/adminhtml_shipment/massPrintLabels'));
        // $massactionBlock->addItem('print_shipping_label', $printShippingLabel->toArray());
        
        return $this;
    }
    
    /**
     * Adds additional joins to the collection that will be used by newly added columns
     * 
     * @param Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection $collection
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
     */
    protected function _joinCollection($collection)
    {
        $resource = Mage::getSingleton('core/resource');
        
        $select = $collection->getSelect();
        
        /**
         * Join sales_flat_order table
         */
        $select->joinInner(
            array('order' => $resource->getTableName('sales/order')),
            '`main_table`.`order_id`=`order`.`entity_id`',
            array(
                'shipping_method'      => 'order.shipping_method',
                'shipping_description' => 'order.shipping_description',
            )
        );
        
        /**
         * join sales_flat_order_address table
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
         * Join tig_postnl_shipment table
         */
        $select->joinLeft(
            array('postnl_shipment' => $resource->getTableName('postnl_core/shipment')),
            '`main_table`.`entity_id`=`postnl_shipment`.`shipment_id`',
            array(
                'confirm_date'   => 'postnl_shipment.confirm_date',
                'barcode'        => 'postnl_shipment.barcode',
                'confirm_status' => 'postnl_shipment.confirm_status',
            )
        );
        
        return $this;
    }
    
    /**
     * Applies sorting and filtering to the collection
     * 
     * @param Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection $collection
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
     */
    protected function _applySortAndFilter($collection)
    {
        $session = Mage::getSingleton('adminhtml/session');
        
        $filter = $session->getData(self::SHIPMENT_GRID_FILTER_VAR_NAME);
        $filter = Mage::helper('adminhtml')->prepareFilterString($filter);
        
        if ($filter) {
            $this->_filterCollection($collection, $filter);
        }
        
        $postnlColumns = $this->getPostnlColumns();
        $sort = $session->getData(self::SHIPMENT_GRID_SORT_VAR_NAME);
        
        if ($sort && in_array($sort, $postnlColumns)) {
            $dir = $session->getData(self::SHIPMENT_GRID_DIR_VAR_NAME);
            
            $this->_sortCollection($collection, $sort, $dir);
        }
        
        return $this;
    }
    
    /**
     * Adds new filters to the collection if these filters are based on columns added by this observer
     * 
     * @param Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection $collection
     * @param array $filter Array of filters to be added
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
     */
    protected function _filterCollection($collection, $filter)
    {
        $postnlColumns = $this->getPostnlColumns();
        $block = $this->getBlock();
        
        foreach ($filter as $columnName => $value) {
            if (!in_array($columnName, $postnlColumns)) {
                continue;
            }
            
            $column = $block->getColumn($columnName);
            
            $column->getFilter()->setValue($value);
            $this->_addColumnFilterToCollection($column);
        }
        
        return $this;
    }
    
    /**
     * Based on Mage_Adminhtml_Block_Widget_Grid::_addColumnFilterToCollection()
     * 
     * Adds a filter condition tot eh collection for a specified column
     * 
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
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
            $this->getCollection()->addFieldToFilter($field , $cond);
        }
        
        return $this;
    }
    
    /**
     * Sorts the collection by a specified column in a specified direction
     * 
     * @param Mage_Sales_Model_Resource_Order_Shipment_Grid_Collection $collection
     * @param string $sort The column that the collection is sorted by
     * @param string $dir The direction that is used to sort the collection
     * 
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
     */
    protected function _sortCollection($collection, $sort, $dir)
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
     * @return TIG_PostNL_Model_Adminhtml_ShipmentGridObserver
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
