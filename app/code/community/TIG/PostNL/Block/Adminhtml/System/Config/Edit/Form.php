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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Edit_Form extends Mage_Adminhtml_Block_System_Config_Form
{    
    /**
     * Creates the system > config > edit form for the PostNL section.
     * 
     * The only reason we have a custom form, rather than the default Adminhtml form is because the default form doesn't allow
     * for 'forwards' field dependency.
     * 
     * Due to the way the form is initialized, each fieldset is initialized with it's fields in order. Due to this order a field 
     * can only depend on a field that is in the same fieldset or in a fieldset that is already initialized. An example:
     * We have fieldset_a containing field_a. We also have fieldset_b containing field_b. If field_a depends on field_b, this
     * is not possible. When field_a is initialized, fieldset_b and therefore field_b, will not yet have been initialized and 
     * will not be available.
     * 
     * We have split the initialization of fieldsets and fields. This way Magento will first initialize all fieldsets and then
     * init all fields. So when field_a is initialized, fieldset_b is already available and the dependency will work.
     *
     * @return TIG_PostNL_Block_Adminhtml_System_Config_Edit_Form
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
     * @param Varien_Data_Form $form
     * @param Varien_Simplexml_Element $group
     * @param Varien_Simplexml_Element $section
     * @param Varien_Data_Form_Element_Fieldset|null $parentElement
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
                $fieldsetConfig['comment'] = Mage::helper($helperName)->__((string)$group->comment);
            }
            if (!empty($group->expanded)) {
                $fieldsetConfig['expanded'] = (bool)$group->expanded;
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
}
