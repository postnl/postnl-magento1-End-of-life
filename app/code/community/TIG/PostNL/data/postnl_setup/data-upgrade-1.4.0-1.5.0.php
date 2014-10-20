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
    please send an email
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
 */

/**
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

$settingsToMove = array(
    'postnl/cif_return_address/firstname'                                   => 'postnl/cif_address/return_firstname',
    'postnl/cif_return_address/lastname'                                    => 'postnl/cif_address/return_lastname',
    'postnl/cif_return_address/company'                                     => 'postnl/cif_address/return_company',
    'postnl/cif_return_address/department'                                  => 'postnl/cif_address/return_department',
    'postnl/cif_return_address/street_name'                                 => 'postnl/cif_address/return_street_name',
    'postnl/cif_return_address/house_number'                                => 'postnl/cif_address/return_house_number',
    'postnl/cif_return_address/postcode'                                    => 'postnl/cif_address/return_postcode',
    'postnl/cif_return_address/city'                                        => 'postnl/cif_address/return_city',
    'postnl/cif_return_address/region'                                      => 'postnl/cif_address/return_region',
    'postnl/cif_return_address/country'                                     => 'postnl/cif_address/return_country',
    'postnl/general/active'                                                 => 'postnl/cif/mode',
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
    'postnl/cif_sender_address/street_name'                                 => 'postnl/cif_address/street_name',
    'postnl/cif_sender_address/house_number'                                => 'postnl/cif_address/house_number',
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
);

foreach ($settingsToMove as $from => $to) {
    $installer->moveConfigSetting($from, $to, false);
}

$installer->clearConfigCache();
