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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Action 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Additional column names used
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    const COUNTRY_ID_COLUMN      = 'country_id';
    const LABELS_PRINTED_COLUMN  = 'labels_printed';
    const CONFIRM_STATUS_COLUMN  = 'confirm_status';
    
    /**
     * Renders column
     *
     * @param Varien_Object $row
     * 
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }
        
        $actionLinks = array();
        foreach ($actions as $action) {
            /**
             * Check if this action is allowed. 
             */
            if (!$this->_isActionAllowed($row, $action)) {
                continue;
            }
            
            $action = $this->_disableAction($row, $action);
            
            if (is_array($action)) {
                $actionLinks[] = $this->_toLinkHtml($action, $row);
            }
        }
        
        $output = implode(' / ', $actionLinks);
        
        return $output;
    }
    
    /**
     * Checks if a certain action is allowed for this row
     * 
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param array $action
     * 
     * @return boolean
     */
    protected function _isActionAllowed($row, $action)
    {
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        
        /**
         * If this is a PostNL action, but this shipment was not shipped using PosTNL, skip it
         */
        if (array_key_exists('is_postnl', $action) 
            && $action['is_postnl']
            && !in_array($shippingMethod, $postnlShippingMethods)
        ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * In some cases an action must be disabled
     * 
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param array $action
     * 
     * @return array
     */
    protected function _disableAction($row, $action)
    {
        $helper = Mage::helper('postnl');
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        
        $euCountries = Mage::helper('postnl/cif')->getEuCountries();
        $countryId = $row->getData(self::COUNTRY_ID_COLUMN);
        $confirmStatus = $row->getData(self::CONFIRM_STATUS_COLUMN);
        
        /**
         * Right now only the confirm action needs to be disabled in certain instances
         */
        if ($action['caption'] != $helper->__('Confirm')) {
            return $action;
        }
        
        /**
         * If the shipment is confirmed, we can't confirm it again
         */
        if ($confirmStatus == $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED) {
            $action['style'] = 'color:gray; cursor:not-allowed;';
            $action['onClick'] = 'return false;';
            $action['title'] = $helper->__('This shipment has already been confirmed.');
            return $action;
        }
        
        /**
         * EU shipments can only confirm after their labels have been printed
         */
        if (in_array($countryId, $euCountries)
            && !$row->getData(self::LABELS_PRINTED_COLUMN)
        ){
            $action['style'] = 'color:gray; cursor:not-allowed;;';
            $action['onClick'] = 'return false;';
            $action['title'] = $helper->__("You must first print a shipping label for this shipment.");
            return $action;
        }
        
        return $action;
    }
}
