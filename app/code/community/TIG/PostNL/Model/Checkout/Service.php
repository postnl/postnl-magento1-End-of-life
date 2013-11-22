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
class TIG_PostNL_Model_Checkout_Service extends Varien_Object
{
    /**
     * XML path to public webshop ID setting
     */
    const XML_PATH_WEBSHOP_ID = 'postnl/checkout/webshop_id';
    
    /**
     * Constants containing XML paths to cif address configuration options
     */
    const XML_PATH_SPLIT_STREET                = 'postnl/cif_address/split_street';
    const XML_PATH_STREETNAME_FIELD            = 'postnl/cif_address/streetname_field';
    const XML_PATH_HOUSENUMBER_FIELD           = 'postnl/cif_address/housenr_field';
    const XML_PATH_SPLIT_HOUSENUMBER           = 'postnl/cif_address/split_housenr';
    const XML_PATH_HOUSENUMBER_EXTENSION_FIELD = 'postnl/cif_address/housenr_extension_field';
    const XML_PATH_AREA_FIELD                  = 'postnl/cif_address/area_field';
    const XML_PATH_BUILDING_NAME_FIELD         = 'postnl/cif_address/building_name_field';
    const XML_PATH_DEPARTMENT_FIELD            = 'postnl/cif_address/department_field';
    const XML_PATH_DOORCODE_FIELD              = 'postnl/cif_address/doorcode_field';
    const XML_PATH_FLOOR_FIELD                 = 'postnl/cif_address/floor_field';
    const XML_PATH_REMARK_FIELD                = 'postnl/cif_address/remark_field';
    
    /**
     * Updates a quote with the given PostNL order data. Each part of the data is used to replace the data normally acquired
     * during checkout.
     * 
     * @param StdClass $data
     * @param Mage_Sales_Model_Quote | null $quote
     * 
     * @return TIG_PostNL_Model_Checkout_Service
     * 
     * @throws TIG_PostNL_Exception
     */
    public function updateQuote($data, $quote = null)
    {
        if (is_null($quote)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        
        $quoteId = $data->Order->ExtRef;
        if ($quote->getId() != $quoteId) {
            throw Mage::exception('TIG_PostNL', 'Invalid quote supplied.');
        }
        
        $this->setStoreId($quote->getStoreId());
        
        $webshopId = $data->Webshop->IntRef;
        if (Mage::getStoreConfig(self::XML_PATH_WEBSHOP_ID, $this->getStoreId()) != $webshopId) {
            throw Mage::exception('TIG_PostNL', 'Invalid data supplied.');
        }
        
        $shippingAddressData = $data->Bezorging->Geadresseerde;
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress = $this->_parseAddress($shippingAddress, $shippingAddressData);
        
        $billingAddressData = $data->Facturatie->Adres;
        $billingAddress = $quote->getBillingAddress();
        $billingAddress = $this->_parseAddress($billingAddress, $billingAddressData);
        
        $quote->setShippingAddress($shippingAddress)
              ->setBillingAddress($billingAddress)
              ->save();
        
        return $this;
    }
    
    /**
     * Parses a PostNL Checkout address into a varien object that can be used by Magento
     * 
     * @param StdClass $addressData
     * 
     * @return Varien_Object
     */
    protected function _parseAddress($address, $addressData)
    {
        $storeId = $this->getStoreId();
        
        /**
         * First parse the street data (streetname, house nr. house nr. ext.)
         */
        $splitStreet = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_STREET, $storeId);
        $splitHousenumber = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_HOUSENUMBER, $storeId);
        
        if ($splitStreet) {
            $streetData = array();
            
            /**
             * If the store uses multiple address lines, check which part of the address goes where
             */
            $streetnameField = Mage::getStoreConfig(self::XML_PATH_STREETNAME_FIELD, $storeId);
            $housenumberField = Mage::getStoreCOnfig(self::XML_PATH_HOUSENUMBER_FIELD, $storeId);
            
            /**
             * Set the streetname to the appropriate field
             */
            $streetData[$streetnameField] = $addressData->Straat;
            
            /**
             * Check if the store splits housenumber and housenumber extensions as well. Place them in appriopriate fields
             */
            if (!$splitHousenumber) {
                $housenumber = $addressData->Huisnummer . ' ' . $addressData->HuisnummerExt;
                $streetData[$housenumberField] = $housenumber;
            } else {
                $housenumberExtensionField = Mage::getStoreConfig(self::XML_PATH_HOUSENUMBER_EXTENSION_FIELD, $storeId);
                $streetData[$housenumberField] = $addressData->Huisnummer;
                $streetData[$housenumberExtensionField] = $addressData->HuisnummerExt;
            }
            
            /**
             * Sort the street data according to the field numbers and set it
             */
            ksort($streetData);
            $address->setStreet($streetData);
        } else {
            /**
             * If the store uses single line addresses, merge the street fields
             */
            $streetData = $addressData->Straat . PHP_EOL . $addressData->Huisnummer . PHP_EOL . $addressData->HuisnummerExt;
            
            $address->setStreet($streetData);
        }
        
        /**
         * Parse optional address fields
         */
        $buildingNameField = Mage::getStoreConfig(self::XML_PATH_BUILDING_NAME_FIELD, $storeId);
        $departmentField   = Mage::getStoreConfig(self::XML_PATH_DEPARTMENT_FIELD, $storeId);
        $doorcodeField     = Mage::getStoreConfig(self::XML_PATH_DOORCODE_FIELD, $storeId);
        $floorField        = Mage::getStoreConfig(self::XML_PATH_FLOOR_FIELD, $storeId);
        $areaField         = Mage::getStoreConfig(self::XML_PATH_AREA_FIELD, $storeId);
        
        if ($buildingNameField) {
            $address->setData('street' . $buildingNameField, $addressData->Gebouw);
        }
        
        if ($departmentField) {
            $address->setData('street' . $departmentField, $addressData->Afdeling);
        }
        
        if ($doorcodeField) {
            $address->setData('street' . $doorcodeField, $addressData->Deurcode);
        }
        
        if ($floorField) {
            $address->setData('street' . $floorField, $addressData->Verdieping);
        }
        
        if ($areaField) {
            $address->setData('street' . $areaField, $addressData->Wijk);
        }
        
        /**
         * Parse the remaining simple fields that require no additional logic
         */
        $firstname  = $addressData->Voornaam;
        $lastname   = $addressData->Achternaam;
        $middlename = $addressData->Tussenvoegsel;
        $country    = $addressData->Land;
        $city       = $addressData->Plaats;
        $postcode   = $addressData->Postcode;
        
        $address->setFirstname($firstname)
                ->setLastname($lastname)
                ->setMiddelname($middlename)
                ->setCountry($country)
                ->setCity($city)
                ->setPostcode($postcode);
                
        return $address;
    }
}
