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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Barcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Column name containing the shipment's shipping method
     * 
     * @var string
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    
    /**
     * Column name containing the shipping address' postcode
     * 
     * @var string
     */
    const POSTCODE_COLUMN = 'postcode';
    
    /**
     * Column name containing the shipping address' country id
     * 
     * @var string
     */
    const COUNTRY_ID_COLUMN = 'country_id';
    
    /**
     * Code of postnl shipping method
     * 
     * @var string
     */
    const POSTNL_SHIPPING_METHOD = 'postnl_postnl';
    
    /**
     * PostNL's track and trace base URL
     * 
     * @var
     */
    const POSTNL_DUTCH_TRACK_AND_TRACE_BASE_URL = 'http://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl';
    
    /**
     * PostNL's track and trace base URL
     * 
     * @var
     */
    const POSTNL_GLOBAL_TRACK_AND_TRACE_BASE_URL = '    http://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl&I=True';
    
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
        
        $value = $row->getData($this->getColumn()->getIndex());
        if (!$value) {
            $value = Mage::helper('postnl')->__('Not yet confirmed');
            return $value;
        }
        
        $countryCode = $row->getData(self::COUNTRY_ID_COLUMN);
        
        if ($countryCode == 'NL') {
            $postcode = $row->getData(self::POSTCODE_COLUMN);
            $barcodeBaseUrl = self::POSTNL_DUTCH_TRACK_AND_TRACE_BASE_URL
                            . '&P=' . $postcode;
        } else {
            $barcodeBaseUrl = self::POSTNL_GLOBAL_TRACK_AND_TRACE_BASE_URL;
        }
        
        $barcodeUrl = $barcodeBaseUrl . '&B=' . $value;
        $barcodeHtml = "<a href='{$barcodeUrl}'>{$value}</a>";
        
        return $barcodeHtml;
    }
}
