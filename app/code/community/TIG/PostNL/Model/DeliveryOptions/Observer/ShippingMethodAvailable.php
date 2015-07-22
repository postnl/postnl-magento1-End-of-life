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
class TIG_PostNL_Model_DeliveryOptions_Observer_ShippingMethodAvailable extends Varien_Object
{
    /**
     * The block class that we want to edit.
     */
    const BLOCK_NAME = 'checkout/onepage_shipping_method_available';
    const BPOST_BLOCK_NAME = 'shippingmanager/onepage_shipping_method_available';

    /**
     *
     */
    const IGNORE_POSTNL_ORDER_RESET_REGISTRY_KEY = 'IGNORE_POSTNL_ORDER_RESET_FLAG';

    /**
     * @var boolean|null
     */
    protected $_canUseDeliveryOptions = null;

    /**
     * @var boolean
     */
    protected $_bpostBlockModified = false;

    /**
     * @param boolean $canUseDeliveryOptions
     */
    public function setCanUseDeliveryOptions($canUseDeliveryOptions)
    {
        $this->_canUseDeliveryOptions = $canUseDeliveryOptions;
    }

    /**
     * @return boolean
     */
    public function getCanUseDeliveryOptions()
    {
        if ($this->_canUseDeliveryOptions !== null) {
            return $this->_canUseDeliveryOptions;
        }

        /**
         * Check if delivery options are available for the current quote.
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $canUseDeliveryOptions = Mage::helper('postnl/deliveryOptions')->canUseDeliveryOptions($quote);

        $this->setCanUseDeliveryOptions($canUseDeliveryOptions);
        return $this->_canUseDeliveryOptions;
    }

    /**
     * @return boolean
     */
    public function isBpostBlockModified()
    {
        return $this->_bpostBlockModified;
    }

    /**
     * @param boolean $bpostBlockModified
     *
     * @return $this
     */
    public function setBpostBlockModified($bpostBlockModified)
    {
        $this->_bpostBlockModified = $bpostBlockModified;

        return $this;
    }

    /**
     * Gets the classname for the block that we want to alter.
     *
     * @return string
     */
    public function getBlockClass()
    {
        if ($this->hasData('block_class')) {
            return $this->getData('block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::BLOCK_NAME);

        $this->setData('block_class', $blockClass);
        return $blockClass;
    }

    /**
     * Gets the classname for the Bpost block that we want to alter.
     *
     * @return string
     */
    public function getBpostBlockClass()
    {
        if ($this->hasData('bpost_block_class')) {
            return $this->getData('bpost_block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::BPOST_BLOCK_NAME);

        $this->setData('bpost_block_class', $blockClass);
        return $blockClass;
    }

    /**
     * Alters the template of the onepage checkout shipping method available block so that we can display our delivery
     * options.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return TIG_PostNL_Model_DeliveryOptions_Observer_ShippingMethodAvailable
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer checkout_onepage_billing_postcodecheck
     */
    public function addDeliveryOptions(Varien_Event_Observer $observer)
    {
        if ($this->isBpostBlockModified()) {
            return $this;
        }

        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         *
         * @var Mage_Checkout_Block_Onepage_Shipping_Method_Available $block
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $block = $observer->getBlock();
        $blockClass = $this->getBlockClass();

        if (!($block instanceof $blockClass)) {
            return $this;
        }

        $ignorePostnlOrderResetFlag = Mage::registry(self::IGNORE_POSTNL_ORDER_RESET_REGISTRY_KEY);
        if (true !== $ignorePostnlOrderResetFlag) {
            $this->_resetPostnlOrder();
        }

        if (!$this->getCanUseDeliveryOptions()) {
            return $this;
        }

        /**
         * Get the template for the current module.
         */
        $template = 'TIG/PostNL/delivery_options/onepage/available.phtml';
        if (Mage::app()->getRequest()->getModuleName() == 'onestepcheckout') {
            $template = 'TIG/PostNL/delivery_options/onestepcheckout/available.phtml';

            if (!$block->getChild('postnl.osc.delivery.options')) {
                $block = $this->_addDeliveryOptionBlocks($block);
            }
        } elseif (Mage::app()->getRequest()->getModuleName() == 'gomage_checkout') {
            $template = 'TIG/PostNL/delivery_options/gomage_checkout/available.phtml';

            if (!$block->getChild('postnl.gomage.delivery.options')) {
                $block = $this->_addGoMageDeliveryOptionBlocks($block);
            }
        }

        /**
         * @var Mage_Checkout_Block_Onepage_Shipping_Method_Available $block
         */
        $block->setTemplate($template);

        return $this;
    }

    /**
     * Checks if a PostNL Order is associated with the current quote. If so, deactivate it. Then recalculate the quote
     * totals so the shipping costs are updated correctly.
     *
     * @return TIG_PostNL_Model_DeliveryOptions_Observer_ShippingMethodAvailable
     */
    protected function _resetPostnlOrder()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        /**
         * Remove shipment costs from the PostNL order associated with the current quote.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->loadByQuote($quote);
        if ($postnlOrder->getId() && !$postnlOrder->hasOrderId()) {
            $postnlOrder->setIsActive(false)
                        ->setShipmentCosts(0)
                        ->setType(false)
                        ->setOptions(false)
                        ->setExpectedDeliveryTimeStart(false)
                        ->setExpectedDeliveryTimeEnd(false)
                        ->save();
        }

        $shippingAddress = $quote->getShippingAddress();

        /**
         * Get the new shipping costs.
         */
        $shippingAddress->setCollectShippingRates(true);

        $quote->collectTotals()
              ->save();

        $shippingAddress->setCollectShippingRates(true);

        return $this;
    }

    /**
     * Adds the delivery option blocks in case these were not added by the layout XML. This occurs during certain OSC
     * AJAX requests that ignore the layout XML and generate blocks manually instead.
     *
     * @param Mage_Checkout_Block_Onepage_Shipping_Method_Available $block
     *
     * @return Mage_Checkout_Block_Onepage_Shipping_Method_Available
     */
    protected function _addDeliveryOptionBlocks(Mage_Checkout_Block_Onepage_Shipping_Method_Available $block)
    {
        /**
         * @var TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions $firstChild
         */
        $deliveryOptionsBlock = $block->getLayout()->createBlock(
            'postnl_deliveryoptions/checkout_deliveryOptions',
            'postnl.osc.delivery.options'
        );
        $deliveryOptionsBlock->setTemplate('TIG/PostNL/delivery_options/onestepcheckout/deliveryoptions.phtml');

        /**
         * @var Mage_Core_Block_Template $addLocationBlock
         */
        $addLocationBlock = $block->getLayout()->createBlock(
            'core/template',
            'postnl.osc.add.location'
        );
        $addLocationBlock->setTemplate('TIG/PostNL/delivery_options/addlocation.phtml');

        /**
         * @var TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber $addPhoneNumberBlock
         */
        $addPhoneNumberBlock = $block->getLayout()->createBlock(
            'postnl_deliveryoptions/checkout_addPhoneNumber',
            'postnl.add.phonenumber'
        );
        $addPhoneNumberBlock->setTemplate('TIG/PostNL/delivery_options/addphonenumber.phtml');

        $deliveryOptionsBlock->append($addLocationBlock)
                             ->append($addPhoneNumberBlock);

        $block->append($deliveryOptionsBlock);

        return $block;
    }

    /**
     * Adds the delivery option blocks in case these were not added by the layout XML. This occurs during certain GoMage
     * LightCheckout AJAX requests that ignore the layout XML and generate blocks manually instead.
     *
     * @param Mage_Checkout_Block_Onepage_Shipping_Method_Available $block
     *
     * @return Mage_Checkout_Block_Onepage_Shipping_Method_Available
     */
    protected function _addGoMageDeliveryOptionBlocks(Mage_Checkout_Block_Onepage_Shipping_Method_Available $block)
    {
        /**
         * @var TIG_PostNL_Block_DeliveryOptions_Checkout_GoMage_LightCheckout_DeliveryOptions $deliveryOptionsBlock
         */
        $deliveryOptionsBlock = $block->getLayout()->createBlock(
            'postnl_deliveryoptions/checkout_goMage_lightCheckout_deliveryOptions',
            'postnl.gomage.delivery.options'
        );
        $deliveryOptionsBlock->setTemplate('TIG/PostNL/delivery_options/gomage_checkout/deliveryoptions.phtml');

        /**
         * @var Mage_Core_Block_Template $addLocationBlock
         */
        $addLocationBlock = $block->getLayout()->createBlock(
            'core/template',
            'postnl.gomage.add.location'
        );
        $addLocationBlock->setTemplate('TIG/PostNL/delivery_options/addlocation.phtml');

        /**
         * @var TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber $addPhoneNumberBlock
         */
        $addPhoneNumberBlock = $block->getLayout()->createBlock(
            'postnl_deliveryoptions/checkout_addPhoneNumber',
            'postnl.add.phonenumber'
        );
        $addPhoneNumberBlock->setTemplate('TIG/PostNL/delivery_options/addphonenumber.phtml');

        $deliveryOptionsBlock->append($addLocationBlock)
                             ->append($addPhoneNumberBlock);

        $block->append($deliveryOptionsBlock);

        return $block;
    }

    /**
     * Fix a conflict between PostNL delivery options and the Bpost shipping manager extension.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function fixBpostConflict(Varien_Event_Observer $observer)
    {
        if ($this->isBpostBlockModified()) {
            return $this;
        }

        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block.
         *
         * @var Bpost_ShippingManager_Block_Onepage_Shipping_Method_Available $block
         */
        $block = $observer->getBlock();
        $blockClass = $this->getBpostBlockClass();

        if (!($block instanceof $blockClass)) {
            return $this;
        }

        if (!$this->getCanUseDeliveryOptions()) {
            return $this;
        }

        /**
         * Make sure we don't end up in an infinite loop.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        if ($block->getTemplate() == 'TIG/PostNL/delivery_options/onestepcheckout/bpost/available.phtml') {
            return $this;
        }

        /**
         * Same check as used by the Bpost ShippingManager extension to decide whether the block's template needs to be
         * altered.
         */
        if (Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links') == 1) {
            /** @noinspection PhpUndefinedMethodInspection */
            $block->setTemplate('TIG/PostNL/delivery_options/onestepcheckout/bpost/available.phtml');

            $this->setBpostBlockModified(true);

            /**
             * Re-render the block so it uses a modified version of the PostNL extension's shipping method available
             * template that is compatible with both PostNL delivery options and the Bpost shipping manager.
             *
             * Re-rendering the block like this is a performance drain, however at present we have no other viable
             * solution.
             */
            /** @var Varien_Object $transport */
            /** @noinspection PhpUndefinedMethodInspection */
            $transport = $observer->getTransport();
            /** @noinspection PhpUndefinedMethodInspection */
            $transport->setHtml($block->renderView());

            /** @noinspection PhpUndefinedMethodInspection */
            $observer->setTransport($transport);
        }

        return $this;
    }
}