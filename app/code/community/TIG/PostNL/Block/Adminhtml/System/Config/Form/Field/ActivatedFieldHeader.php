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
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_ActivatedFieldHeader
    extends TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_TextBox_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_system_config_form_field_activatedfieldheader';

    /**
     * Template file used
     *
     * @var string
     */
    protected $_template = 'TIG/PostNL/system/config/form/field/field_header.phtml';

    /**
     * Get the element's label
     *
     * @return string
     */
    public function getLabel()
    {
        if (!$this->getElement()) {
            return '';
        }

        $element = $this->getElement();
        /** @noinspection PhpUndefinedMethodInspection */
        $label = $element->getLabel();

        $section = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');

        $urlParams = array(
            '_secure' => true,
        );

        if ($section) {
            $urlParams['section'] = $section;
        }

        if ($website) {
            $urlParams['website'] = $website;
        }

        if ($store) {
            $urlParams['store'] = $store;
        }

        $url = $this->getUrl('adminhtml/postnlAdminhtml_extensionControl/showActivationFields', $urlParams);
        $onclick = "confirmSetLocation('"
                 . $this->__(
                       "Are you sure? The PostNL extension will not function until you\'ve registered the extension."
                   )
                 . "', '"
                 . $url
                 . "');";

        $label = sprintf(
            $label,
            $onclick
        );

        return $label;
    }
}
