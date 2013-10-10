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
class TIG_PostNL_Model_Adminhtml_ShipmentGridObserver extends Varien_Object
{
    /**
     * The default name given to the shipment grid block when it is added by the container
     */
    const SHIPMENT_GRID_BLOCK_NAME = 'sales_shipment.grid';
    
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
     * @event sales_order_shipment_grid_collection_load_before
     * 
     * @observer postnl_adminhtml_shipmentgrid
     */
    public function modifyGrid(Varien_Event_Observer $observer)
    {
        $block = Mage::app()->getLayout()->getBlock(self::SHIPMENT_GRID_BLOCK_NAME);
        
        $collection = $observer->getOrderShipmentGridCollection();
        
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
            'url'       => Mage::helper('adminhtml')->getUrl('postnl/adminhtml_shipment/printLabel'),
            'field'     => 'shipment_id',
            'is_postnl' => true //custom flag for renderer
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
        $block->getMassactionBlock()
              ->addItem(
                  'print_labels', 
                  array(
                      'label'=> Mage::helper('postnl')->__('Print shipping labels & confirm shipment'),
                      'url'  => Mage::helper('adminhtml')->getUrl('postnl/adminhtml_shipment/massPrintLabels'),
                  )
              );
              
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
        $select->joinInner(
            array('order' => $resource->getTableName('sales/order')),
            'main_table.order_id=order.entity_id',
            array(
                'shipping_method' => 'order.shipping_method',
                'shipping_description' => 'order.shipping_description',
            )
        );
        $select->joinLeft(
            array('postnl_shipment' => $resource->getTableName('postnl/shipment')),
            'main_table.entity_id=postnl_shipment.shipment_id',
            array(
                'confirm_date' => 'postnl_shipment.confirm_date',
                'barcode'      => 'postnl_shipment.barcode',
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
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }
}
