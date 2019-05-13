<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Core_System_Config_Source_ExtraAtHomeProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected  $_options = array(
        array(
            'value'             => '3628',
            'label'             => 'Extra@Home Top service 2 person delivery NL',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'NL',
        ),
        array(
            'value'             => '3629',
            'label'             => 'Extra@Home Top service Btl 2 person delivery',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
        ),
        array(
            'value'             => '3653',
            'label'             => 'Extra@Home Top service 1 person delivery NL',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'NL',
        ),
        array(
            'value'             => '3783',
            'label'             => 'Extra@Home Top service Btl 1 person delivery',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
        ),
        array(
            'value'             => '3790',
            'label'             => 'Extra@Home Drempelservice 1 person delivery NL',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'NL',
        ),
        array(
            'value'             => '3791',
            'label'             => 'Extra@Home Drempelservice 2 person delivery NL',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'NL',
        ),
        array(
            'value'             => '3792',
            'label'             => 'Extra@Home Drempelservice Btl 1 person delivery',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
        ),
        array(
            'value'             => '3793',
            'label'             => 'Extra@Home Drempelservice Btl 2 person delivery',
            'isExtraCover'      => false,
            'isEvening'           => false,
            'isSunday'          => false,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => 'BE',
        )
    );

    /**
     * Get available id check options
     *
     * @param bool $flat
     *
     * @return array
     */
    public function getAvailableOptions($flat = false)
    {
        return $this->getOptions(array(), $flat);
    }
}
