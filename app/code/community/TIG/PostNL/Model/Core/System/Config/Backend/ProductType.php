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
class TIG_PostNL_Model_Core_System_Config_Backend_ProductType extends Mage_Core_Model_Config_Data
{
    const ATTRIBUTE_CODE_PRODUCT_TYPE = 'postnl_product_type';

    const PRODUCTY_TYPE_NON_FOOD      = '0';
    const PRODUCTY_TYPE_DRY_GROCERIES = '1';
    const PRODUCTY_TYPE_COOL_PRODUCTS = '2';

    /**
     * @var array
     */
    protected $_validOptions = array(
        self::PRODUCTY_TYPE_NON_FOOD,
        self::PRODUCTY_TYPE_DRY_GROCERIES,
        self::PRODUCTY_TYPE_COOL_PRODUCTS,
    );

    /**
     * Validate the value chosen by the user.
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();

        if (!in_array($value, $this->_validOptions)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__("Please enter a valid default value for PostNL product type."),
                ''
            );
        }
    }

    /**
     * The product attribute's default value needs to be updated to the chosen value.
     */
    protected function _afterSave()
    {
        $value = $this->getValue();

        /** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributeModel->loadByCode(Mage_Catalog_Model_Product::ENTITY, self::ATTRIBUTE_CODE_PRODUCT_TYPE);

        /** @noinspection PhpUndefinedMethodInspection */
        $attributeModel->setDefaultValue($value)->save();
    }
}
