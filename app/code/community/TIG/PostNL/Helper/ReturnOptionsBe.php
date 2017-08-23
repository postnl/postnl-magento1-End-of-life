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
    protected $_returnCodes = array(
        array(
            'value'   => '3250',
            'route'   => 'nl_be',
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
     *
     * @return array|bool|string
     */
    public function get($shippingCountry)
    {
        if ($this->getDomesticCountry() == 'BE' && $shippingCountry == 'BE') {
            return $this->getReturnCodeByRoute('be_be');
        }

        if ($this->getDomesticCountry() == 'NL' && $shippingCountry == 'BE') {
            return $this->getReturnCodeByRoute('nl_be');
        }

        return false;
    }

    /**
     * @param string $route
     * @param bool   $asArray
     *
     * @return array|string
     */
    public function getReturnCodeByRoute($route = 'nl_be', $asArray = false)
    {
        $code = array_filter($this->_returnCodes, function ($code) use ($route) {
            return $code['route'] == $route && $code['default'] == true;
        });

        if ($asArray && count($code) == 1){
            return $code[0];
        }

        return $code[0]['value'];
    }
}