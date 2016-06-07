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
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_SupportTab
    extends TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_TextBox_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_system_config_form_field_supporttab';

    /**
     * Xpaths to URLs used in the support tab.
     */
    const POSTNL_REGISTER_URL_XPATH     = 'postnl/general/postnl_register_url';
    const KNOWLEDGEBASE_URL_XPATH       = 'postnl/general/knowledgebase_url';
    const NEW_TICKET_URL_XPATH          = 'postnl/general/new_ticket_url';
    const INSTALLATION_MANUAL_URL_XPATH = 'postnl/general/installation_manual_url';
    const USER_GUIDE_URL_XPATH          = 'postnl/general/user_guide_url';
    const KB_URL_XPATH                  = 'postnl/general/kb_url';
    const POSTNL_DOCUMENTATION_URL      = 'postnl/general/postnl_documentation_url';
    const CIT_SERVICEDESK_EMAIL         = 'postnl/general/cit_servicedesk_email';

    /**
     * Template file used
     *
     * @var string
     */
    protected $_template = 'TIG/PostNL/system/config/form/field/support_tab.phtml';

    /**
     * @return string
     */
    public function getVersion()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $version =  $helper->getModuleVersion();

        return $version;
    }

    /**
     * @return string
     */
    public function getStability()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $version = $helper->getModuleStability();

        return $version;
    }

    /**
     * @return array|Mage_Core_Model_Config_Element|string|false
     */
    public function getCompatibility()
    {
        $postnlConfig = Mage::app()->getConfig()->getNode('tig/compatibility/postnl');

        if ($postnlConfig) {
            $postnlConfig = $postnlConfig->asArray();
        }

        return $postnlConfig;
    }

    /**
     * @param string $extensionKey
     *
     * @return string
     */
    public function getCompatibleExtensionLabel($extensionKey)
    {
        switch ($extensionKey) {
            case 'Idev_OneStepCheckout':
                $label = "Idev's OneStepCheckout";
                break;
            case 'Bpost_ShippingManager':
                $label = "Bpost Shipping Manager";
                break;
            case 'GoMage_Checkout':
                $label = "GoMage's Checkout";
                break;
            case 'Picqer_PostNL':
                $label = "Picqer's PostNL add-on";
                break;
            default:
                $label = $extensionKey;
                break;
        }

        return $label;
    }

    /**
     * @param $versions
     *
     * @return string
     */
    public function formatCompatibleVersion($versions)
    {
        $versionString = '';
        $versions = explode(',', $versions);

        $count = count($versions);
        foreach (array_values($versions) as $index => $version) {
            if ($index > 0 && $index < $count) {
                $versionString .= ', ';
            } elseif ($index > 0) {
                $versionString .= ' & ';
            }

            $versionString .= 'v' . $version;
        }

        return $versionString;
    }

    /**
     * @return string
     */
    public function getPostnlRegisterUrl()
    {
        $url = Mage::getStoreConfig(self::POSTNL_REGISTER_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }

    /**
     * @return string
     */
    public function getKnowledgebaseUrl()
    {
        $url = Mage::getStoreConfig(self::KNOWLEDGEBASE_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }

    /**
     * @return string
     */
    public function getNewTicketUrl()
    {
        $url = Mage::getStoreConfig(self::NEW_TICKET_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }

    /**
     * @return string
     */
    public function getInstallationManualUrl()
    {
        $url = Mage::getStoreConfig(self::INSTALLATION_MANUAL_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }

    /**
     * @return string
     */
    public function getUserGuideUrl()
    {
        $url = Mage::getStoreConfig(self::USER_GUIDE_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }

    /**
     * @return string
     */
    public function getPostnlDocumentationUrl()
    {
        $url = Mage::getStoreConfig(self::POSTNL_DOCUMENTATION_URL, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }

    /**
     * @return string
     */
    public function getCitServicedeskEmail()
    {
        $url = Mage::getStoreConfig(self::CIT_SERVICEDESK_EMAIL, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }

    /**
     * @return string
     */
    public function getChangelogUrl()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $url = $helper->getChangelogUrl();

        return $url;
    }

    /**
     * @return string
     */
    public function getKbUrl()
    {
        $url = Mage::getStoreConfig(self::KB_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $url;
    }
}
