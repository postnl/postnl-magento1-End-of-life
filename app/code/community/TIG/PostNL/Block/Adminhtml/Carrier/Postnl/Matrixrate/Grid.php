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
class TIG_PostNL_Block_Adminhtml_Carrier_Postnl_Matrixrate_Grid
    extends Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
{
    /**
     * Define grid properties.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('postnlShippingMatrixrateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('postnl_carrier/matrixrate_collection');
        $collection->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            array(
                'header'   => $this->__('Country'),
                'index'    => 'dest_country_id',
                'default'  => '*',
                'renderer' => 'postnl_adminhtml/widget_grid_column_renderer_countryArray'
            )
        );

        $this->addColumn(
            'dest_region',
            array(
                'header'  => $this->__('Region/State'),
                'index'   => 'dest_region',
                'default' => '*',
            )
        );

        $this->addColumn(
            'dest_zip',
            array(
                'header'  => $this->__('Zip/Postal Code'),
                'index'   => 'dest_zip',
                'default' => '*',
            )
        );

        $this->addColumn(
            'weight',
            array(
                'header'  => $this->__('Minimum Order Weight'),
                'index'   => 'weight',
                'default' => 0,
            )
        );

        $this->addColumn(
            'subtotal',
            array(
                'header'  => $this->__('Minimum Order Amount'),
                'index'   => 'subtotal',
                'default' => 0,
            )
        );

        $this->addColumn(
            'qty',
            array(
                'header'  => $this->__('Minimum Quantity'),
                'index'   => 'qty',
                'default' => 0,
            )
        );

        $this->addColumn(
            'parcel_type',
            array(
                'header'  => $this->__('Parcel Type'),
                'index'   => 'parcel_type',
                'default' => '*',
            )
        );

        $this->addColumn(
            'price',
            array(
                'header' => $this->__('Shipping Price'),
                'index'  => 'price',
            )
        );

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}