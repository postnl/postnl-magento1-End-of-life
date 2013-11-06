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
 * Class containing all default methods used for CIF communication by this extension.
 * 
 * If you wish to add new methods you can etxend this class or create a new class that extends TIG_PostNL_Model_Core_Cif_Abstract
 * 
 * @todo implement warning processing
 */
class TIG_PostNL_Model_Core_Cif extends TIG_PostNL_Model_Core_Cif_Abstract
{
    /**
     * Constants containing xml paths to cif configuration options
     */
    const XML_PATH_CUSTOMER_CODE               = 'postnl/cif/customer_code';
    const XML_PATH_CUSTOMER_NUMBER             = 'postnl/cif/customer_number';
    const XML_PATH_COMPANY_NAME                = 'postnl/cif/company_name';
    const XML_PATH_CONTACT_NAME                = 'postnl/cif/contact_name';
    const XML_PATH_CONTACT_EMAIL               = 'postnl/cif/contact_email';
    const XML_PATH_COLLECTION_LOCATION         = 'postnl/cif/collection_location';
    const XML_PATH_GLOBAL_BARCODE_TYPE         = 'postnl/cif/global_barcode_type';
    const XML_PATH_GLOBAL_BARCODE_RANGE        = 'postnl/cif/global_barcode_range';
    
    /**
     * Constants containing xml paths to cif address configuration options
     */
    const XML_PATH_SPLIT_STREET                = 'postnl/cif_address/split_street';
    const XML_PATH_STREETNAME_FIELD            = 'postnl/cif_address/streetname_field';
    const XML_PATH_HOUSENUMBER_FIELD           = 'postnl/cif_address/housenr_field';
    const XML_PATH_SPLIT_HOUSENUMBER           = 'postnl/cif_address/split_housenr';
    const XML_PATH_HOUSENUMBER_EXTENSION_FIELD = 'postnl/cif_address/housenr_extension_field';
    
    /**
     * Constants containing xml paths to cif customs configuration options
     */
    const XML_PATH_GLOBALPACK_CUSTOMS_LICENSE_NUMBER      = 'postnl/cif_globalpack_settings/customs_license_number';
    const XML_PATH_GLOBALPACK_CUSTOMS_CERTIFICATE_NUMBER  = 'postnl/cif_globalpack_settings/customs_certificate_number';
    const XML_PATH_GLOBALPACK_USE_HS_TARIFF_ATTRIBUTE     = 'postnl/cif_globalpack_settings/use_hs_tariff';
    const XML_PATH_GLOBALPACK_HS_TARIFF_ATTRIBUTE         = 'postnl/cif_globalpack_settings/hs_tariff_attribute';
    const XML_PATH_GLOBALPACK_CUSTOMS_VALUE_ATTRIBUTE     = 'postnl/cif_globalpack_settings/customs_value_attribute';
    const XML_PATH_GLOBALPACK_COUNTRY_OF_ORIGIN_ATTRIBUTE = 'postnl/cif_globalpack_settings/country_of_origin_attribute';
    const XML_PATH_GLOBALPACK_DESCRIPTION_ATTRIBUTE       = 'postnl/cif_globalpack_settings/description_attribute';
    const XML_PATH_GLOBALPACK_PRODUCT_SORTING_ATTRIBUTE   = 'postnl/cif_globalpack_settings/product_sorting_attribute';
    const XML_PATH_GLOBALPACK_PRODUCT_SORTING_DIRECTION   = 'postnl/cif_globalpack_settings/product_sorting_direction';
    
    /**
     * XML path to setting that dtermines whether to use a seperate return address
     */
    const XML_PATH_USE_SENDER_ADDRESS_AS_RETURN = 'postnl/cif_return_address/use_sender_address';
    
    /**
     * XML path to sender address data.
     * 
     * N.B. missing last part so this will return an array of all fields.
     */
    const XML_PATH_SENDER_ADDRESS = 'postnl/cif_sender_address';
    
    /**
     * XML path to return address data.
     * 
     * N.B. missing last part so this will return an array of all fields.
     */
    const XML_PATH_RETURN_ADDRESS = 'postnl/cif_return_address';
    
    /**
     * Possible barcodes series per barcode type
     */
    const NL_BARCODE_SERIE_LONG   = '0000000000-9999999999';
    const NL_BARCODE_SERIE_SHORT  = '000000000-999999999';
    const EU_BARCODE_SERIE_LONG   = '00000000-99999999';
    const EU_BARCODE_SERIE_SHORT  = '0000000-9999999';
    const GLOBAL_BARCODE_SERIE    = '0000-9999';
    
    /**
     * XML path to weight per parcel config setting
     */
    const XML_PATH_WEIGHT_PER_PARCEL = 'postnl/cif_labels_and_confirming/weight_per_parcel'; 
    
    /**
     * Regular expression used to split streetname from housenumber. This regex works well for dutch 
     * addresses, but may fail for international addresses. We strongly recommend using split address 
     * lines instead.
     */
    const SPLIT_STREET_REGEX = '#\A(.*?)\s+(\d+[a-zA-Z]{0,1}\s{0,1}[-]{1}\s{0,1}\d*[a-zA-Z]{0,1}|\d+[a-zA-Z-]{0,1}\d*[a-zA-Z]{0,1})#';
    
    /**
     * Regular expression used to split housenumber and housenumber extension
     */
    const SPLIT_HOUSENUMBER_REGEX = '#^([\d]+)(.*)#s';
    
