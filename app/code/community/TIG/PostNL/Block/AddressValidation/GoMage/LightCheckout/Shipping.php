<?php

class TIG_PostNL_Block_AddressValidation_GoMage_LightCheckout_Shipping extends GoMage_Checkout_Block_Onepage_Shipping
{
    protected function _renderFields($fields)
    {
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

                        $_html .= '<div class="field field-' . $field_code . ' ' . ($i % 2 == 0 ? ' field-first ' : ' field-last ') . '">' . $this->getLayout()->createBlock('gomage_checkout/onepage_' . $this->prefix)->setTemplate($template)->addData($data)->toHtml() . '</div>';

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

                    $html .= '<li>' . $this->getLayout()->createBlock('gomage_checkout/onepage_' . $this->prefix)->setTemplate($template)->addData($data)->toHtml() . '</li>';
                }

            }
        }

        return $html;
    }
}