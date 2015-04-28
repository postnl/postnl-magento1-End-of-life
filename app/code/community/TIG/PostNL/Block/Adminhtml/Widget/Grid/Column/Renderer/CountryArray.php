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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_CountryArray
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @var array
     */
    protected $_countries;

    /**
     * Get an array of country codes.
     *
     * @return array
     */
    public function getCountries()
    {
        if ($this->_countries) {
            return $this->_countries;
        }

        /** @var $collection Mage_Directory_Model_Resource_Country_Collection */
        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection->getData() as $row) {
            $this->_countries[$row['iso2_code']] = $row['iso3_code'];
        }

        return $this->_countries;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $value = $row->getDataUsingMethod($this->getColumn()->getIndex());

        $formattedCountryArray = array();
        $iso3Countries = $this->getCountries();
        $countries = explode(',', $value);
        foreach ($countries as $country) {
            if (isset($iso3Countries[$country])) {
                $formattedCountryArray[] = $iso3Countries[$country];
            }
        }

        return implode(',', $formattedCountryArray);
    }
}