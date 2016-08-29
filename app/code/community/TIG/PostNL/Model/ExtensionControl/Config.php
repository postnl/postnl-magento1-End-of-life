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
class TIG_PostNL_Model_ExtensionControl_Config
{
    const VALIDATION_REGEX = '/^[a-zA-Z0-9!?_-]*$/';

    /**
     * @var Zend_Validate_Regex
     */
    protected $_validator;

    /**
     * @return Zend_Validate_Regex
     */
    public function getValidator()
    {
        $validator = $this->_validator;
        if (!$validator) {
            $validator = new Zend_Validate_Regex(self::VALIDATION_REGEX);
            $this->setValidator($validator);
        }

        return $validator;
    }

    /**
     * @param Zend_Validate_Regex $validator
     *
     * @return $this
     */
    public function setValidator(Zend_Validate_Regex $validator)
    {
        $this->_validator = $validator;

        return $this;
    }

    /**
     * Save config settings as returned by the activateWebshop call.
     *
     * @param array $settings
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function saveConfigSettings(array $settings)
    {
        /**
         * Check to make sure the settings are present.
         */
        if (!is_array($settings)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid settings provided.'),
                'POSTNL-0211'
            );
        }

        foreach ($settings as $setting => $value) {
            if (empty($value)) {
                continue;
            }

            $xpath = false;
            switch ($setting) {
                case 'cendrisPassword':
                    $xpath = TIG_PostNL_Model_AddressValidation_Cendris::XPATH_PASSWORD;
                    break;
                case 'cendrisUsername':
                    $xpath = TIG_PostNL_Model_AddressValidation_Cendris::XPATH_USERNAME;
                    break;
                case 'googleMapsApiKey':
                    $xpath = TIG_PostNL_Helper_DeliveryOptions::XPATH_GOOGLE_MAPS_API_KEY;
                    break;
                //no default
            }

            if (!$xpath) {
                continue;
            }

            $this->_saveConfigSetting($xpath, $value);
        }

        return $this;
    }

    /**
     * Save a configuration value with the specified Xpath and value.
     *
     * @param string $xpath
     * @param string $value
     *
     * @return $this
     *
     * @throws Exception
     * @throws TIG_PostNL_Exception
     */
    protected function _saveConfigSetting($xpath, $value)
    {
        $validator = $this->getValidator();
        if (!$validator->isValid($value)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid config value provided.'),
                'POSTNL-0212'
            );
        }

        /** @var Mage_Core_Model_Config_Data $configData */
        $configData = Mage::getModel('core/config_data')
                          ->load($xpath, 'path');

        $configData->setValue($value)
                   ->setPath($xpath)
                   ->save();

        return $this;
    }
}
