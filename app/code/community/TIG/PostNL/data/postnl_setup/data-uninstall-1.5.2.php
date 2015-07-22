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

/** @var TIG_PostNL_Helper_Data $helper */
$helper = Mage::helper('postnl');

// First delete table row
$installer->deleteTableRow($installer->getTable('core/resource'), 'code', 'postnl_setup');

// These are the cronjobs we're going to delete
$config = Mage::getConfig();
$deleteCronjobs = array(
    'crontab/jobs/postnl_update_shipping_status/schedule/cron_expr',
    'crontab/jobs/postnl_update_shipping_status/run/model',
    'crontab/jobs/postnl_update_statistics/schedule/cron_expr',
    'crontab/jobs/postnl_update_statistics/run/model',
    'crontab/jobs/postnl_update_product_attribute/schedule/cron_expr',
    'crontab/jobs/postnl_update_product_attribute/run/model',
    'crontab/jobs/postnl_update_return_status/schedule/cron_expr',
    'crontab/jobs/postnl_update_return_status/run/model',
    'crontab/jobs/postnl_update_date_time_zone/schedule/cron_expr',
    'crontab/jobs/postnl_update_date_time_zone/run/model',
);
// And then we delete them
foreach ($deleteCronjobs as $cron) {
    $config->deleteConfig($cron);
}

// These are the attributes we're going to delete
$deleteAttributes = array(
    'postnl_shipping_duration',
    'postnl_allow_delivery_options',
    'postnl_max_qty_for_buspakje',
    'postnl_allow_delivery_days',
    'postnl_allow_timeframes',
    'postnl_allow_pakje_gemak',
    'postnl_allow_pakketautomaat'
);

// And then we (try to) delete them
foreach($deleteAttributes as $attribute){
    try {
        $installer->removeAttribute('catalog_product', $attribute);
    } catch (Mage_Core_Exception $e) {
        // Log that we couldn't remove the attribute, but do continue
        $message = $helper->__('PostNL uninstall failed on removing product attribute: %s', $attribute);
        $helper->log($message, Zend_Log::ERR, null, true, 'TIG_PostNL' . DS . 'TIG_Uninstall_Log.log');
    }
}

// Load TIG_PostNL.xml file and set active to false
$xmlLocation = 'app' . DS . 'etc' . DS . 'modules' . DS . 'TIG_PostNL.xml';

// Check if the file exists and is writable
if (file_exists($xmlLocation)) {
    // If $xmlLocation is_writable, we can load the XML file, change it and then save it
    $writable = is_writable($xmlLocation);
    if ($writable) {
        // Load the XML
        $xml = simplexml_load_file($xmlLocation);
        $xml->modules->TIG_PostNL->active = 'false';
        // Suppress errors in case of the file not being writable after all (which should not happen)
        $writable = @file_put_contents($xmlLocation, $xml->asXML());
    }
    // If either $writable is false due to is_writable or because file_put_contents failed, we're going to log a message
    if ($writable === false) {
        // Log that we really couldn't write the file
        $message = $helper->__('PostNL uninstall found but could not write to XML file: %s', $xmlLocation);
        $helper->log($message, Zend_Log::ERR, null, true, 'TIG_PostNL' . DS . 'TIG_Uninstall_Log.log');
    } else {
        $message = $helper->__('PostNL has been uninstalled successfully.');
        $helper->log($message, Zend_Log::NOTICE, null, true, 'TIG_PostNL' . DS . 'TIG_Uninstall_Log.log');
    }
} else {
    // Log that the file doesn't exist or isn't writable
    $message = $helper->__('PostNL uninstall could not find or XML file: %s', $xmlLocation);
    $helper->log($message, Zend_Log::ERR, null, true, 'TIG_PostNL' . DS . 'TIG_Uninstall_Log.log');
}