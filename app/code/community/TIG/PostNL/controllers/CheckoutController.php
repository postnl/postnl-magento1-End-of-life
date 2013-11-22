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
     * Checks if the PostNL webservice is available for the current account
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function pingAction()
    {
        $cif = Mage::getModel('postnl_checkout/cif');
        $result = $cif->ping();
        
        if (!$result || $result == 'NOK') {
            $this->getResponse()->setBody('NOK');
            return $this;
        }
        
        $this->getResponse()->setBody('OK');
        return $this;
    }
    
    /**
     * Prepares a new PostNL Checkout Order
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function prepareOrderAction()
    {
        /**
         * Prepare the order with PostNL
         */
        $cif = Mage::getModel('postnl_checkout/cif');
        $result = $cif->prepareOrder();
        
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
        
        $response = json_encode($responseArray);
        
        /**
         * Save a new PostNL order containing the current quote ID as well as the recieved order token
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $postnlOrder = Mage::getModel('postnl_checkout/order');
        $postnlOrder->load($quote->getId(), 'quote_id') //load the order in case it aleady exists
                    ->setQuoteId($quote->getId())
                    ->setToken($orderToken)
                    ->save();
        
        /**
         * Return the result as a json response
         */
        $this->getResponse()->setHeader('Content-type', 'application/x-json');
        $this->getResponse()->setBody('[' . $response . ']');
        return $this;
    }
    
    /**
     * Shows a summary of the PostNL Checkout order before the user confirms it
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function summaryAction()
    {
        $cif = Mage::getModel('postnl_checkout/cif');
        $orderDetails = $cif->readOrder();
        
        $service = Mage::getModel('postnl_checkout/service');
        $service->updateQuote($orderDetails);
    }
    
    /**
     * Finishes the checkout process and asks the payment method to finish the transaction
     * 
     * @return TIG_PostNL_CheckoutController
     */
    public function finishCheckoutAction()
    {
        
    }
}
