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
 * @method Varien_Data_Form_Element_Abstract getElement()
 * @method string                            getBeforeElementHtml()
 *
 * @method TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_Anchor setElement(Varien_Data_Form_Element_Abstract $element)
 * @method TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_Anchor setBeforeElementHtml($html)
 * @method TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_Anchor setAfterElementHtml($html)
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_Anchor
    extends Varien_Data_Form_Element_Link
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Get the element's HTML.
     *
     * @return string
     */
    public function getElementHtml()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $originalData = $this->getElement()->getOriginalData();
        $anchorName   = $originalData['anchor_name'];

        $html = $this->getBeforeElementHtml();
        $html .= '<a name="' . $anchorName . '"></a>';
        $html .= $this->getAfterElementHtml();

        return $html;
    }

    /**
     * Get the element's HTML ID
     *
     * @return string
     */
    public function getHtmlId()
    {
        if (!$this->getElement()) {
            return '';
        }

        $element = $this->getElement();
        $id = $element->getHtmlId();

        return $id;
    }

    /**
     * Render field html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $this->setBeforeElementHtml('<tr id="row_' . $this->getHtmlId() . '"><td colspan="5">');
        $this->setAfterElementHtml('</tr></td>');

        return $this->getElementHtml();
    }
}
