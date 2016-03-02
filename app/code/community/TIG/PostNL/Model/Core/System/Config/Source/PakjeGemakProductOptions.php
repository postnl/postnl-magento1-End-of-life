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
class TIG_PostNL_Model_Core_System_Config_Source_PakjeGemakProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected $_options = array(
        array(
            'value'         => '3534',
            'label'         => 'Post Office + Extra Cover',
            'isExtraCover'  => true,
            'isPge'         => false,
            'isCod'         => false,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '3544',
            'label'         => 'Post Office + Extra Cover + Notification',
            'isExtraCover'  => true,
            'isPge'         => true,
            'isCod'         => false,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '3533',
            'label'         => 'Post Office + Signature on Delivery',
            'isExtraCover'  => false,
            'isPge'         => false,
            'isCod'         => false,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '3543',
            'label'         => 'Post Office + Signature on Delivery + Notification',
            'isExtraCover'  => false,
            'isPge'         => true,
            'isCod'         => false,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '3535',
            'label'         => 'Post Office + COD',
            'isExtraCover'  => false,
            'isPge'         => false,
            'isCod'         => true,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '3545',
            'label'         => 'Post Office + COD + Notification',
            'isExtraCover'  => false,
            'isPge'         => true,
            'isCod'         => true,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '3536',
            'label'         => 'Post Office + COD + Extra Cover',
            'isExtraCover'  => false,
            'isPge'         => false,
            'isCod'         => true,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '3546',
            'label'         => 'Post Office + COD + Extra Cover + Notification',
            'isExtraCover'  => false,
            'isPge'         => true,
            'isCod'         => true,
            'isBelgiumOnly' => false,
        ),
        array(
            'value'         => '4932',
            'label'         => 'Post Office (Belgium)',
            'isExtraCover'  => false,
            'isPge'         => false,
            'isCod'         => false,
            'isBelgiumOnly' => true,
        ),
    );

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
     * @return array
     */
    public function getAvailableOptions($flat = false)
    {
        return $this->getOptions(array('isCod' => false, 'isBelgiumOnly' => false), $flat, true);
    }

    /**
     * Get a list of available options for Belgium. This is a filtered/modified version of the array supplied by
     * toOptionArray();
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getAvailableBeOptions($flat = false)
    {
        return $this->getOptions(array('isCod' => false, 'isBelgiumOnly' => true), $flat, true);
    }

    /**
     * Get available COD options.
     *
     * @param bool $flat
     *
     * @return array
     */
    public function getAvailableCodOptions($flat = false)
    {
        return $this->getOptions(array('isCod' => true), $flat, true);
    }

    /**
     * Get available PGE options.
     *
     * @param bool $flat
     *
     * @return array
     */
    public function getAvailablePgeOptions($flat = false)
    {
        return $this->getOptions(array('isPge' => true, 'isCod' => false), $flat, true);
    }

    /**
     * Get available PGE options that are also COD.
     *
     * @param bool $flat
     *
     * @return array
     */
    public function getAvailablePgeCodOptions($flat = false)
    {
        return $this->getOptions(array('isPge' => true, 'isCod' => true), $flat, true);
    }
}
