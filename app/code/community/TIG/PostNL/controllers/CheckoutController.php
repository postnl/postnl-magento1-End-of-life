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
 */
class TIG_PostNL_CheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * XML path of show_summary_page setting.
     */
    const XPATH_SHOW_SUMMARY_PAGE = 'postnl/checkout/show_summary_page';

    /**
     * Order class variable.
     *
     * @var Mage_Sales_Model_Order | void
     */
    protected $_order;

    /**
     * Gets the stored order object.
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $order = $this->_order;
        return $order;
    }

    /**
     * Sets an order object.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Checks if the PostNL webservice is available for the current account.
     *
     * @return $this
     */
    public function pingAction()
    {
        /** @var TIG_PostNL_Helper_Checkout $helper */
        $helper = Mage::helper('postnl/checkout');

        if (!$this->_isPostnlCheckoutActive()) {
            $this->getResponse()
                 ->setBody('NOK');
            return $this;
        }

        try {
            /** @var TIG_PostNL_Model_Checkout_Cif $cif */
            $cif = Mage::getModel('postnl_checkout/cif');
            $result = $cif->ping();
        } catch (Exception $e) {
            $helper->logException($e);

            $errorMessage = $helper->__('PostNL Checkout is not available due to the following reasons:')
                          . PHP_EOL
                          . $helper->__('Ping status request resulting in an error.');

            $helper->log($errorMessage);

            $this->getResponse()
                 ->setBody('NOK');
            return $this;
        }

        if (!$result || $result == 'NOK') {
            $errorMessage = $helper->__('PostNL Checkout is not available due to the following reasons:')
                          . PHP_EOL
                          . $helper->__('Ping status response indicated PostNL Checkout is currently not available.');

            $helper->log($errorMessage);

            $this->getResponse()
                 ->setBody('NOK');
            return $this;
        }

        $this->getResponse()
             ->setBody('OK');

        return $this;
    }

    /**
     * Prepares a new PostNL Checkout Order.
     *
     * @return $this
     */
    public function prepareOrderAction()
    {
        if (!$this->_isPostnlCheckoutActive()) {
            $this->getResponse()
                 ->setBody('error');
            return $this;
        }

        try {
            /** @var Mage_Checkout_Model_Session $session */
            $session = Mage::getSingleton('checkout/session');
            /** @noinspection PhpUndefinedMethodInspection */
            $session->setCartWasUpdated(false);
            $quote = $session->getQuote();

            /**
             * Set the quote's shipping method and collect it's totals
             */
            /** @var TIG_PostNL_Helper_Carrier $carrierHelper */
            $carrierHelper = Mage::helper('postnl/carrier');
            $shippingMethod = $carrierHelper->getCurrentPostnlShippingMethod();
            $shippingAddress = $quote->getShippingAddress();

            if (!$shippingAddress->getCountryId()) {
                $shippingAddress->setCountryId('NL');
            }

            $shippingAddress->setPostcode('')
                            ->setCollectShippingRates(true)
                            ->collectShippingRates()
                            ->setShippingMethod($shippingMethod)
                            ->save();

            $quote->save();

            /**
             * Update the cart
             */
            /** @var Mage_Checkout_Model_Cart $cart */
            $cart = Mage::getSingleton('checkout/cart');
            $cart->init();
            $cart->save();

            /**
             * Prepare the order with PostNL
             */
            /** @var TIG_PostNL_Model_Checkout_Cif $cif */
            $cif = Mage::getModel('postnl_checkout/cif');
            $result = $cif->prepareOrder($quote);

            /**
             * Retrieve the order token used to identify the order with PostNL and the checkout URL.
             *
             * @var StdClass $result
             */
            $orderToken  = $result->Checkout->OrderToken;
            $checkoutUrl = $result->Checkout->Url;

            /**
             * Turn these values into a JSON encoded associative array
             */
            $responseArray = array(
                'checkoutUrl' => $checkoutUrl,
                'orderToken'  => $orderToken,
            );

            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');
            $response = $coreHelper->jsonEncode($responseArray);

            /**
             * Save a new PostNL order containing the current quote ID as well as the received order token.
             *
             * @var TIG_PostNL_Model_Core_Order $postnlOrder
             */
            $postnlOrder = Mage::getModel('postnl_core/order')->load($quote->getId(), 'quote_id');
            $postnlOrder->setQuoteId($quote->getId())
                        ->setToken($orderToken)
                        ->setIsActive(1)
                        ->save();
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json')
             ->setBody('[' . $response . ']');

        return $this;
    }

    /**
     * Shows a summary of the PostNL Checkout order before the user confirms it.
     *
     * @return $this
     */
    public function summaryAction()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * Validate the quote.
         */
        $quoteIsValid = $this->_validateQuote($quote);
        if (!$quoteIsValid || !$this->_isPostnlCheckoutActive()) {
            $this->_redirect('checkout/cart');
            return $this;
        }

        /**
         * Check if showing the checkout summary page is allowed.
         */
        $showSummarypage = Mage::getStoreConfigFlag(self::XPATH_SHOW_SUMMARY_PAGE);
        if (!$showSummarypage) {
            $this->_redirect('checkout/cart');
            return $this;
        }

        try {
            /**
             * Get the order details from CIF for the order the customer just placed.
             */
            /** @var TIG_PostNL_Model_Checkout_Cif $cif */
            $cif = Mage::getModel('postnl_checkout/cif');
            $orderDetails = $cif->readOrder();

            /**
             * Update the quote with the received order details.
             */
            /** @var TIG_PostNL_Model_Checkout_Service $service */
            $service = Mage::getModel('postnl_checkout/service');
            $service->setQuote($quote)
                    ->updateQuoteAddresses($orderDetails)
                    ->updateQuotePayment($orderDetails, true, true) // Only set the payment method, not all possible
                                                                    // fields.
                    ->updateQuoteCustomer($orderDetails)
                    ->updatePostnlOrder($orderDetails);

            Mage::register('current_quote', $quote);

            /**
             * Load the layout
             */
            $this->loadLayout();

            $layout = $this->getLayout();

            /**
             * If the chosen payment method has a form block, add it to the layout
             */
            $paymentMethod = $quote->getPayment()->getMethodInstance();
            $formBlockType = $paymentMethod->getFormBlockType();
            if ($formBlockType) {
                /**
                 * @var Mage_Payment_Block_Form $formBlock
                 */
                $formBlock = $layout->createBlock($formBlockType);
                /** @noinspection PhpUndefinedMethodInspection */
                $formBlock->setMethod($paymentMethod);
                $layout->getBlock('postnl_checkout_summary')->setChild('payment_method_form', $formBlock);
            }

            /**
             * Initialize customer and checkout session messages
             */
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('checkout/session');

            /**
             * Set the page title and render the layout
             */
            /** @noinspection PhpUndefinedMethodInspection */
            $layout->getBlock('head')->setTitle($this->__('PostNL Checkout Summary'));
            $this->renderLayout();
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('checkout/session', 'POSTNL-0021', 'error',
                'An error occurred while processing your order. Please try again.'
            );

            $this->_redirect('checkout/cart');
            return $this;
        }

        return $this;
    }

    /**
     * Finishes the checkout process and asks the payment method to finish the transaction
     *
     * @return $this
     */
    public function finishCheckoutAction()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        /** @var TIG_PostNL_Helper_Checkout $helper */
        $helper = Mage::helper('postnl/checkout');

        $quoteIsValid = $this->_validateQuote($quote);
        if (!$quoteIsValid || !$this->_isPostnlCheckoutActive()) {
            $this->_redirect('checkout/cart');
            return $this;
        }

        /**
         * First get the order details from CIF and process the chosen addresses
         */
        try {
            /** @var TIG_PostNL_Model_Checkout_Cif $cif */
            $cif = Mage::getModel('postnl_checkout/cif');
            $orderDetails = $cif->readOrder($quote);

            /** @var TIG_PostNL_Model_Checkout_Service $service */
            $service = Mage::getModel('postnl_checkout/service');
            $service->setQuote($quote)
                    ->updateQuoteAddresses($orderDetails)
                    ->updateQuoteCustomer($orderDetails);
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('checkout/session', 'POSTNL-0021', 'error',
                $this->__(
                    'An error occurred while processing your order.'
                    . 'Please try again. '
                    . 'if this problem persists, please contact the webshop owner.'
                )
            );

            $helper->restoreQuote($quote);

            $this->_redirect('checkout/cart');
            return $this;
        }

        /**
         * Next we process the chosen payment method
         */
        $skipUpdatePayment = false;
        $data = $this->getRequest()->getPost('payment', array());
        $data = $this->_validatePaymentData($data);

        if ($data) {
            /**
             * If we have payment method data, process it
             */
            try {
                $service->updateQuotePayment($data, false);

                $skipUpdatePayment = true;
            } catch (Mage_Payment_Exception $e) {
                $helper->addExceptionSessionMessage('checkout/session', $e);

                $this->_redirect('*/*/summary');
                return $this;
            } catch (Mage_Core_Exception $e) {
                $helper->addExceptionSessionMessage('checkout/session', $e);

                $this->_redirect('*/*/summary');
                return $this;
            } catch (Exception $e) {
                $helper->logException($e);
                $helper->addSessionMessage('checkout/session', 'POSTNL-0022', 'error',
                    $this->__('Unable to set Payment Method.')
                );

                $this->_redirect('*/*/summary');
                return $this;
            }
        }

        /**
         * Next we update the quote's payment if we didn't get to do that in the previous step and then place the order.
         * Also we need to process any chosen communication options.
         */
        try {
            if ($skipUpdatePayment === false) {
                $service->updateQuotePayment($orderDetails);
            }

            $service->updatePostnlOrder($orderDetails);

            /**
             * Create the order.
             */
            $order = $service->saveOrder();

            $service->confirmPostnlOrder();

            /**
             * Parse any possible communication options.
             */
            $this->_parseCommunicationOptions($orderDetails, $order);
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('checkout/session', 'POSTNL-0021', 'error',
                $this->__(
                    'An error occurred while processing your order. Please try again.  if this problem persists, ' .
                    'please contact the webshop owner.'
                )
            );

            $this->_redirect('checkout/cart');
            return $this;
        }

        /**
         * Finally we redirect the customer to the success page or payment page.
         */

        /**
         * Get the redirect URL from the payment method. If none exists, redirect to the order success page.
         */
        $paymentMethod = $order->getPayment()->getMethodInstance();

        /** @noinspection PhpUndefinedMethodInspection */
        $redirectUrl = $paymentMethod->getCheckoutRedirectUrl();
        if(empty($redirectUrl)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $redirectUrl = $paymentMethod->getOrderPlaceRedirectUrl();
        }

        if(empty($redirectUrl)) {
            $redirectUrl = 'checkout/onepage/success';
            $this->_redirect($redirectUrl);
        } else {
            $this->_redirectUrl($redirectUrl);
        }

        return $this;
    }

    /**
     * Cancels the checkout and disables the PostNL order
     *
     * @return $this
     */
    public function cancelAction()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        $quoteIsValid = $this->_validateQuote($quote);
        if (!$quoteIsValid || !$this->_isPostnlCheckoutActive()) {
            $this->_redirect('checkout/cart');
            return $this;
        }

        /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($quote->getId(), 'quote_id');
        $postnlOrder->setIsActive(false)
                    ->save();

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $helper->addSessionMessage('checkout/session', 'POSTNL-0023', 'notice',
            $this->__('Your order has been cancelled. Please try again.')
        );

        $this->_redirect('checkout/cart');
        return $this;
    }

    /**
     * Parses any communication options that may have been selected
     *
     * @param StdClass $orderDetails
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    protected function _parseCommunicationOptions($orderDetails, $order)
    {
        if (!isset($orderDetails->CommunicatieOpties)
            || !is_object($orderDetails->CommunicatieOpties)
            || !isset($orderDetails->CommunicatieOpties->ReadOrderResponseCommunicatieOptie)
        ) {
            return $this;
        }

        /**
         * Get the selected communication options and process them
         */
        $communicationOptions = $orderDetails->CommunicatieOpties->ReadOrderResponseCommunicatieOptie;

        foreach ($communicationOptions as $option) {
            $this->_processCommunicationOption($option, $order);
        }

        return $this;
    }

    /**
     * Processes selected communication options
     *
     * @param StdClass $option
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    protected function _processCommunicationOption($option, $order)
    {
        $code = $option->Code;

        /**
         * If a remark has been entered, add it as a history comment to the order
         */
        if ($code == 'REMARK') {
            $remark = $this->__(
                'The customer left the following remark: %s',
                Mage::helper('core')->escapeHtml($option->Text)
            );

            /** @noinspection PhpUndefinedMethodInspection */
            $order->addStatusHistoryComment($remark)
                  ->save();

            return $this;
        }

        /**
         * If the customer has checked the subscribe to newsletter checkbox, subscribe him to the newsletter
         */
        if ($code == 'NEWS') {
            $customerEmail = $order->getCustomerEmail();

            /**
             * Attempt to load the subscriber if he exists
             */
            /** @var Mage_Newsletter_Model_Subscriber $newsletter */
            $newsletter = Mage::getModel('newsletter/subscriber');
            $newsletter->loadByEmail($customerEmail);

            /**
             * If the customer is already subscribed we don't need to do anything
             */
            if ($newsletter->isSubscribed()) {
                return $this;
            }

            /**
             * Subscribe the customer
             */
            $newsletter->subscribe($customerEmail);

            return $this;
        }

        return $this;
    }

    /**
     * Validate payment method data. Validation of other data is the responsibility of the chosen payment method as we
     * simply do not know what data we can expect.
     *
     * @param array $data
     *
     * @return array|bool
     */
    protected function _validatePaymentData($data)
    {
        if (!isset($data['method'])) {
            return false;
        }

        $method = $data['method'];
        /** @var Mage_Payment_Model_Config $paymentConfig */
        $paymentConfig = Mage::getSingleton('payment/config');
        $availablePaymentMethods = array_keys($paymentConfig->getActiveMethods());

        /**
         * Validate that the method is a string and is listed among available payment methods.
         */
        $stringValidator  = new Zend_Validate_Alpha(false);
        $inArrayValidator = new Zend_Validate_InArray(array('haystack' => $availablePaymentMethods));

        if (!$stringValidator->isValid($method) || !$inArrayValidator->isValid($method)) {
            return false;
        }

        return $data;
    }

    /**
     * Checks if PostNL Checkout is active
     *
     * @return boolean
     */
    protected function _isPostnlCheckoutActive()
    {
        /** @var TIG_PostNL_Helper_Checkout $helper */
        $helper = Mage::helper('postnl/checkout');
        $isActive = $helper->isCheckoutActive();
        return $isActive;
    }

    /**
     * Checks if a quote is (still) valid.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param boolean $addErrors
     *
     * @return boolean
     */
    protected function _validateQuote($quote, $addErrors = true)
    {
        /**
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($quote->getId(), 'quote_id');

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * Check if the quote is active.
         */
        if (!$quote->getIsActive()) {
            if ($addErrors) {
                $helper->addSessionMessage('checkout/session', 'POSTNL-0024', 'error',
                    $this->__('Unfortunately the checkout process cannot be finished. Please try again.')
                );
            }

            $postnlOrder->setIsActive(false)->save();
            return false;
        }

        /**
         * Check if a valid PostNL order exists for this quote.
         */
        if (!$postnlOrder->getIsActive()
            || !$postnlOrder->getId()
            || !$postnlOrder->getToken()
        ) {
            if ($addErrors) {
                $helper->addSessionMessage('checkout/session', 'POSTNL-0025', 'error',
                    $this->__('Unfortunately no PostNL Checkout order could be found. Please try again.')
                );
            }

            return false;
        }

        /**
         * Make sure the cart hasn't changed since we started the checkout process.
         */
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        /** @noinspection PhpUndefinedMethodInspection */
        if ($session->getCartWasUpdated(true)) {
            if ($addErrors) {
                $helper->addSessionMessage('checkout/session', 'POSTNL-0026', 'error',
                    $this->__('It seems your cart has been changed since you started the checkout process. Please try again.')
                );
            }

            $postnlOrder->setIsActive(false)->save();
            return false;
        }

        /**
         * Check if the quote actually has any items.
         */
        /** @var Mage_Checkout_Helper_Cart $cartHelper */
        $cartHelper = Mage::helper('checkout/cart');
        if ($cartHelper->getItemsCount() < 1) {
            if ($addErrors) {
                $helper->addSessionMessage('checkout/session', 'POSTNL-0112', 'error',
                    $this->__('Your shopping cart is empty. Please add a product and try again.')
                );
            }

            $postnlOrder->setIsActive(false)->save();
            return false;
        }

        return true;
    }
}
