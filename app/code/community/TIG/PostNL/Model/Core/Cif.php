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
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * Class containing all default methods used for CIF communication by this extension.
 *
 * If you wish to add new methods you can extend this class or create a new class that extends
 * TIG_PostNL_Model_Core_Cif_Abstract.
 *
 * @method string                    getHSTariffAttribute()
 * @method string                    getCountryOfOriginAttribute()
 * @method string                    getCustomsValueAttribute()
 * @method string                    getCustomsDescriptionAttribute()
 *
 * @method TIG_PostNL_Model_Core_Cif setStoreId(int $value)
 * @method TIG_PostNL_Model_Core_Cif setHSTariffAttribute(string $value)
 * @method TIG_PostNL_Model_Core_Cif setCountryOfOriginAttribute(string $value)
 * @method TIG_PostNL_Model_Core_Cif setCustomsValueAttribute(string $value)
 * @method TIG_PostNL_Model_Core_Cif setCustomsDescriptionAttribute(string $value)
 *
 * @method boolean                   hasHSTariffAttribute()
 * @method boolean                   hasCountryOfOriginAttribute()
 * @method boolean                   hasCustomsValueAttribute()
 * @method boolean                   hasCustomsDescriptionAttribute()
 */
class TIG_PostNL_Model_Core_Cif extends TIG_PostNL_Model_Core_Cif_Abstract
{
    /**
     * Constants containing xpaths to cif configuration options.
     */
    const XPATH_CUSTOMER_CODE               = 'postnl/cif/customer_code';
    const XPATH_DUTCH_CUSTOMER_CODE         = 'postnl/cif/dutch_customer_code';
    const XPATH_CUSTOMER_NUMBER             = 'postnl/cif/customer_number';
    const XPATH_DUTCH_CUSTOMER_NUMBER       = 'postnl/cif/dutch_customer_number';
    const XPATH_COLLECTION_LOCATION         = 'postnl/cif/collection_location';
    const XPATH_GLOBAL_BARCODE_TYPE         = 'postnl/cif_globalpack_settings/global_barcode_type';
    const XPATH_GLOBAL_BARCODE_RANGE        = 'postnl/cif_globalpack_settings/global_barcode_range';

    /**
     * Constants containing xpaths to cif address configuration options.
     */
    const XPATH_AREA_FIELD                  = 'postnl/cif_labels_and_confirming/area_field';
    const XPATH_BUILDING_NAME_FIELD         = 'postnl/cif_labels_and_confirming/building_name_field';
    const XPATH_DEPARTMENT_FIELD            = 'postnl/cif_labels_and_confirming/department_field';
    const XPATH_DOORCODE_FIELD              = 'postnl/cif_labels_and_confirming/doorcode_field';
    const XPATH_FLOOR_FIELD                 = 'postnl/cif_labels_and_confirming/floor_field';
    const XPATH_REMARK_FIELD                = 'postnl/cif_labels_and_confirming/remark_field';

    /**
     * Constants containing xpaths to cif customs configuration options.
     */
    const XPATH_GLOBALPACK_CUSTOMS_LICENSE_NUMBER      = 'postnl/cif_globalpack_settings/customs_license_number';
    const XPATH_GLOBALPACK_CUSTOMS_CERTIFICATE_NUMBER  = 'postnl/cif_globalpack_settings/customs_certificate_number';
    const XPATH_GLOBALPACK_USE_HS_TARIFF_ATTRIBUTE     = 'postnl/cif_globalpack_settings/use_hs_tariff';
    const XPATH_GLOBALPACK_HS_TARIFF_ATTRIBUTE         = 'postnl/cif_globalpack_settings/hs_tariff_attribute';
    const XPATH_GLOBALPACK_CUSTOMS_VALUE_ATTRIBUTE     = 'postnl/cif_globalpack_settings/customs_value_attribute';
    const XPATH_GLOBALPACK_COUNTRY_OF_ORIGIN_ATTRIBUTE = 'postnl/cif_globalpack_settings/country_of_origin_attribute';
    const XPATH_GLOBALPACK_DESCRIPTION_ATTRIBUTE       = 'postnl/cif_globalpack_settings/description_attribute';
    const XPATH_GLOBALPACK_PRODUCT_SORTING_ATTRIBUTE   = 'postnl/cif_globalpack_settings/product_sorting_attribute';
    const XPATH_GLOBALPACK_PRODUCT_SORTING_DIRECTION   = 'postnl/cif_globalpack_settings/product_sorting_direction';

    /**
     * Xpath to setting that determines whether to use a separate return address.
     */
    const XPATH_USE_SENDER_ADDRESS_AS_ALTERNATIVE_SENDER = 'postnl/cif_address/use_sender_address';

    /**
     * Xpath to sender and return addresses data.
     *
     * N.B. missing last part so this will return an array of all fields.
     */
    const XPATH_SENDER_ADDRESS = 'postnl/cif_address';
    const XPATH_RETURN_ADDRESS = 'postnl/returns';

    /**.
     * Xpaths for shipment reference info.
     */
    const XPATH_SHIPMENT_REFERENCE_TYPE   = 'postnl/packing_slip/shipment_reference_type';
    const XPATH_CUSTOM_SHIPMENT_REFERENCE = 'postnl/packing_slip/custom_shipment_reference';

    /**
     * Possible barcodes series per barcode type.
     */
    const NL_BARCODE_SERIE_LONG   = '0000000000-9999999999';
    const NL_BARCODE_SERIE_SHORT  = '000000000-999999999';
    const EU_BARCODE_SERIE_LONG   = '00000000-99999999';
    const EU_BARCODE_SERIE_SHORT  = '0000000-9999999';
    const GLOBAL_BARCODE_SERIE    = '0000-9999';

    /**
     * Xpath to weight per parcel config setting.
     */
    const XPATH_WEIGHT_PER_PARCEL = 'postnl/packing_slip/weight_per_parcel';

    /**
     * XPaths for COD specific settings.
     */
    const XPATH_COD_ACCOUNT_NAME = 'postnl/cod/account_name';
    const XPATH_COD_BIC          = 'postnl/cod/bic';
    const XPATH_COD_IBAN         = 'postnl/cod/iban';

