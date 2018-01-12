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
class TIG_PostNL_Test_Unit_ExceptionTest extends \TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return array
     */
    public function invalidGenerateLabelsResponseProvider()
    {
        return array(
            'without response data' => array(
                array(),
                "Invalid generateLabels response."
            ),
            'with response data' => array(
                array('message' => 'request is invalid'),
                "Invalid generateLabels response: array (".PHP_EOL."  'message' => 'request is invalid',".PHP_EOL.")"
            ),
        );
    }

    /**
     * @param $response
     * @param $expected
     *
     * @dataProvider invalidGenerateLabelsResponseProvider
     */
    public function testInvalidGenerateLabelsResponse($response, $expected)
    {
        $result = TIG_PostNL_Exception::invalidGenerateLabelsResponse($response);
        $this->assertEquals($expected, $result->getMessage());
    }
}
