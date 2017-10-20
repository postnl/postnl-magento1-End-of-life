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

class TIG_PostNL_Test_Unit_Model_Core_Shipment_LabelTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return false|TIG_PostNL_Model_Core_Shipment_Label
     */
    protected function _getInstance()
    {
        return Mage::getModel('postnl_core/shipment_label');
    }

    public function testPgBeLabelsAreRotatedWhenTheModelIsSaved()
    {
        // @todo: Find out why this is failing on Travis
        if (getenv('TRAVIS_BUILD_NUMBER')) {
            $this->markTestSkipped('For some reason the Imagick stuff is not working on Travis');
        }

        $this->requireImagick();

        $label = $this->getLabel('pg_be_before_save.pdf');

        $instance = $this->_getInstance();
        $instance->setResize(true);
        $instance->setLabel($label);
        $instance->setLabelType(TIG_PostNL_Model_Core_Shipment_Label::LABEL_TYPE_LABEL_COMBI);
        $instance->save();

        $result = $instance->getLabel();
        $expected = $this->getLabel('pg_be_after_save.pdf');

        $this->compareImageOrPdf(base64_decode($result), base64_decode($expected));
        $this->assertEquals(TIG_PostNL_Model_Core_Shipment_Label::LABEL_TYPE_LABEL, $instance->getLabelType());
    }
}
