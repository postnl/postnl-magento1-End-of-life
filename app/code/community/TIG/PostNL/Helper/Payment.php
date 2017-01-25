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
 * @category    TIG
 * @package     TIG_PostNL
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * @link        http://store.tig.nl/tig/postnl.html
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
     * @var TIG_PostNL_Model_Payment_Service
     */
    protected $_serviceModel;

    /**
     * An array of PostNL COD payment methods.
     *
     * @var array
     */
    protected $_codPaymentMethods = array(
        'postnl_cod',
    );

    /**
     * @return TIG_PostNL_Model_Payment_Service
     */
    public function getServiceModel()
    {
        if ($this->_serviceModel) {
            return $this->_serviceModel;
        }

        /** @var TIG_PostNL_Model_Payment_Service $serviceModel */
        $serviceModel = Mage::getModel('postnl_payment/service');

        $this->setServiceModel($serviceModel);
        return $serviceModel;
    }

    /**
     * @param TIG_PostNL_Model_Payment_Service $serviceModel
     *
     * @return $this
     */
    public function setServiceModel(TIG_PostNL_Model_Payment_Service $serviceModel)
    {
        $this->_serviceModel = $serviceModel;

        return $this;
    }

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
     * Alias for TIG_PostNL_Model_Payment_Service::addPostnlCodFeeTaxInfo()
     *
     * @param array                                                                                   $fullInfo
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $source
     * @param Mage_Sales_Model_Order                                                                  $order
     *
     * @return array
     *
     * @see TIG_PostNL_Model_Payment_Service::addPostnlCodFeeTaxInfo()
     */
    public function addPostnlCodFeeTaxInfo($fullInfo, $source, Mage_Sales_Model_Order $order)
    {
        $fullInfo = $this->getServiceModel()->addPostnlCodFeeTaxInfo($fullInfo, $source, $order);

        return $fullInfo;
    }
}
