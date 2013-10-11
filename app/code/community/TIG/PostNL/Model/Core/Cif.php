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
 */
class TIG_PostNL_Model_Core_Cif extends TIG_PostNL_Model_Core_Cif_Abstract
{
    const XML_PATH_CUSTOMER_CODE       = 'postnl/cif/customer_code';
    const XML_PATH_CUSTOMER_NUMBER     = 'postnl/cif/customer_number';
    const XML_PATH_COMPANY_NAME        = 'postnl/cif/company_name';
    const XML_PATH_CONTACT_NAME        = 'postnl/cif/contact_name';
    const XML_PATH_CONTACT_EMAIL       = 'postnl/cif/contact_email';
    const XML_PATH_COLLECTION_LOCATION = 'postnl/cif/collection_location';
    
    /**
     * array containing various barcode types.
     * 
     * Types are as follows:
     * NL: dutch addresses
     * EU: european addresses
     * CD: global addresses
     * 
     * @var array
     */
    protected $_barcodeTypes = array(
        //dutch address
        'NL' => array(
                    'type'  => '3S', 
                    'range' => 'TOTA', 
                    'serie' => '000000000-999999999',
                ),
        // european address
        'EU' => array( 
                    'type'  => '3S', 
                    'range' => '', 
                    'serie' => '0000000-9999999',
                ),
        //global address
        'CD' => array(
                    'type'  => 'CD', 
                    'range' => '', 
                    'serie' => '0000-9999',
                ),
    );
    
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
        'Alternative' => '08', // alternative sender
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
    
    public function getBarcodeTypes()
    {
        return $this->_barcodeTypes;
    }
    
    public function getAddressTypes()
    {
        return $this->_addressTypes;
    }
    
    public function getPrinterTypes()
    {
        return $this->_printerTypes;
    }
    
