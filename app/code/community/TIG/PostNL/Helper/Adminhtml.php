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
class TIG_PostNL_Helper_Adminhtml extends TIG_PostNL_Helper_Data
{
    /**
     * Gets the hidden notifications for the current admin user.
     *
     * @return array
     */
    public function getHiddenNotifications()
    {
        if (!$this->isAdmin()) {
            return array();
        }

        /** @var Mage_Admin_Model_Session $adminSession */
        $adminSession = Mage::getSingleton('admin/session');
        /** @var Mage_Admin_Model_User $adminUser */
        /** @noinspection PhpUndefinedMethodInspection */
        $adminUser = $adminSession->getUser();
        if (!$adminUser) {
            return array();
        }

        $extra = $adminUser->getExtra();
        if (empty($extra['postnl']['hidden_notification'])) {
            return array();
        }

        return $extra['postnl']['hidden_notification'];
    }

    /**
     * Returns either the store id of the current scope, or returns 0 for global level scope.
     *
     * @return int|mixed
     * @throws Mage_Core_Exception
     */
    public function getCurrentScope()
    {
        $storeId = 0;

        /** @var Mage_Adminhtml_Model_Config_Data $configData */
        $configData = Mage::getSingleton('adminhtml/config_data');
        /** @noinspection PhpUndefinedMethodInspection */
        $code = $configData->getStore();
        if (strlen($code)) {
            $storeId = Mage::getModel('core/store')->load($code)->getId();
        }

        return $storeId;
    }

}
