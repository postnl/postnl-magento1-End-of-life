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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean                                 hasIsOldMagento()
 * @method TIG_PostNL_Block_Adminhtml_WindowsTheme setIsOldMagento()
 */
class TIG_PostNL_Block_Adminhtml_WindowsTheme extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * For Magento versions below these versions we need to execute some special backwards compatibility code.
     */
    const MINIMUM_VERSION_COMPATIBILITY            = '1.7.0.0';
    const MINIMUM_ENTERPRISE_VERSION_COMPATIBILITY = '1.12.0.0';

    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_windowstheme';

    /**
     * Checks whether the current version of Magento is an old version (C.E. 1.6 or E.E. 1.11).
     *
     * @return boolean
     */
    public function getIsOldMagento()
    {
        if ($this->hasIsOldMagento()) {
            return $this->_getData('is_old_magento');
        }

        $version = Mage::getVersion();
        $isEnterprise = Mage::helper('postnl')->isEnterprise();

        /**
         * Get the minimum version requirement for the current Magento edition.
         */
        if($isEnterprise) {
            $minimumVersion = self::MINIMUM_ENTERPRISE_VERSION_COMPATIBILITY;
        } else {
            $minimumVersion = self::MINIMUM_VERSION_COMPATIBILITY;
        }

        /**
         * Check if the current version is below the minimum version requirement.
         */
        $isOldVersion = version_compare($version, $minimumVersion, '<');

        $this->setIsOldMagento($isOldVersion);
        return $isOldVersion;
    }
}