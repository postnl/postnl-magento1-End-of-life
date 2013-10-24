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
class TIG_PostNL_Helper_Carrier extends Mage_Core_Helper_Abstract
{
    /**
     * Shipping method code used by PostNL
     */
    const POSTNL_SHIPPING_METHOD = 'postnl_postnl';
    
    /**
     * PostNL's track and trace base URL
     */
    const POSTNL_TRACK_AND_TRACE_BASE_URL = 'http://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl';
    
    /**
     * Returns the PostNL shipping method
     * 
     * @return string
     */
    public function getPostnlShippingMethod()
    {
        return self::POSTNL_SHIPPING_METHOD;
    }
    
    /**
     * Checks if a given postnl shipment exists using Zend_Validate_Db_RecordExists.
     * 
     * @param string $shipmentId
     * 
     * @return boolean
     * 
     * @see Zend_Validate_Db_RecordExists
     * 
     * @link http://framework.zend.com/manual/1.12/en/zend.validate.set.html#zend.validate.Db
     */
    public function postnlShipmentExists($shipmentId)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $readAdapter = $coreResource->getConnection('core_read');
        
        $validator = Mage::getModel('Zend_Validate_Db_RecordExists', 
            array(
                'table'   => $coreResource->getTableName('postnl_core/shipment'),
                'field'   => 'shipment_id',
                'adapter' => $readAdapter,
            )
        );
        
        $postnlShipmentExists = $validator->isValid($shipmentId);
        
        if ($postnlShipmentExists) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Constructs a PostNL track & trace url based on a barcode and the destination of the package (country and zipcode)
     * 
     * @param string $barcode
     * @param mixed $destination An array or object containing the shipment's destination data
     * 
     * @return string
     */
    public function getBarcodeUrl($barcode, $destination = false)
    {
        $countryCode = null;
        $postcode    = null;
        if (is_array($destination)) {
            $countryCode = $destination['countryCode'];
            $postcode    = $destination['postcode'];
        }
        
        if (is_object($destination) && $destination instanceof Varien_Object) {
            $countryCode = $destination->getCountry();
            $postcode    = $destination->getPostcode();
        }
        
        $barcodeUrl = self::POSTNL_TRACK_AND_TRACE_BASE_URL
                    . '&B=' . $barcode;
        if ($countryCode == 'NL' && $postcode) {
            /**
             * For Dutch shipments we can add the destination zip code
             */
            $barcodeUrl .= '&P=' . $postcode;
        } elseif (!empty($countryCode) && $countryCode != 'NL') {
            /**
             * For international shipments we need to add a flag
             */
            $barcodeUrl .= '&I=True';
        }
        
        return $barcodeUrl;
    }
}
