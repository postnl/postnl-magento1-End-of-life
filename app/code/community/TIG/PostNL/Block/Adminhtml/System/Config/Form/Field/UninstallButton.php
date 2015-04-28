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
 * @method TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_ActivateButton setElement(Varien_Data_Form_Element_Abstract $value)
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_UninstallButton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Gets the element's html. In this case: a button redirecting the user to the extensionControl controller
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $confirmText = $this->__(
            "Are you sure you wish to disable the PostNL extension?<br/>Warning: this action cannot be undone.<br/>" .
            "The following changes will be made:" .
            "<ul>" .
            "<li>The extension will be disabled in the app/etc/modules/TIG_PostNL.xml file.</li>" .
            "<li>The PostNL product attributes will be removed from the webshop.</li>" .
            "</ul>" .
            "The following will be preserved:" .
            "<ul>" .
            "<li>All data pertaining to existing orders and shipments.</li>" .
            "<li>All PostNL configuration settings.</li>" .
            "<li>All PostNL code files.</li>" .
            "</ul>" .
            "For questions regarding this process and how to re-install the PostNl extension, please contact the " .
            "TIG servicedesk."
        );
        $uninstallUrl = $this->getUrl('postnl_admin/adminhtml_extensionControl/uninstall');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                     ->setId($element->getHtmlId())
                     ->setType('button')
                     ->setClass('scalable postnl-button')
                     ->setLabel($this->__('Permanently disable the PostNL extension'))
                     ->setOnClick("confirmSetLocation('" . $confirmText . "', '" . $uninstallUrl . "');")
                     ->toHtml();

        return $html;
    }

    /**
     * Render the element without a scope label
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     *
     * @see parent::render()
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setScopeLabel('');
        return parent::render($element);
    }
}
