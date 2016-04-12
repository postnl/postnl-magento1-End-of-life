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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_DeliveryOptions_Observer_UpdateConfig {
    /**
     * Xpaths for the required config options.
     */
    const XPATH_POSTNL_CIF_ADDRESS_COUNTRY = 'postnl/cif_address/country';
    const XPATH_POSTNL_CIF_LABELS_AND_CONFIRMING_USE_DUTCH_PRODUCTS = 'postnl/cif_labels_and_confirming/use_dutch_products';

    /**
     * The required attributes codes.
     */
    const ATTRIBUTE_CODE_POSTNL_MAX_QTY_FOR_BUSPAKJE = 'postnl_max_qty_for_buspakje';
    const ATTRIBUTE_CODE_POSTNL_PRODUCT_TYPE = 'postnl_product_type';

    /**
     * The array that holds the attributes models.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Check if we need to show some product attributes. If not, hide them.
     *
     * @return $this
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function updateAttributes()
    {
        $country = Mage::getStoreConfig(self::XPATH_POSTNL_CIF_ADDRESS_COUNTRY);
        $use_dutch_products = Mage::getStoreConfig(self::XPATH_POSTNL_CIF_LABELS_AND_CONFIRMING_USE_DUTCH_PRODUCTS);

        $xpaths = array(self::ATTRIBUTE_CODE_POSTNL_MAX_QTY_FOR_BUSPAKJE, self::ATTRIBUTE_CODE_POSTNL_PRODUCT_TYPE);
        foreach ($xpaths as $xpath) {
            $model = $this->getModel($xpath);

            if ($model->getId() !== null) {
                $this->attributes[] = $model;
            }
        }

        /**
         * If the domestic country is NL, always show the options.
         */
        if ($country == 'NL') {
            $this->setIsVisible(true);
        } else {
            /**
             * The country is not NL, and the option use_dutch_products is disabled. So hide this options.
             */
            if ($use_dutch_products == '0') {
                $this->setIsVisible(false);
            } else {
                $this->setIsVisible(true);
            }
        }

        /**
         * Too bad dataHasChangedFor returns false when setIsVisible is false.
         */
        foreach ($this->attributes as $attribute) {
            $attribute->save();
        }

        return $this;
    }

    /**
     * Load a model by the xpath.
     *
     * @param $xpath
     *
     * @return Mage_Eav_Model_Entity_Attribute
     * @throws Mage_Core_Exception
     */
    protected function getModel($xpath) {
        /** @var Mage_Eav_Model_Entity_Attribute $model */
        $model = Mage::getModel('eav/entity_attribute');
        $model->loadByCode(Mage_Catalog_Model_Product::ENTITY, $xpath);

        return $model;
    }

    /**
     * Loop over the models and set the isVisible value.
     *
     * @param $value
     *
     * @return $this
     */
    protected function setIsVisible($value) {
        foreach ($this->attributes as $attribute) {
            $attribute->setIsVisible($value);
        }

        return $this;
    }
}