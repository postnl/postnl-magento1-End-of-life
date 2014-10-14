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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * Class TIG_PostNL_Model_Core_Shipment_Label
 *
 * @method string getLabelType()
 * @method TIG_PostNL_Model_Core_Shipment_Label setLabelType(string $value)
 * @method int getLabelId()
 * @method TIG_PostNL_Model_Core_Shipment_Label setLabelId(int $value)
 * @method TIG_PostNL_Model_Core_Shipment_Label setLabel(string $value)
 * @method int getParentId()
 * @method TIG_PostNL_Model_Core_Shipment_Label setParentId(int $value)
 */
class TIG_PostNL_Model_Core_Shipment_Label extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'postnl_shipment_label';

    public function _construct()
    {
        $this->_init('postnl_core/shipment_label');
    }

    /**
     * Alias for magic getLabelType()
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->getLabelType();
    }

    /**
     * Gets label contents. Optional parameter to base64 decode the content
     *
     * @param boolean $decode
     *
     * @return string
     */
    public function getLabel($decode = false)
    {
        $label = $this->getData('label');
        if ($decode && $label) {
            $label = base64_decode($label);
        }

        return $label;
    }
}