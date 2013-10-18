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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_ConfirmDate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{
    /**
     * Additional column names used
     * 
     * @var string
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    const CONFIRM_STATUS_COLUMN  = 'confirm_status';
    
    /**
     * Code of postnl shipping method
     * 
     * @var string
     */
    const POSTNL_SHIPPING_METHOD = 'postnl_postnl';
    
    /**
     * Renders column.
     *
     * @param Varien_Object $row
     * 
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        if ($shippingMethod != self::POSTNL_SHIPPING_METHOD) {
            return parent::render($row);
        }
        
        $postnlShipmentModel = Mage::app()->getConfig()->getModelClassName('postnl/shipment');
        if ($row->getData(self::CONFIRM_STATUS_COLUMN) == $postnlShipmentModel::CONFIRM_STATUS_CONFIRMED) {
            return Mage::helper('postnl')->__('Confirmed');
        }
        
        $value = $row->getData($this->getColumn()->getIndex());
        if (date('Ymd') == date('Ymd', strtotime($value))) { //check if value equals today
            return Mage::helper('postnl')->__('Today');
        }
        
        return parent::render($row);
    }
}
