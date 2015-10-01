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
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_WizardSaveButton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Get the element's html.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $elementOriginalData = $element->getOriginalData();

        /**
         * Get the next step rel that will be used as a parameter for the button's on click event.
         */
        $nextStepRel = '';
        if (isset($elementOriginalData['next_step_rel'])) {
            $nextStepRel = $elementOriginalData['next_step_rel'];
        }

        /**
         * Get the current step's rel as well.
         *
         * @var Varien_Data_Form_Element_Fieldset $container
         */
        $currentStepRel = '';
        $container = $element->getContainer();
        if ($container) {
            $currentStepRel = $container->getHtmlId();
        }

        /**
         * Create a new button and return the html output.
         *
         * @var Mage_Adminhtml_Block_Widget_Button $button
         */
        $button = $this->getLayout()
                     ->createBlock('adminhtml/widget_button');

        $html = $button->setType('button')
                       ->setId($element->getHtmlId())
                       ->setClass('scalable postnl-button')
                       ->setLabel($element->getLabel())
                       ->setOnClick("postnlWizardSaveAndContinue('{$nextStepRel}', '{$currentStepRel}')")
                       ->toHtml();

        return $html;
    }

    /**
     * Render the element without a scope and label.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     *
     * @see parent::render()
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<td class="label"></td>';

        if ($element->getTooltip()) {
            $html .= '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
        } else {
            $html .= '<td class="value">';
            $html .= $this->_getElementHtml($element);
        };

        if ($element->getComment()) {
            $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
        }

        $html.= '</td>';

        $html.= '<td class="scope-label"></td>';

        $html.= '<td class="">';
        if ($element->getHint()) {
            $html.= '<div class="hint" >';
            $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html.= '</div>';
        }
        $html.= '</td>';

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Decorate field row html.
     *
     * Extended, because this method is only present in Magento since v1.7.0.1.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string                            $html
     *
     * @return string
     *
     * @see Mage_Adminhtml_Block_System_Config_Form_Field::_decorateRowHtml()
     */
    protected function _decorateRowHtml($element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }
}
