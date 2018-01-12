<?php
class TIG_PostNL_Test_Unit_Model_Core_Cif_AbstractTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_Core_Cif_Abstract
     */
    protected function _getInstance()
    {
        return $this->getMockForAbstractClass(
            'TIG_PostNL_Model_Core_Cif_Abstract',
            array(),
            '',
            true,
            true,
            true,
            array('someNonExistingMethod')
        );
    }

    /**
     * @test
     */
    public function callShouldBeCallable()
    {
        $cif = $this->_getInstance();
        $this->assertInstanceOf('TIG_PostNL_Model_Core_Cif_Abstract', $cif);

        $isCallable = is_callable(array($cif, 'call'));

        $this->assertTrue($isCallable);
    }

    /**
     * @test
     */
    public function shouldBeAbleToGetAHelper()
    {
        $cif = $this->_getInstance();
        $helper = $cif->getHelper();

        $this->assertInstanceOf('TIG_PostNL_Helper_Data', $helper);
    }

    /**
     * @test
     *
     * @dataProvider testModeProvider
     */
    public function testModeShouldBeSet($return)
    {
        $this->resetMagento();

        Mage::unregister('postnl_test_mode_allowed');
        Mage::register('postnl_test_mode_allowed', true);

        Mage::app()->getStore()->setConfig('postnl/cif/mode', (int) $return);

        $cif = $this->_getInstance();

        $testMode = $cif->isTestMode();

        $this->assertSame($return, $testMode);
    }

    /**
     * @test
     * @test
     *
     * @dataProvider testModeProvider
     */
    public function apikeyShouldbeRetrieved($testMode)
    {
        $helperMock = $this->getMock('Mage_Core_Helper_Data');
        $helperMock->expects($this->once())
                   ->method('decrypt')
                   ->with('testApikey')
                   ->will($this->returnValue('testApikey2'));

        $this->setHelperMock('core', $helperMock);

        $instance = $this->_getInstance();
        $instance->setTestMode($testMode);

        $storeCode = Mage::app()->getStore()->getCode();

        if ($testMode) {
            $xPath = $instance::XPATH_TEST_APIKEY;
        } else {
            $xPath = $instance::XPATH_LIVE_APIKEY;
        }

        Mage::getConfig()->setNode("stores/{$storeCode}/{$xPath}", 'testApikey');

        $apikey = $instance->getApikey();
        $expected = 'testApikey2';
        $this->assertEquals($expected, $apikey);
    }

    public function testModeProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @test
     *
     * @expectedException TIG_PostNL_Exception
     * @expectedExceptionCode POSTNL-0053
     */
    public function getSoapClientShouldThrowAnExceptionIfTheWsdlTypeIsUnknown()
    {
        $instance = $this->_getInstance();
        $instance->getSoapClient('nonExistingWsdlType');
    }
}
