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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Filter_ConfirmDate
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Date
{
    /**
     * Generate HTML for this filter.
     *
     * @return string
     */
    public function getHtml()
    {
        $html = '<select name="'
              . $this->_getHtmlName()
              . '[select]" id="'
              . $this->_getHtmlId()
              . '" class="no-changes" style="width:122px;">';

        $value = $this->getValue();

        $todaySelected = '';
        $tomorrowSelected = '';
        $chooseDateSelected = '';
        if (isset($value['select'])) {
            switch ($value['select']) {
                case null:
                    break;
                case 'today':
                    $todaySelected = ' selected="selected"';
                    break;
                case 'tomorrow':
                    $tomorrowSelected = ' selected="selected"';
                    break;
                case 'pick_date': //no break
                default:
                    $chooseDateSelected = ' selected="selected"';
                    break;
            }
        }

        $html .= '<option value=""></option>';
        $html .= '<option value="today"' . $todaySelected . '>' . $this->__('Today') . '</option>';
        $html .= '<option value="tomorrow"' . $tomorrowSelected . '>' . $this->__('Tomorrow') . '</option>';
        $html .= '<option value="pick_date"' . $chooseDateSelected . '>' . $this->__('Choose date') . '</option>';

        $html .='</select>';

        $htmlId = $this->_getHtmlId() . '_date_' . microtime(true);
        $format = $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $html .= '<div class="range" id="' . $htmlId . '"><div class="range-line date">'
            . '<span class="label">' . Mage::helper('adminhtml')->__('From') . ':</span>'
            . '<input type="text" name="' . $this->_getHtmlName() . '[from]" id="' . $htmlId . '_from"'
            . ' value="' . $this->getEscapedValue('from') . '" class="input-text no-changes"/>'
            . '<img src="' . Mage::getDesign()->getSkinUrl('images/grid-cal.gif') . '" alt="" class="v-middle"'
            . ' id="' . $htmlId . '_from_trig"'
            . ' title="' . $this->escapeHtml(Mage::helper('adminhtml')->__('Date selector')) . '"/>'
            . '</div>';
        $html.= '<div class="range-line date">'
            . '<span class="label">' . Mage::helper('adminhtml')->__('To') . ' :</span>'
            . '<input type="text" name="' . $this->_getHtmlName() . '[to]" id="' . $htmlId . '_to"'
            . ' value="' . $this->getEscapedValue('to') . '" class="input-text no-changes"/>'
            . '<img src="' . Mage::getDesign()->getSkinUrl('images/grid-cal.gif') . '" alt="" class="v-middle"'
            . ' id="' . $htmlId . '_to_trig"'
            . ' title="' . $this->escapeHtml(Mage::helper('adminhtml')->__('Date selector')) . '"/>'
            . '</div></div>';
        $html.= '<input type="hidden" name="' . $this->_getHtmlName() . '[locale]"'
            . 'value="' . $this->getLocale()->getLocaleCode() . '"/>';
        $html.= '<script type="text/javascript">
            Calendar.setup({
                inputField : "' . $htmlId . '_from",
                ifFormat : "' . $format . '",
                button : "' . $htmlId . '_from_trig",
                align : "Bl",
                singleClick : true
            });
            Calendar.setup({
                inputField : "' . $htmlId . '_to",
                ifFormat : "' . $format . '",
                button : "' . $htmlId . '_to_trig",
                align : "Bl",
                singleClick : true
            });

            $("' . $htmlId . '_to_trig").observe("click", showCalendar);
            $("' . $htmlId . '_from_trig").observe("click", showCalendar);

            function showCalendar(event){
                var element = event.element(event);
                var offset = $(element).viewportOffset();
                var scrollOffset = $(element).cumulativeScrollOffset();
                var dimensionsButton = $(element).getDimensions();
                var index = $("widget-chooser").getStyle("zIndex");

                $$("div.calendar").each(function(item){
                    if ($(item).visible()) {
                        var dimensionsCalendar = $(item).getDimensions();

                        $(item).setStyle({
                            "zIndex" : index + 1,
                            "left" : offset[0] + scrollOffset[0] - dimensionsCalendar.width
                                + dimensionsButton.width + "px",
                            "top" : offset[1] + scrollOffset[1] + dimensionsButton.height + "px"
                        });
                    }
                });
            }

            var confirmDateDatePicker = function() {
                if ($("' . $this->_getHtmlId() . '").getValue() == "pick_date") {
                    $("' . $htmlId . '").show();
                } else {
                    $("' . $htmlId . '").hide();
                }
            };

            $("' . $this->_getHtmlId() . '").observe("change", confirmDateDatePicker);
            confirmDateDatePicker();
        </script>';

        return $html;
    }

    /**
     * Get the filter's current value.
     *
     * @param null|string $index
     *
     * @return array|mixed|null
     */
    public function getValue($index=null)
    {
        if ($index) {
            if ($data = $this->getData('value', 'orig_'.$index)) {
                return $data;
            }
            return null;
        }
        $value = $this->getData('value');
        if (is_array($value)) {
            $value['datetime'] = true;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        if (!empty($value['to']) && !$this->getColumn()->getFilterTime()) {
            /** @noinspection PhpUndefinedClassInspection */
            /** @var Zend_Date $datetimeTo */
            $datetimeTo = $value['to'];

            //calculate end date considering timezone specification
            $datetimeTo->setTimezone(
                Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)
            );
            $datetimeTo->addDay(1)->subSecond(1);
            $datetimeTo->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
        }
        return $value;
    }

    /**
     * Set the filter's value.
     *
     * @param array $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $utcTimeZone = new DateTimeZone('UTC');

        /**
         * Set the value for the 'today' and 'tomorrow' filter options.
         */
        if (isset($value['select'])) {
            if ($value['select'] == 'today') {
                $today = new DateTime('today', $utcTimeZone);
                $tomorrow = new DateTime('tomorrow - 1 second', $utcTimeZone);
                $value['from'] = $today->format('d-m-Y');
                $value['to'] = $tomorrow->format('d-m-Y');
            } elseif ($value['select'] == 'tomorrow') {
                $tomorrow = new DateTime('tomorrow', $utcTimeZone);
                $dayAfterTomorrow = new DateTime('tomorrow + 1day - 1 second', $utcTimeZone);
                $value['from'] = $tomorrow->format('d-m-Y');
                $value['to'] = $dayAfterTomorrow->format('d-m-Y');
            }
        } else {
            $value['from'] = null;
            $value['to'] = null;
        }

        if (isset($value['locale'])) {
            if (!empty($value['from'])) {
                $value['orig_from'] = $value['from'];
                $value['from'] = $this->_convertDate($value['from'], $value['locale']);
            }
            if (!empty($value['to'])) {
                $value['orig_to'] = $value['to'];
                $value['to'] = $this->_convertDate($value['to'], $value['locale']);
            }
        }

        if (empty($value['from']) && empty($value['to']) && empty($value['select'])) {
            $value = null;
        }

        $this->setData('value', $value);
        return $this;
    }
}
