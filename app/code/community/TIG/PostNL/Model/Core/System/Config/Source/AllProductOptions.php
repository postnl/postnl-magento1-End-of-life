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
class TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected $_options = array(
        '3085' => array(
            'value'             => '3085',
            'label'             => 'Standard shipment',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3086' => array(
            'value'             => '3086',
            'label'             => 'COD',
            'isExtraCover'      => false,
            'isAvond'           => true,
            'isSunday'          => false,
            'isCod'             => true,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3091' => array(
            'value'             => '3091',
            'label'             => 'COD + Extra cover',
            'isExtraCover'      => true,
            'isAvond'           => true,
            'isSunday'          => false,
            'isCod'             => true,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3093' => array(
            'value'             => '3093',
            'label'             => 'COD + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => true,
            'isSunday'          => false,
            'isCod'             => true,
            'countryLimitation'=> 'NL',
            'group'             => 'standard_options',
        ),
        '3097' => array(
            'value'             => '3097',
            'label'             => 'COD + Extra cover + Return when not home',
            'isExtraCover'      => true,
            'isAvond'           => true,
            'isSunday'          => false,
            'isCod'             => true,
            'countryLimitation'=> 'NL',
            'group'             => 'standard_options',
        ),
        '3087' => array(
            'value'             => '3087',
            'label'             => 'Extra Cover',
            'isExtraCover'      => true,
            'isAvond'           => true,
            'isSunday'          => true,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3094' => array(
            'value'            => '3094',
            'label'            => 'Extra cover + Return when not home',
            'isAvond'          => true,
            'isSunday'         => true,
            'isExtraCover'     => true,
            'isCod'            => false,
            'countryLimitation'=> 'NL',
            'group'            => 'standard_options',
        ),
        '3189' => array(
            'value'             => '3189',
            'label'             => 'Signature on delivery',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3089' => array(
            'value'             => '3089',
            'label'             => 'Signature on delivery + Delivery to stated address only',
            'isExtraCover'      => false,
            'isAvond'           => true,
            'isSunday'          => true,
            'isCod'             => false,
            'statedAddressOnly' => true,
            'isBelgiumOnly'     => false,
            'group'             => 'standard_options',
        ),
        '3389' => array(
            'value'             => '3389',
            'label'             => 'Signature on delivery + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3096' => array(
            'value'             => '3096',
            'label'             => 'Signature on delivery + Deliver to stated address only + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => true,
            'isSunday'          => true,
            'isCod'             => false,
            'statedAddressOnly' => true,
            'isBelgiumOnly'     => false,
            'group'             => 'standard_options',
        ),
        '3090' => array(
            'value'             => '3090',
            'label'             => 'Delivery to neighbour + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => true,
            'isSunday'          => false,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3385' => array(
            'value'             => '3385',
            'label'             => 'Deliver to stated address only',
            'isExtraCover'      => false,
            'isAvond'           => true,
            'isSunday'          => true,
            'isCod'             => false,
            'statedAddressOnly' => true,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3390' => array(
            'value'             => '3390',
            'label'             => 'Deliver to stated address only + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => true,
            'isSunday'          => true,
            'isCod'             => false,
            'statedAddressOnly' => true,
            'countryLimitation' => 'NL',
            'group'             => 'standard_options',
        ),
        '3535' => array(
            'value'             => '3535',
            'label'             => 'Post Office + COD',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isSunday'          => false,
            'isCod'             => true,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '3545' => array(
            'value'             => '3545',
            'label'             => 'Post Office + COD + Notification',
            'isExtraCover'      => false,
            'isSunday'          => false,
            'isPge'             => true,
            'isCod'             => true,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '3536' => array(
            'value'             => '3536',
            'label'             => 'Post Office + COD + Extra Cover',
            'isExtraCover'      => false,
            'isSunday'          => false,
            'isPge'             => true,
            'isCod'             => true,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '3546' => array(
            'value'             => '3546',
            'label'             => 'Post Office + COD + Extra Cover + Notification',
            'isExtraCover'      => true,
            'isPge'             => true,
            'isSunday'          => false,
            'isCod'             => true,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '3534' => array(
            'value'             => '3534',
            'label'             => 'Post Office + Extra Cover',
            'isExtraCover'      => true,
            'isPge'             => false,
            'isSunday'          => false,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '3544' => array(
            'value'             => '3544',
            'label'             => 'Post Office + Extra Cover + Notification',
            'isExtraCover'      => true,
            'isPge'             => true,
            'isSunday'          => false,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '3533' => array(
            'value'             => '3533',
            'label'             => 'Post Office + Signature on Delivery',
            'isExtraCover'      => false,
            'isPge'             => false,
            'isSunday'          => false,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '3543' => array(
            'value'             => '3543',
            'label'             => 'Post Office + Signature on Delivery + Notification',
            'isExtraCover'      => false,
            'isSunday'          => false,
            'isPge'             => true,
            'isCod'             => false,
            'countryLimitation' => 'NL',
            'group'             => 'pakjegemak_options',
        ),
        '4952' => array(
            'value'             => '4952',
            'label'             => 'EU Pack Special Consumer (incl. signature)',
            'isExtraCover'      => false,
            'isSunday'          => false,
            'countryLimitation' => false,
            'group'             => 'eu_options',
        ),
        /**
         * This option has been removed since v1.1.4.
         */
        /*'4955' => array(
            'value' => '4955',
            'label' => $helper->__('EU Pack Standard (Belgium only, no signature)'),
            'isBelgiumOnly' => true,
        ),*/
        /**
         * These are not currently implemented.
         */
        /*'4950' => array(
            'value' => '4950',
            'label' => $helper->__('EU Pack Special (B2B)'),
        ),
        '4954' => array(
            'value' => '4954',
            'label' => $helper->__('EU Pack Special COD (Belgium and Luxembourg only)'),
        ),*/
        '4945' => array(
            'value'             => '4945',
            'label'             => 'GlobalPack',
            'isExtraCover'      => true,
            'isSunday'          => false,
            'extraCover'        => 200,
            'countryLimitation' => false,
            'group'             => 'global_options',
        ),
        '3553' => array(
            'value'             => '3553',
            'label'             => 'Parcel Dispenser',
            'isExtraCover'      => false,
            'isSunday'          => false,
            'countryLimitation' => 'NL',
            'group'             => 'pakketautomaat_options',
        ),
        '2828' => array(
            'value'             => '2828',
            'label'             => 'Letter Box Parcel',
            'isExtraCover'      => false,
            'isSunday'               => false,
            'countryLimitation' => 'NL',
            'group'             => 'buspakje_options',
        ),
        '2928' => array(
            'value'             => '2928',
            'label'             => 'Letter Box Parcel Extra',
            'isExtraCover'      => false,
            'isSunday'          => false,
            'countryLimitation' => 'NL',
            'group'             => 'buspakje_options',
        ),
        '4970' => array(
            'value'             => '4970',
            'label'             => 'Belgium Deliver to stated address only + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'statedAddressOnly' => true,
            'countryLimitation' => 'BE',
            'group'             => 'standard_options',
        ),
        '4971' => array(
            'value'             => '4971',
            'label'             => 'Belgium Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
            'group'             => 'standard_options',
        ),
        '4972' => array(
            'value'             => '4972',
            'label'             => 'Belgium Signature on delivery + Deliver to stated address only + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'statedAddressOnly' => true,
            'countryLimitation' => 'BE',
            'group'             => 'standard_options',
        ),
        '4973' => array(
            'value'             => '4973',
            'label'             => 'Belgium Signature on delivery + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
            'group'             => 'standard_options',
        ),
        '4974' => array(
            'value'             => '4974',
            'label'             => 'Belgium COD + Return when not home',
            'isExtraCover'      => false,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => true,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
            'group'             => 'standard_options',
        ),
        '4975' => array(
            'value'             => '4975',
            'label'             => 'Belgium Extra cover (EUR 500)+ Return when not home + Deliver to stated address only',
            'isExtraCover'      => true,
            'extraCover'        => 500,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'statedAddressOnly' => true,
            'countryLimitation' => 'BE',
            'group'             => 'standard_options',
        ),
        '4976' => array(
            'value'             => '4976',
            'label'             => 'Belgium COD + Extra cover (EUR 500) + Return when not home',
            'isExtraCover'      => true,
            'extraCover'        => 500,
            'isAvond'           => false,
            'isSunday'          => false,
            'isCod'             => true,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
            'group'             => 'standard_options',
        ),
    );

    /**
     * @var array
     */
    protected $_groups = array(
        'standard_options'       => 'Domestic options',
        'pakjegemak_options'     => 'Post Office options',
        'eu_options'             => 'EU options',
        'global_options'         => 'Global options',
        'pakketautomaat_options' => 'Parcel Dispenser options',
        'buspakje_options'       => 'Letter Box Parcel options',
        'sunday_options'         => 'Sunday options',
    );

    /**
     * Gets all possible options.
     *
     * @param array $flags
     * @param bool  $asFlatArray
     * @param bool  $checkAvailable
     *
     * @return array
     */
    public function getOptions($flags = array(), $asFlatArray = false, $checkAvailable = false)
    {
        $helper = Mage::helper('postnl');
        if (!isset($flags['countryLimitation'])) {
            $domesticCountry = $helper->getDomesticCountry();
            $flags['countryLimitation'] =  array(
                $domesticCountry,
                false,
            );
        }

        $options = parent::getOptions($flags, $asFlatArray, $checkAvailable);

        /**
         * Add the EU EPS BE only option if it's allowed and if either EPS options are requested or if all groups are
         * requested.
         */
        if ($helper->canUseEpsBEOnlyOption()
            && (!isset($flags['group'])
                || $flags['group'] == 'eu_options'
            )
        ) {
            if (!$asFlatArray) {
                $options['4955'] = array(
                    'value'         => '4955',
                    'label'         => $helper->__('EU Pack Standard (Belgium only, no signature)'),
                    'isBelgiumOnly' => true,
                    'isExtraCover'  => false,
                );
            } else {
                $options['4955'] = $helper->__('EU Pack Standard (Belgium only, no signature)');
            }

            ksort($options);
        }

        return $options;
    }

    /**
     * Returns an option array for all possible PostNL product options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getGroupedOptions();

        $helper = Mage::helper('postnl');
        if ($helper->canUseEpsBEOnlyOption()) {
            $options['eu_options']['value']['4955'] = array(
                'value'         => '4955',
                'label'         => $helper->__('EU Pack Standard (Belgium only, no signature)'),
                'isBelgiumOnly' => true,
                'isExtraCover'  => false,
            );
        }

        return $options;
    }

    /**
     * Get a flat array of all options.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getOptions(array(), true);
    }

    /**
     * Get the list of available product options that have extra cover.
     *
     * @param bool $valuesOnly
     *
     * @return array
     */
    public function getExtraCoverOptions($valuesOnly = false)
    {
        return $this->getOptions(array('isExtraCover' => true), $valuesOnly, true);
    }
}
