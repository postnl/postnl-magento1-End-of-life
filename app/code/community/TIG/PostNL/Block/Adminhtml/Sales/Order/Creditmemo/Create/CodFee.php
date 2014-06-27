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
class TIG_PostNL_Block_Adminhtml_Sales_Order_Creditmemo_Create_CodFee extends Mage_Adminhtml_Block_Template
{
    /**
     * Source model.
     *
     * @var Mage_Sales_Model_Order_Creditmemo
     */
    protected $_source;

    /**
     * Initialize creditmemo PostNL COD fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        /**
         * @var Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals $parent
         */
        $parent = $this->getParentBlock();

        $this->_source = $parent->getSource();

        $total = new Varien_Object(
            array(
                'code'       => 'postnl_cod_fee',
                'block_name' => $this->getNameInLayout()
            )
        );

        $parent->addTotalBefore($total, 'agjustments');

        return $this;
    }

    /**
     * Get the source model.
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getSource()
    {
        return $this->_source;
    }

}