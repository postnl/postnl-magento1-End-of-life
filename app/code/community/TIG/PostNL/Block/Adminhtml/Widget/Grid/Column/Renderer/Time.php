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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Time
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Date format string
     */
    protected static $_format = null;

    /**
     * Retrieve datetime format
     *
     * @return string
     */
    protected function _getFormat()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $format = $this->getColumn()->getFormat();
        if (!$format) {
            if (is_null(self::$_format)) {
                try {
                    self::$_format = Mage::app()->getLocale()->getTimeFormat(
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                    );
                }
                catch (Exception $e) {
                    /** @var TIG_PostNL_Helper_Data $helper */
                    $helper = Mage::helper('postnl');
                    $helper->logException($e);
                }
            }
            $format = self::$_format;
        }
        return $format;
    }

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     *
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $data = $this->_getValue($row);
        if (!$data) {
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            return $this->getColumn()->getDefault();
        }

        $format = $this->_getFormat();
        try {
            $data = Mage::app()->getLocale()
                ->date($data, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            return $this->getColumn()->getDefault();
        }
        return $data;
    }
}
