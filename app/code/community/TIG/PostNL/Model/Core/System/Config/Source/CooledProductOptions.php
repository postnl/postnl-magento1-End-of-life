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
    class TIG_PostNL_Model_Core_System_Config_Source_CooledProductOptions
        extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
    {
        /**postnl_product_option
         * @var array
         */
        protected $_options = array(
            array(
                'value'             => '3084',
                'label'             => 'Cooled Products',
                'isExtraCover'      => false,
                'isAvond'           => true,
                'isCod'             => false,
                'isSameDay'         => true,
                'statedAddressOnly' => true,
                'countryLimitation' => 'NL',
            ),
        );

        /**
         * Gets an array of possible food delivery product options.
         *
         * @return array
         */
        public function toOptionArray()
        {
            return $this->getOptions(array('isCod' => false));
        }

        /**
         * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
         *
         * @param boolean           $flat
         * @param string|null|false $country
         *
         * @return array
         */
        public function getAvailableOptions($flat = false, $country = null)
        {
            $flags = array(
                'isCod' => false,
            );

            if (!$country) {
                /** @var TIG_PostNL_Helper_Data $helper */
                $helper = Mage::helper('postnl');
                $country = $helper->getDomesticCountry();
            }

            if ($country) {
                $flags['countryLimitation'] = $country;
            }

            return $this->getOptions($flags, $flat, true);
        }

        /**
         * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
         *
         * @param boolean $flat
         *
         * @return array
         */
        public function getAvailableNlOptions($flat = false)
        {
            return $this->getAvailableOptions($flat, 'NL');
        }

        /**
         * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
         *
         * @param boolean $flat
         *
         * @return array
         */
        public function getAvailableBeOptions($flat = false)
        {
            return $this->getAvailableOptions($flat, 'BE');
        }

        /**
         * Get available avond options.
         *
         * @param boolean $flat
         *
         * @return array
         */
        public function getAvailableAvondOptions($flat = false)
        {
            return $this->getOptions(array('isAvond' => true, 'isCod' => false), $flat, true);
        }

        /**
         * Get available same day delivery options.
         *
         * @param boolean $flat
         *
         * @return array
         */
        public function getAvailableSameDayOptions($flat = false)
        {
            return $this->getOptions(array('isSameDay' => true, 'isCod' => false), $flat, true);
        }

        /**
         * Get available 'stated address only' options.
         *
         * @param boolean $flat
         *
         * @return array
         */
        public function getAvailableStatedAddressOnlyOptions($flat = false)
        {
            return $this->getOptions(array('statedAddressOnly' => true), $flat, true);
        }
    }
