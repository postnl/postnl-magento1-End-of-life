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

/**
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();

$installer->resetWebserviceVersions();

if (!$installer->getAttribute('catalog_product', 'postnl_product_parcel_count')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_product_parcel_count',
        array(
            'group'                      => 'PostNL',
            'sort_order'                 => 180,
            'frontend'                   => '',
            'frontend_class'             => 'validate-digits',
            'default'                    => '1',
            'label'                      => 'PostNL product parcel count',
            'note'                       => 'Dit product moet over dit aantal colli verspreid worden. ' .
                                            'Deze functionaliteit werkt alleen als Extra@Home geactiveerd is.',
            'input'                      => 'text',
            'type'                       => 'varchar',
            'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible'                    => true,
            'required'                   => false,
            'searchable'                 => false,
            'filterable'                 => false,
            'filterable_in_search'       => false,
            'unique'                     => false,
            'comparable'                 => false,
            'visible_on_front'           => false,
            'visible_in_advanced_search' => false,
            'is_html_allowed_on_front'   => false,
            'used_in_product_listing'    => false,
            'user_defined'               => false,
            'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
        )
    );
}

if (!$installer->getAttribute('catalog_product', 'postnl_product_volume')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_product_volume',
        array(
            'group'                      => 'PostNL',
            'sort_order'                 => 190,
            'frontend'                   => '',
            'frontend_class'             => 'validate-digits',
            'default'                    => '1',
            'label'                      => 'PostNL product volume',
            'note'                       => 'Wanneer het product type Extra@Home is, dan is dit veld verplicht. ' .
                                            'Vul hier het aantal kubieke centimeters in, bijvoorbeeld 30000',
            'input'                      => 'text',
            'type'                       => 'varchar',
            'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible'                    => true,
            'required'                   => false,
            'searchable'                 => false,
            'filterable'                 => false,
            'filterable_in_search'       => false,
            'unique'                     => false,
            'comparable'                 => false,
            'visible_on_front'           => false,
            'visible_in_advanced_search' => false,
            'is_html_allowed_on_front'   => false,
            'used_in_product_listing'    => false,
            'user_defined'               => false,
            'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
        )
    );
}

/***********************************************************************************************************************
 * POSTNL ORDER
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/order');

if (!$conn->tableColumnExists($tableName, 'parcel_count')) {
    $conn->addColumn(
        $tableName,
        'parcel_count',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => '10',
            'nullable' => true,
            'comment'  => 'Estimated parcel count',
            'after'    => 'product_code',
        )
    );
}

$installer->endSetup();
