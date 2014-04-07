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
 *
 * @method boolean                                      hasBlockClass()
 * @method TIG_PostNL_Model_Mijnpakket_Observer_Onepage setBlockClass(string $value)
 */
class TIG_PostNL_Model_Mijnpakket_Observer_Onepage extends Varien_Object
{
    /**
     * The block class that we want to edit.
     */
    const BLOCK_NAME = 'checkout/onepage_login';

    /**
     * The new login template.
     */
    const LOGIN_TEMPLATE = 'TIG/PostNL/mijnpakket/onepage/login.phtml';

    /**
     * Gets the classname for the block that we want to alter.
     *
     * @return string
     */
    public function getBlockClass()
    {
        if ($this->hasBlockClass()) {
            return $this->_getData('block_class');
        }

        $blockClass = Mage::getConfig()->getBlockClassName(self::BLOCK_NAME);

        $this->setBlockClass($blockClass);
        return $blockClass;
    }

    /**
     * Replace the onepage checkout login template.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event core_block_abstract_to_html_before
     *
     * @observer checkout_onepage_login
     */
    public function addMijnpakketLogin(Varien_Event_Observer $observer)
    {
        /**
         * Checks if the current block is the one we want to edit.
         *
         * Unfortunately there is no unique event for this block
         *
         * @var Mage_Core_Block_Abstract $block
         */
        $block      = $observer->getBlock();
        $blockClass = $this->getBlockClass();

        if (!($block instanceof $blockClass)) {
            return $this;
        }

        /**
         * @var Mage_Checkout_Block_Onepage_Login $block
         */
        $block->setTemplate(self::LOGIN_TEMPLATE);

        return $this;
    }
}