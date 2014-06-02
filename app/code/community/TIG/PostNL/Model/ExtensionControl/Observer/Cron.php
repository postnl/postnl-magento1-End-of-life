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
class TIG_PostNL_Model_ExtensionControl_Observer_Cron
{
    /**
     * updates the shop's statistics with the extension control system.
     *
     * @return TIG_PostNL_Model_ExtensionControl_Observer_Cron
     */
    public function updateStatistics()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        /**
         * Check if the extension may send statistics to the extension control system
         */
        if (!Mage::helper('postnl/webservices')->canSendStatistics()) {
            return $this;
        }

        $helper->cronLog('UpdateStatistics cron starting...');

        /**
         * Attempt to update the shop's statistics
         */
        try {
            $helper->cronLog('Updating shop statistics.');

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
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active.
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        /**
         * Check if the extension may send statistics to the extension control system.
         */
        if (!Mage::helper('postnl/webservices')->canReceiveUpdates()) {
            return $this;
        }

        $helper->cronLog('CheckFeedUpdate cron starting...');

        $feedData = array();

        /**
         * Get the feed.
         */
        $feed = Mage::getModel('postnl_extensioncontrol/feed');
        $feedXml = $feed->getFeedData();

        /**
         * Parse the feed.
         */
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
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
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }

        }

        $helper->cronLog('CheckFeedUpdate cron has finished.');
        return $this;
    }
}
