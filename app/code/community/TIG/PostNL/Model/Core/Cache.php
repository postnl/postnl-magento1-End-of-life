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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean                     getPostnlCoreIsEnabled()
 * @method boolean                     getPostnlCoreIsConfigured()
 * @method boolean                     getPostnlCoreIsGlobalConfigured()
 * @method boolean                     getPostnlCoreCanUseStandard()
 * @method boolean                     getPostnlCoreCanUsePakjeGemak()
 * @method boolean                     getPostnlCoreCanUseEps()
 * @method boolean                     getPostnlCoreCanUseGlobalPack()
 * @method boolean                     getPostnlCoreCanUseEpsBeOnlyOption()
 *
 * @method boolean                     hasPostnlCoreIsEnabled()
 * @method boolean                     hasPostnlCoreIsConfigured()
 * @method boolean                     hasPostnlCoreIsGlobalConfigured()
 * @method boolean                     hasPostnlCoreCanUseStandard()
 * @method boolean                     hasPostnlCoreCanUsePakjeGemak()
 * @method boolean                     hasPostnlCoreCanUseEps()
 * @method boolean                     hasPostnlCoreCanUseGlobalPack()
 * @method boolean                     hasPostnlCoreCanUseEpsBeOnlyOption()
 *
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreIsEnabled(boolean $value)
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreIsConfigured(boolean $value)
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreIsGlobalConfigured(boolean $value)
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreCanUseStandard(boolean $value)
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreCanUsePakjeGemak(boolean $value)
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreCanUseEps(boolean $value)
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreCanUseGlobalPack(boolean $value)
 * @method TIG_PostNL_Model_Core_Cache setPostnlCoreCanUseEpsBeOnlyOption(boolean $value)
 */
class TIG_PostNL_Model_Core_Cache extends Varien_Object
{
    /**
     * PostNL cache tag.
     */
    const CACHE_TAG = 'postnl_config';

    /**
     * PostNl cache ID.
     *
     * @var null|string
     */
    protected $_cacheId = null;

    /**
     * Constructor method. Initializes the cache if the cache has not yet been loaded.
     *
     * @return $this
     */
    protected function _construct()
    {
        if (!$this->hasData()) {
            $this->init();
        }

        return $this;
    }

    /**
     * Initialize the cache.
     *
     * @return $this
     */
    public function init()
    {
        if ($this->canUseCache()) {
            $data = $this->loadCache();
            $this->setData($data);
        }

        return $this;
    }

    /**
     * @param string $cacheId
     *
     * @return $this
     */
    public function setCacheId($cacheId)
    {
        $this->_cacheId = $cacheId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCacheId()
    {
        return $this->_cacheId;
    }

    /**
     * @return boolean
     */
    public function hasCacheId()
    {
        if ($this->_cacheId === null) {
            return false;
        }

        return true;
    }

    /**
     * Loading data cache.
     *
     * @return  array|false
     */
    public function loadCache()
    {
        if (!$this->canUseCache()) {
            return array();
        }

        $data = Mage::app()->loadCache($this->_getCacheId());
        $data = unserialize($data);
        return $data;
    }

    /**
     * Save cache data.
     *
     * @return $this
     */
    public function saveCache()
    {
        if (!$this->canUseCache()) {
            return $this;
        }

        Mage::app()->saveCache(serialize($this->getData()), $this->_getCacheId(), array(self::CACHE_TAG), null);
        return $this;
    }

    /**
     * Check if the cache may be used.
     *
     * @return bool
     */
    public function canUseCache()
    {
        return Mage::app()->useCache('postnl_config');
    }

    /**
     * Get the current cache id.
     *
     * @return string
     */
    protected function _getCacheId()
    {
        if ($this->hasCacheId()) {
            return $this->getCacheId();
        }

        $cacheId = 'postnl_' . Mage::app()->getStore()->getId();

        $this->setCacheId($cacheId);
        return $cacheId;
    }
}