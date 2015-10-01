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
 * @method boolean                                         hasCanShowNotification()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setCanShowNotification(bool $value)
 * @method boolean                                         hasCanShowCreateAccountLink()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setCanShowCreateAccountLink(bool $value)
 * @method boolean                                         hasCanShowAppLink()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setCanShowAppLink(bool $value)
 * @method boolean                                         hasPublicWebshopId()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setPublicWebshopId(string $value)
 * @method boolean                                         hasOrder()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setOrder(mixed $value)
 * @method boolean                                         hasShippingAddress()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setShippingAddress(mixed $value)
 * @method boolean                                         hasCreateAccountUrl()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setCreateAccountUrl(string $value)
 * @method boolean                                         hasCreateAccountBaseUrl()
 * @method TIG_PostNL_Block_Mijnpakket_AccountNotification setCreateAccountBaseUrl(string $value)
 */
class TIG_PostNL_Block_Mijnpakket_AccountNotification extends TIG_PostNL_Block_Core_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_mijnpakket_accountnotification';

    /**
     * Base URL to create a new MijnPakket account.
     */
    const CREATE_ACCOUNT_BASE_URL_XPATH = 'postnl/delivery_options/create_account_base_url';

    /**
     * The webshop's public webshop ID is used to secure communications with PostNL's servers.
     */
    const XPATH_PUBLIC_WEBSHOP_ID = 'postnl/cif/public_webshop_id';

    /**
     * Xpaths determining various options regarding the MijnPakket notification.
     */
    const XPATH_SHOW_CREATE_MIJNPAKKET_ACCOUNT_LINK = 'postnl/delivery_options/show_create_mijnpakket_account_link';
    const XPATH_SHOW_MIJNPAKKET_APP_LINK            = 'postnl/delivery_options/show_mijnpakket_app_link';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/mijnpakket/account_notification.phtml';

    /**
     * Checks if showing the MijnPakket notification is allowed.
     *
     * @return bool|mixed
     */
    public function getCanShowNotification()
    {
        if ($this->hasCanShowNotification()) {
            return $this->_getData('can_show_notification');
        }

        $canShowNotification = Mage::helper('postnl/mijnpakket')->canShowMijnpakketNotification();

        $this->setCanShowNotification($canShowNotification);
        return $canShowNotification;
    }

    /**
     * Checks if showing the MijnPakket create account link is allowed.
     *
     * @return bool|mixed
     */
    public function getCanShowCreateAccountLink()
    {
        if ($this->hasCanShowCreateAccountLink()) {
            return $this->_getData('can_show_create_account_link');
        }

        $storeId = Mage::app()->getStore()->getId();
        $canShowLink = Mage::getStoreConfigFlag(self::XPATH_SHOW_CREATE_MIJNPAKKET_ACCOUNT_LINK, $storeId);

        $this->setCanShowCreateAccountLink($canShowLink);
        return $canShowLink;
    }

    /**
     * Checks if showing the MijnPakket app link is allowed.
     *
     * @return bool|mixed
     */
    public function getCanShowAppLink()
    {
        if ($this->hasCanShowAppLink()) {
            return $this->_getData('can_show_app_link');
        }

        $storeId = Mage::app()->getStore()->getId();
        $canShowLink = Mage::getStoreConfigFlag(self::XPATH_SHOW_MIJNPAKKET_APP_LINK, $storeId);

        $this->setCanShowAppLink($canShowLink);
        return $canShowLink;
    }

    /**
     * Get the current public webshop ID.
     *
     * @return string
     */
    public function getPublicWebshopId()
    {
        if ($this->hasPublicWebshopId()) {
            return $this->_getData('public_webshop_id');
        }

        $publicWebshopId = Mage::getStoreConfig(self::XPATH_PUBLIC_WEBSHOP_ID, Mage::app()->getStore()->getId());

        $this->setPublicWebshopId($publicWebshopId);
        return $publicWebshopId;
    }

    /**
     * Gets the last placed order.
     *
     * @return Mage_Sales_Model_Order|boolean
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->_getData('order');
        }

        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if (!$orderId) {
            return false;
        }

        $order = Mage::getModel('sales/order')->load($orderId);

        $this->setOrder($order);
        return $order;
    }

    /**
     * Gets the shipping address of the last placed order.
     *
     * @return Mage_Sales_Model_Order_Address|boolean
     */
    public function getShippingAddress()
    {
        if ($this->hasShippingAddress()) {
            return $this->_getData('shipping_address');
        }

        $order = $this->getOrder();
        if (!$order) {
            $this->setShippingAddress(false);
            return false;
        }

        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            $this->setShippingAddress(false);
            return false;
        }

        $this->setShippingAddress($shippingAddress);
        return $shippingAddress;
    }

    /**
     * Gets the base create MijnPakket account URL.
     *
     * @return string
     */
    public function getCreateAccountBaseUrl()
    {
        if ($this->hasCreateAccountBaseUrl()) {
            return $this->_getData('create_account_base_url');
        }

        $baseUrl = Mage::getStoreConfig(self::CREATE_ACCOUNT_BASE_URL_XPATH);

        $this->setCreateAccountBaseUrl($baseUrl);
        return $baseUrl;
    }

    /**
     * Form the create MijnPakket account url based on the hardcoded base URL and a dynamic set of parameters.
     *
     * @return string
     */
    public function getCreateAccountUrl()
    {
        if ($this->hasCreateAccountUrl()) {
            return $this->_getData('create_account_url');
        }

        $baseUrl = $this->getCreateAccountBaseUrl();

        /**
         * Add the optional params to the base url.
         */
        $urlParams   = $this->_getUrlParams();
        $queryString = http_build_query($urlParams);

        $createAccountUrl = $baseUrl . $queryString;

        $this->setCreateAccountUrl($createAccountUrl);
        return $createAccountUrl;
    }

    /**
     * Gets all the URL parameters to create a MijnPakket account. While alle parameters are optional, the more we add,
     * the less the customer will have to add manually later on.
     *
     * @return array
     */
    protected function _getUrlParams()
    {
        /**
         * get the webshop's public ID. This should be the only parameter that is always available.
         */
        $publicWebshopId = $this->getPublicWebshopId();

        /**
         * If no order or shipping address is available, return just the public webshop ID.
         */
        $order = $this->getOrder();
        $shippingAddress = $this->getShippingAddress();
        if (!$order || !$shippingAddress) {
            return array('webshopPublicId' => $publicWebshopId);
        }

        $helper = Mage::helper('postnl/mijnpakket');

        /**
         * Get the basic order parameters.
         */
        $firstname = $shippingAddress->getFirstname();
        $params = array(
            'webshopPublicId' => $publicWebshopId,
            'initials'        => $helper->getInitials($firstname),
            'firstName'       => $firstname,
            'middleName'      => $shippingAddress->getMiddlename(),
            'lastName'        => $shippingAddress->getLastname(),
            'email'           => $shippingAddress->getEmail(),
            'postalCode'      => str_replace(' ', '', $shippingAddress->getPostcode()),
            'business'        => 'P',
        );

        /**
         * If this address has a VAT ID, it's probably a B2B client.
         */
        $vat = $shippingAddress->getVatId();
        if ($vat) {
            $params['business'] = 'Z';
        }

        /**
         * Optionally add the dob.
         */
        $dob = $shippingAddress->getDob();
        if ($dob) {
            $dob = new DateTime($dob);
            $params['birthDate'] = $dob->format('d-m-Y');
        }

        /**
         * If we have a mobile phone number for this address, add that as well.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($order->getId(), 'order_id');
        if ($postnlOrder->getId() && $postnlOrder->getMobilePhoneNumber()) {
            $params['mobileNumber'] = $postnlOrder->getMobilePhoneNumber();
        }

        /**
         * Get the split address data of this order.
         */
        $streetData = false;
        try {
            $streetData = Mage::helper('postnl/cif')->getStreetData($order->getStoreId(), $shippingAddress, false);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        /**
         * If we have split address data, add the housenumber and housenumber extension.
         */
        if ($streetData && isset($streetData['housenumber'])) {
            $params['houseNumber'] = $streetData['housenumber'];

            if (isset($streetData['housenumberExtension']) && !empty($streetData['housenumberExtension'])) {
                $params['houseNumberSuffix'] = $streetData['housenumberExtension'];
            }
        }

        return $params;
    }

    /**
     * Check if MijnPakket notification can be shown before rendering the template.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getCanShowNotification()) {
            return '';
        }

        return parent::_toHtml();
    }
}