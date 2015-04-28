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
 * If you are unable to obtain it through the world-wide-web,
 * please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

$settingsToMove = array(
    'postnl/cif_return_address/firstname'                                   => 'postnl/cif_address/return_firstname',
    'postnl/cif_return_address/lastname'                                    => 'postnl/cif_address/return_lastname',
    'postnl/cif_return_address/company'                                     => 'postnl/cif_address/return_company',
    'postnl/cif_return_address/department'                                  => 'postnl/cif_address/return_department',
    'postnl/cif_return_address/streetname'                                  => 'postnl/cif_address/return_streetname',
    'postnl/cif_return_address/housenumber'                                 => 'postnl/cif_address/return_housenumber',
    'postnl/cif_return_address/housenumber_extension'                       => 'postnl/cif_address/return_housenumber_extension',
    'postnl/cif_return_address/postcode'                                    => 'postnl/cif_address/return_postcode',
    'postnl/cif_return_address/city'                                        => 'postnl/cif_address/return_city',
    'postnl/cif_return_address/region'                                      => 'postnl/cif_address/return_region',
    'postnl/cif_return_address/country'                                     => 'postnl/cif_address/return_country',
    'postnl/cif_return_address/use_sender_address'                          => 'postnl/cif_address/use_sender_address',
    'postnl/cif/use_globalpack'                                             => 'postnl/cif_globalpack_settings/use_globalpack',
    'postnl/cif/global_barcode_type'                                        => 'postnl/cif_globalpack_settings/global_barcode_type',
    'postnl/cif/global_barcode_range'                                       => 'postnl/cif_globalpack_settings/global_barcode_range',
    'postnl/cif_labels_and_confirming/weight_per_parcel'                    => 'postnl/packing_slip/weight_per_parcel',
    'postnl/cif_labels_and_confirming/weight_unit'                          => 'postnl/packing_slip/weight_unit',
    'postnl/cif_labels_and_confirming/shipment_reference_type'              => 'postnl/packing_slip/shipment_reference_type',
    'postnl/cif_labels_and_confirming/custom_shipment_reference'            => 'postnl/packing_slip/custom_shipment_reference',
    'postnl/cif_product_options/default_cod_product_option'                 => 'postnl/cod/default_cod_product_option',
    'postnl/cif_product_options/default_evening_cod_product_option'         => 'postnl/cod/default_evening_cod_product_option',
    'postnl/cif_product_options/default_pakjegemak_cod_product_option'      => 'postnl/cod/default_pakjegemak_cod_product_option',
    'postnl/cif_product_options/default_pge_cod_product_option'             => 'postnl/cod/default_pge_cod_product_option',
    'postnl/cif_labels_and_confirming/send_track_and_trace_email'           => 'postnl/track_and_trace/send_track_and_trace_email',
    'postnl/cif_labels_and_confirming/track_and_trace_email_template'       => 'postnl/track_and_trace/track_and_trace_email_template',
    'postnl/cif_labels_and_confirming/send_copy'                            => 'postnl/track_and_trace/send_copy',
    'postnl/cif_labels_and_confirming/copy_to'                              => 'postnl/track_and_trace/copy_to',
    'postnl/cif_labels_and_confirming/copy_method'                          => 'postnl/track_and_trace/copy_method',
    'postnl/cif_labels_and_confirming/show_grid_options'                    => 'postnl/grid/show_grid_options',
    'postnl/cif_labels_and_confirming/show_buspakje_option'                 => 'postnl/grid/show_buspakje_option',
    'postnl/cif_labels_and_confirming/order_grid_columns'                   => 'postnl/grid/order_grid_columns',
    'postnl/cif_labels_and_confirming/order_grid_massaction_default'        => 'postnl/grid/order_grid_massaction_default',
    'postnl/cif_labels_and_confirming/shipping_grid_columns'                => 'postnl/grid/shipping_grid_columns',
    'postnl/cif_labels_and_confirming/shipping_grid_massaction_default'     => 'postnl/grid/shipping_grid_massaction_default',
    'postnl/cif_product_options/supported_product_options'                  => 'postnl/grid/supported_product_options',
    'postnl/cif_sender_address/firstname'                                   => 'postnl/cif_address/firstname',
    'postnl/cif_sender_address/lastname'                                    => 'postnl/cif_address/lastname',
    'postnl/cif_sender_address/company'                                     => 'postnl/cif_address/company',
    'postnl/cif_sender_address/department'                                  => 'postnl/cif_address/department',
    'postnl/cif_sender_address/streetname'                                  => 'postnl/cif_address/streetname',
    'postnl/cif_sender_address/housenumber'                                 => 'postnl/cif_address/housenumber',
    'postnl/cif_sender_address/housenumber_extension'                       => 'postnl/cif_address/housenumber_extension',
    'postnl/cif_sender_address/postcode'                                    => 'postnl/cif_address/postcode',
    'postnl/cif_sender_address/city'                                        => 'postnl/cif_address/city',
    'postnl/cif_sender_address/region'                                      => 'postnl/cif_address/region',
    'postnl/cif_sender_address/country'                                     => 'postnl/cif_address/country',
    'postnl/cif_product_options/default_product_option'                     => 'postnl/grid/default_product_option',
    'postnl/cif_product_options/use_alternative_default'                    => 'postnl/grid/use_alternative_default',
    'postnl/cif_product_options/alternative_default_max_amount'             => 'postnl/grid/alternative_default_max_amount',
    'postnl/cif_product_options/alternative_default_option'                 => 'postnl/grid/alternative_default_option',
    'postnl/cif_product_options/default_evening_product_option'             => 'postnl/grid/default_evening_product_option',
    'postnl/cif_product_options/default_pakjegemak_product_option'          => 'postnl/grid/default_pakjegemak_product_option',
    'postnl/cif_product_options/default_pge_product_option'                 => 'postnl/grid/default_pge_product_option',
    'postnl/cif_product_options/default_buspakje_product_option'            => 'postnl/grid/default_buspakje_product_option',
    'postnl/cif_product_options/default_stated_address_only_product_option' => 'postnl/grid/default_stated_address_only_product_option',
    'postnl/cif_product_options/default_pakketautomaat_product_option'      => 'postnl/delivery_options/default_pakketautomaat_product_option',
    'postnl/cif_product_options/default_eu_product_option'                  => 'postnl/grid/default_eu_product_option',
    'postnl/cif_product_options/default_eu_be_product_option'               => 'postnl/grid/default_eu_be_product_option',
    'postnl/cif_product_options/default_global_product_option'              => 'postnl/cif_globalpack_settings/default_global_product_option',
    'postnl/cif_labels_and_confirming/use_buspakje'                         => 'postnl/delivery_options/use_buspakje',
    'postnl/cif_labels_and_confirming/buspakje_calculation_mode'            => 'postnl/delivery_options/buspakje_calculation_mode',
    'postnl/cif_address/use_postcode_check'                                 => 'postnl/cif_labels_and_confirming/use_postcode_check',
    'postnl/cif_address/postcode_check_in_checkout'                         => 'postnl/cif_labels_and_confirming/postcode_check_in_checkout',
    'postnl/cif_address/postcode_check_in_addressbook'                      => 'postnl/cif_labels_and_confirming/postcode_check_in_addressbook',
    'postnl/cif_address/postcode_check_max_attempts'                        => 'postnl/cif_labels_and_confirming/postcode_check_max_attempts',
    'postnl/cif_address/postcode_check_timeout'                             => 'postnl/cif_labels_and_confirming/postcode_check_timeout',
    'postnl/cif_address/split_street'                                       => 'postnl/cif_labels_and_confirming/split_street',
    'postnl/cif_address/streetname_field'                                   => 'postnl/cif_labels_and_confirming/streetname_field',
    'postnl/cif_address/housenr_field'                                      => 'postnl/cif_labels_and_confirming/housenr_field',
    'postnl/cif_address/split_housenr'                                      => 'postnl/cif_labels_and_confirming/split_housenr',
    'postnl/cif_address/housenr_extension_field'                            => 'postnl/cif_labels_and_confirming/housenr_extension_field',
    'postnl/cif_address/building_name_field'                                => 'postnl/cif_labels_and_confirming/building_name_field',
    'postnl/cif_address/department_field'                                   => 'postnl/cif_labels_and_confirming/department_field',
    'postnl/cif_address/doorcode_field'                                     => 'postnl/cif_labels_and_confirming/doorcode_field',
    'postnl/cif_address/floor_field'                                        => 'postnl/cif_labels_and_confirming/floor_field',
    'postnl/cif_address/remark_field'                                       => 'postnl/cif_labels_and_confirming/remark_field',
    'postnl/cif_address/area_field'                                         => 'postnl/cif_labels_and_confirming/area_field',
);

foreach ($settingsToMove as $from => $to) {
    $installer->moveConfigSettingInDb($from, $to);
}

$installer->moveActiveSetting()
          ->clearConfigCache();
