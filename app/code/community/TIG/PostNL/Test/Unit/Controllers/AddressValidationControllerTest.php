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
class TIG_PostNL_Test_Unit_Controllers_AddressValidationControllerTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected $_class = 'TIG_PostNL_AddressValidationController';

    public static function setUpBeforeClass()
    {
        require_once(__DIR__ . '/../../../controllers/AddressValidationController.php');
    }

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

        $this->assertInstanceOf('TIG_PostNL_AddressValidationController', $controller);
    }

    /**
     * @test
     */
    public function shouldRejectWithoutAjax()
    {
        $controller = $this->_getInstance(array('postcode' => '1043AJ', 'housenumber' => 8));
        $controller->postcodeCheckAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'missing_data');

        $this->assertTrue($dataMissing !== false, 'Check AJAX fails!');
    }

    /**
     * @test
     */
    public function shouldRejectWithoutPostcode()
    {
        $controller = $this->_getInstance(array('isAjax' => true));
        $controller->postcodeCheckAction();

        $body = Mage::app()->getResponse()->getBody();
        $dataMissing = strpos($body, 'missing_data');

        $this->assertTrue($dataMissing !== false, 'Check data fails!');
    }

    /**
     * @test
     */
    public function shouldValidatePostcodeSuccess()
    {
        $controller = $this->_getInstance();

        $valid = $controller->validatePostcode('1043AJ', 8);
        $this->assertTrue($valid, 'Failed to validate postcode');
    }

    /**
     * @test
     *
     * @dataProvider validatePostcodeFailures
     */
    public function shouldValidatePostcodeFail($data)
    {
        $controller = $this->_getInstance();
        $valid = $controller->validatePostcode($data[0], $data[1]);
        $this->assertTrue(!$valid, 'Failed to validate postcode failure');
    }

    public function validatePostcodeFailures()
    {
        return array(
            array('1043 AJ', 8),
            array('1043AJ', 'test'),
            array('1043AJ', '8f'),
            array('1043', 8)
        );
    }

    /**
     * @test
     */
    public function shouldValidateResultSuccess()
    {
        $controller = $this->_getInstance();

        $data = new StdClass();
        $data->woonplaats = 'test';
        $data->straatnaam = 'teststraat';

        $valid = $controller->validateResult($data);

        $this->assertTrue($valid, 'Failed to validate result');
    }

    /**
     * @test
     */
    public function shouldValidateResultFail()
    {
        $controller = $this->_getInstance();

        $data = new StdClass();
        $data->woonplaats = '';
        $data->straatnaam = '';

        $valid = $controller->validateResult($data);

        $this->assertTrue(!$valid, 'Failed to invalidate result');
    }
}
