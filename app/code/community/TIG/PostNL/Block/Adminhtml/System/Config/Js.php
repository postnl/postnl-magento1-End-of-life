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
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Js extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * Get the current wizard step as saved for the current admin user.
     *
     * @return string
     */
    public function getCurrentWizardStep()
    {
        /**
         * Get the current admin user and it's saved extra data.
         */
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        $extra     = $adminUser->getExtra();

        /**
         * If no data exists or the data we want is not set, return an empty string.
         */
        if (!$extra || !isset($extra['postnl']['current_wizard_step'])) {
            return '';
        }

        /**
         * Get the saved step and return it.
         */
        $currentWizardStep = $extra['postnl']['current_wizard_step'];
        return $currentWizardStep;
    }

    /**
     * Get the save wizard step URL for the current scope.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSaveWizardStepUrl()
    {
        /**
         * Get the current scope data for the URL.
         */
        $urlParams = array('_secure' => true);
        if ($this->getRequest()->getParam('section')) {
            $urlParams['section'] = $this->getRequest()->getParam('section');
        }

        if ($this->getRequest()->getParam('website')) {
            $urlParams['website'] = $this->getRequest()->getParam('website');
        }

        if ($this->getRequest()->getParam('store')) {
            $urlParams['store'] = $this->getRequest()->getParam('store');
        }

        /**
         * Build the URL.
         */
        $url = $this->getUrl('postnl_admin/adminhtml_config/saveWizardStep', $urlParams);

        return $url;
    }
}