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
class TIG_PostNL_Helper_ProductLimitation extends TIG_PostNL_Helper_Base
{
    /**
     * @param $productCode
     * @param $keyToCheck
     * @param $expect
     *
     * @return bool
     */
    public function check($productCode, $keyToCheck, $expect)
    {
        /** @var TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions $sourceModel */
        $sourceModel = Mage::getModel('postnl_core/system_config_source_allProductOptions');
        $option = $sourceModel->getOptionsByCode($productCode);
        if (!$option) {
            return false;
        }

        if (!isset($option[$keyToCheck])) {
            return false;
        }

        return $option[$keyToCheck] == $expect;
    }
}