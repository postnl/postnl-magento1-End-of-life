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
class TIG_PostNL_Model_Core_Observer_Returns
{
    /**
     * The block we want to edit's class.
     */
    const ORDER_INFO_BLOCK_CLASS = 'sales/order_info';

    /**
     * @var string
     */
    protected $_blockClassName = '';

    /**
     * @var bool
     */
    protected $_processed = false;

    /**
     * @return string
     */
    public function getBlockClassName()
    {
        $className = $this->_blockClassName;
        if (!empty($className)) {
            return $className;
        }

        $className = Mage::getConfig()->getBlockClassName(self::ORDER_INFO_BLOCK_CLASS);

        $this->setBlockClassName($className);
        return $className;
    }

    /**
     * @param string $blackClassName
     *
     * @return $this
     */
    public function setBlockClassName($blackClassName)
    {
        $this->_blockClassName = $blackClassName;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isProcessed()
    {
        return $this->_processed;
    }

    /**
     * @param boolean $processed
     *
     * @return $this
     */
    public function setProcessed($processed)
    {
        $this->_processed = $processed;

        return $this;
    }

    /**
     * Add a link to the PostNL returns page to the sales/order/view page.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer returns_view_link
     */
    public function addReturnLinkToAccount(Varien_Event_Observer $observer)
    {
        if ($this->isProcessed()) {
            return $this;
        }

        /**
         * Check if this block is the block we need to edit.
         */
        $block = $observer->getBlock();
        $blockClassName = $this->getBlockClassName();
        if (!($block instanceof $blockClassName)) {
            return $this;
        }

        /**
         * @var Mage_Sales_Block_Order_Info $block
         */

        /**
         * If the link was already added through layout.xml we don't have to add it again.
         */
        $links = $block->getLinks();
        if (isset($links['postnl_returns'])) {
            return $this;
        }

        $helper = Mage::helper('postnl');

        /**
         * Check if printing return labels is allowed for the current order.
         */
        if (!$helper->canPrintReturnLabelForOrder(Mage::registry('current_order'))) {
            return $this;
        }

        /**
         * Add the link.
         */
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $block->addLink(
                'postnl_returns',
                'postnl/order/returns',
                $helper->__('Returns')
            );
        } else {
            $block->addLink(
                'postnl_returns',
                'postnl/guest/returns',
                $helper->__('Returns')
            );
        }

        $this->setProcessed(true);
        return $this;
    }
}