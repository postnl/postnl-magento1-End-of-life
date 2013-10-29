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
 * Observer to edit the sales > order grid
 */
class TIG_PostNL_Model_Adminhtml_Observer_OrderGrid
{
    /**
     * The block we want to edit
     */
    const SHIPMENT_GRID_BLOCK_NAME = 'adminhtml/sales_order_grid';
    
    /**
     * XML path to show_grid_options setting
     */
    const XML_PATH_SHOW_OPTIONS = 'postnl/cif_product_options/show_grid_options';
    
    /**
     * Edits the sales order grid by adding a mass action to create shipments for selected orders
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return TIG_PostNL_Model_Adminhtml_OrderGridObserver
     * 
     * @event adminhtml_block_html_before
     * 
     * @observer postnl_adminhtml_ordergrid
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
        $orderGridClass = Mage::getConfig()->getBlockClassName(self::SHIPMENT_GRID_BLOCK_NAME);
       
        if (get_class($block) !== $orderGridClass) {
            return $this;
        }
        
        $massActionData = array(
            'label'=> Mage::helper('postnl')->__('Create Shipments'),
            'url'  => Mage::helper('adminhtml')->getUrl('postnl/adminhtml_shipment/massCreateShipments'),
        );
        
        $showOptions = Mage::getStoreConfig(self::XML_PATH_SHOW_OPTIONS, Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($showOptions) {
            $massActionData['additional'] = array(
                'product_options' => array(
                    'name'   => 'product_options',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper('postnl')->__('Product options'),
                    'values' => Mage::getModel('postnl_core/system_config_source_allProductOptions')->getAvailableOptions(true)
                ),
            );
        }
        
        $block->getMassactionBlock()
              ->addItem(
                  'create_shipments', 
                  $massActionData
              );
             
        return $this;
    }
}
