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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
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
            'value'        => '3085',
            'label'        => 'Standard shipment',
            'isExtraCover' => false,
            'isAvond'      => false,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3086' => array(
            'value'        => '3086',
            'label'        => 'COD',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => true,
            'group'        => 'standard_options',
        ),
        '3091' => array(
            'value'        => '3091',
            'label'        => 'COD + Extra cover',
            'isExtraCover' => true,
            'isAvond'      => true,
            'isCod'        => true,
            'group'        => 'standard_options',
        ),
        '3093' => array(
            'value'        => '3093',
            'label'        => 'COD + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => true,
            'group'        => 'standard_options',
        ),
        '3097' => array(
            'value'        => '3097',
            'label'        => 'COD + Extra cover + Return when not home',
            'isExtraCover' => true,
            'isAvond'      => true,
            'isCod'        => true,
            'group'        => 'standard_options',
        ),
        '3087' => array(
            'value'        => '3087',
            'label'        => 'Extra Cover',
            'isExtraCover' => true,
            'isAvond'      => true,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3094' => array(
            'value'        => '3094',
            'label'        => 'Extra cover + Return when not home',
            'isAvond'      => true,
            'isExtraCover' => true,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3189' => array(
            'value'        => '3189',
            'label'        => 'Signature on delivery',
            'isExtraCover' => false,
            'isAvond'      => false,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3089' => array(
            'value'        => '3089',
            'label'        => 'Signature on delivery + Delivery to stated address only',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3389' => array(
            'value'        => '3389',
            'label'        => 'Signature on delivery + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => false,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3096' => array(
            'value'        => '3096',
            'label'        => 'Signature on delivery + Deliver to stated address only + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3090' => array(
            'value'        => '3090',
            'label'        => 'Delivery to neighbour + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3385' => array(
            'value'        => '3385',
            'label'        => 'Deliver to stated address only',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3390' => array(
            'value'        => '3390',
            'label'        => 'Deliver to stated address only + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
            'group'        => 'standard_options',
        ),
        '3535' => array(
            'value'        => '3535',
            'label'        => 'Post Office + COD',
            'isExtraCover' => false,
            'isPge'        => false,
            'isCod'        => true,
            'group'        => 'pakjegemak_options',
        ),
        '3545' => array(
            'value'        => '3545',
            'label'        => 'Post Office + COD + Notification',
            'isExtraCover' => false,
            'isPge'        => true,
            'isCod'        => true,
            'group'        => 'pakjegemak_options',
        ),
        '3536' => array(
            'value'        => '3536',
            'label'        => 'Post Office + COD + Extra Cover',
            'isExtraCover' => false,
            'isPge'        => true,
            'isCod'        => true,
            'group'        => 'pakjegemak_options',
        ),
        '3546' => array(
            'value'        => '3546',
            'label'        => 'Post Office + COD + Extra Cover + Notification',
            'isExtraCover' => true,
            'isPge'        => true,
            'isCod'        => true,
            'group'        => 'pakjegemak_options',
        ),
        '3534' => array(
            'value'        => '3534',
            'label'        => 'Post Office + Extra Cover',
            'isExtraCover' => true,
            'isPge'        => false,
            'isCod'        => false,
            'group'        => 'pakjegemak_options',
        ),
        '3544' => array(
            'value'        => '3544',
            'label'        => 'Post Office + Extra Cover + Notification',
            'isExtraCover' => true,
            'isPge'        => true,
            'isCod'        => false,
            'group'        => 'pakjegemak_options',
        ),
        '3533' => array(
            'value'        => '3533',
            'label'        => 'Post Office + Signature on Delivery',
            'isExtraCover' => false,
            'isPge'        => false,
            'isCod'        => false,
            'group'        => 'pakjegemak_options',
        ),
        '3543' => array(
            'value'        => '3543',
            'label'        => 'Post Office + Signature on Delivery + Notification',
            'isExtraCover' => false,
            'isPge'        => true,
            'isCod'        => false,
            'group'        => 'pakjegemak_options',
        ),
        '4952' => array(
            'value'        => '4952',
            'label'        => 'EU Pack Special Consumer (incl. signature)',
            'isExtraCover' => false,
            'group'        => 'eu_options',
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
            'value'        => '4945',
            'label'        => 'GlobalPack',
            'isExtraCover' => true,
            'extraCover'   => 200,
            'group'        => 'global_options',
        ),
        '3553' => array(
            'value'        => '3553',
            'label'        => 'Parcel Dispenser',
            'isExtraCover' => false,
            'group'        => 'pakketautomaat_options',
        ),
        '2828' => array(
            'value'        => '2828',
            'label'        => 'Letter Box Parcel',
            'isExtraCover' => false,
            'group'        => 'buspakje_options',
        ),
        '2928' => array(
            'value'        => '2928',
            'label'        => 'Letter Box Parcel Extra',
            'isExtraCover' => false,
            'group'        => 'buspakje_options',
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
        $options = parent::getOptions($flags, $asFlatArray, $checkAvailable);

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
