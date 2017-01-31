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

        $warningTitle = 'Uninstall PostNL Extension';

        $warningMessage = array();
        /** @noinspection HtmlUnknownAttribute */
        $warningMessage[] = "<br><div class=\'module-message warning\'><h4>" . $this->__('Warning: this action cannot be undone!') ."</h4></div>";
        $warningMessage[] = $this->__('The following changes will be made:');
        $warningMessage[] = "<br>- " . $this->__('The extension will be disabled in the app/etc/modules/TIG_PostNL.xml file');
        $warningMessage[] = "<br>- " . $this->__('The PostNL product attributes will be removed from the webshop');
        $warningMessage[] = "<br><br>" . $this->__('The following will be preserved:');
        $warningMessage[] = "<br>- " . $this->__('All data pertaining to existing orders and shipments');
        $warningMessage[] = "<br>- " . $this->__('All PostNL configuration settings');
        $warningMessage[] = "<br>- " . $this->__('All PostNL code files.');
        $warningMessage[] = "<br><br>" . $this->__('For questions regarding this process and how to re-install the PostNL extension, please contact the TIG servicedesk.');
        $warningMessage[] = "<br><br><div class=\'module-message\'><h4>";
        $warningMessage[] = $this->__('Type `uninstall` in the box to proceed') . "&nbsp; &nbsp;";
        $warningMessage[] = "<input type=\'text\' id=\'verify_uninstall\' />";
        $warningMessage[] = "</h4></div>";

        $confirmText = implode($warningMessage);
        $confirmTitle = $this->__($warningTitle);
        $uninstallUrl = $this->getUrl('adminhtml/postnlAdminhtml_extensionControl/uninstall');

        /** @noinspection PhpUndefinedMethodInspection */
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                     ->setId($element->getHtmlId())
                     ->setType('button')
                     ->setClass('scalable postnl-button')
                     ->setLabel($this->__('Permanently disable the PostNL extension'))
                     ->setOnClick("openModalConfirm('" . $confirmTitle . "', '" . $confirmText . "', handleConfirmUninstall, '" . $uninstallUrl . "');")
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
        /** @noinspection PhpUndefinedMethodInspection */
        $element->setScopeLabel('');
        return parent::render($element);
    }
}
