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

/** @var TIG_PostNL_Model_Resource_Setup $this */
$installer = $this;

$config = Mage::getConfig();
$config->deleteConfig('crontab/jobs/postnl_update_shipping_status/schedule/cron_expr');
$config->deleteConfig('crontab/jobs/postnl_update_shipping_status/run/model');
$config->deleteConfig('crontab/jobs/postnl_update_statistics/schedule/cron_expr');
$config->deleteConfig('crontab/jobs/postnl_update_statistics/run/model');
$config->deleteConfig('crontab/jobs/postnl_update_product_attribute/schedule/cron_expr');
$config->deleteConfig('crontab/jobs/postnl_update_product_attribute/run/model');
$config->deleteConfig('crontab/jobs/postnl_update_return_status/schedule/cron_expr');
$config->deleteConfig('crontab/jobs/postnl_update_return_status/run/model');
$config->deleteConfig('crontab/jobs/postnl_update_date_time_zone/schedule/cron_expr');
$config->deleteConfig('crontab/jobs/postnl_update_date_time_zone/run/model');

$installer->deleteTableRow($installer->getTable('core/resource'), 'code', 'postnl_setup');

//Attributes to be deleted
$deleteArray = array(
    'postnl_shipping_duration',
    'postnl_allow_delivery_options',
    'postnl_max_qty_for_buspakje',
    'postnl_allow_delivery_days',
    'postnl_allow_timeframes',
    'postnl_allow_pakje_gemak',
    'postnl_allow_pakketautomaat'
);

//Delete the attributes
foreach($deleteArray as $deleteElement){
    $setup = Mage::getResourceModel('catalog/setup', 'core_setup');
    try {
        $setup->startSetup();
        $setup->removeAttribute('catalog_product', $deleteElement);
        $setup->endSetup();
    } catch (Mage_Core_Exception $e) {
        print_r($e->getMessage());
    }
}

//Load TIG_PostNL.xml file and set active to false
$TigPostNlXml = simplexml_load_file('app/etc/modules/TIG_PostNL.xml');
if($TigPostNlXml) {
    $TigPostNlXml->modules->TIG_PostNL->active = 'false';
    file_put_contents('app/etc/modules/TIG_PostNL.xml', $TigPostNlXml->asXML());
}