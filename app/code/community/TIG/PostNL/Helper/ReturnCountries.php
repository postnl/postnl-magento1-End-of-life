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
class TIG_PostNL_Helper_ReturnCountries extends Mage_Core_Helper_Abstract
{
    /**
     * @return array
     */
    public function get()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        $allowedCountries = array();
        if ($helper->isReturnsEnabled()) {
            $allowedCountries[] = 'NL';
        }

        if ($helper->isReturnsEnabled(false, true)) {
            $allowedCountries[] = 'BE';
        }

        return $allowedCountries;
    }

    /**
     * @param $countryId
     *
     * @return bool
     */
    public function isAllowed($countryId)
    {
        $allowedCountries = $this->get();
        return in_array($countryId, $allowedCountries);
    }
}
