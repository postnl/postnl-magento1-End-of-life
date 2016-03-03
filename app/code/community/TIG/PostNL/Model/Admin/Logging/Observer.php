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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @deprecated this class is no longer used as of v1.7.0.
 */
class TIG_PostNL_Model_Admin_Logging_Observer
{
    /**
     * Check if the Enterprise Logging extension is present and if so, call it's observer method. This prevents errors
     * in Magento community edition.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @see Enterprise_Logging_Model_Observer::controllerPostdispatch()
     *
     * @deprecated v1.7.0
     */
    public function controllerPostdispatch(Varien_Event_Observer $observer)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);

        /** @noinspection PhpParamsInspection */
        $loggingObserverClassName = Mage::getConfig()->getModelClassName('enterprise_logging/observer');
        $found                    = mageFindClassFile($loggingObserverClassName);

        /**
         * If we can't find the model, there's nothing that can be logged.
         */
        if ($found === false) {
            return $this;
        }

        /** @var Enterprise_Logging_Model_Observer $loggingObserver */
        $loggingObserver = Mage::getModel('enterprise_logging/observer');
        $loggingObserver->controllerPostdispatch($observer);

        return $this;
    }
}
