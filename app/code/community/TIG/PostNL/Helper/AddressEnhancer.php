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
class TIG_PostNL_Helper_AddressEnhancer extends TIG_PostNL_Helper_Data
{
    const STREET_SPLIT_NAME_FROM_NUMBER = '/^(?P<street>\d*[\wäöüßÀ-ÖØ-öø-ÿĀ-Ž\d \'\-\.]+)[,\s]+(?P<number>\d+)\s*(?P<addition>[\wäöüß\d\-\/]*)$/i';

    /** @var array */
    protected $address = array(
        'streetname'           => '',
        'housenumber'          => '',
        'housenumberExtension' => '',
        'fullStreet'           => '',
    );

    /**
     * @param $street
     */
    public function set($street)
    {
        $this->address = $this->appendHouseNumber($street);
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->address;
    }

    /**
     * @param $street
     *
     * @return mixed
     */
    protected function appendHouseNumber($street)
    {
        if (!$street) {
            return $this->address;
        }

        if (is_array($street)) {
            $street = implode( ' ', $street);
        }

        $address = array(
            'streetname' => $street,
        );

        return $this->extractHousenumber($address);
    }

    /**
     * @param $address
     *
     * @return mixed
     * @throws TIG_PostNL_Exception
     */
    protected function extractHousenumber($address)
    {
        $matched = preg_match(self::STREET_SPLIT_NAME_FROM_NUMBER, trim($address['streetname']), $result);
        if (!$matched || !is_array($result)) {
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__(
                        'Unable to extract the house number, could not find a number inside the street value'
                    ),
                    'POSTNL-0124'
                );
        }

        if ($result['street']) {
            $address['streetname'] = trim($result['street']);
        }

        if ($result['number']) {
            $address['housenumber'] = trim($result['number']);
            $address['housenumberExtension'] = trim($result['addition']);
        }

        return $address;
    }
}
