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
class TIG_PostNL_CheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order class variable
     * 
     * @var Mage_Sales_Model_Order | void
     */
    protected $_order;
    
    /**
     * Gets the stored order object
     * 
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $order = $this->_order;
        return $order;
    }
    
    /**
     * Sets an order object
     * 
     * @param Mage_Sales_Model_Order $order
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }
    
    /**
     * Checks if the PostNL webservice is available for the current account
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function pingAction()
    {
        try {
            $cif = Mage::getModel('postnl_checkout/cif');
            $result = $cif->ping();
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            
            $this->getResponse()
                 ->setBody('NOK');
            return $this;
        }
        
        if (!$result || $result == 'NOK') {
            $this->getResponse()
                 ->setBody('NOK');
            return $this;
        }
        
        $this->getResponse()
             ->setBody('OK');
        return $this;
    }
    
    /**
     * Prepares a new PostNL Checkout Order
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function prepareOrderAction()
    {
        try {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            
            /**
             * Set the quote's shipping method and collect it's totals
             */
            $shippingMethod = Mage::helper('postnl/carrier')->getCurrentPostnlShippingMethod();
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
            $cart = Mage::getSingleton('checkout/cart');
            $cart->init();
            $cart->save();
            
            /**
             * Prepare the order with PostNL
             */
            $cif = Mage::getModel('postnl_checkout/cif');
            $result = $cif->prepareOrder($quote);
            
            /**
             * Retrieve the order token used to identify the order with PostNL and the checkout URL
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
            
            $response = Mage::helper('core')->jsonEncode($responseArray);
            
            /**
             * Save a new PostNL order containing the current quote ID as well as the recieved order token
             */
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $postnlOrder = Mage::getModel('postnl_checkout/order');
            $postnlOrder->load($quote->getId(), 'quote_id') //load the order in case it aleady exists
                        ->setQuoteId($quote->getId())
                        ->setToken($orderToken)
                        ->save();
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            
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
     * Shows a summary of the PostNL Checkout order before the user confirms it
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function summaryAction()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $postnlOrder = Mage::getModel('postnl_checkout/order')->load($quote->getId(), 'quote_id');
        if (!$quote->getIsActive() 
            || !$postnlOrder->getId() 
            || !$postnlOrder->getToken()
        ) {
            $this->_redirect('checkout/cart');
            return $this;
        }
        
        try {
            $cif = Mage::getModel('postnl_checkout/cif');
            $orderDetails = $cif->readOrder();
            
            $service = Mage::getModel('postnl_checkout/service');
            $service->setQuote($quote)
                    ->updateQuoteAddresses($orderDetails)
                    ->updateQuotePayment($orderDetails)
                    ->updateQuoteCustomer($orderDetails);
            
            Mage::register('current_quote', $quote);
            
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            
            $layout = $this->getLayout();
            
            $paymentMethod = $quote->getPayment()->getMethodInstance();
            $formBlockType = $paymentMethod->getFormBlockType();
            if ($formBlockType) {
                $formBlock = $layout->createBlock($formBlockType)->setMethod($paymentMethod);
                $layout->getBlock('postnl_checkout_summary')->setChild('payment_method_form', $formBlock);
            }
            
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('checkout/session');
            
            $layout->getBlock('head')->setTitle($this->__('PostNL Checkout Summary'));
            $this->renderLayout();
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            
            Mage::getSingleton('checkout/session')->addError(
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
     * @return TIG_PostNL_CheckoutController
     */
    public function finishCheckoutAction()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $postnlOrder = Mage::getModel('postnl_checkout/order')->load($quote->getId(), 'quote_id');
        if (!$quote->getIsActive() 
            || !$postnlOrder->getId() 
            || !$postnlOrder->getToken()
        ) {
            $this->_redirect('checkout/cart');
            return $this;
        }
        
        /**
         * First get the order details from CIF and process the chosen addresses
         */
        try {
            $cif = Mage::getModel('postnl_checkout/cif');
            $orderDetails = $cif->readOrder($quote);
            
            $service = Mage::getModel('postnl_checkout/service');
            $service->setQuote($quote)
                    ->updateQuoteAddresses($orderDetails)
                    ->updateQuoteCustomer($orderDetails);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('checkout/session')->addError(
                $this->__('An error occurred while processing your order. Please try again. if this problem persists, please contact the webshop owner.')
            );
            
            Mage::helper('postnl/checkout')->restoreQuote($quote);
            
            $this->_redirect('checkout/cart');
            return $this;
        }
        
        /**
         * Next we process the chosen payment method
         */
        $skipUpdatePayment = false;
        $data = $this->getRequest()->getPost('payment', array());
        if ($data) {
            /**
             * If we have payment method data, process it
             */
            try {
                $service->updateQuotePayment($data, false);
            
                $skipUpdatePayment = true;
            } catch (Mage_Payment_Exception $e) {
                Mage::getSingleton('checkout/session')->addError(
                   $e->getMessage()
                );
                
                $this->_redirect('*/*/summary');
                return $this;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('checkout/session')->addError(
                   $e->getMessage()
                );
                
                $this->_redirect('*/*/summary');
                return $this;
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
                Mage::getSingleton('checkout/session')->addError(
                   $this->__('Unable to set Payment Method.')
                );
                
                $this->_redirect('*/*/summary');
                return $this;
            }
        }
        
        /**
         * Next we update the quote's payment if we didn't get to do that in the previous step and then place the order. Also we
         * need to process any chosen communication options
         */
        try {     
            if ($skipUpdatePayment === false) {
                $service->updateQuotePayment($orderDetails);
            }
                    
            $service->updatePostnlOrder($orderDetails);
            
            /**
             * Create the order
             */
            $order = $service->saveOrder();
            
            $service->confirmPostnlOrder();

            /**
             * Parse any possible communication options
             */
            $this->_parseCommunicationOptions($orderDetails);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('checkout/session')->addError(
                $this->__('An error occurred while processing your order. Please try again. if this problem persists, please contact the webshop owner.')
            );
            
            Mage::helper('postnl/checkout')->restoreQuote($quote);
            
            $this->_redirect('checkout/cart');
            return $this;
        }

        /**
         * Finally we redirect the customer to the success page or payment page
         */
        
        /**
         * Get the redirect URL from the payment method. If none exists, redirect to the order success page
         */
        $paymentMethod = $order->getPayment()->getMethodInstance();
        
        $redirectUrl = $paymentMethod->getCheckoutRedirectUrl();
        if(empty($redirectUrl)) {
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
     * Parses any communication options that may have been selected
     * 
     * @param StdClass $orderDetails
     * 
     * @return TIG_PostNL_CheckoutController
     */
    protected function _parseCommunicationOptions($orderDetails)
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
            $this->_processCommunicationOption($option);
        }
        
        return $this;
    }
    
    /**
     * Processes selected communication options
     * 
     * @param StdClass $option
     * 
     * @return TIG_PostNL_CheckoutController
     */
    protected function _processCommunicationOption($option)
    {
        $order = $this->getOrder();
        $code = $option->Code;
        
        /**
         * If a remark has been entered, add it as a history comment to the order
         */
        if ($code == 'REMARK') {
            $remark = $this->__(
                'The customer left the following remark: %s', 
                Mage::helper('core')->escapeHtml($option->Text)
            );
            
            $order->addStatusHistoryComment($remark)
                  ->save();
                  
            return $this;
        }
        
        /**
         * If the customer has checked the subscrive to newsletter checkbox, subscribe him to the newsletter
         */
        if ($code == 'NEWS') {
            $customerEmail = $order->getCustomerEmail();
            
            /**
             * Attempt to load the subscriber if he exists
             */
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
}