    /**
     * array containing possible address types
     * 
     * @var array
     */
    protected $_addressTypes = array(
        'Receiver'    => '01',
        'Sender'      => '02',
        'Return'      => '03',
        'Collection'  => '04',
        'Alternative' => '08', // alternative sender. Parcels that cannot be delivered will be returned here
        'Delivery'    => '09', // for use with PakjeGemak
    );
    
    /**
     * array containing all available printer types. These are used to determine the output type of shipping labels
     * currently only GraphicFile|PDF is supported
     * 
     * printer type syntax is: <printer family>|<printer type>
     * 
     * @var array
     */
    protected $_printerTypes = array(
        //graphic files
        'GraphicFile|GIF 200 dpi',
        'GraphicFile|GIF 400 dpi',
        'GraphicFile|GIF 600 dpi',
        'GraphicFile|JPG 200 dpi',
        'GraphicFile|JPG 400 dpi',
        'GraphicFile|JPG 600 dpi',
        'GraphicFile|PDF',
        'GraphicFile|PS',
        
        //Intermec FingerPrint
        'IntermecEasyCoder PF4i',
        
        //Intermec IDP
        'Intermec|EasyCoder E4',
        
        //Intermec IPL
        'Intermec|EasyCoder PF4i IPL',
        
        //Sato
        'Sato|GL408e',
        
        //Tec TCPL
        'TEC|B472',
        
        //TECISQ
        'Meto|SP 40',
        'TEC|B-SV4D',
        
        //Zebra EPS2
        'Zebra|LP 2844',
        'Intermec|Easycoder C4',
        'Eltron|EPL 2 Printers',
        'Zebra|EPL 2 Printers',
        'Eltron|Orion',
        'Intermec|PF8d',
        
        //Zebra ZPL II
        'Zebra|LP 2844-Z',
        'Zebra|Stripe S600',
        'Zebra|Z4Mplus',
        'Zebra|Generic ZPL || 200 dpi',
        'Zebra|Generic ZPL || 400 dpi',
        'Zebra|DA 402',
        'Zebra|105Se',
        'Zebra|105SL',
        'Zebra|Stripe S300',
        'Zebra|Stripe S400',
        'Zebra|Stripe S500',
        'Zebra|A300',
        'Zebra|S4M',
        'Zebra|GK420d',
    );
    
    /**
     * Array of countires which may send their full street data in a single line, 
     * rather than having to split them into streetname, housenr and extension parts
     * 
     * @var array
     */
    protected $_allowedFullStreetCountries = array(
        'NL',
        'BE'
    );
    
    public function getAddressTypes()
    {
        return $this->_addressTypes;
    }
    
    public function getPrinterTypes()
    {
        return $this->_printerTypes;
    }
    
    public function getAllowedFullStreetCountries()
    {
        return $this->_allowedFullStreetCountries;
    }
    
    /**
     * Gets the current store id. If no store id is specified, return the default admin store id
     * 
     * @return int
     */
    public function getStoreId()
    {
        if ($this->getData('store_id')) {
            return $this->getData('store_id');
        }

        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $this->setStoreId($storeId);
        
        return $storeId;
    }
    
    /**
     * Retrieves a barcode from CIF
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $barcodeType Which kind of barcode to generate
     * 
     * @return string
     * 
     * @throws TIG_PostNL_Exception
     */
    public function generateBarcode($shipment, $barcodeType = 'NL')
    {
        $this->setStoreId($shipment->getStoreId());
        
        $barcode = $this->_getBarcodeData($barcodeType);
        
        $message  = $this->_getMessage('');
        $customer = $this->_getCustomer();
        $range    = $barcode['range'];
        $type     = $barcode['type'];
        $serie    = $barcode['serie'];
        
        $soapParams = array(
            'Message'  => $message,
            'Customer' => $customer,
            'Barcode'  => array(
                'Type'  => $type,
                'Range' => $range,
                'Serie' => $serie,
            ),
        );
        
        $response = $this->call(
            'Barcode', 
            'GenerateBarcode',
            $soapParams
        );
        
        if (!is_object($response) 
            || !isset($response->Barcode)
        ) {
            throw Mage::exception('TIG_PostNL', 'Invalid barcode response: ' . "\n" . var_export($reponse, true));
        }
        
        return $response->Barcode;
    }
    
    /**
     * Retrieves the latest shipping status of a shipment from CIF
     * 
     * @param $barcode The barcode of the shipment
     * 
     * @return StdClass 
     * 
     * @throws TIG_PostNL_Exception
     */
    public function getShipmentStatus($shipment)
    {
        $barcode  = $shipment->getMainBarcode();
        $message  = $this->_getMessage($shipment->getMainBarcode());
        $customer = $this->_getCustomer();
        
        $soapParams = array(
            'Message'  => $message,
            'Customer' => $customer,
            'Shipment' => array(
                'Barcode' => $barcode,
            ),
        );
        
        $response = $this->call(
            'ShippingStatus', 
            'CompleteStatus', 
            $soapParams
        );
        
        if (!is_object($response) 
            || !isset($response->Shipments) 
            || (!is_array($response->Shipments) && !is_object($response->Shipments))
        ) {
            throw Mage::exception('TIG_PostNL', 'Invalid shippingStatus response: ' . "\n" . var_export($reponse, true));
        }
        
        foreach($response->Shipments as $shipment) {
            if ($shipment->Barcode === $barcode) { // we need the original shipment, not a related shipment (such as a return shipment)
                return $shipment;
            }
        }
        
        /**
         * no shipment could be matched to the supplied barcode
         */ 
        throw Mage::exception('TIG_PostNL', 'Unable to match barcode to shippingStatus response: ' . "\n" . var_export($reponse, true));
    }
    
