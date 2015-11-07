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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * Primary webservices class. Contains all methods used to communicate with the extensioncontrol webservice.
 *
 * @category   TIG
 * @package    TIG_PostNL
 * @subpackage TIG_PostNL_ExtensionControl
 * @copyright  Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license    http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * @version    v1.2.0
 * @since      v1.0.0
 */
class TIG_PostNL_Model_ExtensionControl_Webservices extends TIG_PostNL_Model_ExtensionControl_Webservices_Abstract
{
    /**
     * XML paths for security keys
     */
    const XPATH_EXTENSIONCONTROL_UNIQUE_KEY  = 'postnl/general/unique_key';
    const XPATH_EXTENSIONCONTROL_PRIVATE_KEY = 'postnl/general/private_key';

    /**
     * XML paths for webshop activation settings
     */
    const XPATH_GENERAL_EMAIL     = 'postnl/general/email';
    const XPATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    /**
     * XML paths for setting statistics
     */
    const XPATH_SUPPORTED_PRODUCT_OPTIONS       = 'postnl/grid/supported_product_options';
    const XPATH_SPLIT_STREET                    = 'postnl/cif_labels_and_confirming/split_street';
    const XPATH_CHECKOUT_ACTIVE                 = 'postnl/checkout/active';
    const XPATH_CHECKOUT_WEBSHOP_ID             = 'postnl/cif/webshop_id';
    const XPATH_CONTACT_NAME                    = 'postnl/cif/contact_name';
    const XPATH_CUSTOMER_NUMBER                 = 'postnl/cif/customer_number';
    const XPATH_DELIVERY_OPTIONS_ACTIVE         = 'postnl/delivery_options/delivery_options_active';
    const XPATH_ENABLE_DELIVERY_DAYS            = 'postnl/delivery_options/enable_delivery_days';
    const XPATH_ENABLE_TIMEFRAMES               = 'postnl/delivery_options/enable_timeframes';
    const XPATH_ENABLE_EVENING_TIMEFRAMES       = 'postnl/delivery_options/enable_evening_timeframes';
    const XPATH_ENABLE_PAKJEGEMAK               = 'postnl/delivery_options/enable_pakjegemak';
    const XPATH_ENABLE_PAKKETAUTOMAAT_LOCATIONS = 'postnl/delivery_options/enable_pakketautomaat_locations';
    const XPATH_ENABLE_PAKJEGEMAK_EXPRESS       = 'postnl/delivery_options/enable_pakjegemak_express';
    const XPATH_USE_BUSPAKJE                    = 'postnl/delivery_options/use_buspakje';
    const XPATH_BUSPAKJE_CALCULATION_MODE       = 'postnl/delivery_options/buspakje_calculation_mode';
    const XPATH_COD_ACTIVE                      = 'payment/postnl_cod/active';
    const XPATH_MIJNPAKKET_LOGIN_ACTIVE         = 'postnl/delivery_options/mijnpakket_login_active';
    const XPATH_USE_POSTCODE_CHECK              = 'postnl/cif_labels_and_confirming/use_postcode_check';
    const XPATH_CHECKOUT_EXTENSION              = 'postnl/cif_address/checkout_extension';
    const XPATH_PARCELWARE_EXPORT_ACTIVE        = 'postnl/parcelware_export/active';
    const XPATH_SEND_TRACK_AND_TRACE_EMAIL      = 'postnl/track_and_trace/send_track_and_trace_email';
    const XPATH_TRACK_AND_TRACE_EMAIL_TEMPLATE  = 'postnl/track_and_trace/track_and_trace_email_template';
    const XPATH_SHOW_LABEL                      = 'postnl/packing_slip/show_label';

    /**
     * XML path to extension activation setting
     */
    const XPATH_ACTIVE = 'postnl/general/active';

    /**
     * XML path to 'is_activated' flag
     */
    const XPATH_IS_ACTIVATED = 'postnl/general/is_activated';

    /**
     * Expected success response
     */
    const SUCCESS_MESSAGE = 'success';