    public function getStoreId()
    {
        if ($this->getData('store_id')) {
            return $this->getData('store_id');
        }

        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        
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
        
        $availableBarcodeTypes = self::getBarcodeTypes();
        if(!array_key_exists($barcodeType, $availableBarcodeTypes)) {
            throw Mage::exception('TIG_PostNL', 'Invalid barcode type requested: ' . $barcodeType);
        }
        
        $barcode = $availableBarcodeTypes[$barcodeType];
        
        $message = $this->_getMessage();
        $customer = $this->_getCustomer();
        $type = $barcode['type'];
        $range = $barcode['range'];
        $serie = $barcode['serie'];
        
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
        
        if (!is_object($response) || !isset($response->Barcode)) {
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
    public function getShipmentStatus($barcode)
    {
        if (!$barcode) {
            throw Mage::exception('TIG_PostNL', 'No barcode supplied for ShippingStatus soap call');
        }
        
        $message = $this->_getMessage();
        $customer = $this_>_getCustomer();
        
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
        
        if (!isset($response->Shipments) || !is_array($response->Shipments)) {
            throw Mage::exception('TIG_PostNL', 'Invalid shippingStatus response: ' . "\n" . var_export($reponse, true));
        }
        
        foreach($response->Shipments as $shipment) {
            if($shipment->Barcode === $barcode) { // we need the original shipment, not a related shipment (such as a return shipment)
                return $shipment;
            }
        }
        
        // no shipment could be matched to the supplied barcode
        throw Mage::exception('TIG_PostNL', 'Unable to match barcode to shippingStatus response: ' . "\n" . var_export($reponse, true));
    }
    
    /**
     * @TODO: implement this method
     */
    public function sendConfirmation($shipment)
    {
        throw new Exception("Error: PostNL Confirming method not implemented");
        /*
        $response = $this->_soapCall('Confirming', 'Confirming', array(
            'Message'   => $this->_getMessage(),
            'Customer'  => $this->_getCustomer(true),
            'Shipments' => array(
                'Shipment' => $this->_getShipment($shipment),
            ),
        ));
        throw new Exception("PostNL error: no confirmation success for shipment '" . $shipment->getBarcode() . "'");
        */
    }
    
    /**
     * Generates shipping labels for the chosen shipment
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $printerType
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    public function generateLabels($shipment, $printerType = 'GraphicFile|PDF')
    {
        $availablePrinterTypes = $this->_printerTypes;
        if (!in_array($printerType, $availablePrinterTypes)) {
            throw Mage::exception('TIG_PostNL', 'Invalid printer type requested: ' . $printerType);
        }
        
        $message     = $this->_getMessage(array('Printertype' => $printerType));
        $customer    = $this->_getCustomer();
        $cifShipment = $this->_getShipment($shipment);
        
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
        
        if (!isset($response->Labels) || !is_object($response->Labels)) {
            throw Mage::exception('TIG_PostNL', 'Invalid generateLabels response: ' . "\n" . var_export($reponse, true));
        }
        
        $labels = $response->Labels->Label;
        if (!is_array($labels)) {
            $labels = array($labels);
        }
        
        return $labels;
    }
    
    /**
     * Gets the Message parameter
     * 
     * @param array $extra An array of additional parameters to add
     * 
     * @return array
     */
    protected function _getMessage($extra = array())
    {
        $time = Mage::getModel('core/date')->timestamp();
        $message = array(
            'MessageID'        => $time, // TODO: improve to something unique
            'MessageTimeStamp' => date('d-m-Y H:i:s', $time),
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
                'Address'            => $this->_getAddress('Sender', $shipment->getShippingAddress()),
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
     * Gets an array containing required address data
     * 
     * @param string $addressType
     * @param Mage_Sales_Model_Order_Address $address
     * 
     * @return array
     */
    protected function _getAddress($addressType, $address)
    {
        $availableAddressTypes = $this->getAddressTypes();
        if (!array_key_exists($addressType, $availableAddressTypes)) {
            throw Exception('TIG_PostNL', 'Invalid address type supplied: ' . $addressType);
        }
        
        $addressType  = array(
            'AddressType' => $availableAddressTypes[$addressType]
        );
        
        $addressArray = $this->_prepareAddressArray($address);

        $addressArray =  array_merge($addressType, $addressArray);
        
        return $addressArray;
    }

    /**
     * Forms an array of address data compatible with CIF
     * 
     * @param Mage_Sales_Model_Order_Address $address
     * 
     * @return array
     */
    protected function _prepareAddressArray($address)
    {
        $addressArray = array(
            'Area'             => $address->getArea(),
            'Buildingname'     => $address->getBuilding(),
            'City'             => $address->getCity(),
            'CompanyName'      => $address->getCompany(),
            'Countrycode'      => $address->getCountry(),
            'Department'       => $address->getDepartment(),
            'Doorcode'         => $address->getDoorcode(),
            'FirstName'        => $address->getFirstName(),
            'Floor'            => $address->getFloor(),
            'HouseNr'          => $address->getHouseNr(),
            'HouseNrExt'       => $address->getHouseNrExt(),
            'Name'             => $address->getName(),
            'Region'           => $address->getRegion(),
            'Remark'           => $address->getRemark(),
            'Street'           => $address->getStreet(),
            'Zipcode'          => $address->getZipcode(),
            'StreetHouseNrExt' => $address->getStreetHouseNrExt(),
        );
        
        return $addressArray;
    }

    protected function _getAmount($shipment)
    {
        if($insuredAmount = $shipment->insuredAmount())
        {
            return array(
                'AccountName'       => '',
                'AccountNr'         => '',
                'AmountType'        => '02', // 01 = COD, 02 = Insured
                'Currency'          => 'EUR',
                'Reference'         => '',
                'TransactionNumber' => '',
                'Value'             => $insuredAmount,
            );
        }
        return array();
    }

    protected function _getCustoms($shipment)
    {
        $invoiceNumber = $shipment->customs_invoice;
        $res = array(
            'ShipmentType'           => $shipment->customs_shipment_type, // Gift / Documents / Commercial Goods / Commercial Sample / Returned Goods
            'HandleAsNonDeliverable' => 'False',
            'Invoice'                => empty($invoiceNumber) ? 'False' : 'True',
            'InvoiceNr'              => empty($invoiceNumber) ? '' : $invoiceNumber,
            'Certificate'            => 'False',
            'License'                => 'False',
            'Currency'               => 'EUR',
            'Content' => array(
                0 => array(
                    'Description'     => '...',
                    'Quantity'        => '...',
                    'Weight'          => '...',
                    'Value'           => $shipment->customs_value,
                    'HSTariffNr'      => '...',
                    'CountryOfOrigin' => '...',
                ),
            ),
        );
        return $res;
    }

    protected function _getContact($shipment)
    {
        $res = array(
            'ContactType' => '01', // Receiver
            'Email'       => $shipment->email,
            'SMSNr'       => '', // never sure if clean 06 number - TODO: check needed for PakjeGemak?
            'TelNr'       => $shipment->phone_number,
        );
        if(empty($res['Email']) && empty($res['SMSNr']) && empty($res['TelNr']))
        {
            // avoid empty contact errors
            $res['Email'] = $this->_customerEmail;
        }
        return $res;
    }

    protected function _getGroup($shipment)
    {
        // NOTE: extra fields can be used to group multi collo shipments (GroupType 03)
        return array(
            'GroupType' => '01',
        );
    }

    protected function _getShipment($shipment)
    {
        $res = array(
            'Addresses' => array(
                'Address' => $this->_getAddress('Receiver', $shipment),
            ),
            'Amounts' => array(
                'Amount' => $this->_getAmount($shipment),
            ),
            'Barcode' => $shipment->getBarcode(),
            'CollectionTimeStampEnd'   => '',
            'CollectionTimeStampStart' => '',
            'Contacts' => array(
                'Contact' => $this->_getContact($shipment),
            ),
            'Dimension' => array(
                'Weight' => $shipment->getWeight(),
            ),
            'DownPartnerBarcode' => '',
            'DownPartnerID'      => '',
            'Groups' => array(
                'Group' => $this->_getGroup($shipment),
            ),
            'ProductCodeDelivery' => $shipment->getProductCode(),
            'Reference'           => $shipment->getReference(),
        );
        if($shipment->isPakjeGemak())
        {
            // we do not save a separate PakjeGemak address, so duplicate and filter it
            $res['Addresses']['Address'] = array(
                0 => $this->_getAddress('Receiver', $shipment),
                1 => $this->_getAddress('Delivery', $shipment),
            );
            $res['Addresses']['Address'][0]['CompanyName'] = '';
            $res['Addresses']['Address'][1]['Name'] = '';

            $res['Contacts']['Contact']['SMSNr'] = $shipment->phone_number;
        }
        if($shipment->isCD())
        {
            $res['Customs'] = $this->_getCustoms($shipment);
        }
        return $res;
    }
    
    /**
     * Gets the customer code from system/config
     * 
     * @return string
     */
    protected function _getCustomerCode()
    {
        $storeId = $this->getStoreId();
        $customerCode = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_CODE, $storeId);
        
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
        $customerNumber = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_NUMBER, $storeId);
        
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
        $companyName = Mage::getStoreConfig(self::XML_PATH_COMPANY_NAME, $storeId);
        
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
        $contactName = Mage::getStoreConfig(self::XML_PATH_CONTACT_NAME, $storeId);
        
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
        $contactEmail = Mage::getStoreConfig(self::XML_PATH_CONTACT_EMAIL, $storeId);
        
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
        $collectionLocation = Mage::getStoreConfig(self::XML_PATH_COLLECTION_LOCATION, $storeId);
        
        return $collectionLocation;
    }
}
