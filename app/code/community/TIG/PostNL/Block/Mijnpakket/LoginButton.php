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
 *
 * @method boolean                                 hasPublicWebshopId()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setPublicWebshopId(string $value)
 * @method boolean                                 hasSavedMijnpakketData()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setSavedMijnpakketData(string $value)
 */
class TIG_PostNL_Block_Mijnpakket_LoginButton extends Mage_Core_Block_Template
{
    /**
     * The webshop's public webshop ID is used to secure communications with PostNL's servers.
     */
    const XPATH_PUBLIC_WEBSHOP_ID = 'postnl/cif/public_webshop_id';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/mijnpakket/login_button.phtml';

    /**
     * Get the current public webshop ID.
     *
     * @return string
     */
    public function getPublicWebshopId()
    {
        if ($this->hasPublicWebshopId()) {
            return $this->_getData('public_webshop_id');
        }

        $publicWebshopId = Mage::getStoreConfig(self::XPATH_PUBLIC_WEBSHOP_ID, Mage::app()->getStore()->getId());

        $this->setPublicWebshopId($publicWebshopId);
        return $publicWebshopId;
    }

    /**
     * Get saved mijnpakkt data if available.
     *
     * @return array|null
     */
    public function getSavedMijnpakketData()
    {
        if ($this->hasSavedMijnpakketData()) {
            return $this->_getData('saved_mijnpakket_data');
        }

        $data = Mage::getSingleton('checkout/session')->getPostnlMijnpakketData();

        $this->setSavedMijnpakketData($data);
        return $data;
    }

    /**
     * Check if the current customer may login using Mijnpakket.
     *
     * @return string
     */
    protected function _tohtml()
    {
        $helper = Mage::helper('postnl/mijnpakket');
        if (!$helper->canLoginWithMijnpakket()) {
            return '';
        }

        return parent::_toHtml();
    }
}