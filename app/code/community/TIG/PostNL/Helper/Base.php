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

class TIG_PostNL_Helper_Base extends Mage_Core_Helper_Abstract
{
    protected $_helpers = array();

    /**
     * Gets a PostNL helper object.
     *
     * @param string $type
     *
     * @return Mage_Core_Helper_Abstract|TIG_PostNL_Helper_Data|TIG_PostNL_Helper_DeliveryOptions
     */
    public function getHelper($type = 'data')
    {
        if (array_key_exists($type, $this->_helpers)) {
            return $this->_helpers[$type];
        }

        $helper = Mage::helper('postnl/' . $type);

        $this->_helpers[$type] = $helper;
        return $helper;
    }
}
