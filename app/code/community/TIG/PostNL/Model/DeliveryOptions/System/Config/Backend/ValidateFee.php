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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean                                                            hasIsIncludingTax()
 * @method TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee setIsIncludingTax(boolean $value)
 * @method boolean                                                            hasMockShippingAddress()
 * @method TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee setMockShippingAddress(Mage_Customer_Model_Address $value)
 */
class TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee extends Mage_Core_Model_Config_Data
{
    /**
     * Min and max values for the fee.
     */
    const FEE_MIN_AMOUNT = 0;
    const FEE_MAX_AMOUNT = 2;

    /**
     * @return boolean
     */
    public function getIsIncludingTax()
    {
        if ($this->hasIsIncludingTax()) {
            return $this->_getData('is_including_tax');
        }

        $includingTax = Mage::getSingleton('tax/config')->shippingPriceIncludesTax();

        $this->setIsIncludingTax($includingTax);
        return $includingTax;
    }

    /**
     * @return Mage_Customer_Model_Address
     */
    public function getMockShippingAddress()
    {
        if ($this->hasMockShippingAddress()) {
            return $this->_getData('mock_shipping_address');
        }

        /**
         * @var Mage_Customer_Model_Address $mockShippingAddress
         */
        $mockShippingAddress = Mage::getModel('customer/address');
        /** @noinspection PhpUndefinedMethodInspection */
        $mockShippingAddress->setCountryId('NL')
                            ->setPostcode('1000AA');

        $this->setMockShippingAddress($mockShippingAddress);
        return $mockShippingAddress;
    }

    /**
     * @param mixed $fee
     *
     * @throws TIG_PostNL_Exception
     *
     * @return boolean
     */
    public function validateFee($fee)
    {
        $fee = (float) $fee;

        if (abs($fee) < 0.00001) {
            $this->setValue(0);

            return true;
        }

        /**
         * If the fee is including tax, make sure it falls within the specified parameters.
         */
        $isIncludingTax = $this->getIsIncludingTax();
        if ($isIncludingTax
            && ($fee > self::FEE_MAX_AMOUNT || $fee < self::FEE_MIN_AMOUNT)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Invalid fee amount entered: %s incl. VAT. Please enter a value between 0.00 and 2.00 EUR incl. '
                    . 'VAT.',
                    $fee
                ),
                'POSTNL-0153'
            );
        } elseif($isIncludingTax) {
            return true;
        }

        /**
         * If the fee is excluding tax, calculate it with the tax and validate it.
         */
        $shippingAddress = $this->getMockShippingAddress();

        $feeIncludingTax = Mage::helper('tax')->getShippingPrice($fee, true, $shippingAddress, null, 0);
        if ($feeIncludingTax > self::FEE_MAX_AMOUNT || $feeIncludingTax < self::FEE_MIN_AMOUNT) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Invalid fee amount entered: %s incl. VAT. Please enter a value between 0.00 and 2.00 EUR incl. '
                    . 'VAT.',
                    $feeIncludingTax
                ),
                'POSTNL-0153'
            );
        }

        return true;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $this->validateFee($value);

        return parent::_beforeSave();
    }
}