    /**
     * Activates the webshop. This will trigger a private key and a unique key to be sent to the specified e-mail, which
     * must be entered into system config by the merchant in order to finish the activation process.
     *
     * @param boolean|string $email
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    public function activateWebshop($email = false)
    {
        if (!$email) {
            $email = $this->_getEmail();
        }

        $soapParams = array(
            'email'    => $email,
            'hostName' => $this->_getHostName(),
        );

        $result = $this->call('activateWebshop', $soapParams);

        if (!is_array($result)
            || !isset($result['status'])
            || $result['status'] != self::SUCCESS_MESSAGE
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid activateWebshop response: %s', var_export($result, true)),
                'POSTNL-0079'
            );
        }

        return $result;
    }

    /**
     * Updates the ExtensionControl server with updated statistics.
     *
     * @param boolean $forceUpdate
     *
     * @throws TIG_PostNL_Exception
     *
     * @return TIG_PostNL_Model_ExtensionControl_Webservices
     */
    public function updateStatistics($forceUpdate = false)
    {
        $canSendStatictics = Mage::helper('postnl/webservices')->canSendStatistics();
        if ($forceUpdate !== true && !$canSendStatictics) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to update statistics. This feature has been disabled.'),
                'POSTNL-0080'
            );
        }

        /**
         * Get the security keys used to encrypt the message
         */
        $uniqueKey  = $this->_getUniqueKey();
        $privateKey = $this->_getPrivateKey();

        if (!$uniqueKey || !$privateKey) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No private or unique key found. Unable to complete the request.'),
                'POSTNL-0081'
            );
        }

        /**
         * get version statistics of Magento and the PostNL extension
         */
        $versionData = $this->_getVersionData();

        /**
         * Get statistics of the websites using the extension
         */
        $websiteData = $this->_getWebsites();

        /**
         * Merge the website and version data
         */
        $data = array_merge($versionData, $websiteData);

        /**
         * Serialize the data so we can encrypt it.
         */
        $serializedData = serialize($data);

        /**
         * Prepare the private key for encryption and get the IV (initialization vector) we'll use with mcrypt.
         *
         * @link http://www.php.net/manual/en/function.mcrypt-get-iv-size.php
         * @link http://www.php.net/manual/en/function.mcrypt-create-iv.php
         */
        $mcryptKey = pack('H*', $privateKey);
        $ivSize    = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv        = mcrypt_create_iv($ivSize, MCRYPT_RAND);

        /**
         * Encrypt the data.
         *
         * @link http://www.php.net/manual/en/function.mcrypt-encrypt.php
         */
        $encryptedData = mcrypt_encrypt(
            MCRYPT_RIJNDAEL_256,
            $mcryptKey,
            $serializedData,
            MCRYPT_MODE_CBC,
            $iv
        );

        /**
         * Prepend the IV, so the server can decrypt it later.
         * Contrary to what some people may believe, sending the IV with the encrypted data does NOT constitute a
         * security risk {@link http://www.php.net/manual/en/function.mcrypt-create-iv.php}.
         */
        $encryptedData = $iv . $encryptedData;
        $encryptedData = base64_encode($encryptedData);

        /**
         * Build the SOAP parameter array
         */
        $soapParams = array(
            'uniqueKey'    => $uniqueKey,
            'integrityKey' => sha1($serializedData . $privateKey),
            'data'         => $encryptedData,
        );

        /**
         * Send the request
         */
        $result = $this->call('updateStatistic', $soapParams);

        /**
         * Check if the request was successful
         */
        if (!is_array($result)
            || !isset($result['status'])
            || $result['status'] != self::SUCCESS_MESSAGE
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid updateStatistics response: %s', var_export($result, true)),
                'POSTNL-0082'
            );
        }

        /**
         * If a successful update has taken place we can confirm that the extension has been activated
         */
        $isActivated = Mage::getStoreConfig(self::XPATH_IS_ACTIVATED, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (!$isActivated || $isActivated == '1') {
            Mage::getModel('core/config')->saveConfig(self::XPATH_IS_ACTIVATED, 2);
        }

        return $result;
    }

    /**
     * Get the most recent statistics from the extension control system.
     *
     * @return array
     *
     * @throws Exception
     * @throws SoapFault
     * @throws TIG_PostNL_Exception
     */
    public function updateConfigSettings()
    {
        $result = $this->call('getSettings');

        if (!is_array($result)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid updateConfigSettings response: %s', var_export($result, true)),
                'POSTNL-0213'
            );
        }

        return $result;
    }

    /**
     * Gets information about the Magento version and edition as well as the version of the currently installed PostNL extension.
     *
     * @return array
     */
    protected function _getVersionData()
    {
        /**
         * Get Magento and PosTNL extension version numbers.
         *
         * @var Varien_Simplexml_Element $moduleConfig
         */
        $magentoVersion = Mage::getVersion();
        $moduleConfig = Mage::getConfig()->getModuleConfig('TIG_PostNL');
        $moduleVersion = (string) $moduleConfig->version;

        /**
         * Get the edition of the current Magento install. Possible options: Enterprise, Community
         *
         * N.B. Professional and Go editions are not supported at this time
         */
        $isEnterprise = Mage::helper('postnl')->isEnterprise();
        if ($isEnterprise === true) {
            $magentoEdition = 'Enterprise';
        } else {
            $magentoEdition = 'Community';
        }

        $versionData = array(
            'magentoVersion' => $magentoVersion,
            'moduleVersion'  => $moduleVersion,
            'magentoEdition' => $magentoEdition,
        );

        return $versionData;
    }

    /**
     * Creates the website array for the updateStatistics method
     *
     * @return array
     */
    protected function _getWebsites()
    {
        /**
         * @var Mage_Core_Model_Website $website
         */
        $websites = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $extensionEnabled = $website->getConfig(self::XPATH_ACTIVE);
            if (!$extensionEnabled) {
                continue;
            }

            $websites[] = array(
                'websiteId'                 => $website->getId(),
                'hostName'                  => $this->_getHostName($website),
                'amountOfShipments'         => $this->_getAmountOfShipments($website),
                'amountOfShipmentsStandard' => $this->_getAmountOfShipments($website, array('domestic')),
                'amountOfShipmentsPg'       => $this->_getAmountOfShipments($website, array('pg')),
                'amountOfShipmentsPa'       => $this->_getAmountOfShipments($website, array('pa')),
                'amountOfShipmentsPge'      => $this->_getAmountOfShipments($website, array('pge')),
                'amountOfShipmentsAvond'    => $this->_getAmountOfShipments($website, array('avond')),
                'amountOfShipmentsCod'      => $this->_getAmountOfShipments(
                    $website,
                    array(
                        'domestic_cod',
                        'pg_cod',
                        'pge_cod'
                    )
                ),
                'amountOfShipmentsBuspakje' => $this->_getAmountOfShipments($website, array('buspakje')),
                'merchantName'              => $this->_getMerchantName($website),
                'lastOrderDate'             => $this->_getLastOrderDate($website),
                'settings'                  => array(
                    'globalShipping'                 => $this->_getUsesGlobalShipping($website),
                    'splitAddress'                   => $this->_getUsesSplitAddress($website),
                    'postnlCheckout'                 => $this->_getUsesPostnlCheckout($website),
                    'postnlCheckoutWebshopId'        => $this->_getCheckoutWebshopId($website),
                    'customerNumber'                 => $this->_getCustomerNumber($website),
                    'useDeliveryOptions'             => $this->_getUseDeliveryOptions($website),
                    'useDeliveryDays'                => $this->_getUseDeliveryDays($website),
                    'useTimeframes'                  => $this->_getUseTimeframes($website),
                    'useAvond'                       => $this->_getUseAvond($website),
                    'usePg'                          => $this->_getUsePg($website),
                    'usePa'                          => $this->_getUsePa($website),
                    'usePge'                         => $this->_getUsePge($website),
                    'modeBuspakje'                   => $this->_getBuspakjeCalcMode($website),
                    'cod'                            => $this->_getCod($website),
                    'useMijnpakketLog'               => $this->_getUseMijnPakketLogin($website),
                    'usePostcodeCheck'               => $this->_getUsePostcodeCheck($website),
                    'useParcelwareExport'            => $this->_getUseParcelwareExport($website),
                    'automaticallySendTrackAndTrace' => $this->_getUseSendTrackAndTrace($website),
                    'modifiedTrackAndTrace'          => $this->_getUseModifiedTrackAndTrace($website),
                    'showShippingLabelOnPackingSlip' => $this->_getShowShippingLabelOnPackingSlip($website),
                ),
            );
        }

        $websiteData = array('websites' => $websites);
        return $websiteData;
    }

    /**
     * Get the email contact to which the unique- and privatekeys will be sent after activation
     *
     * @return string
     */
    protected function _getEmail()
    {
        $email = Mage::getStoreConfig(self::XPATH_GENERAL_EMAIL, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $email;
    }

    /**
     * Get the hostname of the admin area to use in the module activation procedure or the hostname of a specified website to
     * use with the updateStatistics method
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return string
     */
    protected function _getHostName($website = null)
    {
        /**
         * If no website ID is provided, get the current hostname. In most cases this will be the hostname of the admin
         * environment.
         */
        if ($website === null) {
            $hostName = Mage::helper('core/http')->getHttpHost();
            return $hostName;
        }

        /**
         * Get the website's base URL
         */
        $baseUrl = $website->getConfig(self::XPATH_UNSECURE_BASE_URL, $website->getId());

        /**
         * Parse the URL and get the host name
         */
        $urlParts = parse_url($baseUrl);
        $hostName = $urlParts['host'];

        return $hostName;
    }

    /**
     * Gets the unique key from system/config. Keys will be decrypted using Magento's encryption key.
     *
     * @return string
     */
    protected function _getUniqueKey()
    {
        $uniqueKey = Mage::getStoreConfig(self::XPATH_EXTENSIONCONTROL_UNIQUE_KEY, Mage_Core_Model_App::ADMIN_STORE_ID);
        $uniqueKey = Mage::helper('core')->decrypt($uniqueKey);

        $uniqueKey = trim($uniqueKey);

        return $uniqueKey;
    }

    /**
     * Gets the unique key from system/config. Keys will be decrypted using Magento's encryption key.
     *
     * @return string
     */
    protected function _getPrivateKey()
    {
        $privateKey = Mage::getStoreConfig(
            self::XPATH_EXTENSIONCONTROL_PRIVATE_KEY,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );
        $privateKey = Mage::helper('core')->decrypt($privateKey);

        $privateKey = trim($privateKey);

        return $privateKey;
    }

    /**
     * Get the number of PostNL shipments a specified website has sent
     *
     * @param Mage_Core_Model_Website $website
     * @param array|boolean           $shipmentTypes
     *
     * @return int
     */
    protected function _getAmountOfShipments($website, $shipmentTypes = false)
    {
        if ($shipmentTypes !== false && !is_array($shipmentTypes)) {
            $shipmentTypes = array($shipmentTypes);
        }

        $shipmentCollection = $this->_getShipmentCollection($website, $shipmentTypes);

        $amountOfShipments = $shipmentCollection->getSize();
        return $amountOfShipments;
    }

    /**
     * Gets the shipment collection for a specified website.
     *
     * @param Mage_Core_Model_Website $website
     * @param boolean|array           $shipmentTypes
     *
     * @return Mage_Sales_Model_Resource_Order_Shipment_Collection
     */
    protected function _getShipmentCollection($website, $shipmentTypes = false)
    {
        /**
         * Get a list of all storeIds associated with this website.
         *
         * @var Mage_Core_Model_Store_Group $group
         */
        $storeIds = array();
        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
            /**
             * @var Mage_Core_Model_Store $store
             */
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        }

        $resource = Mage::getSingleton('core/resource');

        /**
         * Get the shipment collection.
         */
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection');
        $shipmentCollection->addFieldToSelect('entity_id');

        $select = $shipmentCollection->getSelect();

        /**
         * Join sales_flat_order table.
         */
        $select->joinInner(
            array('order' => $resource->getTableName('sales/order')),
            '`main_table`.`order_id`=`order`.`entity_id`',
            array(
                'shipping_method' => 'order.shipping_method',
            )
        );

        /**
         * Join the tig_postnl_shipment table.
         */
        $select->joinInner(
            array('postnl_shipment' => $resource->getTableName('postnl_core/shipment')),
            '`main_table`.`entity_id`=`postnl_shipment`.`shipment_id`',
            array(
                'shipment_type' => 'postnl_shipment.shipment_type',
            )
        );

        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        $postnlShippingMethodsRegex = '';
        foreach ($postnlShippingMethods as $method) {
            if ($postnlShippingMethodsRegex) {
                $postnlShippingMethodsRegex .= '|';
            } else {
                $postnlShippingMethodsRegex .= '^';
            }

            $postnlShippingMethodsRegex .= "({$method})(_{0,1}[0-9]*)";
        }

        $postnlShippingMethodsRegex .= '$';
        $shipmentCollection->addFieldToFilter(
                               'order.shipping_method',
                               array(
                                   'regexp' => $postnlShippingMethodsRegex
                               )
                           )
                           ->addFieldToFilter(
                               'main_table.store_id',
                               array(
                                   'in' => $storeIds
                               )
                           );

        if ($shipmentTypes) {
            $shipmentCollection->addFieldToFilter('shipment_type', array('in', $shipmentTypes));
        }

        return $shipmentCollection;
    }

    /**
     * gets the last order date if any for this website
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return null|string
     */
    protected function _getLastOrderDate($website)
    {
        /**
         * Get a list of all storeIds associated with this website
         *
         * @var Mage_Core_Model_Store_Group $group
         */
        $storeIds = array();
        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
            /**
             * @var Mage_Core_Model_Store $store
             */
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        }

        /**
         * Get the order collection and filter it by store
         *
         * Resulting SQL:
         * SELECT `main_table`.`created_at`
         * FROM `sales_flat_order` AS `main_table`
         * WHERE
         * (
         *     `main_table`.`store_id` IN(
         *         {$storeIds}
         *     )
         * )
         * ORDER BY `created_at` DESC
         * LIMIT 1
         */
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection->addFieldToSelect('created_at')
                        ->addFieldToFilter('main_table.store_id', array('in' => $storeIds));

        $orderCollection->getSelect()
                        ->order('created_at DESC')
                        ->limit(1);

        /**
         * If the collection is empty, return false
         */
        if ($orderCollection->getSize() < 1) {
            return false;
        }

        /**
         * Get the created_at date from the only item in the collection.
         *
         * @var Mage_Sales_Model_Order $lastOrder
         */
        // @codingStandardsIgnoreStart
        $lastOrder = $orderCollection->getFirstItem();
        // @codingStandardsIgnoreEnd
        $createdAt = $lastOrder->getCreatedAt();
        $createdAt = Mage::getModel('core/date')->date('Y-m-d H:i:s', $createdAt);

        return $createdAt;
    }

    /**
     * Gets the merchant's name if set
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return string
     */
    protected function _getMerchantName($website)
    {
        $name = $website->getConfig(self::XPATH_CONTACT_NAME);

        return $name;
    }

    /**
     * Get whether a specified website uses global shipping
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUsesGlobalShipping($website)
    {
        /**
         * Get a list of supported product options and a list of global product options
         */
        $supportedProductOptions = $website->getConfig(self::XPATH_SUPPORTED_PRODUCT_OPTIONS);
        $supportedProductOptions = explode(',', $supportedProductOptions);

        $globalProductOptions = Mage::helper('postnl/cif')->getGlobalProductCodes();

        /**
         * Check each global product option if it's supported.
         */
        foreach ($globalProductOptions as $productOption) {
            if (in_array($productOption, $supportedProductOptions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the split_street setting for a specified website
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUsesSplitAddress($website)
    {
        $splitStreet = (bool) $website->getConfig(self::XPATH_SPLIT_STREET);

        return $splitStreet;
    }

    /**
     * Gets whether the website makes use of PostNL Checkout
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUsesPostnlCheckout($website)
    {
        $checkoutActive = (bool) $website->getConfig(self::XPATH_CHECKOUT_ACTIVE);

        return $checkoutActive;
    }

    /**
     * Gets the checkout webshop ID
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return string
     */
    protected function _getCheckoutWebshopId($website)
    {
        $webshopId = $website->getConfig(self::XPATH_CHECKOUT_WEBSHOP_ID);
        $webshopId = Mage::helper('core')->decrypt($webshopId);

        return $webshopId;
    }

    /**
     * Gets the CIF customer number
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return string
     */
    protected function _getCustomerNumber($website)
    {
        $webshopId = $website->getConfig(self::XPATH_CUSTOMER_NUMBER);

        return $webshopId;
    }

    /**
     * Gets whether delivery options are used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseDeliveryOptions($website)
    {
        $checkoutExtension = $this->_getCheckoutExtension($website);
        if (!$checkoutExtension || $checkoutExtension == 'other') {
            return false;
        }

        $useDeliveryoptions = (bool) $website->getConfig(self::XPATH_DELIVERY_OPTIONS_ACTIVE);

        return $useDeliveryoptions;
    }

    /**
     * Gets whether delivery days are used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseDeliveryDays($website)
    {
        if (!$this->_getUseDeliveryOptions($website)) {
            return false;
        }

        $useDeliveryDays = (bool) $website->getConfig(self::XPATH_ENABLE_DELIVERY_DAYS);

        return $useDeliveryDays;
    }

    /**
     * Gets whether time frames are used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseTimeframes($website)
    {
        if (!$this->_getUseDeliveryOptions($website)) {
            return false;
        }

        $useTimeframes = (bool) $website->getConfig(self::XPATH_ENABLE_TIMEFRAMES);

        return $useTimeframes;
    }

    /**
     * Gets whether evening delivery is used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseAvond($website)
    {
        if (!$this->_getUseDeliveryOptions($website)) {
            return false;
        }

        $useAvond = (bool) $website->getConfig(self::XPATH_ENABLE_EVENING_TIMEFRAMES);

        return $useAvond;
    }

    /**
     * Gets whether PG is used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUsePg($website)
    {
        if (!$this->_getUseDeliveryOptions($website)) {
            return false;
        }

        $usePg = (bool) $website->getConfig(self::XPATH_ENABLE_PAKJEGEMAK);

        return $usePg;
    }

    /**
     * Gets whether PA is used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUsePa($website)
    {
        if (!$this->_getUseDeliveryOptions($website)) {
            return false;
        }

        $usePa = (bool) $website->getConfig(self::XPATH_ENABLE_PAKKETAUTOMAAT_LOCATIONS);

        return $usePa;
    }

    /**
     * Gets whether PGE is used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUsePge($website)
    {
        if (!$this->_getUseDeliveryOptions($website)) {
            return false;
        }

        $usePge = (bool) $website->getConfig(self::XPATH_ENABLE_PAKJEGEMAK_EXPRESS);

        return $usePge;
    }

    /**
     * Gets the buspakje calculation mode.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return string
     */
    protected function _getBuspakjeCalcMode($website)
    {
        $useBuspakje = (bool) $website->getConfig(self::XPATH_USE_BUSPAKJE);
        if (!$useBuspakje) {
            return 'off';
        }

        $buspakjeCalcMode = $website->getConfig(self::XPATH_BUSPAKJE_CALCULATION_MODE);

        return $buspakjeCalcMode;
    }

    /**
     * Gets whether PostNL COD is used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getCod($website)
    {
        $cod = (bool) $website->getConfig(self::XPATH_COD_ACTIVE);

        return $cod;
    }

    /**
     * Gets whether MijnPakket login is used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseMijnPakketLogin($website)
    {
        if (!$this->_getUseDeliveryOptions($website)) {
            return false;
        }

        $useMijnPakketLogin = (bool) $website->getConfig(self::XPATH_MIJNPAKKET_LOGIN_ACTIVE);

        return $useMijnPakketLogin;
    }

    /**
     * Gets whether postcode check is active.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUsePostcodeCheck($website)
    {
        $checkoutExtension = $this->_getCheckoutExtension($website);
        if (!$checkoutExtension || $checkoutExtension == 'other') {
            return false;
        }

        $usePostcodeCheck = (bool) $website->getConfig(self::XPATH_USE_POSTCODE_CHECK);

        return $usePostcodeCheck;
    }

    /**
     * Get the currently used checkout extension for this website.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return mixed
     */
    protected function _getCheckoutExtension($website)
    {
        $checkoutExtension = $website->getConfig(self::XPATH_CHECKOUT_EXTENSION);

        return $checkoutExtension;
    }

    /**
     * Gets whether parcelware export is active.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseParcelwareExport($website)
    {
        $useParcelwareExport = (bool) $website->getConfig(self::XPATH_PARCELWARE_EXPORT_ACTIVE);

        return $useParcelwareExport;
    }

    /**
     * Gets whether the track&trace email is sent automatically.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseSendTrackAndTrace($website)
    {
        $useSendTrackAndTrace = (bool) $website->getConfig(self::XPATH_SEND_TRACK_AND_TRACE_EMAIL);

        return $useSendTrackAndTrace;
    }

    /**
     * Gets whether a modified track&trace email template is used.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getUseModifiedTrackAndTrace($website)
    {
        $template = $website->getConfig(self::XPATH_TRACK_AND_TRACE_EMAIL_TEMPLATE);
        if ($template != 'postnl_cif_labels_and_confirming_track_and_trace_email_template') {
            return true;
        }

        return false;
    }

    /**
     * Gets whether the packing slip includes the PostNl shipping label.
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return boolean
     */
    protected function _getShowShippingLabelOnPackingSlip($website)
    {
        $showShippingLabel = (bool) $website->getConfig(self::XPATH_SHOW_LABEL);

        return $showShippingLabel;
    }
}
