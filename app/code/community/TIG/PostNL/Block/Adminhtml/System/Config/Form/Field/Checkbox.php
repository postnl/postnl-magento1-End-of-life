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
 *
 * @method mixed getValue()
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_Checkbox extends Varien_Data_Form_Element_Checkbox
{
    /**
     * Check if the element should be checked before rendering the element.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->getIsChecked();

        $html = parent::getElementHtml();

        /**
         * Render a second, hidden element to store the checkbox's unchecked value.
         */
        $html .= "<input type='hidden' name='{$this->getName()}' id='{$this->getHtmlId()}_hidden'"
               . " value='{$this->getValue()}'/>";

        return $html;
    }

    /**
     * Add some JS to store the checkbox's empty value in a hidden element.
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $html = '
        <script type="text/javascript">
            //<![CDATA[
            $("' . $this->getHtmlId() . '").observe("change", function() {
                var element = $("' . $this->getHtmlId() . '");
                var hiddenElement = $("' . $this->getHtmlId() . '_hidden");
                if (element.checked) {
                    hiddenElement.setValue(1);
                } else {
                    hiddenElement.setValue(0);
                }
            });
            //]]>
        </script>';

        return $html;
    }

    /**
     * Return check status of checkbox
     *
     * @return boolean
     */
    public function getIsChecked() {
        if ($this->hasData('checked')) {
            return $this->getData('checked');
        }

        /**
         * Get the current element's value.
         */
        $value = $this->getValue();
        if (is_object($value) && $value instanceof Mage_Core_Model_Config_Element) {
            $value = $value->__toString();
        }

        $checked = (bool) $value;

        $this->setIsChecked($checked);
        return $checked;
    }
}