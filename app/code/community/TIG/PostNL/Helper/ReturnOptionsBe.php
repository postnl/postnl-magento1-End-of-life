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
class TIG_PostNL_Helper_ReturnOptionsBe extends TIG_PostNL_Helper_Data
{
    /**
     * Product code: dependent on the origin country.
     * If your company is located in the Netherlands, you must use product code 3250.
     * If your company is located in Belgium you must use product code 4882
     */
    protected $_returnCodes = array(
        array(
            'value'   => '3250',
            'route'   => 'nl_be', // Heenstroom
            'default' => true,
        ),
        array(
            'value'   => '4882',
            'route'   => 'be_be',
            'default' => true
        )
    );

    /**
     * @param $shippingCountry
     * @param $asArray
     *
     * @return array|bool|string
     */
    public function get($shippingCountry, $asArray = false)
    {
        if ($this->_domesticCountry() == 'BE' && $shippingCountry == 'BE') {
            return $this->getReturnCodeByRoute('be_be', $asArray);
        }

        if ($this->_domesticCountry() == 'NL' && $shippingCountry == 'BE') {
            return $this->getReturnCodeByRoute('nl_be', $asArray);
        }

        return false;
    }

    /**
     * @param string $route
     * @param bool   $asArray
     *
     * @return array|string
     */
    public function getReturnCodeByRoute($route = 'nl_be', $asArray)
    {
        $code = array_filter($this->_returnCodes, function ($code) use ($route) {
            return $code['route'] == $route && $code['default'] == true;
        });

        $code = array_values($code);
        if ($asArray && count($code) == 1){
            return $code[0];
        }

        return $code[0]['value'];
    }

    /**
     * @return mixed
     */
    protected function _domesticCountry()
    {
        return Mage::getStoreConfig(self::XPATH_SENDER_COUNTRY, Mage_Core_Model_App::ADMIN_STORE_ID);
    }
}
