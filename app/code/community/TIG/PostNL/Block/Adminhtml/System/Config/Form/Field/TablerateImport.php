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
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_TablerateImport
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Get a hidden form element and some JS to support it.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     *
     * @see Mage_Adminhtml_Block_System_Config_Form_Field_Import
     */
    public function _getJsHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<input id="postnl_time_condition" type="hidden" name="'.$element->getName().'" value="'.time().'" />';

        $html .= "<script type='text/javascript'>
        document.observe('dom:loaded', function() {
            Event.observe($('carriers_postnl_condition_name'), 'change', checkConditionName.bind(this));
            function checkConditionName(event)
            {
                var conditionNameElement = Event.element(event);
                if (conditionNameElement && conditionNameElement.id) {
                    $('postnl_time_condition').value = '_' + conditionNameElement.value + '/' + Math.random();
                }
            }
        });
        </script>";

        return $html;
    }

    /**
     * Render the element.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setType('file')
                ->removeClass('input-text');

        $html = parent::render($element);

        $html .= $this->_getJsHtml($element);

        return $html;
    }
}