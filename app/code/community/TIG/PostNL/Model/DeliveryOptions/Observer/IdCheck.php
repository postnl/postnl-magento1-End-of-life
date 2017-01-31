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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
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
     * @var null|Mage_Sales_Model_Quote_Address
     */
    protected $_validator = null;

    /**
     * Validates the ID Check data.
     *
     * @param $observer
     *
     * @return $this
     */
    public function validate($observer = null)
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        $shipmentType = $this->getHelper()->getQuoteIdCheckType($quote);
        if (!$shipmentType) {
            return array(
                'error' => false,
                'message' => null,
            );
        }

        if ($observer !== null) {
            /** @var Mage_Sales_Model_Quote_Address $validator */
            $this->_validator = $observer->getAddress();
        }

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');

        $post = Mage::app()->getRequest()->getPost('billing_postnl_idcheck');
        if ($post !== null && $shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_ID) {
            /**
             * Check the document type.
             */
            if (!isset($post['type']) || empty($post['type'])) {
                return $this->error($this->getHelper()->__('Please provide a document type'));
            } else {
                /** @var TIG_PostNL_Helper_DeliveryOptions_IDCheck $helper */
                $helper = $this->getHelper('postnl/deliveryOptions_iDCheck');

                if (!$helper->isValidOption($post['type'])) {
                    return $this->error($this->getHelper()->__('Please provide a valid document type'));
                }
            }

            /**
             * Check the document number.
             */
            if (!isset($post['number']) || empty($post['number'])) {
                return $this->error($this->getHelper()->__('Please provide a document number'));
            }

            /**
             * Check the expiration date.
             */
            if (!isset($post['expiration_date_full']) || empty($post['expiration_date_full'])) {
                return $this->error($this->getHelper()->__('Please provide a expiration date'));
            }
        }

        $customer = Mage::getSingleton('customer/session');
        $post = Mage::app()->getRequest()->getPost('billing');
        if (
            $post !== null &&
            $shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_BIRTHDAY &&
            !$customer->isLoggedIn()
        ) {
            if (
                isset($post['day']) && !empty($post['day']) &&
                isset($post['month']) && !empty($post['month']) &&
                isset($post['year']) && !empty($post['year']) &&
                (!isset($post['dob']) || empty($post['dob']))
            ) {
                $post['dob'] = $post['year'] . '-' . $post['month'] . '-' . $post['year'];
            }

            if (!isset($post['dob']) || empty($post['dob'])) {
                return $this->error($this->getHelper()->__('Please provide a valid birthday'));
            }
        }

        return array(
            'error' => false,
            'message' => null,
        );
    }

    /**
     * @param $error
     *
     * @return $this|array
     */
    public function error($error)
    {
        if ($this->_validator !== null) {
            $this->_validator->addError($error);

            return $this;
        } else {
            return array(
                'error' => true,
                'message' => $error,
            );
        }
    }

    /**
     * Saves some extra data after the saveBilling call.
     *
     * @return $this
     */
    public function saveData()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        $shipmentType = $this->getHelper()->getQuoteIdCheckType($quote);
        if (!$shipmentType) {
            return $this;
        }

        $service = $this->getServiceModel();

        /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
        $postnlOrder = $service->getPostnlOrder();

        if (!$postnlOrder->getId()) {
            $postnlOrder->setQuoteId($quote->getId())
                ->setOrderId(null)
                ->setIsActive(true)
                ->setIsPakjeGemak(false)
                ->setIsPakketautomaat(false)
                ->setProductCode(false)
                ->setMobilePhoneNumber(false, true)
                ->setType($shipmentType)
                ->setExpectedDeliveryTimeStart(false)
                ->setExpectedDeliveryTimeEnd(false);
        }

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');
        if ($shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_BIRTHDAY) {
            if (!Mage::getSingleton('eav/config')->getAttribute('customer', 'dob')->getIsVisible()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                $data = Mage::app()->getRequest()->getPost('billing');
                if (isset($data['dob'])) {
                    $post = $data;
                }

                if (!isset($post)) {
                    $post = Mage::app()->getRequest()->getPost();
                }

                if (isset($post['dob'])) {
                    $quote->setCustomerDob($post['dob']);
                    $quote->save();

                    if ($customer && $customer->getId()) {
                        $customer->setData('dob', $post['dob']);
                        $customer->save();
                    }
                }
            }
        } elseif ($shipmentType == $deliveryOptionsHelper::IDCHECK_TYPE_ID) {
            $post = Mage::app()->getRequest()->getPost('billing_postnl_idcheck');

            $postnlOrder->setIdcheckType($post['type']);
            $postnlOrder->setIdcheckNumber($post['number']);
            $postnlOrder->setIdcheckExpirationDate($post['expiration_date_full']);
        }

        $postnlOrder->save();
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return bool
     */
    public function validateCustomerData(Varien_Event_Observer $observer)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$customer || !$customer->getId()) {
            return true;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $dob = $customer->getDob();
        if (trim($dob) != '') {
            return true;
        }

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = $this->getHelper();
        if (!$helper->quoteIsBirthdayCheck()) {
            return true;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Checkout_OnepageController $controller */
        $controller = $observer->getControllerAction();
        Mage::getSingleton('customer/session')->addError($helper->__('The Date of Birth is required.'));
        $controller->setRedirectWithCookieCheck('customer/account/edit');
        $controller->setFlag('', $controller::FLAG_NO_DISPATCH, true);

        return false;
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