    /**
     * Confirms the choen shipment without generating labels
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $printerType The printertype used. Currently only 'GraphicFile|PDF' is fully supported
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    public function confirmShipment($postnlShipment, $printerType = 'GraphicFile|PDF')
    {
        $shipment = $postnlShipment->getShipment();
        
        $availablePrinterTypes = $this->_printerTypes;
        if (!in_array($printerType, $availablePrinterTypes)) {
            throw Mage::exception('TIG_PostNL', 'Invalid printer type requested: ' . $printerType);
        }
        
        $parcelCount = $postnlShipment->getParcelCount();
        $mainBarcode = $postnlShipment->getMainBarcode();
        
        $message     = $this->_getMessage($mainBarcode, array('Printertype' => $printerType));
        $customer    = $this->_getCustomer($shipment);
        
        if ($parcelCount < 2) {
            /**
             * Create a single shipment object
             */
            $cifShipment = array(
                'Shipment' => $this->_getShipment($postnlShipment, $mainBarcode)
            );
        } else {
            /**
             * Create a shipment object for each parcel
             */
            $shipments = array();
            for ($i = 0; $i < $parcelCount; $i++) {
                $barcode = $postnlShipment->getBarcode($i);
                $shipments[] = $this->_getShipment(
                                   $postnlShipment, 
                                   $barcode, 
                                   $mainBarcode,
                                   $i
                               );
            }
            
            $cifShipment = array('Shipment' => $shipments);
        }
        
        $soapParams =  array(
            'Message'   => $message,
            'Customer'  => $customer,
            'Shipments' => $cifShipment,
        );
        
        $response = $this->call(
            'Confirming', 
            'Confirming', 
            $soapParams
        );
        
        if (!is_object($response)) {
            throw Mage::exception('TIG_PostNL', 'Invalid confirmShipment response: ' . "\n" . var_export($response, true));
        }
        
        if (isset($response->ConfirmingResponseShipment) 
            && (is_object($response->ConfirmingResponseShipment)
                || is_array($response->ConfirmingResponseShipment)
            )
        ) {
            return $response;
        }
        
        throw Mage::exception('TIG_PostNL', 'Invalid confirmShipment response: ' . "\n" . var_export($response, true));
    }
    
    /**
     * Generates shipping labels for the chosen shipment
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $printerType The printertype used. Currently only 'GraphicFile|PDF' is fully supported
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    public function generateLabels($postnlShipment, $barcodeNumber = null, $printerType = 'GraphicFile|PDF')
    {
        $shipment = $postnlShipment->getShipment();
        
        $availablePrinterTypes = $this->_printerTypes;
        if (!in_array($printerType, $availablePrinterTypes)) {
            throw Mage::exception('TIG_PostNL', 'Invalid printer type requested: ' . $printerType);
        }
        
        $barcode = $postnlShipment->getBarcode($barcodeNumber);
        
        $message     = $this->_getMessage($barcode, array('Printertype' => $printerType));
        $customer    = $this->_getCustomer($shipment);
        $cifShipment = $this->_getShipment($postnlShipment, $barcode);
        
        $soapParams =  array(
            'Message'  => $message,
            'Customer' => $customer,
            'Shipment' => $cifShipment,
        );
        
        $response = $this->call(
            'Labelling', 
            'GenerateLabel', 
            $soapParams
        );
        
        if (!is_object($response) 
            || !isset($response->Labels) 
            || !is_object($response->Labels)
        ) {
            throw Mage::exception('TIG_PostNL', 'Invalid generateLabels response: ' . "\n" . var_export($reponse, true));
        }
        
        return $response;
    }
    
    /**
     * Generates shipping labels for the chosen shipment without confirming it
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $printerType The printertype used. Currently only 'GraphicFile|PDF' is fully supported
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    public function generateLabelsWithoutConfirm($postnlShipment, $barcodeNumber = null, $printerType = 'GraphicFile|PDF')
    {
        $shipment = $postnlShipment->getShipment();
        
        $availablePrinterTypes = $this->_printerTypes;
        if (!in_array($printerType, $availablePrinterTypes)) {
            throw Mage::exception('TIG_PostNL', 'Invalid printer type requested: ' . $printerType);
        }
        
        $barcode = $postnlShipment->getBarcode($barcodeNumber);
        
        $message     = $this->_getMessage($barcode, array('Printertype' => $printerType));
        $customer    = $this->_getCustomer($shipment);
        $cifShipment = $this->_getShipment($postnlShipment, $barcode);
        
        $soapParams =  array(
            'Message'  => $message,
            'Customer' => $customer,
            'Shipment' => $cifShipment,
        );
        
        $response = $this->call(
            'Labelling', 
            'GenerateLabelWithoutConfirm', 
            $soapParams
        );
        
        if (!is_object($response) 
            || !isset($response->Labels) 
            || !is_object($response->Labels)
        ) {
            throw Mage::exception('TIG_PostNL', 'Invalid generateLabels response: ' . "\n" . var_export($reponse, true));
        }
        
        return $response;
    }
    
    /**
     * Gets the Message parameter
     * 
     * @param array $extra An array of additional parameters to add
     * 
     * @return array
     * 
     * @todo change message ID to truly unique value
     */
    protected function _getMessage($barcode, $extra = array())
    {
        $message = array(
            'MessageID'        => md5(uniqid('postnl_') .  md5($barcode)), //TODO change to truly unique value (based on barcode, perhaps)
            'MessageTimeStamp' => date('d-m-Y H:i:s', Mage::getModel('core/date')->timestamp()),
        );
        
        if ($extra) {
            $message = array_merge($message, $extra);
        }
        
        return $message;
    }
    
    /**
     * Gets the customer parameter
     * 
     * @param Mage_Sales_Model_Order_Shipment | boolean $shipment
     * 
     * @return array
     */
    protected function _getCustomer($shipment = false)
    {
        $customer = array(
            'CustomerCode'       => $this->_getCustomerCode(),
            'CustomerNumber'     => $this->_getCustomerNumber(),
        );
        
        if ($shipment) {
            $additionalCustomerData = array(
                'Address'            => $this->_getAddress('Sender'),
                'CollectionLocation' => $this->_getCollectionLocation(),
                'ContactPerson'      => $this->_getContactName(),
                'Email'              => $this->_getContactEmail(),
                'Name'               => $this->_getCompanyName(),
            );
            
            $customer = array_merge($customer, $additionalCustomerData);
        }
        
        return $customer;
    }
    
    /**
     * Creates the CIF shipment object based on a PostNL shipment
     * 
     * @param TIG_PostNL_Model_Core_Shipment $shipment
     * 
     * @return array
     * 
     * @todo modify to support OVM and PostNL checkout shipments
     */
    protected function _getShipment($postnlShipment, $barcode, $mainBarcode = false, $shipmentNumber = false)
    {
        $shipment        = $postnlShipment->getShipment();
        $shippingAddress = $shipment->getShippingAddress();
        
        $parcelCount = $postnlShipment->getParcelCount();
        $shipmentWeight = $postnlShipment->getTotalWeight(true, true);
        
        /**
         * If a shipmentNumber is provided it means that this is not a single shipment and the weight of the shipment
         * needs to be calculated
         */
        if ($shipmentNumber !== false) {
            $parcelWeight = Mage::getStoreConfig(self::XML_PATH_WEIGHT_PER_PARCEL, $postnlShipment->getStoreId());
            $parcelWeight *= 1000; //convert the parcelweight to grams
            
            /**
             * All parcels except for the last one weigh a configured amount. The last parcel weighs the remainder
             */
            if ($shipmentNumber < $parcelCount) {
                $shipmentWeight = $parcelWeight;
            } else {
                $shipmentWeight = $shipmentWeight % $parcelWeight;
            }
        }
        
        /**
         * Shipment weight may not be less than 1 gram
         */
        if ($shipmentWeight < 1) {
            $shipmentWeight = 1;
        }
        
        $shipmentData = array(
            'Barcode'                  => $barcode,
            'CollectionTimeStampEnd'   => '',
            'CollectionTimeStampStart' => '',
            'DownPartnerBarcode'       => '',
            'DownPartnerID'            => '',
            'ProductCodeDelivery'      => $postnlShipment->getProductCode(),
            'Reference'                => $shipment->getReference(),
            'Groups'                   => array(
                                           'Group'   => $this->_getGroup(
                                                            $parcelCount, 
                                                            $mainBarcode, 
                                                            $shipmentNumber
                                                        ),
                                       ),
            'Contacts'                 => array(
                                           'Contact' => $this->_getContact($shippingAddress),
                                       ),
            'Dimension'                => array(
                                           'Weight'  => round($shipmentWeight),
                                       ),
            'Reference'                => $shipment->getIncrementId(),
        );
        
        /**
         * Add address data
         */
        $useSenderAddressAsReturn = Mage::getStoreConfig(self::XML_PATH_USE_SENDER_ADDRESS_AS_RETURN, $this->getStoreId());
        if ($useSenderAddressAsReturn) {
            $addresses = array(
                'Address' => $this->_getAddress('Receiver', $shippingAddress),
            );
        } else {
            $addresses = array(
                'Address' => array(
                    $this->_getAddress('Receiver', $shippingAddress),
                    $this->_getAddress('Alternative'),
                ),
            );
        }
        $shipmentData['Addresses'] = $addresses;
        
        /**
         * Add extra cover data
         */
        if ($postnlShipment->hasExtraCover() || $postnlShipment->isCod()) {
            $shipmentData['Amounts'] = $this->_getAmount($postnlShipment);
        }
        
        /**
         * Add customs data
         */
        if ($postnlShipment->isGlobalShipment()) {
            $shipmentData['Customs'] = $this->_getCustoms($shipment);
        }
        
        return $shipmentData;
    }
    
    /**
     * Gets an array containing required address data
     * 
     * @param string $shippingAddress
     * @param Mage_Sales_Model_Order_Address $address
     * 
     * @return array
     */
    protected function _getAddress($addressType, $shippingAddress= false)
    {
        $availableAddressTypes = $this->getAddressTypes();
        if (!array_key_exists($addressType, $availableAddressTypes)) {
            throw Mage::exception('TIG_PostNL', 'Invalid address type supplied: ' . $addressType);
        }
        
        /**
         * Determine which address to use. Currently only 'Sender' and 'Reciever' are fully supported.
         * Other possible address types will use the default 'reciever' address.
         */
        $streetData = false;
        switch ($addressType) {
            case 'Sender':
                /**
                 * Get all cif_sender_address fields as an array and convert that to a Varien_Object
                 * This allows the _prepareAddress method to access this data in the same way as a
                 * conventional Mage_Sales_Model_Order_Address object.
                 */
                $senderAddress = Mage::getStoreConfig(self::XML_PATH_SENDER_ADDRESS, $this->getStoreId());
                
                $streetData = array(
                    'streetname'           => $senderAddress['streetname'],
                    'housenumber'          => $senderAddress['housenumber'],
                    'housenumberExtension' => $senderAddress['housenumber_extension'],
                    'fullStreet'           => '',
                );
                
                $address = new Varien_Object($senderAddress);
                break;
            case 'Alternative':
                /**
                 * Check if the return address is the same as the sender address. If so, no address is returned
                 */
                $useSenderAddress = Mage::getStoreConfig(self::XML_PATH_USE_SENDER_ADDRESS_AS_RETURN, $this->getStoreId());
                if ($useSenderAddress) {
                    return false;
                }
                
                /**
                 * Get all cif_return_address fields as an array and convert that to a Varien_Object
                 * This allows the _prepareAddress method to access this data in the same way as a
                 * conventional Mage_Sales_Model_Order_Address object.
                 */
                $returnAddress = Mage::getStoreConfig(self::XML_PATH_RETURN_ADDRESS, $this->getStoreId());
                
                $streetData = array(
                    'streetname'           => $returnAddress['streetname'],
                    'housenumber'          => $returnAddress['housenumber'],
                    'housenumberExtension' => $returnAddress['housenumber_extension'],
                    'fullStreet'           => '',
                );
                
                $address = new Varien_Object($returnAddress);
                break;
            case 'Reciever': //no break
            default:
                $address = $shippingAddress;
                break;
        }
        
        $addressArray                = $this->_prepareAddressArray($address, $streetData);
        $addressArray['AddressType'] = $availableAddressTypes[$addressType];
        
        return $addressArray;
    }

    /**
     * Forms an array of address data compatible with CIF
     * 
     * @param Mage_Sales_Model_Order_Address $address
     * @param array | boolean $streetData Optional parameter containing streetname, housenr, housenr extension and fullStreet values.
     * 
     * @return array
     */
    protected function _prepareAddressArray($address, $streetData = false)
    {
        if ($streetData === false) {
            $streetData = $this->_getStreetData($address);
        }
        
        $addressArray = array(
            'FirstName'        => $address->getFirstname(),
            'Name'             => $address->getLastname(),
            'CompanyName'      => $address->getCompany(),
            'Street'           => $streetData['streetname'],
            'HouseNr'          => $streetData['housenumber'],
            'HouseNrExt'       => $streetData['housenumberExtension'],
            'StreetHouseNrExt' => $streetData['fullStreet'],
            'Zipcode'          => $address->getPostcode(),
            'City'             => $address->getCity(),
            'Region'           => $address->getRegion(),
            'Countrycode'      => $address->getCountry(),
            'Area'             => '',
            'Buildingname'     => '',
            'Department'       => '',
            'Doorcode'         => '',
            'Floor'            => '',
            'Remark'           => '',
        );
        
        return $addressArray;
    }
    
    /**
     * Gets data for the barcode that's requested. Depending on the destination of the shipment
     * several barcode types may be requested.
     * 
     * @param string $barcodeType
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _getBarcodeData($barcodeType)
    {
        switch ($barcodeType) {
            case 'NL':
                $type  = '3S';
                $range = $this->_getCustomerCode();
                if (strlen($range) > 3) {
                    $serie = self::NL_BARCODE_SERIE_SHORT;
                } else {
                    $serie = self::NL_BARCODE_SERIE_LONG;
                }
                break;
            case 'EU':
                $type  = '3S';
                $range = $this->_getCustomerCode();
                if (strlen($range) > 3) {
                    $serie = self::EU_BARCODE_SERIE_SHORT;
                } else {
                    $serie = self::EU_BARCODE_SERIE_LONG;
                }
                break;
            case 'GLOBAL':
                $type  = $this->_getGlobalBarcodeType();
                $range = $this->_getGlobalBarcodeRange();
                $serie = self::GLOBAL_BARCODE_SERIE;
                break;
            default:
                throw Mage::exception('TIG_PostNL', 'Invalid barcodetype requested: ' . $barcodeType);
        }

        $barcodeData = array(
            'type'  => $type,
            'range' => $range,
            'serie' => $serie,
        );
        
        return $barcodeData;
    }
    
    /**
     * Generates the CIF amount object containing the shipment's insured amount (if any)
     * 
     * @param TIG_PostNL_Model_Core_Shipment $shipment
     * 
     * @return array
     * 
     * @todo implement COD
     */
    protected function _getAmount($postnlShipment)
    {
        $amount = array();
        if (!$postnlShipment->hasExtraCover() && !$postnlShipment->isCod()) {
            return $amount;
        }
        
        if ($postnlShipment->hasExtraCover()) {
            $extraCover = number_format($postnlShipment->getExtraCoverAmount(), 2, '.', '');
            $amount[] = array(
                'AccountName'       => '',
                'AccountNr'         => '',
                'AmountType'        => '02', // 01 = COD, 02 = Insured
                'Currency'          => 'EUR',
                'Reference'         => '',
                'TransactionNumber' => '',
                'Value'             => $extraCover,
            );
        }
        
        if ($postnlShipment->isCod()) {
            //TODO implement COD here
        }
        
        return $amount;
    }
    
    /**
     * Creates the CIF contact object
     * 
     * @param Mage_Sales_Model_Order_Address $address
     * 
     * @return array
     * 
     * @todo check if SMSNr is required for pakjegemak
     */
    protected function _getContact($address)
    {
        $contact = array(
            'ContactType' => '01', // Receiver
            'Email'       => $address->getEmail(),
            'SMSNr'       => '', // never sure if clean 06 number - TODO: check needed for PakjeGemak?
            'TelNr'       => $address->getTelephone(),
        );
        
        return $contact;
    }
    
    /**
     * Creates the CIF group object
     * 
     * @return array
     */
    protected function _getGroup($groupCount = 1, $mainBarcode = false, $shipmentNumber = false)
    {
        /**
         * If $groupCount is 1, this is a single shipment (groupType 1)
         */
        if ($groupCount < 2) {
            $group =  array(
                'GroupType' => '01',
            );
            return $group;
        }
        
        $group = array(
            'GroupCount'    => $groupCount,
            'GroupSequence' => $shipmentNumber,
            'GroupType'     => '03',
            'MainBarcode'   => $mainBarcode,
        );
        
        return $group;
    }
    
    /**
     * Retrieves streetname, housenumber and housenumber extension from the shipping address.
     * The shipping address may be in multiple streetlines configuration or single line 
     * configuration. In the case of multi-line, each part of the street data will be in a seperate 
     * field. In the single line configuration, each part will be in the same field and will have 
     * to be split using PREG.
     * 
     * PREG cannot be relied on as it is impossible to create a regex that can filter all
     * possible street syntaxes. Therefore we strongly recommend to use multiple street lines. This
     * can be enabled in Magento communiy in system > config > customer configuration. Or if you 
     * use Enterprise, in customers > attributes > manage customer address attributes. 
     * 
     * @param Mage_Sales_Model_Order_Address $address
     * 
     * @return array
     */
    protected function _getStreetData($address)
    {
        $storeId = $this->getStoreId();
        $splitStreet = Mage::getStoreConfig(self::XML_PATH_SPLIT_STREET, $storeId);
        
        /**
         * Website uses multi-line address mode
         */
        if ($splitStreet) {
            $streetData = $this->_getMultiLineStreetData($address);
            
            /**
             * If $streetData is false it means a required field was missing. In this
             * case the alternative methods are used to obtain the address data.
             */
            if ($streetData !== false) {
                return $streetData;
            }
        }
        
        /**
         * Website uses single-line address mode
         */
        $allowedFullStreetCountries = $this->getAllowedFullStreetCountries();
        $fullStreet = $address->getStreetFull();
        
        /**
         * Select countries don't have to split their street values into seperate part
         */
        if (in_array($address->getCountry(), $allowedFullStreetCountries)) {
            $streetData = array(
                'streetname'           => '',
                'housenumber'          => '',
                'housenumberExtension' => '',
                'fullStreet'           => $fullStreet,
            );
            return $streetData;
        }
        
        /**
         * All other countries must split them using PREG
         */
        $streetData = $this->_getSplitStreetData($fullStreet);
        
        return $streetData;
    }
    
    /**
     * Retrieves streetname, housenumber and housenumber extension from the shipping address in the multiple streetlines configuration.
     * 
     * @param Mage_Sales_Model_Order_Address $address
     * 
     * @return array
     */
    protected function _getMultiLineStreetData($address)
    {
        $storeId = $this->getStoreId();
        $streetnameField = (int) Mage::getStoreConfig(self::XML_PATH_STREETNAME_FIELD, $storeId);
        $housenumberField = (int) Mage::getStoreConfig(self::XML_PATH_HOUSENUMBER_FIELD, $storeId);
        
        $streetname = $address->getStreet($streetnameField);
        $housenumber = $address->getStreet($housenumberField);
        
        /**
         * If street or housenr fields are empty, use alternative options to obtain the address data
         */
        if (is_null($streetname) || is_null($housenumber)) {
            return false;
        }
        
        /**
         * Split the housenumber into a number and an extension
         */
        $splitHouseNumber = Mage::getStoreConfig(self::XML_PATH_SPLIT_HOUSENUMBER, $storeId);
        if ($splitHouseNumber) {
            $housenumberExtensionField = (int) Mage::getStoreConfig(self::XML_PATH_HOUSENUMBER_EXTENSION_FIELD, $storeId);
            $housenumberExtension = $address->getStreet($housenumberExtensionField);
        } else {
            $housenumberParts = $this->_splitHousenumber($housenumber);
            $housenumber = $housenumberParts['number'];
            $housenumberExtension = $housenumberParts['extension'];
        }
        
        $streetData = array(
            'streetname'           => $streetname,
            'housenumber'          => $housenumber,
            'housenumberExtension' => $housenumberExtension,
            'fullStreet'           => '',
        );
        
        return $streetData;
    }
    
    /**
     * Splits street data into seperate parts for streetname, housenumber and extension.
     * 
     * @param string $fullStreet The full streetname including all parts
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _getSplitStreetData($fullStreet)
    {
        $result = preg_match(self::SPLIT_STREET_REGEX, $fullStreet, $matches);
        if (!$result || !is_array($matches)) {
            throw Mage::exception('TIG_PostNL', 'Invalid full street supplied: ' . $fullStreet);
        }
        
        $streetname = '';
        $housenumber = '';
        if (isset($matches[1])) {
            $streetname = $matches[1];
        }
        
        if (isset($matches[2])) {
            $housenumber = $matches[2];
        }
        
        $housenumberParts = $this->_splitHousenumber($housenumber);
        $housenumber = $housenumberParts['number'];
        $housenumberExtension = $housenumberParts['extension'];
        
        $streetData = array(
            'streetname'           => $streetname,
            'housenumber'          => $housenumber,
            'housenumberExtension' => $housenumberExtension,
            'fullStreet'           => '',
        );
        
        return $streetData;
    }
    
    /**
     * Splits a supplier housenumber into a number and an extension
     * 
     * @param string $housenumber
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _splitHousenumber($housenumber)
    {
        $result = preg_match(self::SPLIT_HOUSENUMBER_REGEX, $housenumber, $matches);
        if (!$result || !is_array($matches)) {
            throw Mage::exception('TIG_PostNL', 'Invalid housnumber supplied: ' . $housenumber);
        }
        
        $extension = '';
        $number = '';
        if (isset($matches[0])) {
            $number = $matches[0];
        }
        
        if (isset($matches[1])) {
            $extension = $matches[1];
        }
        
        $housenumberParts = array(
            'number' => $number,
            'extension' => $extension,
        );
        
        return $housenumberParts;
    }
    
    /**
     * create Customs CIF object
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return array
     */
    protected function _getCustoms($shipment)
    {
        $orderId = $shipment->getOrder()->getIncrementId();
        $customs = array(
            'ShipmentType'           => 'Commercial Goods', // Gift / Documents / Commercial Goods / Commercial Sample / Returned Goods
            'HandleAsNonDeliverable' => 'false',
            'Invoice'                => 'true',
            'InvoiceNr'              => $orderId,
            'Certificate'            => 'false',
            'License'                => 'false',
            'Currency'               => 'EUR',
        );
        
        /**
         * Add license info
         */
        if ($this->_getCustomsLicense()) {
            $customs['License'] = 'true';
            $customs['LicenseNr'] = $this->_getCustomsLicense();
        }
        
        /**
         * Add certificate info
         */
        if ($this->_getCustomsCertificate()) {
            $customs['Certificate'] = 'true';
            $customs['CertificateNr'] = $this->_getCustomsCertificate();
        }
        
        /**
         * Add information about the contents of the shipment
         */
        $itemCount = 0;
        $content = array();
        $items = $this->_sortCustomsItems($shipment->getAllItems());
        
        foreach ($items as $item) {
            /**
             * A maximum of 5 rows are allowed
             */
            if (++$itemCount > 5) {
                break;
            }
            
            $itemWeight = Mage::helper('postnl/cif')->standardizeWeight(
                $item->getWeight(), 
                $this->getStoreId()
            );
            
            /**
             * Item weight may not be less than 1 gram
             */
            if ($itemWeight < 0.01) {
                $itemWeight = 0.01;
            }
            
            $itemData = array(
                'Description'     => $this->_getCustomsDescription($item),
                'Quantity'        => $item->getQty(),
                'Weight'          => $itemWeight,
                'Value'           => ($this->_getCustomsValue($item) * $item->getQty()),
                'HSTariffNr'      => $this->_getHSTariff($item),
                'CountryOfOrigin' => $this->_getCountryOfOrigin($item),
            );
            
            $content[] = $itemData;
        }
        
        /**
         * If no information was present, supply an array of empty lines instead
         */
        if (empty($content)) {
            $content = array(
                array(
                    'Description'     => '',
                    'Quantity'        => '',
                    'Weight'          => '',
                    'Value'           => '',
                    'HSTariffNr'      => '',
                    'CountryOfOrigin' => '',
                ),
            );
        }
        
        $customs['Content'] = $content;
        
        return $customs;
    }
    
    /**
     * Sorts an array of shipment items based on a product attribute that is defined in the module configuration
     * 
     * @param array $items
     * 
     * @return array
     */
    protected function _sortCustomsItems($items)
    {
        /**
         * Get the attribute and direction used for sorting
         */
        $sortingAttribute = Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_PRODUCT_SORTING_ATTRIBUTE, $this->getStoreId());
        $sortingDirection = Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_PRODUCT_SORTING_DIRECTION, $this->getStoreId());
        
        /**
         * Place the item's sorting value in a temporary array where the key is the item's ID
         */
        $sortingValue = array();
        foreach ($items as $item) {
            $product = $item->getOrderItem()->getProduct();
            $sortingAttributeValue = $product->getDataUsingMethod($sortingAttribute);
            $sortedItems[$item->getId()] = $sortingAttributeValue;
        }
        
        /**
         * Sort the array in the specified direction using 'natural' sorting
         * 
         * @link http://us1.php.net/manual/en/function.natsort.php
         */
        natsort($sortedItems);
        if ($sortingDirection == 'desc') {
            $sortedItems = array_reverse($sortedItems, true); //keep key-value associations
        }
        
        /**
         * Switch the sorting values with the items
         */
        foreach ($items as $item) {
            $sortedItems[$item->getId()] = $item;
        }
        
        return $sortedItems;
    }
    
    /**
     * Get a shipment item's HS tariff
     * 
     * @param Mage_Sales_Model_Order_Shipment_item
     * 
     * @return string
     */
    protected function _getHSTariff($shipmentItem)
    {
        $storeId = $this->getStoreId();
        
        /**
         * HS Tariff is an optional attribute. Check if it's used and if not, return a default value of 000000
         */
        if (!Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_USE_HS_TARIFF_ATTRIBUTE, $storeId)) {
            return '000000';
        }
        
        if ($this->getHSTariffAttribute() !== null) {
            $hsTariffAttribute = $this->getHSTariffAttribute();
        } else {
            $hsTariffAttribute = Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_HS_TARIFF_ATTRIBUTE, $storeId);
            $this->setHSTariffAttribute($hsTariffAttribute);
        }
        
        $hsTariff = $shipmentItem->getOrderItem()
                                 ->getProduct()
                                 ->getDataUsingMethod($hsTariffAttribute);
                                    
        return $customsValueAttribute;
    }
    
    /**
     * Get a shipment item's country of origin
     * 
     * @param Mage_Sales_Model_Order_Shipment_item
     * 
     * @return string
     */
    protected function _getCountryOfOrigin($shipmentItem)
    {
        $storeId = $this->getStoreId();
        if ($this->getCountryOfOriginAttribute() !== null) {
            $countryOfOriginAttribute = $this->getCountryOfOriginAttribute();
        } else {
            $countryOfOriginAttribute = Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_COUNTRY_OF_ORIGIN_ATTRIBUTE, $storeId);
            $this->setCountryOfOriginAttribute($countryOfOriginAttribute);
        }
        
        $countryOfOrigin = $shipmentItem->getOrderItem()
                                        ->getProduct()
                                        ->getDataUsingMethod($countryOfOriginAttribute);
                                    
        return $countryOfOrigin;
    }
    
    /**
     * Get a shipment item's customs value
     * 
     * @param Mage_Sales_Model_Order_Shipment_item
     * 
     * @return string
     */
    protected function _getCustomsValue($shipmentItem)
    {
        $storeId = $this->getStoreId();
        if ($this->getCustomsValueAttribute() !== null) {
            $customsValueAttribute = $this->getCustomsValueAttribute();
        } else {
            $customsValueAttribute = Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_CUSTOMS_VALUE_ATTRIBUTE, $storeId);
            $this->setCustomsValueAttribute($customsValueAttribute);
        }
        
        $customsValue = $shipmentItem->getOrderItem()
                                     ->getProduct()
                                     ->getDataUsingMethod($customsValueAttribute);
        
        return $customsValue;
    }
    
    /**
     * Get a shipment item's customs description
     * 
     * @param Mage_Sales_Model_Order_Shipment_item
     * 
     * @return string
     */
    protected function _getCustomsDescription($shipmentItem)
    {
        $storeId = $this->getStoreId();
        if ($this->getCustomsDescriptionAttribute() !== null) {
            $descriptionAttribute = $this->getCustomsDescriptionAttribute();
        } else {
            $descriptionAttribute = Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_DESCRIPTION_ATTRIBUTE, $storeId);
            $this->setCustomsDescriptionAttribute($descriptionAttribute);
        }
        
        $description = $shipmentItem->getOrderItem()
                                    ->getProduct()
                                    ->getDataUsingMethod($descriptionAttribute);
                                    
        return $description;
    }
    
    /**
     * Gets the customer code from system/config
     * 
     * @return string
     */
    protected function _getCustomerCode()
    {
        $storeId = $this->getStoreId();
        $customerCode = (string) Mage::getStoreConfig(self::XML_PATH_CUSTOMER_CODE, $storeId);
        
        return $customerCode;
    }
    
    /**
     * Gets the customer number from system/config
     * 
     * @return string
     */
    protected function _getCustomerNumber()
    {
        $storeId = $this->getStoreId();
        $customerNumber = (string) Mage::getStoreConfig(self::XML_PATH_CUSTOMER_NUMBER, $storeId);
        
        return $customerNumber;
    }
    
    /**
     * Gets the company name from system/config
     * 
     * @return string
     */
    protected function _getCompanyName()
    {
        $storeId = $this->getStoreId();
        $companyName = (string) Mage::getStoreConfig(self::XML_PATH_COMPANY_NAME, $storeId);
        
        return $companyName;
    }
    
    /**
     * Gets the contact name from system/config
     * 
     * @return string
     */
    protected function _getContactName()
    {
        $storeId = $this->getStoreId();
        $contactName = (string) Mage::getStoreConfig(self::XML_PATH_CONTACT_NAME, $storeId);
        
        return $contactName;
    }
    
    /**
     * Gets the contact email address from system/config
     * 
     * @return string
     */
    protected function _getContactEmail()
    {
        $storeId = $this->getStoreId();
        $contactEmail = (string) Mage::getStoreConfig(self::XML_PATH_CONTACT_EMAIL, $storeId);
        
        return $contactEmail;
    }
    
    /**
     * Gets the collection location from system/config
     * 
     * @return string
     */
    protected function _getCollectionLocation()
    {
        $storeId = $this->getStoreId();
        $collectionLocation = (string) Mage::getStoreConfig(self::XML_PATH_COLLECTION_LOCATION, $storeId);
        
        return $collectionLocation;
    }
    
    /**
     * Gets the global barcode type from system/config
     * 
     * @return string
     */
    protected function _getGlobalBarcodeType()
    {
        $storeId = $this->getStoreId();
        $barcodeType = (string) Mage::getStoreConfig(self::XML_PATH_GLOBAL_BARCODE_TYPE, $storeId);
        
        return $barcodeType;
    }
    
    /**
     * Gets the global barcode range from system/config
     * 
     * @return string
     */
    protected function _getGlobalBarcodeRange()
    {
        $storeId = $this->getStoreId();
        $barcodeRange = (string) Mage::getStoreConfig(self::XML_PATH_GLOBAL_BARCODE_RANGE, $storeId);
        
        return $barcodeRange;
    }
    
    /**
     * Gets the customs license from system/config
     * 
     * @return string
     */
    protected function _getCustomsLicense()
    {
        $storeId = $this->getStoreId();
        $customsLicense = (string) Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_CUSTOMS_LICENSE_NUMBER, $storeId);
        
        if (empty($customsLicense)) {
            return false;
        }
        
        return $customsLicense;
    }
    
    /**
     * Gets the customs certificate from system/config
     * 
     * @return string
     */
    protected function _getCustomsCertificate()
    {
        $storeId = $this->getStoreId();
        $customsCertificate = (string) Mage::getStoreConfig(self::XML_PATH_GLOBALPACK_CUSTOMS_CERTIFICATE_NUMBER, $storeId);
        
        if (empty($customsCertificate)) {
            return false;
        }
        
        return $customsCertificate;
    }
}