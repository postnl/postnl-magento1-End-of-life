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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_ShipmentType 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{    
    /**
     * Additional column names used
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    const IS_PAKJE_GEMAK_COLUMN  = 'is_pakje_gemak';
    
    /**
     * Renders the column value as a shipment type value (Domestic, EPS or GlobalPack)
     *
     * @param Varien_Object $row
     * 
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /**
         * The shipment was not shipped using PostNL
         */
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        if (!in_array($shippingMethod, $postnlShippingMethods)) {
            return '';
        }
        
        /**
         * Check if any data is available
         */
        $value = $row->getData($this->getColumn()->getIndex());
        if (is_null($value) || $value === '') {
            return '';
        }
        
        if ($row->getData(self::IS_PAKJE_GEMAK_COLUMN)) {
            $renderedValue = "<div id='postnl-shipmenttype-{$row->getId()}' class='no-display'>pakje_gemak</div>";
            $renderedValue .= Mage::helper('postnl')->__('Post Office');
            return $renderedValue;
        }
        
        /**
         * Check if this order is domestic
         */
        if ($value == 'NL') {
            $renderedValue = "<div id='postnl-shipmenttype-{$row->getId()}' class='no-display'>standard</div>";
            $renderedValue .= Mage::helper('postnl')->__('Domestic');
            return $renderedValue;
        }
        
        /**
         * Check if this order's shipping address is in an EU country
         */
        $euCountries = Mage::helper('postnl/cif')->getEuCountries();
        if (in_array($value, $euCountries)) {
            $renderedValue = "<div id='postnl-shipmenttype-{$row->getId()}' class='no-display'>eps</div>";
            $renderedValue .= Mage::helper('postnl')->__('EPS');
            return $renderedValue;
        }
        
        /**
         * If none of the above, it's an international order
         */
        $renderedValue = "<div id='postnl-shipmenttype-{$row->getId()}' class='no-display'>global_pack</div>";
        $renderedValue .= Mage::helper('postnl')->__('GlobalPack');
        
        return $renderedValue;
    }

    /**
     * Renders the <col> element of the column. Added check for $this->getColumn()->getDisplay() == 'none' that causes the
     * entire element to be hidden
     * 
     * @return string
     * 
     * @see Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract::renderProperty()
     */
    public function renderProperty()
    {
        if ($this->getColumn()->hasData('display')
            && $this->getColumn()->getDisplay() == 'none'
        ) {
            return 'style="display:none;"';
        }
        
        $out = '';
        $width = $this->_defaultWidth;

        if ($this->getColumn()->hasData('width')) {
            $customWidth = $this->getColumn()->getData('width');
            if ((null === $customWidth) || (preg_match('/^[0-9]+%?$/', $customWidth))) {
                $width = $customWidth;
            }
            elseif (preg_match('/^([0-9]+)px$/', $customWidth, $matches)) {
                $width = (int)$matches[1];
            }
        }

        if (null !== $width) {
            $out .= ' width="' . $width . '"';
        }

        return $out;
    }
}
