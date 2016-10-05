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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_DeliveryOptions_Observer_IdCheck
{
    /**
     * @var array
     */
    protected $_helpers = array();

    /**
     * @var null
     */
    protected $_serviceModel = null;

    /**
     * Validates the ID Check data.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function validate(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        $shipmentType = $this->getHelper()->getQuoteIdCheckType($quote);
        if (!$shipmentType) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote_Address $validator */
        $validator = $observer->getAddress();

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');
        if ($shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_ID) {
            $post = Mage::app()->getRequest()->getPost('postnl_idcheck');

            /**
             * Check the document type.
             */
            if (!isset($post['type']) || empty($post['type'])) {
                $validator->addError($this->getHelper()->__('Please provide a document type'));

                return $this;
            } else {
                /** @var TIG_PostNL_Helper_DeliveryOptions_IDCheck $helper */
                $helper = $this->getHelper('postnl/deliveryOptions_iDCheck');

                if (!$helper->isValidOption($post['type'])) {
                    $validator->addError($this->getHelper()->__('Please provide a valid document type'));

                    return $this;
                }
            }

            /**
             * Check the document number.
             */
            if (!isset($post['number']) || empty($post['number'])) {
                $validator->addError($this->getHelper()->__('Please provide a document number'));

                return $this;
            }

            /**
             * Check the expiration date.
             */
            if (!isset($post['expiration_date_full']) || empty($post['expiration_date_full'])) {
                $validator->addError($this->getHelper()->__('Please provide a expiration date'));

                return $this;
            }
        }

        if ($shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_BIRTHDAY) {
            $post = Mage::app()->getRequest()->getPost('billing');

            if (!isset($post['dob']) || empty($post['dob'])) {
                $validator->addError($this->getHelper()->__('Please provide a valid birthday'));

                return $this;
            }
        }

        return $this;
    }

    /**
     * Saves some extra data after the saveBilling call.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function saveData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        $shipmentType = $this->getHelper()->getQuoteIdCheckType($quote);
        if (!$shipmentType) {
            return $this;
        }

        $service = $this->getServiceModel();
        $service->saveDeliveryOption(array(
            'date' => '05-10-2016',
            'type' => $shipmentType,
            'costs' => '',
        ));

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');
        if ($shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_BIRTHDAY) {
            if (!Mage::getSingleton('eav/config')->getAttribute('customer', 'dob')->getIsVisible()) {
                $post = Mage::app()->getRequest()->getPost('billing');
                $quote->setCustomerDob($post['dob']);
                $quote->save();
            }
        } elseif ($shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_ID) {
            $post = Mage::app()->getRequest()->getPost('postnl_idcheck');

            /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
            $postnlOrder = $service->getPostnlOrder();

            $postnlOrder->setIdcheckType($post['type']);
            $postnlOrder->setIdcheckNumber($post['number']);
            $postnlOrder->setIdcheckExpirationDate($post['expiration_date_full']);
            $postnlOrder->save();
        }
    }

    /**
     * Mainly used for testing purposes.
     *
     * @param null $helper
     *
     * @return mixed
     */
    protected function getHelper($helper = null)
    {
        if ($helper === null) {
            $helper = 'postnl';
        }

        if (!array_key_exists($helper, $this->_helpers)) {
            $this->_helpers[$helper] = Mage::helper($helper);
        }

        return $this->_helpers[$helper];
    }

    /**
     * Mainly used for testing purposes.
     *
     * @return TIG_PostNL_Model_DeliveryOptions_Service
     */
    protected function getServiceModel()
    {
        if ($this->_serviceModel !== null) {
            return $this->_serviceModel;
        }

        return Mage::getModel('postnl_deliveryoptions/service');
    }
}