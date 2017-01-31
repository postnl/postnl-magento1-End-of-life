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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Helper_DeliveryOptions_IDCheck extends Mage_Core_Helper_Abstract
{
    /**
     * @source https://developer.postnl.nl/apis/labelling-webservice/how-use#toc-26
     */
    const TYPE_DUTCH_FOREIGNERS_DOCUMENT = '01';
    const TYPE_DUTCH_ID                  = '02';
    const TYPE_DUTCH_PASSPORT            = '03';
    const TYPE_DUTCH_DRIVERS_LICENSE     = '04';
    const TYPE_EUROPEAN_ID               = '05';
    const TYPE_ABROAD_PASSPORT           = '07';

    /**
     * @var null|TIG_PostNL_Helper_Data
     */
    protected $_helper = null;

    /**
     * @return TIG_PostNL_Helper_Data
     */
    protected function getHelper()
    {
        if ($this->_helper === null) {
            /** @var TIG_PostNL_Helper_Data _helper */
            $this->_helper = Mage::helper('postnl');
        }

        return $this->_helper;
    }

    /**
     * @return array
     */
    public function getValidationOptions()
    {
        $helper = $this->getHelper();

        return array(
            array(
                'value' => self::TYPE_DUTCH_PASSPORT,
                'label' => $helper->__('Nederlands paspoort'),
            ),
            array(
                'value' => self::TYPE_DUTCH_ID,
                'label' => $helper->__('Nederlandse identiteitskaart'),
            ),
            array(
                'value' => self::TYPE_DUTCH_DRIVERS_LICENSE,
                'label' => $helper->__('Nederlands rijbewijs'),
            ),
            array(
                'value' => self::TYPE_DUTCH_FOREIGNERS_DOCUMENT,
                'label' => $helper->__('Nederlands vreemdelingendocument'),
            ),
            array(
                'value' => self::TYPE_EUROPEAN_ID,
                'label' => $helper->__('Europese identiteitskaart'),
            ),
            array(
                'value' => self::TYPE_ABROAD_PASSPORT,
                'label' => $helper->__('Buitenlands paspoort'),
            ),
        );
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function isValidOption($value)
    {
        $options = $this->getValidationOptions();

        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return true;
            }
        }

        return false;
    }
}
