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
class TIG_PostNL_Model_Core_System_Config_Source_PakjeGemakProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected $_options = array(
        array(
            'value'             => '3534',
            'label'             => 'Post Office + Extra Cover',
            'isExtraCover'      => true,
            'isPge'             => false,
            'isCod'             => false,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '3544',
            'label'             => 'Post Office + Extra Cover + Notification',
            'isExtraCover'      => true,
            'isPge'             => true,
            'isCod'             => false,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '3533',
            'label'             => 'Post Office + Signature on Delivery',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isCod'             => false,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '3543',
            'label'             => 'Post Office + Signature on Delivery + Notification',
            'isExtraCover'      => false,
            'isPge'             => true,
            'isCod'             => false,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '3535',
            'label'             => 'Post Office + COD',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isCod'             => true,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '3545',
            'label'             => 'Post Office + COD + Notification',
            'isExtraCover'      => false,
            'isPge'             => true,
            'isCod'             => true,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '3536',
            'label'             => 'Post Office + COD + Extra Cover',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isCod'             => true,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '3546',
            'label'             => 'Post Office + COD + Extra Cover + Notification',
            'isExtraCover'      => false,
            'isPge'             => true,
            'isCod'             => true,
            'isBelgiumOnly'     => false,
            'group'             => 'default',
        ),
        array(
            'value'             => '4932',
            'label'             => '4932 - Post Office Belgium + Extra Cover',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isCod'             => false,
            'isBelgiumOnly'     => true,
            'countryLimitation' => 'NL',
            'group'             => 'default',
        ),
        array(
            'value'             => '4878',
            'label'             => '4878 - Post Office Belgium + Extra Cover',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isCod'             => false,
            'isBelgiumOnly'     => true,
            'countryLimitation' => 'BE',
            'group'             => 'default',
        ),
        array(
            'value'             => '4880',
            'label'             => '4880 - Post Office Belgium',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isCod'             => false,
            'isBelgiumOnly'     => true,
            'countryLimitation' => 'BE',
            'group'             => 'default',
        ),
        array(
            'value'             => '3571',
            'label'             => 'Post Office + Agecheck 18+',
            'isExtraCover'      => false,
            'isCod'             => false,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'AgeCheck',
        ),
        array(
            'value'             => '3574',
            'label'             => 'Post Office + Notification + Agecheck 18+',
            'isExtraCover'      => false,
            'isCod'             => false,
            'isPge'             => true,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'AgeCheck',
        ),
        array(
            'value'             => '3581',
            'label'             => 'Post Office + Extra Cover + Agecheck 18+',
            'isExtraCover'      => true,
            'isCod'             => false,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'AgeCheck',
        ),
        array(
            'value'             => '3584',
            'label'             => 'Post Office + Extra Cover + Notification + Agecheck 18+',
            'isExtraCover'      => true,
            'isCod'             => false,
            'isPge'             => true,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'AgeCheck',
        ),
        array(
            'value'             => '3573',
            'label'             => 'Post Office + ID Check',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isCod'             => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'NL',
            'group'             => 'IDCheck',
        ),
        array(
            'value'             => '3576',
            'label'             => 'Post Office + Notification + ID Check',
            'isExtraCover'      => false,
            'isCod'             => false,
            'isPge'             => true,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'IDCheck',
        ),
        array(
            'value'             => '3583',
            'label'             => 'Post Office + Extra Cover + ID Check',
            'isExtraCover'      => true,
            'isCod'             => false,
            'isPge'             => false,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'IDCheck',
        ),
        array(
            'value'             => '3586',
            'label'             => 'Post Office + Extra Cover + Notification + ID Check',
            'isExtraCover'      => true,
            'isCod'             => false,
            'isPge'             => true,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'IDCheck',
        ),
        array(
            'value'             => '3572',
            'label'             => 'Post Office + Birthday Check',
            'isExtraCover'      => false,
            'isCod'             => false,
            'isPge'             => false,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'BirthdayCheck',
        ),
        array(
            'value'             => '3575',
            'label'             => 'Post Office + Notification + Birthday Check',
            'isExtraCover'      => false,
            'isCod'             => false,
            'isPge'             => true,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'BirthdayCheck',
        ),
        array(
            'value'             => '3582',
            'label'             => 'Post Office + Extra Cover + Birthday Check',
            'isExtraCover'      => true,
            'isCod'             => false,
            'isPge'             => false,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'BirthdayCheck',
        ),
        array(
            'value'             => '3585',
            'label'             => 'Post Office + Extra Cover + Notification + Birthday Check',
            'isExtraCover'      => true,
            'isCod'             => false,
            'isPge'             => true,
            'statedAddressOnly' => false,
            'isBelgiumOnly'     => false,
            'group'             => 'BirthdayCheck',
        ),
    );

    /**
     * Gets all possible product options matching an array of flags.
     *
     * @param array $flags
     * @param bool  $asFlatArray
     * @param bool  $checkAvailable
     *
     * @return array
     */
    public function getOptions($flags = array(), $asFlatArray = false, $checkAvailable = false)
    {
        $options = parent::getOptions($flags, $asFlatArray, $checkAvailable);

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if (
            $helper->canUsePakjegemakBeNotInsured()
            && (!isset($flags['isBelgiumOnly'])
                || $flags['isBelgiumOnly'] == true
            ) && (!isset($flags['isExtraCover'])
                || $flags['isExtraCover'] == false
            ) && (!isset($flags['isCod'])
                || $flags['isCod'] == false
            ) && (!isset($flags['isPge'])
                || $flags['isPge'] == false
            ) && (!isset($flags['countryLimitation'])
                || $flags['countryLimitation'] == 'NL'
            )
        ) {
            if (!$asFlatArray) {
                $options[] = array(
                    'value'             => '4936',
                    'label'             => $helper->__('4936 - Post Office Belgium'),
                    'isBelgiumOnly'     => false,
                    'isExtraCover'      => false,
                    'countryLimitation' => 'NL',
                );
            } else {
                $options['4936'] = $helper->__('4936 - Post Office Belgium');
            }
        }

        ksort($options);

        return $options;
    }

    /**
     * Returns an option array for all possible PostNL product options.
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
     * @param boolean $flat
     *
     * @param string  $group
     *
     * @return array
     */
    public function getAvailableOptions($flat = false, $group = 'default')
    {
        return $this->getOptions(array('isCod' => false, 'isBelgiumOnly' => false, 'group' => $group), $flat, true);
    }

    /**
     * Get a list of available options for Belgium. This is a filtered/modified version of the array supplied by
     * toOptionArray();
     *
     * @param boolean $flat
     *
     * @param string  $group
     *
     * @return array
     */
    public function getAvailableBeOptions($flat = false, $group = 'default')
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        return $this->getOptions(array(
            'isCod' => false,
            'isBelgiumOnly' => true,
            'countryLimitation' => $helper->getDomesticCountry(),
            'group' => $group,
        ), $flat, true);
    }

    /**
     * Get available COD options.
     *
     * @param bool   $flat
     *
     * @param string $group
     *
     * @return array
     */
    public function getAvailableCodOptions($flat = false, $group = 'default')
    {
        return $this->getOptions(array('isCod' => true, 'group' => $group), $flat, true);
    }

    /**
     * Get available PGE options.
     *
     * @param bool   $flat
     *
     * @param string $group
     *
     * @return array
     */
    public function getAvailablePgeOptions($flat = false, $group = 'default')
    {
        return $this->getOptions(array('isPge' => true, 'isCod' => false, 'group' => $group), $flat, true);
    }

    /**
     * Get available PGE options that are also COD.
     *
     * @param bool   $flat
     *
     * @param string $group
     *
     * @return array
     */
    public function getAvailablePgeCodOptions($flat = false, $group = 'default')
    {
        return $this->getOptions(array('isPge' => true, 'isCod' => true, 'group' => $group), $flat, true);
    }

    /**
     * Get available Age Check options.
     *
     * @param bool   $flat
     *
     * @return array
     */
    public function getAgeCheckOptions($flat = false)
    {
        return $this->getOptions(array('isCod' => false, 'group' => 'AgeCheck'), $flat, true);
    }

    /**
     * Get available Birthday Check options.
     *
     * @param bool   $flat
     *
     * @return array
     */
    public function getBirthdayCheckOptions($flat = false)
    {
        return $this->getOptions(array('isCod' => false, 'group' => 'BirthdayCheck'), $flat, true);
    }

    /**
     * Get available ID Check options.
     *
     * @param bool   $flat
     *
     * @return array
     */
    public function getIDCheckOptions($flat = false)
    {
        return $this->getOptions(array('isCod' => false, 'group' => 'IDCheck'), $flat, true);
    }
}
