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
class TIG_PostNL_Test_Unit_Controllers_DeliveryOptionsControllerTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected $_class = 'TIG_PostNL_DeliveryOptionsController';

    protected $mockHelper = false;

    protected $mockedHelpers = array();

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . '/../../../controllers/DeliveryOptionsController.php');
    }

    /**
     * @param string $helperClass
     * @param object $mock
     *
     * @return TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
     */
    public function setHelperMock($helperClass, $mock)
    {
        $this->mockedHelpers[$helperClass] = $mock;

        return parent::setHelperMock($helperClass, $mock);
    }

    /**
     * Reset the helpers back to the original.
     *
     * @return $this
     */
    public function breakDown()
    {
        if ($this->mockHelper) {
            foreach ($this->mockedHelpers as $helper => $instance) {
                parent::setHelperMock($helper, $instance);
            }
        }

        return $this;
    }

    /**
     * @param array $data
     *
     * @return TIG_PostNL_DeliveryOptionsController
     */
    protected function _getInstance($data = array())
    {
        $this->prepareFrontendDispatch();
        $request = Mage::app()->getRequest();
        $request->setPost($data);

        $response = Mage::app()->getResponse();

        return new $this->_class($request, $response);
    }

    /**
     * @test
     */
    public function shouldBeTheRightClass()
    {
        $controller = $this->_getInstance();

        $this->assertInstanceOf('TIG_PostNL_DeliveryOptionsController', $controller);
    }

    /**
     * @test
     */
    public function shouldGetValidTypes()
    {
        $controller = $this->_getInstance();

        $validTypes = $controller->getValidTypes();
        $this->assertTrue(is_array($validTypes));
    }

    /**
     * @test
     */
    public function shouldGetSpecificValidTypes()
    {
        $controller = $this->_getInstance();

        $controller->setValidTypes(array('test'));
        $validTypes = $controller->getValidTypes();

        $this->assertTrue(in_array('test', $validTypes));
    }

    /**
     * @test
     */
    public function saveSelectedOptionActionShouldBeCallable()
    {
        $instance = $this->_getInstance();
        $isCallable = is_callable((array($instance, 'saveSelectedOptionAction')));

        $this->assertTrue($isCallable);
    }

    /**
     * @test
     *
     * @depends saveSelectedOptionActionShouldBeCallable
     */
    public function shouldRejectSaveSelectedOptionActionWithoutAjax()
    {
        $controller = $this->_getInstance();
        $controller->setCanUseDeliveryOptions(true);
        $controller->saveSelectedOptionAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'not_allowed');

        $this->assertTrue($dataMissing !== false, 'Check AJAX fails!');
    }

    /**
     * @param $data
     * @param $success
     *
     * @test
     *
     * @depends saveSelectedOptionActionShouldBeCallable
     *
     * @dataProvider saveSelectedPostDataWithoutAddressProvider
     */
    public function shouldValidatePostDataWithoutAnAddress($data, $success)
    {
        $controller = $this->_getInstance($data);

        $mockService = $this->getMock('TIG_PostNL_Model_DeliveryOptions_Service');
        if ($success) {
            $mockService->expects($this->once())
                        ->method('saveDeliveryOption')
                        ->withAnyParameters()
                        ->will($this->returnSelf());
        }

        $mockCoreHelper = $this->getMock('Mage_Core_Helper_Data');
        $mockCoreHelper->expects($this->any())
                       ->method('jsonDecode')
                       ->withAnyParameters()
                       ->will($this->returnArgument(0));
        $this->setHelperMock('core', $mockCoreHelper);

        $controller->setService($mockService);

        $controller->setCanUseDeliveryOptions(true);

        $controller->saveSelectedOptionAction();

        $body = Mage::app()->getResponse()->getBody();

        if ($success) {
            $this->assertTrue(strpos($body, 'OK') !== false);
        } else {
            $this->assertTrue(strpos($body, 'invalid_data') !== false);
        }
    }

    /**
     * @return array
     */
    public function saveSelectedPostDataWithoutAddressProvider()
    {
        return array(
            array(
                'data' => array(
                    'isAjax' => true,
                    'type' => 'PGE',
                    'date' => '18-03-2014',
                    'costs' => array(
                        'incl' => 1,
                        'excl' => 0.5
                    ),
                ),
                true
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'type' => 'testfail',
                    'date' => '18-03-2014',
                    'costs' => array(
                        'incl' => 1,
                        'excl' => 0.5
                    ),
                ),
                false
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'type' => 'PGE',
                    'date' => '180-03-2014',
                    'costs' => array(
                        'incl' => 1,
                        'excl' => 0.5
                    ),
                ),
                false
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'type' => 'PGE',
                    'date' => '18-03-2014',
                    'costs' => array(
                        'incl' => '1,50',
                        'excl' => '1,50'
                    ),
                ),
                false
            ),
        );
    }

    /**
     * @test
     */
    public function saveOptionCostsActionShouldBeCallable()
    {
        $instance = $this->_getInstance();
        $isCallable = is_callable((array($instance, 'saveOptionCostsAction')));

        $this->assertTrue($isCallable);
    }

    public function shouldValidateAddressDataDataProvider()
    {
        return array(
            array(
                '',
                'POSTNL-0139',
                false,
            ),
            array(
                array(
                    'City' => 'ANTWERPEN',
                    'Countrycode' => 'BE',
                    'HouseNr' => '2',
                    'HouseNrExt' => '3',
                    'Street' => 'BRESSTRAAT',
                    'Zipcode' => '2018',
                    'Name' => 'DE STER',
                ),
                null,
                true,
            ),
        );
    }

    /**
     * @test
     *
     * @depends saveOptionCostsActionShouldBeCallable
     */
    public function shouldRejectSaveOptionCostsActionWithoutAjax()
    {
        $controller = $this->_getInstance();
        $controller->setCanUseDeliveryOptions(true);
        $controller->saveOptionCostsAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'not_allowed');

        $this->assertTrue($dataMissing !== false, 'Check AJAX fails!');
    }

    /**
     * @test
     *
     * @depends saveSelectedOptionActionShouldBeCallable
     */
    public function shouldRejectSaveOptionCostsActionIfUnableToUseDeliveryOptions()
    {
        $controller = $this->_getInstance(array('isAjax' => true));
        $controller->setCanUseDeliveryOptions(false);
        $controller->saveOptionCostsAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'not_allowed');

        $this->assertTrue($dataMissing !== false);
    }

    /**
     * @test
     *
     * @depends saveOptionCostsActionShouldBeCallable
     *
     * @dataProvider saveOptionCostsDataProvider
     */
    public function shouldValidatePostDataForSaveOptionCostsAction($data, $success)
    {
        $controller = $this->_getInstance($data);

        $mockService = $this->getMock('TIG_PostNL_Model_DeliveryOptions_Service');
        if ($success) {
            $mockService->expects($this->once())
                        ->method('saveOptionCosts')
                        ->withAnyParameters()
                        ->will($this->returnSelf());
        }

        $mockCoreHelper = $this->getMock('Mage_Core_Helper_Data');
        $mockCoreHelper->expects($this->any())
                       ->method('jsonDecode')
                       ->withAnyParameters()
                       ->will($this->returnArgument(0));
        $this->setHelperMock('core', $mockCoreHelper);

        $controller->setService($mockService);

        $controller->setCanUseDeliveryOptions(true);

        $controller->saveOptionCostsAction();

        $body = Mage::app()->getResponse()->getBody();

        if ($success) {
            $this->assertTrue(strpos($body, 'OK') !== false);
        } else {
            $this->assertTrue(strpos($body, 'invalid_data') !== false);
        }
    }

    /**
     * @return array
     */
    public function saveOptionCostsDataProvider()
    {
        return array(
            array(
                'data' => array(
                    'isAjax' => true,
                    'costs' => array(
                        'incl' => 1,
                        'excl' => 0.5
                    ),
                ),
                'success' => true
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'costs' => array(
                        'incl' => 1,
                        'excl' => 0.5
                    ),
                ),
                'success' => true
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'costs' => array(
                        'incl' => 1,
                        'excl' => 0.5
                    ),
                ),
                'success' => true
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'costs' => array(
                        'incl' => 3,
                        'excl' => 3
                    ),
                ),
                'success' => false
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'costs' => array(
                        'incl' => 'test',
                        'excl' => 'test'
                    ),
                ),
                'success' => false
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'costs' => array(
                        'incl' => '1test',
                        'excl' => '1test'
                    ),
                ),
                'success' => false
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'costs' => array(),
                ),
                'success' => false
            ),
        );
    }

    /**
     * @test
     */
    public function savePhoneActionShouldBeCallable()
    {
        $instance = $this->_getInstance();
        $isCallable = is_callable((array($instance, 'savePhoneNumberAction')));

        $this->assertTrue($isCallable);
    }

    /**
     * @test
     *
     * @depends savePhoneActionShouldBeCallable
     */
    public function shouldRejectSavePhoneActionWithoutAjax()
    {
        $controller = $this->_getInstance();
        $controller->setCanUseDeliveryOptions(true);
        $controller->savePhoneNumberAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'not_allowed');

        $this->assertTrue($dataMissing !== false, 'Check AJAX fails!');
    }

    /**
     * @test
     *
     * @depends savePhoneActionShouldBeCallable
     */
    public function shouldRejectSavePhoneActionIfUnableToUseDeliveryOptions()
    {
        $controller = $this->_getInstance(array('isAjax' => true));
        $controller->setCanUseDeliveryOptions(false);
        $controller->savePhoneNumberAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'not_allowed');

        $this->assertTrue($dataMissing !== false);
    }

    /**
     * @test
     *
     * @depends savePhoneActionShouldBeCallable
     *
     * @dataProvider savePhoneDataProvider
     */
    public function shouldValidatePostDataForSavePhoneAction($data, $success)
    {
        $controller = $this->_getInstance($data);

        $mockService = $this->getMock('TIG_PostNL_Model_DeliveryOptions_Service');
        if ($success) {
            $mockService->expects($this->once())
                        ->method('saveMobilePhoneNumber')
                        ->withAnyParameters()
                        ->will($this->returnSelf());
        }

        $controller->setService($mockService);

        $controller->setCanUseDeliveryOptions(true);

        $controller->savePhoneNumberAction();

        $body = Mage::app()->getResponse()->getBody();

        if ($success) {
            $this->assertTrue(strpos($body, 'OK') !== false);
        } else {
            $this->assertTrue(strpos($body, 'invalid_data') !== false);
        }
    }

    /**
     * @return array
     */
    public function savePhoneDataProvider()
    {
        return array(
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => '0612345678'
                ),
                true,
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => '0031612345678'
                ),
                true,
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => '+31612345678'
                ),
                true,
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => '061234567'
                ),
                false,
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => '061 234567'
                ),
                false,
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => '06-1234567'
                ),
                false,
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => ''
                ),
                false,
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'number' => 'test'
                ),
                false,
            ),
        );
    }
}
