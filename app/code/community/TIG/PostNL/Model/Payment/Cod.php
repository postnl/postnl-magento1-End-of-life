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
class TIG_PostNL_Model_Payment_Cod extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Xpath to PostNL COD settings. N.B. the last part is missing.
     */
    const XPATH_COD_SETTINGS = 'postnl/cod';

    /**
     * This payment method's unique code.
     *
     * @var string
     */
    protected $_code = 'postnl_cod';

    /**
     * Cash On Delivery form block path.
     *
     * @var string
     */
    protected $_formBlockType = 'postnl_payment/form_cod';

    /**
     * Cash on delivery info block path.
     *
     * @var string
     */
    protected $_infoBlockType = 'postnl_payment/info';

    /**
     * Get instructions text from config.
     *
     * @return string
     */
    public function getInstructions()
    {
        $instructions = trim($this->getConfigData('instructions'));
        
        return $instructions;
    }

    /**
     * Checks whether PostNL COD is available.
     *
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $helper = Mage::helper('postnl/payment');

        /**
         * Make sure the quote is available.
         */
        if (is_null($quote)) {
            $helper->log(
                $helper->__('PostNL COD is not available, because the quote is empty.')
            );
            return false;
        }

        /**
         * COD is not available for virtual shipments.
         */
        if ($quote->isVirtual()) {
            $helper->log(
                $helper->__('PostNL COD is not available, because the order is virtual.')
            );
            return false;
        }

        /**
         * If COD is only available for PostNL shipping methods, we need to check if the shipping method is PostNL.
         */
        if (!(bool) $this->getConfigData('allow_for_non_postnl', $quote->getStoreId())) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();

            if (!in_array($shippingMethod, $postnlShippingMethods)) {
                $helper->log(
                       $helper->__('PostNL COD is not available, because the chosen shipping method is not PostNL.')
                );
                return false;
            }
        }

        $quoteId = $quote->getId();

        /**
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($quoteId, 'quote_id');
        if ($postnlOrder->getId() && $postnlOrder->getType() == 'PA') {
            $helper->log(
                $helper->__('PostNL COD is not available, because the chosen delivery option is PA.')
            );
            return false;
        }

        /**
         * Make sure all required fields are entered.
         */
        $codSettings = Mage::getStoreConfig(self::XPATH_COD_SETTINGS, Mage::app()->getStore()->getId());

        if (!isset($codSettings['account_name'])
            || !$codSettings['account_name']
            || !isset($codSettings['iban'])
            || !$codSettings['iban']
            || !isset($codSettings['bic'])
            || !$codSettings['bic']
        ) {
            $helper->log(
                $helper->__('PostNL COD is not available, because required fields are missing.')
            );
            return false;
        }

        /**
         * Finally, perform Magento's own checks.
         */
        $parentIsAvailable = parent::isAvailable($quote);
        if (!$parentIsAvailable) {
            $helper->log(
                $helper->__("PostNL COD is not available, because the abstract isAvailable() check returned 'false'")
            );
        }

        return $parentIsAvailable;
    }
}