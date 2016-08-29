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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Js extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_system_config_js';

    /**
     * Css files loaded for PostNL's system > config section
     */
    const SYSTEM_CONFIG_EDIT_CSS_FILE = 'css/TIG/PostNL/system_config_edit_postnl.css';
    const MAGENTO_16_CSS_FILE         = 'css/TIG/PostNL/system_config_edit_postnl_magento16.css';

    /**
     * Minimum versions required for certain css changes.
     */
    const MIN_ENTERPRISE_VERSION = '1.12.0.0';
    const MIN_COMMUNITY_VERSION  = '1.7.0.0';

    /**
     * Add a new css file to the head. We cannot do this from layout.xml, because it would have loaded for all System >
     * Config pages, rather than just PostNL's section.
     *
     * @return Mage_Adminhtml_Block_Abstract::_prepareLayout()
     *
     * @see Mage_Adminhtml_Block_Abstract::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        if ($this->getRequest()->getParam('section') != 'postnl') {
            return parent::_prepareLayout();
        }

        /**
         * @var Mage_Adminhtml_Block_Page_Head $head
         */
        $head = $this->getLayout()
                     ->getBlock('head');

        $head->addCss(self::SYSTEM_CONFIG_EDIT_CSS_FILE);
        $head->addItem('skin_js', 'js/TIG/PostNL/configuration.js');
        $head->removeItem('js', 'mage/adminhtml/form.js');
        $head->addItem('skin_js', 'js/TIG/PostNL/form.js');

        /**
         * For Magento 1.6 and 1.11 we need to add another css file.
         */
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $isEnterprise = $helper->isEnterprise();

        /**
         * Get the minimum version requirement for the current Magento edition.
         */
        if($isEnterprise) {
            $minimumVersion = self::MIN_ENTERPRISE_VERSION;
        } else {
            $minimumVersion = self::MIN_COMMUNITY_VERSION;
        }

        /**
         * Check if the current version is below the minimum version requirement.
         */
        $isBelowMinimumVersion = version_compare(Mage::getVersion(), $minimumVersion, '<');
        if ($isBelowMinimumVersion) {
            $head->addCss(self::MAGENTO_16_CSS_FILE);
        }

        return parent::_prepareLayout();
    }

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

        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Admin_Model_User $adminUser */
        $adminUser = $session->getUser();
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
        $url = $this->getUrl('adminhtml/postnlAdminhtml_config/saveWizardStep', $urlParams);

        return $url;
    }

    /**
     * Get the hide notification URL.
     *
     * @return string
     */
    public function getHideNotificationUrl()
    {
        $url = $this->getUrl('adminhtml/postnlAdminhtml_config/hideNotification');

        return $url;
    }

    /**
     * Render this block only for the PostNL section.
     *
     * @return string
     * @throws Exception
     */
    protected function _toHtml()
    {
        if ($this->getRequest()->getParam('section') != 'postnl') {
            return '';
        }

        return parent::_toHtml();
    }
}
