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
class TIG_PostNL_Model_Adminhtml_Form_Element_Checkbox extends Varien_Data_Form_Element_Checkbox
{
    /**
     * Render a checkbox with an associated hidden field. This allows you to disable the checkbox while still
     * transmitting it's value when submitting the form.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $checked = $this->getChecked();
        if ($checked) {
            $this->setData('checked', true);
        } else {
            $this->unsetData('checked');
        }

        $html = '<input id="'.$this->getHtmlId().'" name="'.$this->getName()
            .'" value="'.$this->getEscapedValue().'" type="hidden"/>'."\n";
        $html .= '<input id="'.$this->getHtmlId().'_checkbox" name="'.$this->getName()
            .'_checkbox" value="'.$this->getEscapedValue().'" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";

        $html .= '<script type="text/javascript">' . PHP_EOL
               . '//<![CDATA[' . PHP_EOL
               . '$("'.$this->getHtmlId().'_checkbox").observe("click", '
               . 'function(){$("'.$this->getHtmlId().'").setValue(this.getValue());});' . PHP_EOL
               . '//]]>' . PHP_EOL
               . '</script>';

        $html.= $this->getAfterElementHtml();

        return $html;
    }

    /**
     * Render HTML for element's label
     *
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = '')
    {
        if (!is_null($this->getLabel())) {
            $html = '<label for="'.$this->getHtmlId() . $idSuffix . '_checkbox">' . $this->_escape($this->getLabel())
                . ( $this->getRequired() ? ' <span class="required">*</span>' : '' ) . '</label>' . "\n";
        } else {
            $html = '';
        }
        return $html;
    }
}