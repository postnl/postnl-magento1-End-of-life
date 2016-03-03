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
 */
class TIG_PostNL_Model_ExtensionControl_Observer_Cron
{
    /**
     * Updates the shop's statistics with the extension control system.
     *
     * @return TIG_PostNL_Model_ExtensionControl_Observer_Cron
     */
    public function updateStatistics()
    {
        /** @var TIG_PostNL_Helper_Webservices $helper */
        $helper = Mage::helper('postnl/webservices');

        /**
         * Check if the PostNL module is active.
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        /**
         * Check if the extension may send statistics to the extension control system.
         */
        if (!$helper->canSendStatistics()) {
            return $this;
        }

        $helper->cronLog('UpdateStatistics cron starting...');

        /**
         * Attempt to update the shop's statistics.
         */
        try {
            $helper->cronLog('Updating shop statistics.');

            /** @var TIG_PostNL_Model_ExtensionControl_Webservices $webservices */
            $webservices = Mage::getModel('postnl_extensioncontrol/webservices');
            $webservices->updateStatistics();
        } catch (Exception $e) {
            $helper->cronLog('An error occurred: ' . $e->getMessage());
            $helper->logException($e);
        }

        $helper->cronLog('UpdateStatistics has finished.');
        return $this;
    }

    /**
     * Check feed for modification.
     *
     * @return TIG_PostNL_Model_ExtensionControl_Observer_Cron
     */
    public function checkFeedUpdate()
    {
        /** @var TIG_PostNL_Helper_Webservices $helper */
        $helper = Mage::helper('postnl/webservices');

        /**
         * Check if the PostNL module is active.
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        /**
         * Check if the extension may send statistics to the extension control system.
         */
        if (!$helper->canReceiveUpdates()) {
            return $this;
        }

        $helper->cronLog('CheckFeedUpdate cron starting...');

        $feedData = array();

        /**
         * Get the feed.
         */
        /** @var TIG_PostNL_Model_ExtensionControl_Feed $feed */
        $feed = Mage::getModel('postnl_extensioncontrol/feed');
        $feedXml = $feed->getFeedData();

        /**
         * Parse the feed.
         */
        /** @noinspection PhpUndefinedFieldInspection */
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            /** @noinspection PhpUndefinedFieldInspection */
            $items = (array) $feedXml->channel;
            $items = array_reverse((array) $items['item']);

            foreach ($items as $item) {
                $severity = (int) $item->severity;
                if ($severity < 1 || $severity > 4) {
                    $severity = 4;
                }

                /**
                 * Add a notification for each item that is new.
                 */
                $feedData[] = array(
                    'severity'      => $severity,
                    'date_added'    => $feed->getDate((string) $item->pubDate),
                    'title'         => $helper->escapeHtml((string) $item->title),
                    'description'   => $helper->escapeHtml((string) $item->description),
                    'url'           => $helper->escapeHtml((string) $item->link),
                );
            }

            $helper->cronLog('Parsing retrieved data.');
            if ($feedData) {
                /** @var Mage_AdminNotification_Model_Inbox $inbox */
                $inbox = Mage::getModel('adminnotification/inbox');
                $inbox->parse(array_reverse($feedData));
            }

        }

        $helper->cronLog('CheckFeedUpdate cron has finished.');
        return $this;
    }

    /**
     * Update the shop's config settings with settings retrieved from the extension control system. Currently this is
     * used for the Google Maps API key, and the Cendris username and password.
     *
     * N.B. this will not be used to overwrite settings that were configured by the end-user.
     *
     * @return $this
     */
    public function updateSettings()
    {
        /** @var TIG_PostNL_Helper_Webservices $helper */
        $helper = Mage::helper('postnl/webservices');

        /**
         * Check if the PostNL module is active.
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('UpdateSettings cron starting...');

        /**
         * Attempt to update the shop's statistics
         */
        try {
            $helper->cronLog('Updating shop config settings.');

            /** @var TIG_PostNL_Model_ExtensionControl_Webservices $webservices */
            $webservices = Mage::getModel('postnl_extensioncontrol/webservices');
            $settings = $webservices->updateConfigSettings();

            /** @var TIG_PostNL_Model_ExtensionControl_Config $config */
            $config = Mage::getModel('postnl_extensioncontrol/config');
            $config->saveConfigSettings($settings);
        } catch (Exception $e) {
            $helper->cronLog('An error occurred: ' . $e->getMessage());
            $helper->logException($e);
        }

        $helper->cronLog('UpdateSettings has finished.');
        return $this;
    }
}