    /**
     * The maximum amount of products which can be printed on the customs declaration form.
     */
    const MAX_CUSTOMS_PRODUCT_COUNT = 5;

    /**
     * Default HS tariff value.
     */
    const DEFAULT_HS_TARIFF = '000000';

    /**
     * Can we use the dutch address (BE -> NL shipments only)
     */
    const XPATH_USE_DUTCH_ADDRESS = 'postnl/cif_address/use_dutch_address';

    /**
     * Array containing possible address types.
     *
     * N.B. the value of the return and alternative sender addresses were switched in v1.5.0.
     *
     * @var array
     */
    protected $_addressTypes = array(
        'Receiver'    => '01',
        'Sender'      => '02',
        'Alternative' => '03', // Alternative sender. Parcels that cannot be delivered will be returned here.
        'Collection'  => '04',
        'Return'      => '08',
        'Delivery'    => '09', // Post office address. For use with PakjeGemak.
        'Dutch'       => '02',
    );

    /**
     * Array containing all available printer types. These are used to determine the output type of shipping labels
     * currently only GraphicFile|PDF is supported.
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

        //Intermec FinnerPrint
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
     * These shipment types need specific product options.
     *
     * @var array
     */
    protected $_availableProductOptions = array(
        'PGE' => array(
            'Characteristic' => '118',
            'Option'         => '002',
        ),
        'Avond' => array(
            'Characteristic' => '118',
            'Option'         => '006',
        ),
        'Sunday' => array(
            'Characteristic' => '101',
            'Option'         => '008',
        ),
        'Sameday' => array(
            array (
                'Characteristic' => '118',
                'Option'         => '015',
            ),
            array (
                'Characteristic' => '118',
                'Option'         => '006',
            ),
        ),
        'Food' => array(
            array (
                'Characteristic' => '118',
                'Option'         => '015',
            ),
            array (
                'Characteristic' => '118',
                'Option'         => '006',
            ),
        ),
        'Cooledfood' => array(
            array (
                'Characteristic' => '118',
                'Option'         => '015',
            ),
            array (
                'Characteristic' => '118',
                'Option'         => '006',
            ),
        ),
        'AgeCheck' => array(
            'Characteristic' => '014',
            'Option'         => '002',
        ),
        'BirthdayCheck' => array(
            'Characteristic' => '016',
            'Option'         => '002',
        ),
        'IDCheck' => array(
            'Characteristic' => '012',
            'Option'         => '002',
        ),
    );

    /**
     * Get possible address types.
     *
     * @return array
     */
    public function getAddressTypes()
    {
        return $this->_addressTypes;
    }

    /**
     * Get possible printer types.
     *
     * @return array
     */
    public function getPrinterTypes()
    {
        return $this->_printerTypes;
    }

    /**
     * Get shipment types that require an invoice number.
     *
     * @return array
     */
    public function getInvoiceRequiredShipmentTypes()
    {
        return $this->_invoiceRequiredShipmentTypes;
    }

    /**
     * Get an array of available product options per type.
     *
     * @return array
     */
    public function getAvailableProductOptions()
    {
        return $this->_availableProductOptions;
    }

    /**
     * Gets the current store id. If no store id is specified, return the default admin store id.
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }

        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $this->setData('store_id', $storeId);

        return $storeId;
    }

    /**
     * Retrieves a barcode from CIF.
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
     * Requests a new barcode from CIF as a ping request. This can be used to validate account settings or to check if
     * the CIF service is up and running. This is not meant to be used to generate an actual barcode for a shipment.
     * Use the generateBarcode method for that.
     *
     * The generateBarcode CIF call was chosen as it is the simplest CIF function available.
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

        $this->setPassword($data['password']);
        $this->setUsername($data['username']);

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
            $soapParams
        );

        if (!isset($response->Barcode)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid barcode response: %s', "\n" . var_export($response, true)),
                'POSTNL-0054'
            );
        }

        return $response->Barcode;
    }

    /**
     * Retrieves the latest shipping status of a shipment from CIF.
     *
     * @param string $barcode
     *
     * @throws TIG_PostNL_Exception
     *
     * @return StdClass
     */
    public function getShipmentStatus($barcode)
    {
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

        if (!isset($response->Shipments)
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

            /**
             *  We need the original shipment, not a related shipment (such as a return shipment).
             */
            if ($shipment->Barcode === $barcode) {
                return $shipment;
            }
        }

        /**
         * no shipment could be matched to the supplied barcode
         */
        throw new TIG_PostNL_Exception(
            Mage::helper('postnl')->__(
                'Unable to match barcode to shippingStatus response: %s',
                var_export($response, true)
            ),
            'POSTNL-0063'
        );
    }

    /**
     * Retrieves the latest shipping status of a shipment from CIF including full status history.
     *
     * @param string $barcode
     *
     * @throws TIG_PostNL_Exception
     *
     * @return StdClass
     */
    public function getCompleteShipmentStatus($barcode)
    {
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
            'CompleteStatus',
            $soapParams
        );

