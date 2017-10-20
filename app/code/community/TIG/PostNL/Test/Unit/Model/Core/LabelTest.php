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
class TIG_PostNL_Test_Unit_Model_Core_LabelTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_Core_Label
     */
    protected function _getInstance()
    {
        return Mage::getModel('postnl_core/label');
    }

    public function createPackingSlipLabelProvider()
    {
        return array(
            'single page merged' => array(
                'Packingslips/packingslip_singlepage_merged_before_label.pdf',
                'Packingslips/packingslip_singlepage_merged_before_packingslip.pdf',
                'Packingslips/packingslip_singlepage_merged_after.pdf',
            ),
            'two pages merged' => array(
                'Packingslips/packingslip_twopages_merged_before_label.pdf',
                'Packingslips/packingslip_twopages_merged_before_packingslip.pdf',
                'Packingslips/packingslip_twopages_merged_after.pdf',
            ),
        );
    }

    /**
     * @param $label
     * @param $packingslip
     * @param $expected
     *
     * @dataProvider createPackingSlipLabelProvider
     */
    public function testCreatePackingSlipLabel($label, $packingslip, $expected)
    {
        $this->requireImagick();

        $label = $this->getLabel($label);
        $packingslip = $this->getLabel($packingslip);

        /** @var TIG_PostNL_Model_Core_Shipment_Label $labelModel */
        $labelModel = Mage::getModel('postnl_core/shipment_label');
        $labelModel->setLabel($label);
        $labelModel->setLabelType(TIG_PostNL_Model_Core_Shipment_Label::LABEL_TYPE_LABEL);

        $instance = $this->_getInstance();
        $result = $instance->createPackingSlipLabel($labelModel, base64_decode($packingslip));

        $expected = $this->getLabel($expected);
        $this->compareImageOrPdf($result, base64_decode($expected));
    }
}
