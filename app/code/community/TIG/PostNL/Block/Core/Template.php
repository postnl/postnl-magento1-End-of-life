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
abstract class TIG_PostNL_Block_Core_Template extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_core_template';

    /**
     * @var array
     */
    protected $_helpers = array();

    /**
     * @var array
     */
    protected $_models = array();

    /**
     * @param null $helperName
     *
     * @return TIG_PostNL_Helper_Data
     */
    protected function _getHelper($helperName = null)
    {
        $helper = 'postnl';

        if ($helperName !== null) {
            $helper .= '/' . $helperName;
        }

        if (!array_key_exists($helper, $this->_helpers)) {
            $this->_helpers[$helper] = Mage::helper($helper);
        }

        return $this->_helpers[$helper];
    }

    /**
     * @param $model
     *
     * @return false|Mage_Core_Model_Abstract
     */
    protected function _getModel($model)
    {
        if (array_key_exists($model, $this->_models)) {
            return $this->_models[$model];
        }

        return Mage::getModel($model);
    }

    /**
     * Renders a template block. Also throws 2 events based on the current event prefix.
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_to_html_before');

        $html = parent::_toHtml();

        Mage::dispatchEvent($this->_eventPrefix . '_to_html_after');
        return $html;
    }
}
