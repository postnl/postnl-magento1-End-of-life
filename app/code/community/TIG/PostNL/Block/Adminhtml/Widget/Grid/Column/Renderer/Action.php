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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Additional column names used.
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    const COUNTRY_ID_COLUMN      = 'country_id';
    const LABELS_PRINTED_COLUMN  = 'labels_printed';
    const CONFIRM_STATUS_COLUMN  = 'confirm_status';
    const PRODUCT_CODE_COLUMN    = 'product_code';

    /**
     * Render column.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        $actionLinks = array();
        foreach ($actions as $action) {
            /**
             * Check if this action is allowed.
             *
             * @var Mage_Sales_Model_Order_Shipment $row
             */
            if (!$this->_isActionAllowed($row, $action)) {
                continue;
            }

            /**
             * The confirm action may need to be disabled.
             */
            if (isset($action['code']) && $action['code'] == 'postnl_confirm') {
                $action = $this->_checkDisableAction($row, $action);
            }

            /**
             * The print label action needs to use a custom onclick event.
             */
            if (isset($action['code']) && $action['code'] == 'postnl_print_label') {
                $printLabelUrl = $this->getUrl(
                    'adminhtml/postnlAdminhtml_shipment/printLabel',
                    array('shipment_id' => $row->getId())
                );

                $action['onClick'] = "printLabel('{$printLabelUrl}')";
            }

            if (isset($action['code'])) {
                unset($action['code']);
            }

            if (is_array($action)) {
                $actionLinks[] = $this->_toLinkHtml($action, $row);
            }
        }

        $output = implode(' / ', $actionLinks);

        return $output;
    }

    /**
     * Checks if a certain action is allowed for this row
     *
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param array                           &$action
     *
     * @return boolean
     */
    protected function _isActionAllowed($row, &$action)
    {
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);

        /**
         * If this is a PostNL action, but this shipment was not shipped using PosTNL, skip it
         */
        /** @var TIG_PostNL_Helper_Carrier $helper */
        $helper = Mage::helper('postnl/carrier');
        if (isset($action['is_postnl'])
            && $action['is_postnl']
            && !$helper->isPostnlShippingMethod($shippingMethod)
        ) {
            unset($action['is_postnl']);
            return false;
        }

        unset($action['is_postnl']);
        return true;
    }

    /**
     * In some cases an action must be disabled
     *
     * @param Mage_Sales_Model_Order_Shipment $row
     * @param array                           $action
     *
     * @return array
     */
    protected function _checkDisableAction($row, $action)
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        /** @noinspection PhpParamsInspection */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        $euCountries   = $helper->getEuCountries();
        $countryId     = $row->getData(self::COUNTRY_ID_COLUMN);
        $confirmStatus = $row->getData(self::CONFIRM_STATUS_COLUMN);
        $productCode   = $row->getData(self::PRODUCT_CODE_COLUMN);

        /**
         * If the shipment is confirmed, we can't confirm it again.
         *
         * @var $postnlShipmentClass TIG_PostNL_Model_Core_Shipment
         */
        if ($confirmStatus == $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED) {
            $message = $helper->__('This shipment has already been confirmed.');
            $action = $this->_disableAction($action, $message);

            return $action;
        }

        /**
         * EU shipments can only confirm after their labels have been printed
         */
        if (in_array($countryId, $euCountries)
            && !$row->getData(self::LABELS_PRINTED_COLUMN)
        ){
            $message = $helper->__("You must first print a shipping label for this shipment.");
            $action = $this->_disableAction($action, $message);

            return $action;
        }

        /**
         * @todo remove this once PostNL has fixed the issue with manually confirming GlobalPack shipments.
         */
        if (!in_array($countryId, $euCountries)) {
            $message = $helper->__(
                "You cannot manually confirm GlobalPack shipments. Please use the 'print label & confirm' massaction" .
                " instead."
            );
            $action = $this->_disableAction($action, $message);

            return $action;
        }

        /**
         * If this shipment uses a custom barcode it does not need to be confirmed.
         */
        $customBarcodeProductCodes = $helper->getCustomBarcodes();
        if (isset($customBarcodeProductCodes[$productCode])) {
            $message = $helper->__('This shipment does not need to be confirmed.');
            $action = $this->_disableAction($action, $message);

            return $action;
        }

        return $action;
    }

    /**
     * Disable a specified action.
     *
     * @param array  $action
     * @param string $message
     *
     * @return array
     */
    protected function _disableAction($action, $message = '')
    {
        $action['style']   = 'color:gray; cursor:not-allowed;';
        $action['onClick'] = 'return false;';
        $action['title']   = $message;

        return $action;
    }
}
