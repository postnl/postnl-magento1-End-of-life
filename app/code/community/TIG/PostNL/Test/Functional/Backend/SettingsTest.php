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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../../');
$dotenv->load();

define('BROWSERSTACK_USER', getenv('BROWSERSTACK_USERNAME'));
define('BROWSERSTACK_KEY', getenv('BROWSERSTACK_ACCESS_KEY'));

class SettingsTest extends PHPUnit_Extensions_Selenium2TestCase
{
    public static $browsers = array(
//        array(
//            'browserName' => 'chrome',
//            'host' => 'hub.browserstack.com',
//            'port' => 80,
//            'desiredCapabilities' => array(
//                'version' => '30',
//                'browserstack.user' => BROWSERSTACK_USER,
//                'browserstack.key' => BROWSERSTACK_KEY,
//                'os' => 'OS X',
//                'os_version' => 'Mountain Lion'
//            )
//        ),
        array(
            'browserName' => 'chrome',
            'host' => 'hub.browserstack.com',
            'port' => 80,
            'desiredCapabilities' => array(
                'version' => '30',
                'browserstack.user' => BROWSERSTACK_USER,
                'browserstack.key' => BROWSERSTACK_KEY,
                'os' => 'Windows',
                'os_version' => '8.1'
            )
        )
    );

    protected function setUp()
    {
        parent::setUp();
        $this->setBrowserUrl('http://bamboo.ce1920.env3.tig.nl/testadmin/');
    }

    public function testCheckIfConfigExists()
    {
        /**
         * Login on the backend
         */
        $this->url('http://bamboo.ce1920.env3.tig.nl/testadmin/');

        $element = $this->byId('username');
        $element->value('tigamsterdam');

        $element = $this->byId('login');
        $element->value('fso3030!');

        $this->byClassName('form-button')->click();

        /**
         * Find the configuration
         */
        $element = $this->byXPath('//*[text()=\'System\']');
        $this->moveto($element);

        $this->byXPath('//*[text()=\'Configuration\']')->click();

        $this->byClassName('postnl-tab')->click();

        $element = $this->byId('postnl_general_unique_key');

        if ($element->displayed()) {
            $element->value('a57c5f664a1a33d88ee1fb2377dd7a3864087a58');
            $this->byId('postnl_general_private_key')->value('613f3dae3c85ed540139eefd3f6dee6f8cfc92a6');
            $this->byId('postnl_general_finish_activation_button')->click();
        }
    }
}