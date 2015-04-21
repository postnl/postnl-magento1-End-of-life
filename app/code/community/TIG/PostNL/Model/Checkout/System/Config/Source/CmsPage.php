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
class TIG_PostNL_Model_Checkout_System_Config_Source_CmsPage
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * Get the stored options array.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Store the options array.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * Checks if an option array has been stored.
     *
     * @return boolean
     */
    public function hasOptions()
    {
        $options = $this->_options;
        if (empty($options)) {
            return false;
        }

        return true;
    }

    /**
     * Get an option array of all CMS pages available.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->hasOptions()) {
            return $this->getOptions();

        }

        $options = array(
            array(
                'value' => '',
                'label' => Mage::helper('postnl')->__('-- none --'),
            )
        );

        /**
         * @var Mage_Cms_Model_Resource_Page_Collection $pageCollection
         */
        $pageCollection = Mage::getResourceModel('cms/page_collection')->load();
        $pageOptions = $pageCollection->toOptionIdArray();

        $options = array_merge($options, $pageOptions);
        $this->setOptions($options);

        return $options;
    }
}
