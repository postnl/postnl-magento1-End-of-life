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
 * @method TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_ActivateButton setElement(Varien_Data_Form_Element_Abstract $value)
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_ActivateButton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * XML path to 'is_activated' flag
     */
    const XPATH_IS_ACTIVATED = 'postnl/general/is_activated';

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

        $isActivated = Mage::getStoreConfig(self::XPATH_IS_ACTIVATED, Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($isActivated === '1') {
            $label = $this->__('Finish activation');
        } else {
            $label = $this->__('Activate the extension');
        }

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                     ->setId($element->getHtmlId())
                     ->setType('button')
                     ->setClass('scalable postnl-button')
                     ->setLabel($label)
                     ->setOnClick("activatePostNL()")
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
