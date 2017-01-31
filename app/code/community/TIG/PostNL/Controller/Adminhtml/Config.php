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
class TIG_PostNL_Controller_Adminhtml_Config extends TIG_PostNL_Controller_Adminhtml_Abstract
{
    /**
     * Regex to validate the supplied wizard step hash.
     */
    const VALIDATE_WIZARD_HASH_REGEX = '/^[a-zA-Z0-9-_#]+$/';

    /**
     * Saves the current wizard step.
     *
     * @param string $step
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _saveCurrentWizardStep($step)
    {
        $step = $this->_validateStep($step);

        /** @var Mage_Admin_Model_Session $adminSession */
        $adminSession = Mage::getSingleton('admin/session');
        /** @var Mage_Admin_Model_User $adminUser */
        /** @noinspection PhpUndefinedMethodInspection */
        $adminUser = $adminSession->getUser();
        $extra     = $adminUser->getExtra();

        $extra['postnl']['current_wizard_step'] = $step;

        $adminUser->saveExtra($extra);

        return $this;
    }

    /**
     * Validate the step hash. If the step is not valid, return an empty string.
     *
     * @param string $step
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _validateStep($step)
    {
        $validator = new Zend_Validate_Regex(array('pattern' => self::VALIDATE_WIZARD_HASH_REGEX));

        if (!$validator->isValid($step)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'An error occurred while saving this step of the configuration wizard. Please use the regular ' .
                    '"Save Config" button instead.'
                ),
                'POSTNL-0224'
            );
        }

        return $step;
    }
}
