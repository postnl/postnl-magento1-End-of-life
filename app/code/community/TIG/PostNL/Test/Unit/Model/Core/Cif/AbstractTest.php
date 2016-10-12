<?php
class TIG_PostNL_Test_Unit_Model_Core_Cif_AbstractTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_Core_Cif_Abstract_Fake
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
     *
     * @dataProvider testModeProvider
     */
    public function usernameShouldbeRetrieved($testMode)
    {
        $instance = $this->_getInstance();
        $instance->setTestMode($testMode);

        $storeCode = Mage::app()->getStore()->getCode();

        if ($testMode) {
            $xPath = $instance::XPATH_TEST_USERNAME;
        } else {
            $xPath = $instance::XPATH_LIVE_USERNAME;
        }

        Mage::getConfig()->setNode("stores/{$storeCode}/{$xPath}", 'testUser');

        $username = $instance->getUsername();
        $this->assertEquals('testUser', $username);
    }

    /**
     * @test
     *
     * @dataProvider testModeProvider
     */
    public function passwordShouldbeRetrieved($testMode)
    {
        $helperMock = $this->getMock('Mage_Core_Helper_Data');
        $helperMock->expects($this->once())
                   ->method('decrypt')
                   ->with('testPass')
                   ->will($this->returnValue('testPass2'));

        $this->setHelperMock('core', $helperMock);

        $instance = $this->_getInstance();
        $instance->setTestMode($testMode);

        $storeCode = Mage::app()->getStore()->getCode();

        if ($testMode) {
            $xPath = $instance::XPATH_TEST_PASSWORD;
        } else {
            $xPath = $instance::XPATH_LIVE_PASSWORD;
        }

        Mage::getConfig()->setNode("stores/{$storeCode}/{$xPath}", 'testPass');

        $password = $instance->getPassword();
        $expected = sha1('testPass2');
        $this->assertEquals($expected, $password);
    }

    public function testModeProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @param $wsdlType
     *
     * @test
     *
     * @dataProvider barcodeTypeProvider
     */
    public function getSoapClientShouldReturnAZendSoapClientInstance($wsdlType)
    {
        $instance = $this->_getInstance();
        $client = $instance->getSoapClient($wsdlType);

        $this->assertInstanceOf(
            'SoapClient',
            $client,
            'Unable to get a SoapClient instance for wsdl type: ' . $wsdlType
        );
    }

    public function barcodeTypeProvider()
    {
        return array(
            array('barcode'),
            array('confirming'),
            array('labelling'),
            array('shippingstatus'),
            array('checkout'),
            array('deliverydate'),
            array('timeframe'),
            array('location'),
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