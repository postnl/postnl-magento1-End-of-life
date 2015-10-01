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
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_PackingSlipItemColumns
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/system/config/form/field/array.phtml';

    /**
     * Add columns for the array interface.
     */
    public function __construct()
    {
        $this->addColumn(
            'field',
            array(
                'label'   => $this->__('Field'),
                'style'   => 'width:120px',
                'type'    => 'select',
                'options' => Mage::getModel('postnl_core/system_config_source_packingSlipItemFields')->toOptionArray(),
                'class'   => 'required-entry validate-select',
            )
        );
        $this->addColumn(
            'title',
            array(
                'label' => $this->__('Title'),
                'style' => 'width:120px',
                'class' => 'required-entry validate-packing-slip-column-header',
            )
        );
        $this->addColumn(
            'width',
            array(
                'label' => $this->__('Width'),
                'style' => 'width:120px',
                'class' => 'required-entry validate-digits',
            )
        );
        $this->addColumn(
            'position',
            array(
                'label' => $this->__('Position'),
                'style' => 'width:120px',
                'class' => 'required-entry validate-digits',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = $this->__('Add column');

        parent::__construct();
    }

    /**
     * Add a column to the array-grid.
     *
     * @param string $name
     * @param array $params
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = array(
            'label'     => empty($params['label'])   ? 'Column' : $params['label'],
            'size'      => empty($params['size'])    ? false    : $params['size'],
            'style'     => empty($params['style'])   ? null     : $params['style'],
            'class'     => empty($params['class'])   ? null     : $params['class'],
            'type'      => empty($params['type'])    ? null     : $params['type'],
            'options'   => empty($params['options']) ? null     : $params['options'],
            'renderer'  => false,
        );
        if ((!empty($params['renderer'])) && ($params['renderer'] instanceof Mage_Core_Block_Abstract)) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @throws Exception
     *
     * @return string
     */
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }

        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($column['renderer']) {
            return $column['renderer']->setInputName($inputName)
                                      ->setColumnName($columnName)
                                      ->setColumn($column)
                                      ->toHtml();
        }

        if ($column['type'] == 'select') {
            $html = '<select name="';
        } else {
            $html = '<input type="text" name="';
        }

        $html .= $inputName
               . '" value="#{'
               . $columnName
               . '}" '
               . ($column['size'] ? 'size="' . $column['size'] . '"' : '')
               . ' class="'
               . (isset($column['class']) ? $column['class'] : 'input-text')
               . '"'
               . (isset($column['style']) ? ' style="'.$column['style'] . '"' : '');

        if ($column['type'] == 'select') {
            $html .= '>';

            foreach ($column['options'] as $option) {
                $selected = '';
                if ($columnName == $option['value']) {
                    $selected = 'selected="selected"';
                }

                $html .= "<option value=\"{$option['value']}\" {$selected}>{$option['label']}</option>";
            }

            $html .= '</select>';
        } else {
            $html .= '/>';
        }

        return $html;
    }
}