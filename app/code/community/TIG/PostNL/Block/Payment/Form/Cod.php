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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Payment_Form_Cod extends Mage_Payment_Block_Form
{
    /**
     * Xpath to the 'allow_for_buspakje' configuration setting.
     */
    const XPATH_ALLOW_FOR_BUSPAKJE = 'payment/postnl_cod/allow_for_buspakje';

    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_payment_form_cod';

    /**
     * @var string
     */
    protected $_instructions;

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/payment/checkout/form/cod.phtml';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getMethod()->getInstructions();
        }
        return $this->_instructions;
    }

    /**
     * Check if the PostNL COD payment method may be shown for letter box parcel orders.
     *
     * @return boolean
     */
    public function canShowForBuspakje()
    {
        /**
         * Check the configuration setting.
         */
        $showForBuspakje = Mage::getStoreConfigFlag(self::XPATH_ALLOW_FOR_BUSPAKJE, Mage::app()->getStore()->getId());
        if ($showForBuspakje) {
            return true;
        }

        /**
         * Check if the buspakje calculation mode is set to automatic.
         */
        $helper = Mage::helper('postnl');
        $calculationMode = $helper->getBuspakjeCalculationMode();
        if ($calculationMode != 'automatic') {
            return true;
        }

        /**
         * Check if the current quote fits as a letter box parcel.
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$helper->fitsAsBuspakje($quote->getAllItems())) {
            return true;
        }

        return false;
    }

    /**
     * Renders a template block. Also throws 2 events based on the current event prefix.
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_to_html_before');

        $html = parent::_toHtml();

        Mage::dispatchEvent($this->_eventPrefix . '_to_html_after');
        return $html;
    }
}