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
class TIG_PostNL_Model_ExtensionControl_Webservices extends TIG_PostNL_Model_ExtensionControl_Webservices_Abstract
{
    /**
     * XML paths for security keys
     */
    const XML_PATH_EXTENSIONCONTROL_UNIQUE_KEY  = 'postnl/general/unique_key';
    const XML_PATH_EXTENSIONCONTROL_PRIVATE_KEY = 'postnl/general/private_key';

    /**
     * XML paths for webshop activation settings
     */
    const XML_PATH_GENERAL_EMAIL     = 'postnl/general/email';
    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    /**
     * XML paths for setting statistics
     */
    const XML_PATH_SUPPORTED_PRODUCT_OPTIONS = 'postnl/cif_product_options/supported_product_options';
    const XML_PATH_SPLIT_STREET              = 'postnl/cif_address/split_street';
    const XML_PATH_CHECKOUT_ACTIVE           = 'postnl/checkout/active';
    const XML_PATH_CHECKOUT_WEBSHOP_ID       = 'postnl/cif/webshop_id';
    const XML_PATH_CONTACT_NAME              = 'postnl/cif/contact_name';
    const XML_PATH_CUSTOMER_NUMBER           = 'postnl/cif/customer_number';

    /**
     * XML path to extension activation setting
     */
    const XML_PATH_ACTIVE = 'postnl/general/active';

    /**
     * XML path to 'is_activated' flag
     */
    const XML_PATH_IS_ACTIVATED = 'postnl/general/is_activated';

    /**
     * Expected success response
     */
    const SUCCESS_MESSAGE = 'success';

    /**
     * Activates the webshop. This will trigger a private key and a unique key to be sent to the specified e-mail, which must be
     * entered into system config by the merchant in order to finish the activation process.
     *
     * @param boolean|string $email
     *
     * @return TIG_PostNL_Model_ExtensionControl_Webservices
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

        return $this;
    }

    /**
     * Updates the ExtensionControl server with updated statistics.
     *
     * @param boolean $forceUpdate
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
         * Check if the request was succesfull
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
         * If a succesfull update has taken place we can confirm that the extension has been activated
         */
        $isActivated = Mage::getStoreConfig(self::XML_PATH_IS_ACTIVATED, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (!$isActivated || $isActivated == '1') {
            Mage::getModel('core/config')->saveConfig(self::XML_PATH_IS_ACTIVATED, 2);
        }

        return $result;
    }

    /**
     * Gets information about the Magento vrsion and edition as well as the version of the currently installed PosTNL extension.
     *
     * @return array
     */
    protected function _getVersionData()
    {
        /**
         * Get Magento and PosTNL extension version numbers
         */
        $magentoVersion = Mage::getVersion();
        $moduleVersion = (string) Mage::getConfig()->getModuleConfig('TIG_PostNL')->version;

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
        $websites = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $extensionEnabled = $website->getConfig(self::XML_PATH_ACTIVE);
            if (!$extensionEnabled) {
                continue;
            }

            $websites[] = array(
                'websiteId'         => $website->getId(),
                'hostName'          => $this->_getHostName($website),
                'amountOfShipments' => $this->_getAmountOfShipments($website),
                'merchantName'      => $this->_getMerchantName($website),
                'lastOrderDate'     => $this->_getLastOrderDate($website),
                'settings'          => array(
                    'globalShipping'          => $this->_getUsesGlobalShipping($website),
                    'splitAddress'            => $this->_getUsesSplitAddress($website),
                    'postnlCheckout'          => $this->_getUsesPostnlCheckout($website),
                    'postnlCheckoutWebshopId' => $this->_getCheckoutWebshopId($website),
                    'customerNumber'          => $this->_getCustomerNumber($website),
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
        $email = Mage::getStoreConfig(self::XML_PATH_GENERAL_EMAIL, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $email;
    }

    /**
     * Get thje hostname of the admin area to use in the module activation procedure or the hostname of a specified website to
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
        $baseUrl = $website->getConfig(self::XML_PATH_UNSECURE_BASE_URL, $website->getId());

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
        $uniqueKey = Mage::getStoreConfig(self::XML_PATH_EXTENSIONCONTROL_UNIQUE_KEY, Mage_Core_Model_App::ADMIN_STORE_ID);
        $uniqueKey = Mage::helper('core')->decrypt($uniqueKey);

        return $uniqueKey;
    }

    /**
     * Gets the unique key from system/config. Keys will be decrypted using Magento's encryption key.
     *
     * @return string
     */
    protected function _getPrivateKey()
    {
        $privateKey = Mage::getStoreConfig(self::XML_PATH_EXTENSIONCONTROL_PRIVATE_KEY, Mage_Core_Model_App::ADMIN_STORE_ID);
        $privateKey = Mage::helper('core')->decrypt($privateKey);

        return $privateKey;
    }

    /**
     * Get the number of PostNL shipments a specified website has sent
     *
     * @param Mage_Core_Model_Website $website
     *
     * @return int
     */
    protected function _getAmountOfShipments($website)
    {
        /**
         * Get a list of all storeIds associated with this website
         */
        $storeIds = array();
        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        }

        $resource = Mage::getSingleton('core/resource');

        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();

        /**
         * Get the shipment collection
         */
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection');
        $shipmentCollection->addFieldToSelect('entity_id');

        $select = $shipmentCollection->getSelect();

        /**
         * Join sales_flat_order table
         */
        $select->joinInner(
            array('order' => $resource->getTableName('sales/order')),
            '`main_table`.`order_id`=`order`.`entity_id`',
            array(
                'shipping_method'      => 'order.shipping_method',
            )
        );

        $shipmentCollection->addFieldToFilter('`shipping_method`', array('in' => $postnlShippingMethods))
                           ->addFieldToFilter('`main_table`.`store_id`', array('in' => $storeIds));

        $amountOfShipments = $shipmentCollection->getSize();
        return $amountOfShipments;
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
         */
        $storeIds = array();
        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
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
                        ->addFieldToFilter('`main_table`.`store_id`', array('in' => $storeIds));

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
         * Get the created_at date from the only item in the collection
         */
        $lastOrder = $orderCollection->getFirstItem();
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
        $name = $website->getConfig(self::XML_PATH_CONTACT_NAME);

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
        $supportedProductOptions = $website->getConfig(self::XML_PATH_SUPPORTED_PRODUCT_OPTIONS);
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
        $splitStreet = (bool) $website->getConfig(self::XML_PATH_SPLIT_STREET);

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
        $checkoutActive = (bool) $website->getConfig(self::XML_PATH_CHECKOUT_ACTIVE);

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
        $webshopId = $website->getConfig(self::XML_PATH_CHECKOUT_WEBSHOP_ID);
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
        $webshopId = $website->getConfig(self::XML_PATH_CUSTOMER_NUMBER);

        return $webshopId;
    }
}
