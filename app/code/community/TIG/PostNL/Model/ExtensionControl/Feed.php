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
class TIG_PostNL_Model_ExtensionControl_Feed extends Mage_AdminNotification_Model_Feed
{
    /**
     * The XMl feed's url and protocol.
     */
    const XPATH_FEED_USE_HTTPS  = 'postnl/advanced/feed_use_https';
    const XPATH_FEED_URL        = 'postnl/advanced/feed_url';

    /**
     * Retrieve feed url.
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if (!is_null($this->_feedUrl)) {
            return $this->_feedUrl;
        }

        $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $scheme = 'http://';
        $useHttps = Mage::getStoreConfigFlag(self::XPATH_FEED_USE_HTTPS, $adminStoreId);
        if ($useHttps) {
            $scheme = 'https://';
        }

        $feedUrl = $scheme . Mage::getStoreConfig(self::XPATH_FEED_URL, $adminStoreId);

        $this->setFeedurl($feedUrl);
        return $feedUrl;
    }

    /**
     * Set the feed url
     *
     * @param $feedUrl
     *
     * @return TIG_PostNL_Model_ExtensionControl_Feed
     */
    public function setFeedUrl($feedUrl)
    {
        $this->_feedUrl = $feedUrl;
        return $this;
    }

    /**
     * Check feed for modification
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $helper = Mage::helper('core');

        $feedData = array();

        $feedXml = $this->getFeedData();

        /** @noinspection PhpUndefinedFieldInspection */
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            /** @noinspection PhpUndefinedFieldInspection */
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity'      => (int) $item->severity,
                    'date_added'    => $this->getDate((string) $item->pubDate),
                    'title'         => $helper->escapeHtml((string) $item->title),
                    'description'   => $helper->escapeHtml((string) $item->description),
                    'url'           => $helper->escapeHtml((string) $item->link),
                );
            }

            if ($feedData) {
                /** @var Mage_AdminNotification_Model_Inbox $inbox */
                $inbox = Mage::getModel('adminnotification/inbox');
                $inbox->parse(array_reverse($feedData));
            }

        }
        $this->setLastUpdate();

        return $this;
    }
}
