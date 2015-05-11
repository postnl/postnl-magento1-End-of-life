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
 * @method boolean                                       hasFieldsetParam()
 * @method TIG_PostNL_Block_Adminhtml_System_Config_Form setFieldsetParam(string $value)
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{
    /**
     * For Magento versions below these versions we need to execute some special backwards compatibility code.
     */
    const MINIMUM_VERSION_COMPATIBILITY            = '1.7.0.1';
    const MINIMUM_ENTERPRISE_VERSION_COMPATIBILITY = '1.12.0.1';

    /**
     * Gets the fieldset parameter from the GET superglobal if available.
     *
     * @return null|string
     */
    public function getFieldsetParam()
    {
        if ($this->hasFieldsetParam()) {
            return $this->getData('fieldset_param');
        }

        $fieldsetparam = Mage::app()->getRequest()->getParam('fieldset');

        $this->setFieldsetParam($fieldsetparam);
        return $fieldsetparam;
    }

    /**
     * Creates the system > config > edit form for the PostNL section.
     *
     * Due to the way the form is initialized, each fieldset is initialized with it's fields in order. Due to this order
     * a field can only depend on a field that is in the same fieldset or in a fieldset that is already initialized. An
     * example: We have fieldset_a containing field_a. We also have fieldset_b containing field_b. If field_a depends on
     * field_b, this is not possible. When field_a is initialized, fieldset_b and therefore field_b, will not yet have
     * been initialized and will not be available.
     *
     * We have split the initialization of fieldsets and fields. This way Magento will first initialize all fieldsets
     * and then init all fields. So when field_a is initialized, fieldset_b is already available and the dependency will
     * work.
     *
     * @return $this
     */
    public function initForm()
    {
        $this->_initObjects();

        $form = new Varien_Data_Form();

        $sections = $this->_configFields->getSection(
            $this->getSectionCode(),
            $this->getWebsiteCode(),
            $this->getStoreCode()
        );
        if (empty($sections)) {
            $sections = array();
        }
        foreach ($sections as $section) {
            /* @var $section Varien_Simplexml_Element */
            if (!$this->_canShowField($section)) {
                continue;
            }
            foreach ($section->groups as $groups){
                $groups = (array)$groups;
                usort($groups, array($this, '_sortForm'));

                foreach ($groups as $group){
                    /* @var $group Varien_Simplexml_Element */
                    if (!$this->_canShowField($group)) {
                        continue;
                    }
                    $this->_initGroup($form, $group, $section);
                }

                /*************************
                 * This part is new
                 ************************/
                foreach ($groups as $group){
                    if (!isset($this->_fieldsets[$group->getName()])) {
                        continue;
                    }

                    $fieldset = $this->_fieldsets[$group->getName()];
                    $this->initFields($fieldset, $group, $section);
                }
            }
        }

        $this->setForm($form);
        return $this;
    }

    /**
     * Init config group
     *
     * @param Varien_Data_Form                       $form
     * @param Varien_Simplexml_Element               $group
     * @param Varien_Simplexml_Element               $section
     * @param Varien_Data_Form_Element_Fieldset|null $parentElement
     *
     * @throws TIG_PostNL_Exception
     *
     * @return void
     */
    protected function _initGroup($form, $group, $section, $parentElement = null)
    {
        if ($group->frontend_model) {
            $fieldsetRenderer = Mage::getBlockSingleton((string)$group->frontend_model);
        } else {
            $fieldsetRenderer = $this->_defaultFieldsetRenderer;
        }

        $fieldsetRenderer->setForm($this)
            ->setConfigData($this->_configData);

        if ($this->_configFields->hasChildren($group, $this->getWebsiteCode(), $this->getStoreCode())) {
            $helperName = $this->_configFields->getAttributeModule($section, $group);
            $fieldsetConfig = array('legend' => Mage::helper($helperName)->__((string)$group->label));
            if (!empty($group->comment)) {
                if (!empty($group->comment_url)) {
                    if (!empty($group->comment_url->base)) {
                        $baseUrl = (string) $group->comment_url->base;
                    } else {
                        $baseUrl = '';
                    }

                    $params = array();
                    if (!empty($group->comment_url->params)) {
                        foreach ($group->comment_url->params->asArray() as $param => $value) {
                            $params[$param] = $value;
                        }
                    }

                    $commentUrl = $this->getUrl($baseUrl, $params);

                    $comment = Mage::helper($helperName)->__((string)$group->comment, $commentUrl);
                } else {
                    $comment = Mage::helper($helperName)->__((string)$group->comment);
                }
                $fieldsetConfig['comment'] = $comment;
            }
            if (!empty($group->expanded)) {
                $fieldsetConfig['expanded'] = (bool)$group->expanded;
            }

            /**
             * Added support for a 'fieldset' URL parameter that forces a certain fieldset to the expanded state.
             */
            $fieldsetParam = $this->getFieldsetParam();
            if ($fieldsetParam && $fieldsetParam == $group->getName()) {
                $fieldsetConfig['expanded'] = true;
            }

            $fieldset = new Varien_Data_Form_Element_Fieldset($fieldsetConfig);
            $fieldset->setId($section->getName() . '_' . $group->getName())
                ->setRenderer($fieldsetRenderer)
                ->setGroup($group);

            if ($parentElement) {
                $fieldset->setIsNested(true);
                $parentElement->addElement($fieldset);
            } else {
                $form->addElement($fieldset);
            }

            $this->_prepareFieldOriginalData($fieldset, $group);
            $this->_addElementTypes($fieldset);

            $this->_fieldsets[$group->getName()] = $fieldset;

            if ($group->clone_fields) {
                if ($group->clone_model) {
                    $cloneModel = Mage::getModel((string)$group->clone_model);
                } else {
                    throw new TIG_PostNL_Exception(
                        $this->__('Config form fieldset clone model required to be able to clone fields'),
                        'POSTNL-0095'
                    );
                }
                foreach ($cloneModel->getPrefixes() as $prefix) {
                    $this->initFields($fieldset, $group, $section, $prefix['field'], $prefix['label']);
                }
            }
            /**
             * This is where default Magento initializes the fields. Eventhough not all fieldsets are available.
             */
        }
    }

    /**
     * Init fieldset fields. Copied from EE1.13 Mage_Adminhtml_Block_System_Config_Form::initFields to allow for
     * cross-fieldset dependencies in CE 1.6 and 1.7.0.0, and EE 1.11 and 1.12.0.0. Only made a small change to core
     * code for backwards compatibility.
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param Varien_Simplexml_Element          $group
     * @param Varien_Simplexml_Element          $section
     * @param string                            $fieldPrefix
     * @param string                            $labelPrefix
     *
     * @return $this
     */
    public function initFields($fieldset, $group, $section, $fieldPrefix='', $labelPrefix='')
    {
        if (!$this->_configDataObject) {
            $this->_initObjects();
        }

        // Extends for config data
        $configDataAdditionalGroups = array();

        foreach ($group->fields as $elements) {
            $elements = (array)$elements;
            // sort either by sort_order or by child node values bypassing the sort_order
            if ($group->sort_fields && $group->sort_fields->by) {
                $fieldset->setSortElementsByAttribute(
                    (string)$group->sort_fields->by,
                    $group->sort_fields->direction_desc ? SORT_DESC : SORT_ASC
                );
            } else {
                usort($elements, array($this, '_sortForm'));
            }

            foreach ($elements as $element) {
                if (!$this->_canShowField($element)) {
                    continue;
                }

                if ((string)$element->getAttribute('type') == 'group') {
                    $this->_initGroup($fieldset->getForm(), $element, $section, $fieldset);
                    continue;
                }

                /**
                 * Look for custom defined field path
                 */
                $path = (string)$element->config_path;
                if (empty($path)) {
                    $path = $section->getName() . '/' . $group->getName() . '/' . $fieldPrefix . $element->getName();
                } elseif (strrpos($path, '/') > 0) {
                    // Extend config data with new section group
                    $groupPath = substr($path, 0, strrpos($path, '/'));
                    if (!isset($configDataAdditionalGroups[$groupPath])) {
                        $this->_configData = $this->_configDataObject->extendConfig(
                            $groupPath,
                            false,
                            $this->_configData
                        );
                        $configDataAdditionalGroups[$groupPath] = true;
                    }
                }

                $inherit = null;
                $data = $this->_configDataObject->getConfigDataValue($path, $inherit, $this->_configData);

                /**
                 * Because Magento 1.6 and 1.11 retrieved config data in a different manner, we need to provide
                 * backwards compatibility for those versions.
                 */
                $version = Mage::getVersion();
                $isEnterprise = Mage::helper('postnl')->isEnterprise();

                /**
                 * Get the minimum version requirement for the current Magento edition.
                 */
                if($isEnterprise) {
                    $minimumVersion = self::MINIMUM_ENTERPRISE_VERSION_COMPATIBILITY;
                } else {
                    $minimumVersion = self::MINIMUM_VERSION_COMPATIBILITY;
                }

                /**
                 * Check if the current version is below the minimum version requirement.
                 */
                $isBelowMinimumVersion = version_compare($version, $minimumVersion, '<');

                /**
                 * If the current version is below the minimum version or if we have no data, use the old method of
                 * getting config data.
                 */
                if (!$data && $isBelowMinimumVersion === true) {
                    if (isset($this->_configData[$path])) {
                        $data = $this->_configData[$path];
                        $inherit = false;
                    } else {
                        $data = $this->_configRoot->descend($path);
                        $inherit = true;
                    }
                }

                if ($element->frontend_model) {
                    $fieldRenderer = Mage::getBlockSingleton((string)$element->frontend_model);
                } else {
                    $fieldRenderer = $this->_defaultFieldRenderer;
                }

                $fieldRenderer->setForm($this);
                $fieldRenderer->setConfigData($this->_configData);

                $helperName = $this->_configFields->getAttributeModule($section, $group, $element);
                $fieldType  = (string)$element->frontend_type ? (string)$element->frontend_type : 'text';
                $name  = 'groups[' . $group->getName() . '][fields][' . $fieldPrefix.$element->getName() . '][value]';
                $label =  Mage::helper($helperName)->__($labelPrefix) . ' '
                    . Mage::helper($helperName)->__((string)$element->label);
                $hint  = (string)$element->hint ? Mage::helper($helperName)->__((string)$element->hint) : '';

                if ($element->backend_model) {
                    $model = Mage::getModel((string)$element->backend_model);
                    if (!$model instanceof Mage_Core_Model_Config_Data) {
                        Mage::throwException('Invalid config field backend model: '.(string)$element->backend_model);
                    }
                    $model->setPath($path)
                        ->setValue($data)
                        ->setWebsite($this->getWebsiteCode())
                        ->setStore($this->getStoreCode())
                        ->afterLoad();
                    $data = $model->getValue();
                }

                $comment = $this->_prepareFieldComment($element, $helperName, $data);
                $tooltip = $this->_prepareFieldTooltip($element, $helperName);
                $id = $section->getName() . '_' . $group->getName() . '_' . $fieldPrefix . $element->getName();

                if ($element->depends) {
                    foreach ($element->depends->children() as $dependent) {
                        /* @var $dependent Mage_Core_Model_Config_Element */

                        if (isset($dependent->fieldset)) {
                            $dependentFieldGroupName = (string)$dependent->fieldset;
                            if (!isset($this->_fieldsets[$dependentFieldGroupName])) {
                                $dependentFieldGroupName = $group->getName();
                            }
                        } else {
                            $dependentFieldGroupName = $group->getName();
                        }

                        $dependentFieldNameValue = $dependent->getName();
                        $dependentFieldGroup = $dependentFieldGroupName == $group->getName()
                            ? $group
                            : $this->_fieldsets[$dependentFieldGroupName]->getGroup();

                        $dependentId = $section->getName()
                            . '_' . $dependentFieldGroupName
                            . '_' . $fieldPrefix
                            . $dependentFieldNameValue;
                        $shouldBeAddedDependence = true;
                        $dependentValue = (string) (isset($dependent->value) ? $dependent->value : $dependent);
                        if (isset($dependent->separator)) {
                            $dependentValue = explode((string) $dependent->separator, $dependentValue);
                        }
                        if (isset($dependent->eval)) {
                            $dependentValue = array('eval' => (string) $dependent->eval);
                        }
                        $dependentFieldName = $fieldPrefix . $dependent->getName();
                        $dependentField     = $dependentFieldGroup->fields->$dependentFieldName;
                        /*
                         * If dependent field can't be shown in current scope and real dependent config value
                         * is not equal to preferred one, then hide dependence fields by adding dependence
                         * based on not shown field (not rendered field)
                         */
                        if (!$this->_canShowField($dependentField)) {
                            $dependentFullPath = $section->getName()
                                . '/' . $dependentFieldGroupName
                                . '/' . $fieldPrefix
                                . $dependent->getName();
                            $dependentValueInStore = Mage::getStoreConfig($dependentFullPath, $this->getStoreCode());
                            if (is_array($dependentValue)) {
                                $shouldBeAddedDependence = !in_array($dependentValueInStore, $dependentValue);
                            } else {
                                $shouldBeAddedDependence = $dependentValue != $dependentValueInStore;
                            }
                        }
                        if ($shouldBeAddedDependence) {
                            $this->_getDependence()
                                ->addFieldMap($id, $id)
                                ->addFieldMap($dependentId, $dependentId)
                                ->addFieldDependence($id, $dependentId, $dependentValue);
                        }
                    }
                }
                $sharedClass = '';
                if ($element->shared && $element->config_path) {
                    $sharedClass = ' shared shared-' . str_replace('/', '-', $element->config_path);
                }

                $requiresClass = '';
                if ($element->requires) {
                    $requiresClass = ' requires';
                    foreach (explode(',', $element->requires) as $groupName) {
                        $requiresClass .= ' requires-' . $section->getName() . '_' . $groupName;
                    }
                }

                $field = $fieldset->addField($id, $fieldType, array(
                    'name'                  => $name,
                    'label'                 => $label,
                    'comment'               => $comment,
                    'tooltip'               => $tooltip,
                    'hint'                  => $hint,
                    'value'                 => $data,
                    'inherit'               => $inherit,
                    'class'                 => $element->frontend_class . $sharedClass . $requiresClass,
                    'field_config'          => $element,
                    'scope'                 => $this->getScope(),
                    'scope_id'              => $this->getScopeId(),
                    'scope_label'           => $this->getScopeLabel($element),
                    'can_use_default_value' => $this->canUseDefaultValue($element),
                    'can_use_website_value' => $this->canUseWebsiteValue($element),
                ));
                $this->_prepareFieldOriginalData($field, $element);

                if (isset($element->validate)) {
                    $field->addClass($element->validate);
                }

                if (isset($element->autocomplete)) {
                    $field->setAutocomplete($element->autocomplete);
                }

                if (isset($element->frontend_type)
                    && 'multiselect' === (string)$element->frontend_type
                    && isset($element->can_be_empty)
                ) {
                    $field->setCanBeEmpty(true);
                }

                $field->setRenderer($fieldRenderer);

                if ($element->source_model) {
                    // determine callback for the source model
                    $factoryName = (string)$element->source_model;
                    $method = false;
                    if (preg_match('/^([^:]+?)::([^:]+?)$/', $factoryName, $matches)) {
                        array_shift($matches);
                        list($factoryName, $method) = array_values($matches);
                    }

                    $sourceModel = Mage::getSingleton($factoryName);
                    if ($sourceModel instanceof Varien_Object) {
                        $sourceModel->setPath($path);
                    }
                    if ($method) {
                        if ($fieldType == 'multiselect' || $element->multidimensional) {
                            $optionArray = $sourceModel->$method();
                        } else {
                            $optionArray = array();
                            foreach ($sourceModel->$method() as $value => $label) {
                                $optionArray[] = array('label' => $label, 'value' => $value);
                            }
                        }
                    } else {
                        $optionArray = $sourceModel->toOptionArray($fieldType == 'multiselect');
                    }
                    $field->setValues($optionArray);
                }
            }
        }

        return $this;
    }

    /**
     * Add a new checkbox element type.
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $elementTypes = parent::_getAdditionalElementTypes();
        $elementTypes['checkbox'] = Mage::getConfig()
                                        ->getBlockClassName('postnl_adminhtml/system_config_form_field_checkbox');

        $elementTypes['wizard_save_button'] = Mage::getConfig()
                                                  ->getBlockClassName(
                                                      'postnl_adminhtml/system_config_form_field_wizardSaveButton'
                                                  );

        $elementTypes['postnl_radios'] = Mage::getConfig()
                                             ->getBlockClassName('postnl_adminhtml/system_config_form_field_radios');

        return $elementTypes;
    }

    /**
     * Return dependency block object
     *
     * @return TIG_PostNL_Block_Adminhtml_Widget_Form_Element_Dependence
     */
    protected function _getDependence()
    {
        if (!$this->getChild('element_dependense')){
            $this->setChild('element_dependense',
                $this->getLayout()->createBlock('postnl_adminhtml/widget_form_element_dependence'));
        }
        return $this->getChild('element_dependense');
    }

    /**
     * Prepare additional comment for field like tooltip
     *
     * @param Mage_Core_Model_Config_Element $element
     * @param string $helper
     * @return string
     */
    protected function _prepareFieldTooltip($element, $helper)
    {
        if ($element->tooltip_block) {
            return $this->getLayout()
                        ->createBlock((string)$element->tooltip_block)
                        ->setElement($element)
                        ->toHtml();
        } elseif ($element->tooltip) {
            return Mage::helper($helper)->__((string)$element->tooltip);
        }

        return '';
    }
}
