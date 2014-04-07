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
     * Get mijnpakket profile access using a token.
     *
     * @return $this
     */
    public function getProfileAccessAction()
    {
        /**
         * This action may only be called using AJAX requests.
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        /**
         * Get the profile token and validate it.
         */
        $token = $this->getRequest()->getParam('token');
        try {
            $token = $this->_validateToken($token);
        } catch (Exception $e) {
            Mage::helper('postnl/mijnpakket')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        /**
         * Get profile data using the access token.
         */
        $cif = Mage::getModel('postnl_mijnpakket/cif');
        $response = $cif->getProfileAccess($token);

        /**
         * Parse the data into an array that Magento's checkout can use.
         */
        $profile = $response->Profiel;
        $billingData = Mage::getModel('postnl_mijnpakket/service')->parseBillingData($profile);

        /**
         * Save the data as the billing and shipping address.
         */
        $result = $this->getOnepage()->saveBilling($billingData, '');

        /**
         * Get the next step's html.
         */
        if (!isset($result['error'])) {
            $result['goto_section'] = 'shipping_method';
            $result['update_section'] = array(
                'name' => 'shipping-method',
                'html' => $this->_getShippingMethodsHtml()
            );

            $result['duplicateBillingInfo'] = 'true';
        }

        $json = Mage::helper('core')->jsonEncode($result);
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json')
             ->setBody($json);

        return $this;
    }

    /**
     * Get shipping method step html.
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout('checkout_onepage_index');

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
}