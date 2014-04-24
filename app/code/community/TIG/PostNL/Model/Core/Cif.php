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
    /**
     * Constants containing xml paths to cif configuration options
     */
    const XML_PATH_CUSTOMER_CODE               = 'postnl/cif/customer_code';
    const XML_PATH_CUSTOMER_NUMBER             = 'postnl/cif/customer_number';
    const XML_PATH_COLLECTION_LOCATION         = 'postnl/cif/collection_location';
    const XML_PATH_GLOBAL_BARCODE_TYPE         = 'postnl/cif/global_barcode_type';
    const XML_PATH_GLOBAL_BARCODE_RANGE        = 'postnl/cif/global_barcode_range';

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
     * XML paths for shipment reference info
     */
    const XML_PATH_SHIPMENT_REFERENCE_TYPE   = 'postnl/cif_labels_and_confirming/shipment_reference_type';
    const XML_PATH_CUSTOM_SHIPMENT_REFERENCE = 'postnl/cif_labels_and_confirming/custom_shipment_reference';

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
        //Graphic files
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

    /**
     * These shipment types require an invoice in the customs declaration. Other possible shipment types are:
     * - Gift
     * - Documents
     *
     * @var array
     */
    protected $_invoiceRequiredShipmentTypes = array(
        'Commercial Goods',
        'Commercial Sample',
        'Returned Goods',
    );

    /**
     * Get possible address types
     *
     * @return array
     */
    public function getAddressTypes()
    {
        return $this->_addressTypes;
    }

    /**
     * Get possible printer types
     *
     * @return array
     */
    public function getPrinterTypes()
    {
        return $this->_printerTypes;
    }

    /**
     * Get country IDs that allow fullstreet usage
     *
     * @return array
     */
    public function getAllowedFullStreetCountries()
    {
        return $this->_allowedFullStreetCountries;
    }

    /**
     * Get shipment types that require an invoice number
     *
     * @return array
     */
    public function getInvoiceRequiredShipmentTypes()
    {
        return $this->_invoiceRequiredShipmentTypes;
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
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid barcode response: %s', "\n" . var_export($response, true)),
                'POSTNL-0054'
            );
        }

        return $response->Barcode;
    }

    /**
     * Requests a new barcode from CIF as a ping request. This can be used to validate account settings or to check if the CIF
     * service is up and running. This is not meant to be used to generate an actual barcode for a shipment. Use the
     * generateBarcode method for that.
     *
     * The generateBarcode CIF call was chosena s it is the simplest CIF function available.
     *
     * @param array $data Array containing all data required for the request.
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function generateBarcodePing($data)
    {
        $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);

        $barcode = $this->_getBarcodeData('NL');

        $message  = $this->_getMessage('');
        $range    = $data['customerCode'];
        $type     = $barcode['type'];
        $serie    = $barcode['serie'];

        $customer = array(
            'CustomerCode'       => $data['customerCode'],
            'CustomerNumber'     => $data['customerNumber'],
            'CollectionLocation' => $data['locationCode'],
        );

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
            $soapParams,
            $data['username'],
            $data['password']
        );

        if (!is_object($response)
            || !isset($response->Barcode)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid barcode response: %s', "\n" . var_export($response, true)),
                'POSTNL-0054'
            );
        }

        return $response->Barcode;
    }

    /**
     * Retrieves the latest shipping status of a shipment from CIF
     *
     * @param TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return StdClass
     *
     * @throws TIG_PostNL_Exception
     */
    public function getShipmentStatus($postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();

        $barcode  = $postnlShipment->getMainBarcode();
        $message  = $this->_getMessage($barcode);
        $customer = $this->_getCustomer();

        $soapParams = array(
            'Message'  => $message,
            'Customer' => $customer,
            'Shipment' => array(
                'Barcode'   => $barcode,
            ),
        );

        $response = $this->call(
            'ShippingStatus',
            'CurrentStatus',
            $soapParams
        );

        if (!is_object($response)
            || !isset($response->Shipments)
            || (!is_array($response->Shipments) && !is_object($response->Shipments))
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid shippingStatus response: %s', "\n" . var_export($response, true)),
                'POSTNL-0055'
            );
        }

        foreach($response->Shipments as $shipment) {
            /**
             * If $shipment is an array, we need the first item
             */
            if (is_array($shipment)) {
                $shipment = $shipment[0];
            }

            if ($shipment->Barcode === $barcode) { // we need the original shipment, not a related shipment (such as a return shipment)
                return $shipment;
            }
        }

        /**
         * no shipment could be matched to the supplied barcode
         */
        throw new TIG_PostNL_Exception(
            Mage::helper('postnl')->__( 'Unable to match barcode to shippingStatus response: %s', var_export($response, true)),
            'POSTNL-0063'
        );
    }

    /**
     * Retrieves the latest shipping status of a shipment from CIF including full status history
     *
     * @param TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return StdClass
     *
     * @throws TIG_PostNL_Exception
     */
    public function getCompleteShipmentStatus($postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();

        $barcode  = $postnlShipment->getMainBarcode();
        $message  = $this->_getMessage($barcode);
        $customer = $this->_getCustomer();

        $soapParams = array(
            'Message'  => $message,
            'Customer' => $customer,
            'Shipment' => array(
                'Barcode'   => $barcode,
            ),
        );

        $response = $this->call(
            'ShippingStatus',
            'CurrentStatus',
            $soapParams
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
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid shippingStatus response: %s', "\n" . var_export($response, true)),
                'POSTNL-0055'
            );
        }

        foreach($response->Shipments as $shipment) {
            $shipment = $shipment[0];
            if ($shipment->Barcode === $barcode) { // we need the original shipment, not a related shipment (such as a return shipment)
                return $shipment;
            }
        }

        /**
         * no shipment could be matched to the supplied barcode
         */
        throw new TIG_PostNL_Exception(
            Mage::helper('postnl')->__( 'Unable to match barcode to shippingStatus response: %s', var_export($response, true)),
            'POSTNL-0063'
        );
    }

    /**
     * Confirms the choen shipment without generating labels
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $barcode
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    public function confirmShipment($postnlShipment, $barcode, $mainBarcode = false, $shipmentNumber = false)
    {
        $shipment = $postnlShipment->getShipment();

        $message     = $this->_getMessage($barcode);
        $customer    = $this->_getCustomer($shipment);

        /**
         * Create a single shipment object
         */
        if ($mainBarcode === false || $shipmentNumber === false) {
            $cifShipment = array(
                'Shipment' => $this->_getShipment($postnlShipment, $barcode)
            );
        } else {
            $cifShipment = array(
                'Shipment' => $this->_getShipment($postnlShipment, $barcode, $mainBarcode,$shipmentNumber)
            );
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
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid confirmShipment response: %s', var_export($response, true)),
                'POSTNL-0056'
            );
        }

        if (isset($response->ConfirmingResponseShipment)
            && (is_object($response->ConfirmingResponseShipment)
                || is_array($response->ConfirmingResponseShipment)
            )
        ) {
            return $response;
        }

        throw new TIG_PostNL_Exception(
            Mage::helper('postnl')->__('Invalid confirmShipment response: %s', var_export($response, true)),
            'POSTNL-0056'
        );
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
    public function generateLabels($postnlShipment, $barcode, $mainBarcode = false, $shipmentNumber = false, $printerType = 'GraphicFile|PDF')
    {
        $shipment = $postnlShipment->getShipment();

        $availablePrinterTypes = $this->_printerTypes;
        if (!in_array($printerType, $availablePrinterTypes)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid printer type requested: %s', $printerType),
                'POSTNL-0062'
            );
        }

        /**
         * Create a single shipment object
         */
        if ($mainBarcode === false || $shipmentNumber === false) {
            $cifShipment = $this->_getShipment($postnlShipment, $barcode);
        } else {
            $cifShipment = $this->_getShipment($postnlShipment, $barcode, $mainBarcode, $shipmentNumber);
        }

        $message  = $this->_getMessage($barcode, array('Printertype' => $printerType));
        $customer = $this->_getCustomer($shipment);

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
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid generateLabels response: %s', var_export($response, true)),
                'POSTNL-0057'
            );
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
    public function generateLabelsWithoutConfirm($postnlShipment, $barcode, $mainBarcode = false, $shipmentNumber = false, $printerType = 'GraphicFile|PDF')
    {
        $shipment = $postnlShipment->getShipment();

        $availablePrinterTypes = $this->_printerTypes;
        if (!in_array($printerType, $availablePrinterTypes)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid printer type requested: %s', $printerType),
                'POSTNL-0062'
            );
        }

        /**
         * Create a single shipment object
         */
        if ($mainBarcode === false || $shipmentNumber === false) {
            $cifShipment = $this->_getShipment($postnlShipment, $barcode);
        } else {
            $cifShipment = $this->_getShipment($postnlShipment, $barcode, $mainBarcode, $shipmentNumber);
        }

        $message     = $this->_getMessage($barcode, array('Printertype' => $printerType));
        $customer    = $this->_getCustomer($shipment);

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
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid generateLabelsWithoutConfirm response: %s', var_export($response, true)),
                'POSTNL-0058'
            );
        }

        return $response;
    }

    /**
     * Gets the Message parameter
     *
     * @param array $extra An array of additional parameters to add
     *
     * @return array
     */
    protected function _getMessage($barcode, $extra = array())
    {
        $messageIdString = uniqid('postnl_')
                         . $this->_getCustomerNumber()
                         . $barcode
                         . microtime();

        $message = array(
            'MessageID'        => md5($messageIdString),
            'MessageTimeStamp' => date('d-m-Y H:i:s', Mage::getModel('core/date')->gmtTimestamp()),
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
            );

            $customer = array_merge($customer, $additionalCustomerData);
        }

        return $customer;
    }

    /**
     * Creates the CIF shipment object based on a PostNL shipment
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     * @param string                         $barcode
     * @param bool                           $mainBarcode
     * @param bool                           $shipmentNumber
     *
     * @return array
     *
     * @todo     modify to support OVM and PostNL checkout shipments
     */
    protected function _getShipment($postnlShipment, $barcode, $mainBarcode = false, $shipmentNumber = false)
    {
        $shipment        = $postnlShipment->getShipment();
        $order           = $shipment->getOrder();
        $shippingAddress = $shipment->getShippingAddress();

        $parcelCount = $postnlShipment->getParcelCount();
        $shipmentWeight = $postnlShipment->getTotalWeight(true, true);

        /**
         * If a shipmentNumber is provided it means that this is not a single shipment and the weight of the shipment
         * needs to be calculated
         */
        if ($shipmentNumber !== false) {
            $parcelWeight = Mage::getStoreConfig(self::XML_PATH_WEIGHT_PER_PARCEL, $postnlShipment->getStoreId());
            $parcelWeight = Mage::helper('postnl/cif')->standardizeWeight($parcelWeight, $shipment->getStoreId(), true); //convert the parcelweight to grams

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
            'Contacts'                 => array(
                                           'Contact' => $this->_getContact($shippingAddress, $order),
                                       ),
            'Dimension'                => array(
                                           'Weight'  => round($shipmentWeight),
                                       ),
            'Reference'                => $this->_getReference($shipment),
        );

        /**
         * Add group data (for multi-collo shipments)
         */
        if ($parcelCount > 1) {
            $groups = array(
                'Group' => $this->_getGroup(
                               $parcelCount,
                               $mainBarcode,
                               $shipmentNumber
                           ),
            );

            $shipmentData['Groups'] = $groups;
        }

        /**
         * Add address data
         */
        $useSenderAddressAsReturn = Mage::getStoreConfig(self::XML_PATH_USE_SENDER_ADDRESS_AS_RETURN, $this->getStoreId());
        $pakjeGemakAddress = $postnlShipment->getPakjeGemakAddress();

        $addresses = array(
            'Address' => array(
                $this->_getAddress('Receiver', $shippingAddress)
             ),
        );

        if (!$useSenderAddressAsReturn) {
            $addresses['Address'][] = $this->_getAddress('Alternative');
        }

        if ($pakjeGemakAddress) {
            $addresses['Address'][] =$this->_getAddress('Delivery', $pakjeGemakAddress);
        }

        $shipmentData['Addresses'] = $addresses;

        /**
         * Add extra cover data and COD data
         * In the case of a multi-collo shipment this is only added to the first parcel
         */
        if (($shipmentNumber === false || $shipmentNumber == 1)
            && ($postnlShipment->hasExtraCover() || $postnlShipment->isCod())
        ) {
            $shipmentData['Amounts'] = $this->_getAmount($postnlShipment);
        }

        /**
         * Add customs data
         */
        if ($postnlShipment->isGlobalShipment()) {
            $shipmentData['Customs'] = $this->_getCustoms($postnlShipment);
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
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid address type supplied: %s', $addressType),
                'POSTNL-0108'
            );
        }

        /**
         * Determine which address to use. Currently only 'Sender' and 'Receiver' are fully supported.
         * Other possible address types will use the default 'receiver' address.
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
            case 'PakjeGemak': //no break
            case 'Receiver': //no break
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
            'Area'             => $this->_getArea($address),
            'Buildingname'     => $this->_getBuildingName($address),
            'Department'       => $this->_getDepartment($address),
            'Doorcode'         => $this->_getDoorcode($address),
            'Floor'            => $this->_getFloor($address),
            'Remark'           => $this->_getRemark($address),
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
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Invalid barcodetype requested: %s', $barcodeType),
                    'POSTNL-0061'
                );
        }

        if (!$type || !$range) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to retrieve barcode data.'),
                'POSTNL-0111'
            );
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

        if ($postnlShipment->hasExtraCover() && $postnlShipment->getExtraCoverAmount() > 0) {
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
     * @param Mage_Sales_Model_Order         $order
     *
     * @return array
     *
     * @todo check if SMSNr is required for pakjegemak
     */
    protected function _getContact($address, Mage_Sales_Model_Order $order)
    {
        $contact = array(
            'ContactType' => '01', // Receiver
            'Email'       => $order->getCustomerEmail(),
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
     * can be enabled in Magento community in system > config > customer configuration. Or if you
     * use Enterprise, in customers > attributes > manage customer address attributes.
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return array
     */
    protected function _getStreetData($address)
    {
        $storeId = $this->getStoreId();
        $splitStreet = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_STREET, $storeId);

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
        $splitHouseNumber = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_HOUSENUMBER, $storeId);
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
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid full street supplied: %s', $fullStreet),
                'POSTNL-0060'
            );
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
        $housenumber = trim($housenumber);

        $result = preg_match(self::SPLIT_HOUSENUMBER_REGEX, $housenumber, $matches);
        if (!$result || !is_array($matches)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid housnumber supplied: %s', $housenumber),
                'POSTNL-0059'
            );
        }

        $extension = '';
        $number = '';
        if (isset($matches[1])) {
            $number = $matches[1];
        }

        if (isset($matches[2])) {
            $extension = trim($matches[2]);
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
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return array
     */
    protected function _getCustoms($postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();
        $shipmentType = $postnlShipment->getShipmentType();

        $customs = array(
            'ShipmentType'           => $shipmentType, // Gift / Documents / Commercial Goods / Commercial Sample / Returned Goods
            'HandleAsNonDeliverable' => 'false',
            'Invoice'                => 'false',
            'Certificate'            => 'false',
            'License'                => 'false',
            'Currency'               => 'EUR',
        );

        /**
         * Check if the shipment should be treated as abandoned when it can't be delivered or if it should be returned to the sender
         */
        if ($postnlShipment->getTreatAsAbandoned()) {
            $customs['HandleAsNonDeliverable'] = 'true';
        }

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
         * Add invoice info
         *
         * This is mandatory for certain shipment types as well as when neither a certificate nor a license is available
         */
        $invoiceRequiredShipmentTypes = $this->getInvoiceRequiredShipmentTypes();
        if (in_array($shipmentType, $invoiceRequiredShipmentTypes)
            || ($customs['License'] == 'false'
                && $customs['Certificate'] == 'false'
            )
        ) {
            $shipmentId = $shipment->getIncrementId();
            $customs['Invoice'] = 'true';
            $customs['InvoiceNr'] = $shipmentId;
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

            /**
             * Calculate the item's weight in kg
             */
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
                'Weight'          => $itemWeight * $item->getQty(),
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

        if (empty($hsTariff)) {
            $hsTariff = '000000';
        }

        return $hsTariff;
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

        if (empty($countryOfOrigin)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Missing country of origin value for product #%s.', $shipmentItem->getProductId()),
                'POSTNL-0091'
            );
        }

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

        if (empty($customsValue)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Missing customs value for product #%s.', $shipmentItem->getProductId()),
                'POSTNL-0092'
            );
        }

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

        if (empty($description)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Missing customs description for product #%s.', $shipmentItem->getProductId()),
                'POSTNL-0093'
            );
        }

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

    /**
     * Get the area field from an address if enabled
     *
     * @return string
     */
    protected function _getArea($address)
    {
        $storeId = $this->getStoreId();
        $areaField = (string) Mage::getStoreConfig(self::XML_PATH_AREA_FIELD, $storeId);
        if ($areaField) {
            $area = $address->getStreet($areaField);

            return $area;
        }

        /**
         * Attempt to get the area through the magic getter instead
         */
        $area = $address->getArea();

        return $area;
    }

    /**
     * Get the area building name from an address if enabled
     *
     * @return string
     */
    protected function _getBuildingName($address)
    {
        $storeId = $this->getStoreId();
        $buildingNameField = (string) Mage::getStoreConfig(self::XML_PATH_BUILDING_NAME_FIELD, $storeId);
        if ($buildingNameField) {
            $buildingName = $address->getStreet($buildingNameField);

            return $buildingName;
        }

        /**
         * Attempt to get the building name through the magic getter instead
         */
        $buildingName = $address->getBuildingName();

        return $buildingName;
    }

    /**
     * Get the department field from an address if enabled
     *
     * @return string
     */
    protected function _getDepartment($address)
    {
        $storeId = $this->getStoreId();
        $departmentField = (string) Mage::getStoreConfig(self::XML_PATH_DEPARTMENT_FIELD, $storeId);
        if ($departmentField) {
            $department = $address->getStreet($departmentField);

            return $department;
        }

        /**
         * Attempt to get department through the magic getter instead
         */
        $department = $address->getDepartment();

        return $department;
    }

    /**
     * Get the doorcode field from an address if enabled
     *
     * @return string
     */
    protected function _getDoorcode($address)
    {
        $storeId = $this->getStoreId();
        $doorcodeField = (string) Mage::getStoreConfig(self::XML_PATH_DOORCODE_FIELD, $storeId);
        if (!$doorcodeField) {
            $doorcode = $address->getStreet($doorcodeField);

            return $doorcode;
        }

        /**
         * Attempt to get the doorcode through the magic getter instead
         */
        $doorcode = $address->getDoorcode();

        return $doorcode;
    }

    /**
     * Get the floor field from an address if enabled
     *
     * @return string
     */
    protected function _getFloor($address)
    {
        $storeId = $this->getStoreId();
        $floorField = (string) Mage::getStoreConfig(self::XML_PATH_FLOOR_FIELD, $storeId);
        if ($floorField) {
            $floor = $address->getStreet($floorField);

            return $floor;
        }

        /**
         * Attempt to get the floor through the magic getter instead
         */
        $floor = $address->getFloor();

        return $floor;
    }

    /**
     * Get the remark field from an address if enabled
     *
     * @return string
     */
    protected function _getRemark($address)
    {
        $storeId = $this->getStoreId();
        $remarkField = (string) Mage::getStoreConfig(self::XML_PATH_REMARK_FIELD, $storeId);
        if ($remarkField) {
            $remark = $address->getStreet($remarkField);

            return $remark;
        }

        /**
         * Attempt to get the remark through the magic getter instead
         */
        $remark = $address->getRemark();

        return $remark;
    }

    /**
     * Get a shipment's reference. By default this will be the shipment's increment ID
     *
     * @param Mage_Sales_Model_Order_Shipment
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
     protected function _getReference($shipment)
     {
         $storeId = $this->getStoreId();
         $referenceType = Mage::getStoreConfig(self::XML_PATH_SHIPMENT_REFERENCE_TYPE, $storeId);

         /**
          * Parse the reference type
          */
         switch ($referenceType) {
             case '': //no break
             case 'none':
                 $reference = '';
                 break;
             case 'shipment_increment_id':
                 $reference = $shipment->getIncrementId();
                 break;
             case 'order_increment_id':
                 $reference = $shipment->getOrder()->getIncrementId();
                 break;
             case 'custom':
                 $reference = Mage::getStoreConfig(self::XML_PATH_CUSTOM_SHIPMENT_REFERENCE, $storeId);
                 break;
             default:
                 throw new TIG_PostNL_Exception(
                     Mage::helper('postnl')->__('Invalid reference type requested: %s', $referenceType),
                     'POSTNL-0043'
                 );
         }

         /**
          * For custom references we need to replace several optional variables
          */
         if ($referenceType == 'custom') {
             $reference = str_replace('{{var shipment_increment_id}}', $shipment->getIncrementId(), $reference);
             $reference = str_replace('{{var order_increment_id}}', $shipment->getOrder()->getIncrementId(), $reference);

             $store = Mage::getModel('core/store')->load($storeId);
             $reference = str_replace('{{var store_frontend_name}}', $store->getFrontendName(), $reference);
         }

         return $reference;
     }
}