        if (!isset($response->Shipments)
            || (!is_array($response->Shipments) && !is_object($response->Shipments))
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid shippingStatus response: %s', "\n" . var_export($response, true)),
                'POSTNL-0055'
            );
        }

        foreach($response->Shipments as $shipment) {
            $shipment = $shipment[0];
            /**
             * we need the original shipment, not a related shipment (such as a return shipment).
             */
            if ($shipment->Barcode === $barcode) {
                return $shipment;
            }
        }

        /**
         * no shipment could be matched to the supplied barcode
         */
        throw new TIG_PostNL_Exception(
            Mage::helper('postnl')->__(
                'Unable to match barcode to shippingStatus response: %s',
                var_export($response, true)
            ),
            'POSTNL-0063'
        );
    }

    /**
     * Confirms the chosen shipment without generating labels.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     * @param string                         $barcode
     * @param bool                           $mainBarcode
     * @param bool                           $shipmentNumber
     *
     * @throws TIG_PostNL_Exception
     *
     * @return array
     */
    public function confirmShipment(TIG_PostNL_Model_Core_Shipment $postnlShipment, $barcode, $mainBarcode = false,
                                    $shipmentNumber = false)
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
                'Shipment' => $this->_getShipment($postnlShipment, $barcode, $mainBarcode, $shipmentNumber)
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
     * Generates shipping labels for the chosen shipment.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     * @param string                         $barcode
     * @param boolean|string                 $mainBarcode
     * @param boolean|int                    $shipmentNumber
     * @param boolean                        $printReturnLabel
     * @param boolean|string                 $returnBarcode
     * @param string                         $printerType The printertype used. Currently only 'GraphicFile|PDF' is
     *                                                    fully supported
     *
     * @throws TIG_PostNL_Exception
     *
     * @return array
     *
     */
    public function generateLabels(TIG_PostnL_Model_Core_Shipment $postnlShipment, $barcode,
                                   $mainBarcode = false, $shipmentNumber = false,
                                   $printReturnLabel = false, $returnBarcode = false,
                                   $printerType = 'GraphicFile|PDF'

    ) {
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
            $cifShipment = $this->_getShipment(
                $postnlShipment,
                $barcode,
                false,
                false,
                $printReturnLabel,
                $returnBarcode
            );
        } else {
            $cifShipment = $this->_getShipment(
                $postnlShipment,
                $barcode,
                $mainBarcode,
                $shipmentNumber,
                $printReturnLabel,
                $returnBarcode
            );
        }

        $message  = $this->_getMessage($barcode, array('Printertype' => $printerType));
        $customer = $this->_getCustomer($shipment);

        $soapParams =  array(
            'Message'  => $message,
            'Customer' => $customer,
            'Shipments' => array('Shipment' => $cifShipment),
        );

        $response = $this->call(
            'Labelling',
            'GenerateLabel',
            $soapParams
        );

        /**
         * Since Cif structure has been changed as of version 2.0, $shipment is used as a pointer to the shipment data
         * to reach for the label object.
         */
        $shipment = $response->ResponseShipments->ResponseShipment[0];

        if (!isset($shipment->Labels)
            || !is_object($shipment->Labels)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid generateLabels response: %s', var_export($response, true)),
                'POSTNL-0057'
            );
        }

        return $response;
    }

    /**
     * Generates shipping labels for the chosen shipment without confirming it.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     * @param string                         $barcode
     * @param boolean|string                 $mainBarcode
     * @param boolean|int                    $shipmentNumber
     * @param boolean                        $printReturnLabel
     * @param boolean|string                 $returnBarcode
     * @param string                         $printerType The printertype used. Currently only 'GraphicFile|PDF' is
     *                                                    fully supported
     *
     *
     * @throws TIG_PostNL_Exception
     *
     * @return array
     *
     */
    public function generateLabelsWithoutConfirm(TIG_PostnL_Model_Core_Shipment $postnlShipment, $barcode,
                                                 $mainBarcode = false, $shipmentNumber = false,
                                                 $printReturnLabel = false, $returnBarcode = false,
                                                 $printerType = 'GraphicFile|PDF'

    ) {
        /**
         * Return barcodes are required when printing return labels.
         */
        if ($printReturnLabel && empty($returnBarcode)) {
            throw new InvalidArgumentException('Missing return barcode.');
        }

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
            $cifShipment = $this->_getShipment(
                $postnlShipment,
                $barcode,
                false,
                false,
                $printReturnLabel,
                $returnBarcode
            );
        } else {
            $cifShipment = $this->_getShipment(
                $postnlShipment,
                $barcode,
                $mainBarcode,
                $shipmentNumber,
                $printReturnLabel,
                $returnBarcode
            );
        }

        $message     = $this->_getMessage($barcode, array('Printertype' => $printerType));
        $customer    = $this->_getCustomer($shipment);

        $soapParams =  array(
            'Message'  => $message,
            'Customer' => $customer,
            'Shipments' => array('Shipment' => $cifShipment),
        );

        $response = $this->call(
            'Labelling',
            'GenerateLabelWithoutConfirm',
            $soapParams
        );

        /**
         * Since Cif structure has been changed as of version 2.0, $shipment is used as a pointer to the shipment data
         * to reach for the label object.
         */
        $shipment = $response->ResponseShipments->ResponseShipment[0];

        if (!isset($shipment->Labels)
            || !is_object($shipment->Labels)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Invalid generateLabelsWithoutConfirm response: %s',
                    var_export($response, true)
                ),
                'POSTNL-0058'
            );
        }

        return $response;
    }

    /**
     * Gets the Message parameter.
     *
     * @param       $barcode
     * @param array $extra An array of additional parameters to add
     *
     * @return array
     */
    protected function _getMessage($barcode, $extra = array())
    {
        /** @var Mage_Core_Helper_Http $helper */
        $helper = Mage::helper('core/http');
        $messageIdString = uniqid(
                'postnl_'
                . ip2long($helper->getServerAddr())
            )
            . $this->_getCustomerNumber()
            . $barcode
            . microtime();

        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getModel('core/date');
        $message = array(
            'MessageID'        => md5($messageIdString),
            'MessageTimeStamp' => date('d-m-Y H:i:s', $dateModel->gmtTimestamp()),
        );

        if ($extra) {
            $message = array_merge($message, $extra);
        }

        return $message;
    }

    /**
     * Gets the customer parameter.
     *
     * @param Mage_Sales_Model_Order_Shipment|boolean $shipment
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

            if (
                $this->getHelper()->getDomesticCountry() == 'BE' &&
                $shipment->getShippingAddress()->getCountryId() == 'NL' &&
                Mage::helper('postnl/deliveryoptions')->canUseDutchProducts() &&
                Mage::getStoreConfigFlag(self::XPATH_USE_DUTCH_ADDRESS)
            ) {
                $customer['CustomerCode'] = $this->_getDutchCustomerCode();
                $customer['CustomerNumber'] = $this->_getDutchCustomerNumber();
                $customer['Address'] = $this->_getAddress('Dutch');
            }
        }

        return $customer;
    }

    /**
     * Creates the CIF shipment object based on a PostNL shipment.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     * @param string                         $barcode
     * @param bool                           $mainBarcode
     * @param bool                           $shipmentNumber
     * @param bool                           $printReturnLabel
     * @param bool|string                    $returnBarcode
     *
     * @return array
     */
    protected function _getShipment(TIG_PostnL_Model_Core_Shipment $postnlShipment, $barcode, $mainBarcode = false,
                                    $shipmentNumber = false, $printReturnLabel = false, $returnBarcode = false)
    {
        $shipment        = $postnlShipment->getShipment();
        $shippingAddress = $postnlShipment->getShippingAddress();
        $order           = $shipment->getOrder();

        $parcelCount    = $postnlShipment->getParcelCount();
        $shipmentWeight = $postnlShipment->getTotalWeight(true, true);

        /**
         * If a shipmentNumber is provided it means that this is not a single shipment and the weight of the shipment
         * needs to be calculated
         */
        if ($shipmentNumber !== false) {
            /**
             * Get the parcel weight and then convert it to grams.
             */
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $parcelWeight = Mage::getStoreConfig(self::XPATH_WEIGHT_PER_PARCEL, $postnlShipment->getStoreId());
            $parcelWeight = $helper->standardizeWeight($parcelWeight, $shipment->getStoreId(), true);

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

        /**
         * Get and format this shipment's delivery date if available.
         */
        $deliveryDate = null;
        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::helper('postnl/deliveryOptions');
        if ($deliveryOptionsHelper->canUseDeliveryDays(false)) {
            $deliveryDate = $postnlShipment->getDeliveryDate();
            if ($deliveryDate) {
                $deliveryTime = new DateTime($deliveryDate, new DateTimeZone('UTC'));
                $deliveryTime->setTimezone(new DateTimeZone('Europe/Berlin'));

                $deliveryDate = $deliveryTime->format('d-m-Y H:i:s');
            }
        }

        $reference = $this->_getReference($shipment);

        $shipmentData = array(
            'Barcode'                  => $barcode,
            'CollectionTimeStampEnd'   => '',
            'CollectionTimeStampStart' => '',
            'DownPartnerBarcode'       => $postnlShipment->getDownPartnerBarcode(),
            'DownPartnerID'            => $postnlShipment->getDownPartnerId(),
            'ProductCodeDelivery'      => $postnlShipment->getProductCode(),
            'Contacts'                 => array(
                'Contact' => $this->_getContact($shippingAddress, $postnlShipment, $order),
            ),
            'Dimension'                => array(
                'Weight'  => round($shipmentWeight),
            ),
            'Reference'                => $this->_getReference($shipment),
            'DeliveryDate'             => $deliveryDate,
        );

        if ($printReturnLabel) {
            $shipmentData['ReturnBarcode']   = $returnBarcode;
            $shipmentData['ReturnReference'] = $reference;
        }

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
        $addresses = $this->_getShipmentAddresses($postnlShipment, $shippingAddress, $printReturnLabel);
        $shipmentData['Addresses'] = $addresses;

        /**
         * Add extra cover data and COD data.
         * In the case of a multi-colli shipment this is only added to the first parcel.
         */
        if (($shipmentNumber === false || $shipmentNumber == 1)
            && ($postnlShipment->isExtraCover() || $postnlShipment->isCod())
        ) {
            $shipmentData['Amounts'] = $this->_getAmount($postnlShipment, $shipment);
        }

        /**
         * Add customs data.
         */
        if ($postnlShipment->isGlobalShipment()) {
            $shipmentData['Customs'] = $this->_getCustoms($postnlShipment);
        }

        /**
         * Add product options.
         */
        $productOptions = $this->_getProductOptions($postnlShipment);
        if ($productOptions) {
            $shipmentData['ProductOptions'] = $productOptions;
        }

        if ($postnlShipment->isBirthdayCheckShipment()) {
            $customerDob = $order->getCustomerDob();
            $customerDobObject = new DateTime($customerDob, new DateTimeZone('UTC'));
            $customerDobObject->setTimezone(new DateTimeZone('Europe/Berlin'));

            $shipmentData['ReceiverDateOfBirth'] = $customerDobObject->format('d-m-Y');
        }

        /**
         * @source https://developer.postnl.nl/apis/confirming-webservice/documentation#toc-14
         */
        if ($postnlShipment->isIDCheckShipment()) {
            $expirationDate = $postnlShipment->getIdcheckExpirationDate();
            $expirationDateObject = new DateTime($expirationDate, new DateTimeZone('UTC'));
            $expirationDateObject->setTimezone(new DateTimeZone('Europe/Berlin'));

            $shipmentData['IDExpiration'] = $expirationDateObject->format('d-m-Y');
            $shipmentData['IDNumber'] = $postnlShipment->getIdcheckNumber();
            $shipmentData['IDType'] = $postnlShipment->getIdcheckType();
        }

        /**
         * Add 'DownPartner' data.
         */
        $downPartnerData = $this->_getDownPartnerData($postnlShipment);
        if ($downPartnerData) {
            $shipmentData['DownPartnerID'] = $downPartnerData['id'];
            $shipmentData['DownPartnerLocation'] = $downPartnerData['location'];
        }

        return $shipmentData;
    }

    /**
     * Get all addresses for a given shipment.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     * @param Mage_Sales_Model_order_Address $shippingAddress
     * @param boolean                        $printReturnLabel
     *
     * @return array
     */
    protected function _getShipmentAddresses(TIG_PostnL_Model_Core_Shipment $postnlShipment, $shippingAddress,
                                             $printReturnLabel
    ) {
        $useSenderAddressAsReturn = Mage::getStoreConfigFlag(
            self::XPATH_USE_SENDER_ADDRESS_AS_ALTERNATIVE_SENDER,
            $this->getStoreId()
        );
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
            $addresses['Address'][] = $this->_getAddress('Delivery', $pakjeGemakAddress);
        }

        if ($printReturnLabel) {
            $addresses['Address'][] = $this->_getAddress('Return');
        }

        return $addresses;
    }

    /**
     * Gets product options based on a specified shipment.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     *
     * @return array|false
     */
    protected function _getProductOptions(TIG_PostnL_Model_Core_Shipment $postnlShipment)
    {
        $postnlOrder = $postnlShipment->getPostnlOrder();
        if (!$postnlOrder) {
            return false;
        }

        $type = $postnlOrder->getType();
        $availableProductOptions = $this->getAvailableProductOptions();
        if (!isset($availableProductOptions[$type])) {
            return false;
        }

        $productOptions = array(
            'ProductOption' => $availableProductOptions[$type]
        );

        return $productOptions;
    }

    /**
     * Get 'DownPartner' data from the specified PostNL shipment if available.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     *
     * @return array|bool
     */
    protected function _getDownPartnerData(TIG_PostnL_Model_Core_Shipment $postnlShipment)
    {
        if (!$postnlShipment->hasPgLocationCode() || !$postnlShipment->hasPgRetailNetworkId()) {
            return false;
        }

        $downPartnerData = array(
            'id'       => $postnlShipment->getPgRetailNetworkId(),
            'location' => $postnlShipment->getPgLocationCode(),
        );

        return $downPartnerData;
    }

    /**
     * Gets an array containing required address data.
     *
     * @param             $addressType
     * @param bool|string $shippingAddress
     *
     * @throws TIG_PostNL_Exception
     * @return array
     */
    protected function _getAddress($addressType, $shippingAddress = false)
    {
        $availableAddressTypes = $this->getAddressTypes();
        if (!isset($availableAddressTypes[$addressType])) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid address type supplied: %s', $addressType),
                'POSTNL-0108'
            );
        }

        /**
         * Determine which address to use. Currently only 'Sender', 'Alternative', 'PakjeGemak', 'Receiver' and 'Dutch' are fully
         * supported.
         */
        $streetData = false;
        switch ($addressType) {
            case 'Sender':
                /**
                 * Get all cif_address fields as an array and convert that to a Varien_Object
                 * This allows the _prepareAddress method to access this data in the same way as a
                 * conventional Mage_Sales_Model_Order_Address object.
                 */
                $senderAddress = Mage::getStoreConfig(self::XPATH_SENDER_ADDRESS, $this->getStoreId());

                $streetData = array(
                    'streetname'           => $senderAddress['streetname'],
                    'housenumber'          => $senderAddress['housenumber'],
                    'housenumberExtension' => $senderAddress['housenumber_extension'],
                    'fullStreet'           => '',
                );

                $address = new Varien_Object($senderAddress);
                break;
            case 'Return':
                $returnAddress = Mage::getStoreConfig(self::XPATH_RETURN_ADDRESS, $this->getStoreId());

                $streetData = array(
                    'streetname'           => 'Antwoordnummer:',
                    'housenumber'          => $returnAddress['return_freepost_number'],
                    'housenumberExtension' => '',
                    'fullStreet'           => '',
                );

                $returnAddressData = array();
                foreach($returnAddress as $field => $value) {
                    if (strpos($field, 'return_') === false) {
                        continue;
                    }

                    $returnAddressData[substr($field, 7)] = $value;
                }

                $address = new Varien_Object($returnAddressData);
                break;
            case 'Alternative':
                /**
                 * Check if the return address is the same as the sender address. If so, no address is returned.
                 */
                $useSenderAddress = Mage::getStoreConfig(
                    self::XPATH_USE_SENDER_ADDRESS_AS_ALTERNATIVE_SENDER,
                    $this->getStoreId()
                );

                if ($useSenderAddress) {
                    return false;
                }

                /**
                 * Get all cif_address fields with the 'alternative_sender_' prefix as an array and convert that to a
                 * Varien_Object. This allows the _prepareAddress method to access this data in the same way as a
                 * conventional Mage_Sales_Model_Order_Address object.
                 */
                $returnAddress = Mage::getStoreConfig(self::XPATH_SENDER_ADDRESS, $this->getStoreId());

                $streetData = array(
                    'streetname'           => $returnAddress['alternative_sender_streetname'],
                    'housenumber'          => $returnAddress['alternative_sender_housenumber'],
                    'housenumberExtension' => $returnAddress['alternative_sender_housenumber_extension'],
                    'fullStreet'           => '',
                );

                $returnAddressData = array();
                foreach($returnAddress as $field => $value) {
                    if (strpos($field, 'alternative_sender_') === false) {
                        continue;
                    }

                    $returnAddressData[substr($field, 19)] = $value;
                }

                $address = new Varien_Object($returnAddressData);
                break;
            case 'Dutch':
                /**
                 * Get all cif_address fields with the 'dutch_address_' prefix as an array and convert that to a
                 * Varien_Object. This allows the _prepareAddress method to access this data in the same way as a
                 * conventional Mage_Sales_Model_Order_Address object.
                 */
                $dutchAddress = Mage::getStoreConfig(self::XPATH_SENDER_ADDRESS, $this->getStoreId());

                $streetData = array(
                    'streetname'           => $dutchAddress['dutch_address_streetname'],
                    'housenumber'          => $dutchAddress['dutch_address_housenumber'],
                    'housenumberExtension' => $dutchAddress['dutch_address_housenumber_extension'],
                    'fullStreet'           => '',
                );

                $dutchAddressData = array();
                foreach($dutchAddress as $field => $value) {
                    if (strpos($field, 'dutch_address_') === false) {
                        continue;
                    }

                    $dutchAddressData[substr($field, 14)] = $value;
                }

                /**
                 * Dutch is mandatory.
                 */
                $dutchAddressData['country'] = 'NL';

                $address = new Varien_Object($dutchAddressData);
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
     * Forms an array of address data compatible with CIF.

     * @param Mage_Sales_Model_Order_Address|Varien_Object $address
     * @param array|boolean                                $streetData Optional parameter containing streetname,
     *                                                                 housenr, housenr extension and fullStreet values.
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
            'Name'             => $address->getMiddlename() . ' ' . $address->getLastname(),
            'CompanyName'      => $address->getCompany(),
            'Street'           => $streetData['streetname'],
            'HouseNr'          => $streetData['housenumber'],
            'HouseNrExt'       => $streetData['housenumberExtension'],
            'StreetHouseNrExt' => $streetData['fullStreet'],
            'Zipcode'          => strtoupper(str_replace(' ', '', $address->getPostcode())),
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

        foreach ($addressArray as $field => $value) {
            if ($value === '') {
                $addressArray[$field] = null;
            }
        }

        return $addressArray;
    }

    /**
     * Gets data for the barcode that's requested. Depending on the destination of the shipment several barcode types
     * may be requested.
     *
     * @param string $barcodeType
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getBarcodeData($barcodeType)
    {
        $barcodeType = strtoupper($barcodeType);

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
     * Generates the CIF amount object containing the shipment's insured amount (if any).
     *
     * @param TIG_PostnL_Model_Core_Shipment          $postnlShipment
     * @param Mage_Sales_Model_Order_Shipment|boolean $shipment
     *
     * @return array
     */
    protected function _getAmount(TIG_PostnL_Model_Core_Shipment $postnlShipment, $shipment = false)
    {
        $amount = array();
        if (!$postnlShipment->isExtraCover() && !$postnlShipment->isCod()) {
            return $amount;
        }

        if ($postnlShipment->isExtraCover()) {
            $extraCoverAmount = $postnlShipment->getExtraCoverAmount();
            if ($extraCoverAmount < 500) {
                $extraCoverAmount = 500;
            }

            $extraCover = number_format($extraCoverAmount, 2, '.', '');
            $amount[] = array(
                'AccountName'       => '',
                'BIC'               => '',
                'IBAN'              => '',
                'AmountType'        => '02', // 01 = COD, 02 = Insured
                'Currency'          => 'EUR',
                'Reference'         => '',
                'TransactionNumber' => '',
                'Value'             => $extraCover,
            );
        }

        if ($postnlShipment->isCod()) {
            if (!$shipment) {
                $shipment = $postnlShipment->getShipment();
            }

            $order = $shipment->getOrder();

            $value = number_format($order->getBaseGrandTotal(), 2, '.', '');
            $amount[] = array(
                'AccountName'       => $this->_getCodAccountName(),
                'BIC'               => $this->_getCodBic(),
                'IBAN'              => $this->_getCodIban(),
                'AmountType'        => '01', // 01 = COD, 02 = Insured
                'Currency'          => $order->getBaseCurrencyCode(),
                'Reference'         => $this->_getReference($shipment),
                'TransactionNumber' => '',
                'Value'             => $value,
            );
        }

        return $amount;
    }

    /**
     * Creates the CIF contact object.
     *
     * @param Mage_Sales_Model_Order_Address $address
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     * @param Mage_Sales_Model_Order         $order
     *
     * @return array
     */
    protected function _getContact($address, $postnlShipment, $order)
    {
        $smsNr = $this->_getMobilePhoneNumber($postnlShipment);

        $contact = array(
            'ContactType' => '01', // Receiver
            'Email'       => $order->getCustomerEmail(),
            'SMSNr'       => $smsNr,
            'TelNr'       => $address->getTelephone(),
        );

        return $contact;
    }

    /**
     * Gets a mobile phone number for the current shipment.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return string
     */
    protected function _getMobilePhoneNumber(TIG_PostNL_Model_Core_Shipment $postnlShipment)
    {
        $mobilePhoneNumber = '';

        $postnlOrder = $postnlShipment->getPostnlOrder();
        if ($postnlOrder && $postnlOrder->getMobilePhoneNumber()) {
            $mobilePhoneNumber = $postnlOrder->getMobilePhoneNumber();
        }

        return $mobilePhoneNumber;
    }

    /**
     * Creates the CIF group object.
     *
     * @param int  $groupCount
     * @param bool $mainBarcode
     * @param bool $shipmentNumber
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
     * Retrieves streetname, housenumber and housenumber extension from the shipping address. The shipping address may
     * be in multiple streetlines configuration or single line configuration. In the case of multi-line, each part of
     * the street data will be in a seperate field. In the single line configuration, each part will be in the same
     * field and will have to be split using PREG.
     *
     * PREG cannot be relied on as it is impossible to create a regex that can filter all possible street syntaxes.
     * Therefore we strongly recommend to use multiple street lines. This can be enabled in Magento community in
     * system > config > customer configuration. Or if you use Enterprise, in customers > attributes > manage customer
     * address attributes.
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return array
     * @throws TIG_PostNL_Exception
     */
    protected function _getStreetData($address)
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $storeId = $this->getStoreId();

        $streetData = $helper->getStreetData($storeId, $address, false);

        $houseNumberRequiredCountries = $helper->getHouseNumberRequiredCountries($storeId);

        if (in_array($address->getCountryId(), $houseNumberRequiredCountries) && empty($streetData['housenumber'])) {
            throw new TIG_PostNL_Exception(
                $helper->__("House number is required for the destination country (%s).", $address->getCountryId()),
                'POSTNL-0239'
            );
        }

        return $streetData;
    }

    /**
     * Create Customs CIF object.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return array
     */
    protected function _getCustoms($postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();
        $shipmentType = $postnlShipment->getGlobalpackShipmentType();

        $customs = array(
            'ShipmentType'           => $shipmentType,
            'HandleAsNonDeliverable' => 'false',
            'Invoice'                => 'false',
            'Certificate'            => 'false',
            'License'                => 'false',
            'Currency'               => 'EUR',
        );

        /**
         * Check if the shipment should be treated as abandoned when it can't be delivered or if it should be returned
         * to the sender.
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

        /** @var Mage_Sales_Model_Resource_Order_Shipment_Item_Collection $items */
        $items = $shipment->getItemsCollection();
        /** @var Mage_Sales_Model_Order_Shipment_Item $item */
        foreach ($items as $key => $item) {
            if ($item->isDeleted() || $item->getOrderItem()->getProductType() == 'bundle') {
                $items->removeItemByKey($key);
            }
        }

        $items = $this->_sortCustomsItems($items);

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        foreach ($items as $item) {
            /**
             * A maximum of 5 rows are allowed
             */
            if (++$itemCount > self::MAX_CUSTOMS_PRODUCT_COUNT) {
                break;
            }

            /**
             * Calculate the item's weight in kg
             */
            $itemWeight = $helper->standardizeWeight(
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
     * Sorts an array of shipment items based on a product attribute that is defined in the module configuration.
     *
     * @param Mage_Sales_Model_Resource_Order_Shipment_Item_Collection $items
     *
     * @return array
     */
    protected function _sortCustomsItems($items)
    {
        /**
         * Get the attribute and direction used for sorting
         */
        $sortingAttribute = Mage::getStoreConfig(
            self::XPATH_GLOBALPACK_PRODUCT_SORTING_ATTRIBUTE,
            $this->getStoreId()
        );
        $sortingDirection = Mage::getStoreConfig(
            self::XPATH_GLOBALPACK_PRODUCT_SORTING_DIRECTION,
            $this->getStoreId()
        );

        /**
         * Get all products linked to the requested items.
         */
        $productIds = $items->getColumnValues('product_id');
        /** @var Mage_Catalog_Model_Resource_Product_Collection $products */
        $products = Mage::getResourceModel('catalog/product_collection')
                        ->setStoreId($this->getStoreId())
                        ->addFieldToFilter('entity_id', array('in' => $productIds))
                        ->addAttributeToSelect($sortingAttribute)
                        ->setOrder($sortingAttribute, strtoupper($sortingDirection));

        $products->getSelect()->limit(self::MAX_CUSTOMS_PRODUCT_COUNT);

        /**
         * Get the attribute values of the requested sorting attribute.
         */
        $attributeValues = array();
        /** @var Mage_Catalog_Model_Product $product */
        foreach ($products as $product) {
            $attributeValues[$product->getId()] = $product->getDataUsingMethod($sortingAttribute);
        }

        /**
         * Place the item's sorting value in a temporary array where the key is the item's ID
         *
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        $sortedItems = array();
        foreach ($items as $item) {
            $sortingAttributeValue = $attributeValues[$item->getProductId()];

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
     * Get a shipment item's HS tariff.
     *
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     *
     * @return string
     */
    protected function _getHSTariff(Mage_Sales_Model_Order_Shipment_Item $shipmentItem)
    {
        $storeId = $this->getStoreId();

        /**
         * HS Tariff is an optional attribute. Check if it's used and if not, return a default value of 000000
         */
        if (!Mage::getStoreConfig(self::XPATH_GLOBALPACK_USE_HS_TARIFF_ATTRIBUTE, $storeId)) {
            return self::DEFAULT_HS_TARIFF;
        }

        if ($this->hasHSTariffAttribute()) {
            $hsTariffAttribute = $this->getHSTariffAttribute();
        } else {
            $hsTariffAttribute = Mage::getStoreConfig(self::XPATH_GLOBALPACK_HS_TARIFF_ATTRIBUTE, $storeId);
            $this->setHSTariffAttribute($hsTariffAttribute);
        }

        $product = Mage::getModel('catalog/product')->load($shipmentItem->getOrderItem()->getProductId());
        $hsTariff = $product->getDataUsingMethod($hsTariffAttribute);

        if (empty($hsTariff)) {
            $hsTariff = self::DEFAULT_HS_TARIFF;
        }

        return $hsTariff;
    }

    /**
     * Get a shipment item's country of origin.
     *
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _getCountryOfOrigin($shipmentItem)
    {
        $storeId = $this->getStoreId();
        if ($this->hasCountryOfOriginAttribute()) {
            $countryOfOriginAttribute = $this->getCountryOfOriginAttribute();
        } else {
            $countryOfOriginAttribute = Mage::getStoreConfig(
                self::XPATH_GLOBALPACK_COUNTRY_OF_ORIGIN_ATTRIBUTE,
                $storeId
            );
            $this->setCountryOfOriginAttribute($countryOfOriginAttribute);
        }

        $product = Mage::getModel('catalog/product')->load($shipmentItem->getOrderItem()->getProductId());
        $countryOfOrigin = $product->getDataUsingMethod($countryOfOriginAttribute);

        if (empty($countryOfOrigin)) {
            $countryOfOrigin = 'NL'; /** @todo make this configurable */
        }

        return $countryOfOrigin;
    }

    /**
     * Get a shipment item's customs value.
     *
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _getCustomsValue($shipmentItem)
    {
        $storeId = $this->getStoreId();
        if ($this->hasCustomsValueAttribute()) {
            $customsValueAttribute = $this->getCustomsValueAttribute();
        } else {
            $customsValueAttribute = Mage::getStoreConfig(self::XPATH_GLOBALPACK_CUSTOMS_VALUE_ATTRIBUTE, $storeId);
            $this->setCustomsValueAttribute($customsValueAttribute);
        }

        $product = Mage::getModel('catalog/product')->load($shipmentItem->getOrderItem()->getProductId());
        $customsValue = $product->getDataUsingMethod($customsValueAttribute);

        if (empty($customsValue)) {
            /** @var Mage_Adminhtml_Helper_Data $adminhtmlhelper */
            $adminhtmlhelper = Mage::helper('adminhtml');
            $productId = $shipmentItem->getProductId();
            /** @noinspection HtmlUnknownTarget */
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Missing customs value for product <a href="%s" target="_blank">#%s</a>.',
                    $adminhtmlhelper->getUrl('adminhtml/catalog_product/edit', array('id' => $productId)),
                    $productId
                ),
                'POSTNL-0092'
            );
        }

        return $customsValue;
    }

    /**
     * Get a shipment item's customs description
     *
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _getCustomsDescription($shipmentItem)
    {
        $storeId = $this->getStoreId();
        if ($this->hasCustomsDescriptionAttribute()) {
            $descriptionAttribute = $this->getCustomsDescriptionAttribute();
        } else {
            $descriptionAttribute = Mage::getStoreConfig(self::XPATH_GLOBALPACK_DESCRIPTION_ATTRIBUTE, $storeId);
            $this->setCustomsDescriptionAttribute($descriptionAttribute);
        }

        $product = Mage::getModel('catalog/product')->load($shipmentItem->getOrderItem()->getProductId());
        $description = $product->getDataUsingMethod($descriptionAttribute);

        if (empty($description)) {
            /** @var Mage_Adminhtml_Helper_Data $adminhtmlhelper */
            $adminhtmlhelper = Mage::helper('adminhtml');
            $productId = $shipmentItem->getProductId();
            /** @noinspection HtmlUnknownTarget */
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Missing customs description for product <a href="%s" target="_blank">#%s</a>.',
                    $adminhtmlhelper->getUrl('adminhtml/catalog_product/edit', array('id' => $productId)),
                    $productId
                ),
                'POSTNL-0092'
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
        $customerCode = (string) Mage::getStoreConfig(self::XPATH_CUSTOMER_CODE, $storeId);

        return $customerCode;
    }

    /**
     * Gets the dutch customer code from system/config
     *
     * @return string
     */
    protected function _getDutchCustomerCode()
    {
        $storeId = $this->getStoreId();
        $customerCode = (string) Mage::getStoreConfig(self::XPATH_DUTCH_CUSTOMER_CODE, $storeId);

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
        $customerNumber = (string) Mage::getStoreConfig(self::XPATH_CUSTOMER_NUMBER, $storeId);

        return $customerNumber;
    }

    /**
     * Gets the dutch customer number from system/config
     *
     * @return string
     */
    protected function _getDutchCustomerNumber()
    {
        $storeId = $this->getStoreId();
        $customerNumber = (string) Mage::getStoreConfig(self::XPATH_DUTCH_CUSTOMER_NUMBER, $storeId);

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
        $collectionLocation = (string) Mage::getStoreConfig(self::XPATH_COLLECTION_LOCATION, $storeId);

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
        $barcodeType = (string) Mage::getStoreConfig(self::XPATH_GLOBAL_BARCODE_TYPE, $storeId);

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
        $barcodeRange = (string) Mage::getStoreConfig(self::XPATH_GLOBAL_BARCODE_RANGE, $storeId);

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
        $customsLicense = (string) Mage::getStoreConfig(self::XPATH_GLOBALPACK_CUSTOMS_LICENSE_NUMBER, $storeId);

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
        $customsCertificate = (string) Mage::getStoreConfig(
            self::XPATH_GLOBALPACK_CUSTOMS_CERTIFICATE_NUMBER,
            $storeId
        );

        if (empty($customsCertificate)) {
            return false;
        }

        return $customsCertificate;
    }

    /**
     * Get the area field from an address if enabled
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return string
     */
    protected function _getArea($address)
    {
        $storeId = $this->getStoreId();
        $areaField = (string) Mage::getStoreConfig(self::XPATH_AREA_FIELD, $storeId);
        if ($areaField) {
            $area = $address->getStreet($areaField);

            return $area;
        }

        /**
         * Attempt to get the area through the magic getter instead
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $area = $address->getArea();

        return $area;
    }

    /**
     * Get the area building name from an address if enabled
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return string
     */
    protected function _getBuildingName($address)
    {
        $storeId = $this->getStoreId();
        $buildingNameField = (string) Mage::getStoreConfig(self::XPATH_BUILDING_NAME_FIELD, $storeId);
        if ($buildingNameField) {
            $buildingName = $address->getStreet($buildingNameField);

            return $buildingName;
        }

        /**
         * Attempt to get the building name through the magic getter instead
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $buildingName = $address->getBuildingName();

        return $buildingName;
    }

    /**
     * Get the department field from an address if enabled
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return string
     */
    protected function _getDepartment($address)
    {
        $storeId = $this->getStoreId();
        $departmentField = (string) Mage::getStoreConfig(self::XPATH_DEPARTMENT_FIELD, $storeId);
        if ($departmentField) {
            $department = $address->getStreet($departmentField);

            return $department;
        }

        /**
         * Attempt to get department through the magic getter instead
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $department = $address->getDepartment();

        return $department;
    }

    /**
     * Get the door code field from an address if enabled
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return string
     */
    protected function _getDoorcode($address)
    {
        $storeId = $this->getStoreId();
        $doorcodeField = (string) Mage::getStoreConfig(self::XPATH_DOORCODE_FIELD, $storeId);
        if (!$doorcodeField) {
            $doorcode = $address->getStreet($doorcodeField);

            return $doorcode;
        }

        /**
         * Attempt to get the door code through the magic getter instead
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $doorcode = $address->getDoorcode();

        return $doorcode;
    }

    /**
     * Get the floor field from an address if enabled
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return string
     */
    protected function _getFloor($address)
    {
        $storeId = $this->getStoreId();
        $floorField = (string) Mage::getStoreConfig(self::XPATH_FLOOR_FIELD, $storeId);
        if ($floorField) {
            $floor = $address->getStreet($floorField);

            return $floor;
        }

        /**
         * Attempt to get the floor through the magic getter instead
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $floor = $address->getFloor();

        return $floor;
    }

    /**
     * Get the remark field from an address if enabled
     *
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return string
     */
    protected function _getRemark($address)
    {
        $storeId = $this->getStoreId();
        $remarkField = (string) Mage::getStoreConfig(self::XPATH_REMARK_FIELD, $storeId);
        if ($remarkField) {
            $remark = $address->getStreet($remarkField);

            return $remark;
        }

        /**
         * Attempt to get the remark through the magic getter instead
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $remark = $address->getRemark();

        return $remark;
    }

    /**
     * Get a shipment's reference. By default this will be the shipment's increment ID
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getReference($shipment)
    {
        $storeId = $this->getStoreId();
        $referenceType = Mage::getStoreConfig(self::XPATH_SHIPMENT_REFERENCE_TYPE, $storeId);

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
                $reference = Mage::getStoreConfig(self::XPATH_CUSTOM_SHIPMENT_REFERENCE, $storeId);
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
            /** @var Mage_Core_Model_Store $store */
            $store = Mage::getModel('core/store')->load($storeId);

            $reference = str_replace('{{var shipment_increment_id}}', $shipment->getIncrementId(), $reference);
            $reference = str_replace('{{var order_increment_id}}', $shipment->getOrder()->getIncrementId(), $reference);

            $reference = str_replace('{{var store_frontend_name}}', $store->getFrontendName(), $reference);
        }

        return $reference;
    }

    /**
     * Gets account name for COD shipments.
     *
     * @return string
     */
    protected function _getCodAccountName()
    {
        $storeId = $this->getStoreId();
        $customerNumber = (string) Mage::getStoreConfig(self::XPATH_COD_ACCOUNT_NAME, $storeId);

        return $customerNumber;
    }

    /**
     * Gets BIC code for COD shipments.
     *
     * @return string
     */
    protected function _getCodBic()
    {
        $storeId = $this->getStoreId();
        $customerNumber = (string) Mage::getStoreConfig(self::XPATH_COD_BIC, $storeId);

        return $customerNumber;
    }

    /**
     * Gets IBAN code for COD shipments.
     *
     * @return string
     */
    protected function _getCodIban()
    {
        $storeId = $this->getStoreId();
        $customerNumber = (string) Mage::getStoreConfig(self::XPATH_COD_IBAN, $storeId);

        return $customerNumber;
    }
}
