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
 */
class TIG_PostNL_Block_Adminhtml_UpgradeNotification extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * Xpath to PostNL update product attribute cron expression.
     */
    const XPATH_POSTNL_UPDADE_PRODUCT_ATTRIBUTE_CRON_EXPR = 'crontab/jobs/postnl_update_product_attribute/schedule/cron_expr';

    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_upgradenotification';

    /**
     * Check to see if the PostNL extension is currently being upgraded.
     *
     * @return boolean
     */
    public function isUpgradeActive()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        /**
         * Check if the cron job has a cron expression. This will indicate if it is still updating or if it has already
         * finished.
         */
        $attributeCronjob = Mage::getStoreConfig(
            TIG_PostNL_Model_Resource_Setup::UPDATE_PRODUCT_ATTRIBUTE_STRING_PATH,
            $storeId
        );
        $dateTimeZoneCronjob = Mage::getStoreConfig(
            TIG_PostNL_Model_Resource_Setup::UPDATE_DATE_TIME_ZONE_STRING_PATH,
            $storeId
        );

        if (empty($attributeCronjob) && empty($dateTimeZoneCronjob)) {
            return false;
        }

        return true;
    }
}
