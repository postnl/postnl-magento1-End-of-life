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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Parcelware_Export
{
    /**
     * Creates a parcelware export csv based for an array of PostNL shipments
     * 
     * @param array $shipments An array of TIG_PostNL_Model_Core_Shipment objects
     * 
     * @return TIG_PostNL_Model_Parcelware_Export
     */
    public function exportShipments($postnlShipments)
    {
        $csvHeaders = $this->_getCsvHeaders();
        
        $content = array();
        foreach ($postnlShipments as $postnlShipment) {
            $shipment = $postnlShipment->getShipment();
            
            $addressData = $this->_getAddressData($shipment);
            $referenceData = $this->_getReferenceData($shipment);
            
            $parcelCount = $shipment->getparcelCount();
            if ($parcelCount > 1) {
                for ($i = 0; $i <= $parcelCount; $i++) {
                    $shipmentData = array(
                        'product_code'   => $postnlShipment->getProductCode(),
                        'barcode'        => $postnlShipment->getBarcode($i),
                        'main_barcode'   => $postnlShipment->getMainBarcode(),
                        'colli_nr'       => $i,
                        'colli_total_nr' => $parcelCount,
                    );
                }
            
                $shipmentData = array_merge($shipmentData, $addressData, $referenceData);
                
                $content[] = $shipmentData;
                continue;
            }
            
            $shipmentData = array(
                'product_code'   => $postnlShipment->getProductCode(),
                'barcode'        => $postnlShipment->getMainBarcode(),
                'main_barcode'   => $postnlShipment->getMainBarcode(),
                'colli_nr'       => 1,
                'colli_total_nr' => 1,
            );
            
            $shipmentData = array_merge($shipmentData, $addressData, $referenceData);
            
            $content[] = $shipmentData;
        }

        echo '<pre>';var_dump($content);exit;
    }

    protected function _getAddressData($shipment)
    {
        $address = $shipment->getShippingAddress();
        $streetData = Mage::getSingleton('postnl_core/cif')
                          ->setStoreId($shipment->getStoreId())
                          ->getStreetData($address, false);
        
        $data = array(
            'company'               => $address->getCompany(),
            'firstname'             => $address->getFirstname(),
            'lastname'              => $address->getLastname(),
            'street'                => $streetData['streetname'],
            'housenumber'           => $streetData['housenumber'],
            'housenumber_extension' => $streetData['housenumberExtension'],
            'postcode'              => $address->getPostcode(),
            'city'                  => $address->getCity(),
            'country_id'            => $address->getCountryId(),
        );
        
        return $data;
    }
    
    protected function _getReferenceData($shipment)
    {
        $data = array(
            'ref_contract',
            'name_contract',
            'ref_sender',
        );
        
        return $data;
    }
    
    /**
     * Gets CSV headers for the parcelware export file.
     * 
     * @param boolean $isGlobalPack
     * 
     * @return array
     */
    protected function _getCsvHeaders($isGlobalPack = false)
    {
        $csvHeaders = array(
            'company',
            'firstname',
            'lastname',
            'street',
            'housenumber',
            'housenumber_extension',
            'postcode',
            'city',
            'country_id',
            'product_code',
            'barcode',
            'main_barcode',
            'colli_nr',
            'colli_total_nr',
            'ref_contract',
            'name_contract',
            'ref_sender',
        );
        
        if ($isGlobalPack === true) {
            $globalPackHeaders = array(
                'currency',
                'shipment_type',
                'customs_cert',
                'customs_license',
                'treat_as_abandoned',
                'product_1_description',
                'product_1_qty',
                'product_1_weight',
                'product_1_value',
                'product_1_hstariff',
                'product_1_country_of_manufacture',
                'product_2_description',
                'product_2_qty',
                'product_2_weight',
                'product_2_value',
                'product_2_hstariff',
                'product_2_country_of_manufacture',
                'product_3_description',
                'product_3_qty',
                'product_3_weight',
                'product_3_value',
                'product_3_hstariff',
                'product_3_country_of_manufacture',
                'product_4_description',
                'product_4_qty',
                'product_4_weight',
                'product_4_value',
                'product_4_hstariff',
                'product_4_country_of_manufacture',
                'product_5_description',
                'product_5_qty',
                'product_5_weight',
                'product_5_value',
                'product_5_hstariff',
                'product_5_country_of_manufacture',
            );
            
            $csvHeaders = array_merge($csvHeaders, $globalPackHeaders);
        }

        return $csvHeaders;
    }
}
