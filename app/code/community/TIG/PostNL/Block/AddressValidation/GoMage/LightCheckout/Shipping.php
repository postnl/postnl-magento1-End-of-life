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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_AddressValidation_GoMage_LightCheckout_Shipping extends GoMage_Checkout_Block_Onepage_Shipping
{
    protected function _renderFields($fields)
    {
        /** @var TIG_PostNL_Helper_AddressValidation $helper */
        $helper = Mage::helper('postnl/addressValidation');
        if (!$helper->isPostcodeCheckEnabled(null, 'checkout')) {
            return parent::_renderFields($fields);
        }

        $html = '';

        foreach ($fields as $_fields) {
            if (is_array($_fields)) {
                if (count($_fields) > 1) {

                    $_html     = '';
                    $i         = 0;
                    $row_class = array();

                    foreach ($_fields as $field_code) {
                        if ($field_code == 'postcode' || $field_code == 'city' || $field_code == 'country_id') {
                            continue;
                        } elseif ($field_code == 'street') {
                            $html .= $this->getChild('postnl_shipping_postcodecheck')
                                          ->setAddressType('shipping')
                                          ->setAddress($this->getAddress())
                                          ->setCountryHtmlSelect($this->getCountryHtmlSelect('shipping'))
                                          ->toHtml();
                            continue;
                        }

                        $data = array(
                            'prefix'     => $this->prefix,
                            'value'      => $this->getAddress()->getData($field_code),
                            'label'      => @$this->field_code_to_label[$field_code],
                            'input_name' => $this->prefix . '[' . $field_code . ']',
                            'input_id'   => $this->prefix . '_' . $field_code,
                        );

                        if ($this->getConfigData('address_fields/' . $field_code) == 'req') {
                            $data['is_required'] = true;
                        }

                        if (!($template = $this->getData($field_code . '_template'))) {
                            $template = $this->default_address_template;
                        }

                        /** @noinspection PhpUndefinedMethodInspection */
                        $_html .= '<div class="field field-'
                            . $field_code
                            . ' '
                            . ($i % 2 == 0 ? ' field-first ' : ' field-last ')
                            . '">'
                            . $this->getLayout()
                                ->createBlock('gomage_checkout/onepage_' . $this->prefix)
                                ->setTemplate($template)
                                ->addData(
                                    $data
                                )->toHtml()
                            . '</div>';

                        $row_class[] = $field_code;

                        if (++$i == 2) {
                            break;
                        }
                    }

                    $html .= '<li class="fields ' . implode('-', $row_class) . '">' . $_html . '</li>';

                } else {

                    $field_code = array_shift($_fields);
                    if ($field_code == 'postcode' || $field_code == 'city' || $field_code == 'country_id') {
                        continue;
                    } elseif ($field_code == 'street') {
                        $html .= $this->getChild('postnl_shipping_postcodecheck')
                                      ->setAddressType('shipping')
                                      ->setAddress($this->getAddress())
                                      ->setCountryHtmlSelect($this->getCountryHtmlSelect('shipping'))
                                      ->toHtml();
                        continue;
                    }

                    $data = array(
                        'prefix'         => $this->prefix,
                        'address_prefix' => $this->prefix,
                        'value'          => $this->getAddress()->getData($field_code),
                        'label'          => @$this->field_code_to_label[$field_code],
                        'input_name'     => $this->prefix . '[' . $field_code . ']',
                        'input_id'       => $this->prefix . '_' . $field_code,
                    );

                    if ($this->getConfigData('address_fields/' . $field_code) == 'req') {
                        $data['is_required'] = true;
                    }

                    if (!($template = $this->getData($field_code . '_template'))) {
                        $template = $this->default_address_template;
                    }

                    /** @noinspection PhpUndefinedMethodInspection */
                    $html .= '<li>' . $this->getLayout()->createBlock('gomage_checkout/onepage_' . $this->prefix)
                                           ->setTemplate($template)->addData($data)->toHtml() . '</li>';
                }

            }
        }

        return $html;
    }
}
