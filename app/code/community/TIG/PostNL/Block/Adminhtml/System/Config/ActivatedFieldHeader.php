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
class TIG_PostNL_Block_Adminhtml_System_Config_ActivatedFieldHeader
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Template file used
     * 
     * @var string
     */
    protected $_template = 'TIG/PostNL/system/config/field_header.phtml';
    
    /**
     * Get the element's HTML ID
     * 
     * @return string
     */
    public function getHtmlId()
    {
        if (!$this->getElement()) {
            return '';
        }
        
        $element = $this->getElement();
        $id = $element->getHtmlId();
        
        $this->setHtmlId($id);
        return $id;
    }
    /**
     * Get the element's label
     * 
     * @return string
     */
    public function getLabel()
    {
        if (!$this->getElement()) {
            return '';
        }
        
        $element = $this->getElement();
        $label = $element->getLabel();
        
        $section = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');
        
        $urlParams = array(
            '_secure' => true,
        );
        
        if ($section) {
            $urlParams['section'] = $section;
        }
        
        if ($website) {
            $urlParams['website'] = $website;
        }
        
        if ($store) {
            $urlParams['store'] = $store;
        }
        
        $url = $this->getUrl('postnl/adminhtml_extensionControl/showActivationFields', $urlParams);
        $onclick = "confirmSetLocation('" 
                 . $this->__("Are you sure? The PostNL extension will not function until you\'ve reactivated the extension.") 
                 . "', '" 
                 . $url 
                 . "');";
        
        $label = sprintf(
            $label,
            $onclick
        );
        
        $this->setLabel($label);
        return $label;
    }
    
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        
        return $this->toHtml();
    }
}
