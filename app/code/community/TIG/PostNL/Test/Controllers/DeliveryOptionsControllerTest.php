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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Controllers_DeliveryOptionsControllerTest extends TIG_PostNL_Test_Framework_TIG_Test_TestCase
{
    protected $_class = 'TIG_PostNL_DeliveryOptionsController';

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . '/../../controllers/DeliveryOptionsController.php');
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
    public function shouldRejectSaveSelectedOptionActionWithoutAjax()
    {
        $controller = $this->_getInstance();
        $controller->saveSelectedOptionAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'missing_data');

        $this->assertTrue($dataMissing !== false, 'Check AJAX fails!');
    }

    /**
     * @param $data
     * @param $success
     *
     * @test
     *
     * @dataProvider saveSelectedPostDataWithoutAddressProvider
     */
    public function shouldValidatePostDataWithoutAnAddress($data, $success)
    {
        $controller = $this->_getInstance($data);

        $mockService = $this->getMock('TIG_PostNL_Model_DeliveryOptions_Service');
        $controller->setService($mockService);

        $controller->setCanUseDeliveryOptions(true);

        $controller->saveSelectedOptionAction($data);

        $body = Mage::app()->getResponse()->getBody();

        if ($success) {
            $this->assertTrue(strpos($body, 'OK') !== false);
        } else {
            $this->assertTrue(strpos($body, 'OK') === false);
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
                    'costs' => 1.5,
                ),
                true
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'type' => 'testfail',
                    'date' => '18-03-2014',
                    'costs' => 1.5,
                ),
                false
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'type' => 'PGE',
                    'date' => '180-03-2014',
                    'costs' => 1.5,
                ),
                false
            ),
            array(
                'data' => array(
                    'isAjax' => true,
                    'type' => 'PGE',
                    'date' => '18-03-2014',
                    'costs' => '1,50',
                ),
                false
            ),
        );
    }
}