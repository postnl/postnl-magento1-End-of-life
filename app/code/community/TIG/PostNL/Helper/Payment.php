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
 * @category    TIG
 * @package     TIG_PostNL
 * @copyright   Copyright (c) ${YEAR} Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * @link        http://store.totalinternetgroup.nl/tig/postnl.html
 */
class TIG_PostNL_Helper_Payment extends TIG_PostNL_Helper_Data
{
    /**
     * Xpath to PostNL COD fee tax class.
     */
    const XPATH_COD_FEE_TAX_CLASS = 'tax/classes/postnl_cod_fee';

    /**
     * Xpath to PostNL COD fee label setting.
     */
    const XPATH_COD_FEE_LABEL = 'payment/postnl_cod/fee_label';

    /**
     * Debug log file for PostNL payments.
     */
    const POSTNL_DEBUG_LOG_FILE = 'TIG_PostNL_Payment_Debug.log';

    /**
     * An array of PostNL COD payment methods.
     *
     * @var array
     */
    protected $_codPaymentMethods = array(
        'postnl_cod',
    );

    /**
     * Gets an array of PostNL COD payment methods.
     *
     * @return array
     */
    public function getCodPaymentMethods()
    {
        return $this->_codPaymentMethods;
    }

    /**
     * Get the PostNL COD fee label for a given store.
     *
     * @param null|int|Mage_Core_Model_Store $store
     *
     * @return mixed
     */
    public function getPostnlCodFeeLabel($store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }

        $label = Mage::getStoreConfig(self::XPATH_COD_FEE_LABEL, $store);
        return $label;
    }

    /**
     * Add PostNL COD fee tax info to the full tax info array.
     *
     * This is a really annoying hack to fix the problem where the full tax info does not include the custom PostNL COD
     * fee tax info. Magento only supports tax info from shipping tax or product tax by default
     * (see Mage_Tax_Helper_Data::getCalculatedTaxes()). If anybody knows of a better way to fix this (that does not
     * require a core rewrite) please let us know at servicedesk@totalinternetgroup.nl.
     *
     * @param array                                                                                   $fullInfo
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $source
     * @param Mage_Sales_Model_Order                                                                  $order
     *
     * @return array
     *
     * @see Mage_Tax_Helper_Data::getCalculatedTaxes()
     */
    public function addPostnlCodFeeTaxInfo($fullInfo, $source, Mage_Sales_Model_Order $order)
    {
        $feeTax = (float) $order->getPostnlCodFeeTax();
        if ($feeTax <= 0) {
            return $fullInfo;
        }

        $orderClassName = Mage::getConfig()->getModelClassName('sales/order');
        if ($source instanceof $orderClassName) {
            $fullInfo = $this->_updateTaxAmountForTaxInfo($order, $fullInfo);
        } else {
            $taxItemCollection = Mage::getResourceModel('tax/sales_order_tax_item_collection');
            $taxItemCollection->addFieldToSelect('tax_id');
            $taxItemCollection->getSelect()->distinct();

            $taxItemIds = $taxItemCollection->getColumnValues('tax_id');

            $taxCollection = Mage::getResourceModel('sales/order_tax_collection')
                                 ->addFieldToFilter('order_id', array('eq'  => $order->getId()))
                                 ->addFieldToFilter('tax_id',   array('nin' => $taxItemIds));

            if ($taxCollection->getSize()) {
                $fullInfo = $this->_addPostnlCodFeeTaxInfoFromCollection($taxCollection, $fullInfo, $source);
            } else {
                $fullInfo = $this->_addPostnlCodFeeTaxInfoFromRequest($order, $fullInfo, $source);
            }
        }

        return $fullInfo;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param array $fullInfo
     *
     * @return array
     */
    protected function _updateTaxAmountForTaxInfo($order, $fullInfo)
    {
        $taxCollection = Mage::getResourceModel('sales/order_tax_collection')
                             ->addFieldToSelect('amount')
                             ->addFieldToFilter('order_id', array('eq' => $order->getId()));

        foreach ($taxCollection as $tax) {
            foreach ($fullInfo as $key => $taxInfo) {
                if ($tax->getTitle() == $taxInfo['title'] && $tax->getAmount() != $taxInfo['tax_amount']) {
                    $fullInfo[$key]['tax_amount']      = $tax->getAmount();
                    $fullInfo[$key]['base_tax_amount'] = $tax->getBaseAmount();
                }
            }
        }

        return $fullInfo;
    }

    /**
     * @param Mage_Sales_Model_Resource_Order_Tax_Collection                   $taxCollection
     * @param array                                                            $fullInfo
     * @param Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $source
     *
     * @return array
     */
    protected function _addPostnlCodFeeTaxInfoFromCollection($taxCollection, $fullInfo, $source)
    {
        foreach ($taxCollection as $tax) {
            foreach ($fullInfo as $key => $taxInfo) {
                if ($taxInfo['title'] == $tax->getTitle()) {
                    $fullInfo[$key]['tax_amount']      += $source->getPostnlCodFeeTax();
                    $fullInfo[$key]['base_tax_amount'] += $source->getBasePostnlCodFeeTax();

                    break(2);
                }
            }

            $fullInfo[] = array(
                'tax_amount'      => $source->getPostnlCodFeeTax(),
                'base_tax_amount' => $source->getBasePostnlCodFeeTax(),
                'title'           => $tax->getTitle(),
                'percent'         => $tax->getPercent(),
            );
        }

        return $fullInfo;
    }

    /**
     * @param Mage_Sales_Model_Order                                           $order
     * @param array                                                            $fullInfo
     * @param Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $source
     *
     * @return array
     */
    protected function _addPostnlCodFeeTaxInfoFromRequest($order, $fullInfo, $source)
    {
        $store = $order->getStore();
        $taxCalculation = Mage::getSingleton('tax/calculation');

        $customerTaxClass = $order->getCustomerTaxClassId();
        $shippingAddress  = $order->getShippingAddress();
        $billingAddress   = $order->getBillingAddress();
        $codTaxClass      = Mage::getStoreConfig(self::XPATH_COD_FEE_TAX_CLASS, $store);

        $taxRequest = $taxCalculation->getRateRequest(
            $shippingAddress,
            $billingAddress,
            $customerTaxClass,
            $store
        );

        $taxRequest->setProductClassId($codTaxClass);

        if (!$taxRequest) {
            return $fullInfo;
        }

        $appliedRates = Mage::getSingleton('tax/calculation')
                            ->getAppliedRates($taxRequest);

        if (!isset($appliedRates[0]['rates'][0]['title'])) {
            return $fullInfo;
        }

        $postnlCodFeeTaxTitle = $appliedRates[0]['rates'][0]['title'];

        foreach ($fullInfo as $key => $taxInfo) {
            if ($taxInfo['title'] == $postnlCodFeeTaxTitle) {
                $fullInfo[$key]['tax_amount']      += $source->getPostnlCodFeeTax();
                $fullInfo[$key]['base_tax_amount'] += $source->getBasePostnlCodFeeTax();
                break;
            }
        }

        return $fullInfo;
    }
}