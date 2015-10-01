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
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_Hidden
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Decorate field row html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '" style="display:none;">' . $html . '</tr>';
    }

    /**
     * Overloaded for forwards compatibility for Magento 1.6.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $html = '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';

        //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType()==='multiple';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = $this->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = $this->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

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

        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif (isset($v['value'])) {
                        if ($v['value'] == $defText) {
                            $defTextArr[] = $v['label'];
                            break;
                        }
                    } elseif (!is_array($v)) {
                        if ($k == $defText) {
                            $defTextArr[] = $v;
                            break;
                        }
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html.= '<td class="use-default">';
            $html.= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html.= '</td>';
        }

        $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td>';

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
     * Get the element's HTML.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $value = $this->_getValue();

        $html = '<input id="'.$element->getHtmlId().'" name="'.$element->getName()
            .'" value="' . $value . '" '.$this->serialize($element->getHtmlAttributes()).'/>'."\n";
        $html.= $element->getAfterElementHtml();
        return $html;
    }

    /**
     * Value getter.
     *
     * This method should be overloaded in child methods.
     *
     * @return string
     */
    protected function _getValue()
    {
        return '';
    }
}