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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_MijnpakketController extends Mage_Core_Controller_Front_Action
{
    /**
     * Regular expression used to validate tokens.
     */
    const TOKEN_REGEX = '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/';

    /**
     * @var Mage_Checkout_Model_Type_Onepage
     */
    protected $_onepage;

    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $_checkoutSession;

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        if ($this->_onepage) {
            return $this->_onepage;
        }

        $onepage = Mage::getSingleton('checkout/type_onepage');

        $this->_onepage = $onepage;
        return $onepage;
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        if ($this->_checkoutSession) {
            return $this->_checkoutSession;
        }

        $session = Mage::getSingleton('checkout/session');

        $this->_checkoutSession = $session;
        return $session;
    }

    /**
     * Checks if this controller was called from OneStepCheckout.
     *
     * @return bool
     */
    public function isOsc()
    {
        $isOsc = $this->getRequest()->getparam('isOsc');
        if ($isOsc) {
            return true;
        }

        return false;
    }

    /**
     * Get MijnPakket profile access using a token.
     *
     * @return $this
     */
    public function getProfileAccessAction()
    {
        $helper = Mage::helper('postnl/mijnpakket');

        /**
         * This action may only be called using AJAX requests.
         */
        if (!$this->getRequest()->isAjax()) {
            $helper->log(
                $helper->__('Not allowed: ' . var_export($this->getRequest()->isAjax(), true))
            );
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        /**
         * If the session is expired, return a 403 HTTP status code.
         */
        if ($this->_expireAjax()) {
            $helper->log(
                $helper->__('Ajax expired.')
            );
            return $this;
        }

        /**
         * Get profile data.
         */
        try {
            $profileData = $this->_getProfileData();

            /**
             * Save the data as the billing and shipping address and get the result.
             */
            $result = $this->_formResultArray($profileData);
        } catch (Exception $e) {
            Mage::helper('postnl/mijnpakket')->logException($e);
            $helper->log(
                $helper->__('Exception: ' . $e->getMessage())
            );

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        if ($result === false) {
            $helper->log(
                $helper->__('Result empty: ' . var_export($result, true))
            );
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }
        $helper->log(
            $helper->__('Result : ' . var_export($result, true))
        );

        /**
         * Return the result as JSON.
         */
        $json = Mage::helper('core')->jsonEncode($result);
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json')
             ->setBody($json);

        return $this;
    }

    /**
     * Gets billing data. If we have stored data in the checkout session, use that. Otherwise get new data from CIF.
     *
     * @return array
     */
    protected function _getProfileData()
    {
        $checkoutSession = $this->getCheckoutSession();
        $savedData = $checkoutSession->getPostnlMijnpakketData();

        /**
         * If we still have stored data, return that.
         */
        if ($savedData) {
            return $savedData;
        }

        /**
         * Get the profile token and validate it.
         */
        $token = $this->getRequest()->getParam('token');
        $token = $this->_validateToken($token);

        /**
         * Otherwise we need to get data from CIF using the access token.
         */
        $cif = Mage::getModel('postnl_mijnpakket/cif');
        $response = $cif->getProfileAccess($token);

        /**
         * Parse the data into an array that Magento's checkout can use.
         */
        $profile = $response->Profiel;
        $profileData = Mage::getModel('postnl_mijnpakket/service')->parseProfileData($profile);

        return $profileData;
    }

    /**
     * Form the result array.
     *
     * @param array $billingData
     *
     * @throws TIG_PostNL_Exception
     *
     * @return array|boolean
     */
    protected function _formResultArray($billingData)
    {
        $helper = Mage::helper('postnl/mijnpakket');
        $helper->log(
            'MijnPakket data received: '
            . var_export($billingData, true)
        );

        /**
         * Save the address data and get the result.
         */
        $result = $this->getOnepage()->saveBilling($billingData, '');

        /**
         * If we encountered an error, throw the error as an exception.
         */
        if (isset($result['error'])) {
            $helper->log(
                'Error received while saving billing data: '
                . var_export($result, true)
            );

            if (isset($result['message'])) {
                $message = $result['message'];
            } else {
                $message = $this->__('An unknown error has occurred.');
            }

            $this->getCheckoutSession()->setPostnlMijnpakketData(false);

            throw new TIG_PostNL_Exception($message);
        }

        /**
         * Store the data in the customer's checkout session.
         */
        $this->getCheckoutSession()->setPostnlMijnpakketData($billingData);

        $result['origData'] = $billingData;

        if ($this->isOsc()) {
            return $result;
        }

        /**
         * Set required parameters in the result array, including the next step's html.
         */
        $result['duplicateBillingInfo'] = 'true';
        $result['allow_sections']       = array('billing', 'shipping');
        $result['goto_section']         = 'shipping_method';
        $result['update_section']       = array(
            'name' => 'shipping-method',
            'html' => $this->_getShippingMethodsHtml()
        );

        return $result;
    }

    /**
     * Get shipping method step html.
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();

        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');

        $layout->generateXml();
        $layout->generateBlocks();

        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Validate the input token specified.
     *
     * @param string $token
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _validateToken($token)
    {
        $tokenValidator = new Zend_Validate_Regex(self::TOKEN_REGEX);
        if (!$tokenValidator->isValid($token)) {
            throw new TIG_PostNL_Exception(
                $this->__('Invalid token specified: %s', $token),
                'POSTNL-0157'
            );
        }

        return $token;
    }

    /**
     * Validate ajax request and redirect on failure.
     *
     * @see Mage_Checkout_OnepageController::_ajaxRedirectResponse()
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        $onepage = $this->getOnepage();
        $quote   = $onepage->getQuote();

        $helper = Mage::helper('postnl/mijnpakket');

        if (!$quote->hasItems()) {
            $helper->log(
                $helper->__('The customer was redirected to the cart because the quote was empty.')
            );

            $this->_ajaxRedirectResponse();
            return true;
        }

        if ($quote->getHasError()) {
            $helper->log(
               $helper->__(
                   'The customer was redirected to the cart because the quote had the following error(s): %s.',
                   implode(PHP_EOL, $quote->getErrors())
               )
            );

            $this->_ajaxRedirectResponse();
            return true;
        }

        if ($quote->getIsMultiShipping()) {
            $helper->log(
                $helper->__('The customer was redirected to the cart because the quote is multishipping.')
            );

            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    /**
     * Send Ajax redirect response.
     *
     * @see Mage_Checkout_OnepageController::_ajaxRedirectResponse()
     *
     * @return $this
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
             ->setHeader('HTTP/1.1', '403 Session Expired')
             ->setHeader('Login-Required', 'true')
             ->sendResponse();

        return $this;
    }
}