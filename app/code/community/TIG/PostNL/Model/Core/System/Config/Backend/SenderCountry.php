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
class TIG_PostNL_Model_Core_System_Config_Backend_SenderCountry extends Mage_Core_Model_Config_Data
{
    /**
     * Xpath to alternative sender country setting.
     */
    const XPATH_ALTERNATIVE_SENDER_COUNTRY = 'postnl/cif_address/alternative_sender_country';

    /**
     * @var array
     */
    protected $_validSenderCountries = array(
        'NL',
        'BE',
    );

    /**
     * @return array
     */
    public function getValidSenderCountries()
    {
        return $this->_validSenderCountries;
    }

    /**
     * Validate the value before saving.
     *
     * @return Mage_Core_Model_Abstract
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();

        if (!in_array($value, $this->getValidSenderCountries())) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__("Only 'NL' and 'BE' are allowed as sender country."),
                'POSTNL-0236'
            );
        }

        return parent::_beforeSave();
    }

    /**
     * When saving the sender country setting, copy it's value to the alternative sender country setting.
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        $alternativeSenderCountry = Mage::getModel('core/config_data')
                                        ->load(self::XPATH_ALTERNATIVE_SENDER_COUNTRY, 'path');

        $alternativeSenderCountry->setData($this->getData())
                                 ->setPath(self::XPATH_ALTERNATIVE_SENDER_COUNTRY)
                                 ->save();

        return parent::_afterSave();
    }
}