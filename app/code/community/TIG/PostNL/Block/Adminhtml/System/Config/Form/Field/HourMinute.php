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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_HourMinute
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Render the hour/minute field type.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $html = '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $addInheritCheckbox = false;
        $checkboxLabel = '';
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        $inherit = '';
        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        if ($element->getTooltip()) {
            $html .= '<td class="value with-tooltip">';
            $html .= '<div id="' . $element->getHtmlId() . '">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
        } else {
            $html .= '<td class="value">';
            $html .= '<div id="' . $element->getHtmlId() . '">';
            $html .= $this->_getElementHtml($element);
        };
        if ($element->getComment()) {
            $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
        }
        $html .= '</div>';
        $html.= '</td>';

        if ($addInheritCheckbox) {
            $defText = $element->getDefaultValue();

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
     * Gets the element's HTML.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     *
     * @see Varien_Data_Form_Element_Abstract::getElementHtml()
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /**
         * The value is formed as H:i:s. We need to get the H and i value from this.
         */
        $value = $element->getEscapedValue();
        $value = explode(':', $value);

        $hour   = $value[0];
        $minute = $value[1];

        $options = $element->getValues();

        /**
         * The html consists of 2 select fields and corresponding labels. First we build the select field for the hours.
         */
        $html = '<select id="'
              . $element->getHtmlId()
              . '_hour" name="'
              . $element->getName()
              . '[hour]"'
              . $element->serialize($this->getHtmlAttributes())
              . '>';

        /**
         * Add option elements for all possible hours (0-23).
         */
        foreach ($options['hour'] as $option) {
            $selected = '';
            if ($option['value'] == $hour) {
                $selected = ' selected="selected"';
            }

            $html .= "<option value=\"{$option['value']}\"{$selected}>{$option['label']}</option>";
        }

        /**
         * Add the label for the hour field.
         */
        $html .= '</select>'
               . '<label for="'
               . $element->getHtmlId()
               . '_hour">'
               . $this->__('hour')
               . '</label>';

        /**
         * Add the minute field.
         */
        $html .= '<select id="'
              . $element->getHtmlId()
              . '_minute" name="'
              . $element->getName()
              . '[minute]"'
              . $element->serialize($this->getHtmlAttributes())
              . '>;';

        /**
         * Add option elements for all possible minutes (0-60 in 5 min intervals).
         */
        foreach ($options['minute'] as $option) {
            $selected = '';
            if ($option['value'] == $minute) {
                $selected = ' selected="selected"';
            }

            $html .= "<option value=\"{$option['value']}\"{$selected}>{$option['label']}</option>";
        }

        /**
         * Add the minute label and possible 'AfterElementHtml'.
         */
        $html .= '</select>'
               . '<label for="'
               . $element->getHtmlId()
               . '_minute">'
               . $this->__('minutes')
               . '</label>'
               . $element->getAfterElementHtml();

        return $html;
    }

    /**
     * Gets a list of supported HTML attributes for this element.
     *
     * @return array
     */
    public function getHtmlAttributes()
    {
        $attributes = array(
            'type',
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'readonly',
            'tabindex',
        );

        return $attributes;
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