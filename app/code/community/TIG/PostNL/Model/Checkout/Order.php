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
 *
 * Class TIG_PostNL_Model_Checkout_Order
 *
 * @method string getConfirmDate()
 * @method TIG_PostNL_Model_Checkout_Order setConfirmDate(string $value)
 * @method int getIsActive()
 * @method TIG_PostNL_Model_Checkout_Order setIsActive(int $value)
 * @method string getToken()
 * @method TIG_PostNL_Model_Checkout_Order setToken(string $value)
 * @method string getShipmentCosts()
 * @method TIG_PostNL_Model_Checkout_Order setShipmentCosts(string $value)
 * @method string getProductCode()
 * @method TIG_PostNL_Model_Checkout_Order setProductCode(string $value)
 * @method int getIsPakjeGemak()
 * @method TIG_PostNL_Model_Checkout_Order setIsPakjeGemak(int $value)
 * @method int getIsCanceled()
 * @method TIG_PostNL_Model_Checkout_Order setIsCanceled(int $value)
 * @method string getDeliveryDate()
 * @method TIG_PostNL_Model_Checkout_Order setDeliveryDate(string $value)
 * @method int getQuoteId()
 * @method TIG_PostNL_Model_Checkout_Order setQuoteId(int $value)
 * @method string getType()
 * @method TIG_PostNL_Model_Checkout_Order setType(string $value)
 * @method int getOrderId()
 * @method TIG_PostNL_Model_Checkout_Order setOrderId(int $value)
 * @method int getEntityId()
 * @method string getMobilePhoneNumber()
 * @method TIG_PostNL_Model_Checkout_Order setEntityId(int $value)
 * @method TIG_PostNL_Model_Checkout_Order setOrder(Mage_Sales_Model_Order $value)
 * @method TIG_PostNL_Model_Checkout_Order setQuote(Mage_Sales_Model_Quote $value)
 * @method int getIsPakketautomaat()
 * @method TIG_PostNL_Model_Checkout_Order setIsPakketautomaat(int $value)
 */
class TIG_PostNL_Model_Checkout_Order extends Mage_Core_Model_Abstract
{
    /**
     * Regexes for mobile phone number validation.
     */
    const MOBILE_PHONE_NUMBER_REGEX              = '/^(((\+31|0|0031)6){1}[1-9]{1}[0-9]{7})$/i';
    const MOBILE_PHONE_NUMBER_PREFIX_REGEX       = '/^(06|00316){1}(.*?)$/i';
    const MOBILE_PHONE_NUMBER_PREFIX_REPLACEMENT = '+316$2';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'postnl_order';

    public function _construct()
    {
        $this->_init('postnl_checkout/order');
    }

    /**
     * Gets the order associated with this PostNL Checkout Order
     *
     * @return Mage_Sales_Model_Order | null
     */
    public function getOrder()
    {
        if ($this->getData('order')) {
            return $this->getData('order');
        }

        if (!$this->getOrderId()) {
            return null;
        }

        $order = Mage::getModel('sales/order')->load($this->getOrderId());

        $this->setOrder($order);
        return $order;
    }

    /**
     * Gets the quote associated with this PostNL Checkout Order
     *
     * @return Mage_Sales_Model_Quote | null
     */
    public function getQuote()
    {
        if ($this->getData('quote')) {
            return $this->getData('quote');
        }

        if (!$this->getQuoteId()) {
            return null;
        }

        $order = Mage::getModel('sales/quote')->load($this->getQuoteId());

        $this->setQuote($order);
        return $order;
    }

    /**
     * Alias for magic getToken()
     *
     * @return string
     */
    public function getOrderToken()
    {
        return $this->getToken();
    }

    /**
     * Alias for magic getQuoteId()
     *
     * @return int
     */
    public function getExtRef()
    {
        return $this->getQuoteId();
    }

    /**
     * Set a mobile phone number.
     *
     * @param      $phoneNumber
     * @param bool $skipValidation
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function setMobilePhoneNumber($phoneNumber, $skipValidation = false)
    {
        if ($skipValidation) {
            $this->setData('mobile_phone_number', $phoneNumber);
            return $this;
        }

        /**
         * Validate the phone number. It should be a valid Dutch mobile phone number.
         */
        $validPhoneNumber = preg_match(self::MOBILE_PHONE_NUMBER_REGEX, $phoneNumber);
        if (!$validPhoneNumber) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid mobile phone number supplied: %s', $phoneNumber),
                'POSTNL-0149'
            );
        }

        /**
         * Parse the number so that it starts with '+316'.
         */
        $parsedPhoneNumber = preg_replace(
            self::MOBILE_PHONE_NUMBER_PREFIX_REGEX,
            self::MOBILE_PHONE_NUMBER_PREFIX_REPLACEMENT,
            $phoneNumber
        );

        $this->setData('mobile_phone_number', $parsedPhoneNumber);
        return $this;
    }

    /**
     * Cancels the PostNL order
     *
     * @return TIG_PostNL_Model_Checkout_Order
     *
     * @throws TIG_PostNL_Exception
     */
    public function cancel()
    {
        $helper = Mage::helper('postnl');
        if (!$this->getOrderId()) {
            throw new TIG_PostNL_Exception(
                $helper->__('This PostNL Checkout order cannot be cancelled: it has no associated magento order.'),
                'POSTNL-0046'
            );
        }

        if ($this->getIsCanceled()) {
            throw new TIG_PostNL_Exception(
                $helper->__('This PostNL Checkout order cannot be cancelled: it has already been canceled.'),
                'POSTNL-0047'
            );
        }

        $cif = Mage::getModel('postnl_checkout/cif');
        $cif->updateOrder($this, true);

        $this->setIsCanceled(true);

        return $this;
    }

    /**
     * Sets new PostNL Orders to active before saving
     *
     * @return Mage_Core_Model_Abstract::_beforeSave();
     */
    protected function _beforeSave()
    {
        if ($this->isObjectNew()) {
            $this->setIsActive(1);
        }

        return parent::_beforeSave();
    }
}
