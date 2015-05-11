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
class TIG_PostNL_Model_Core_System_Config_Backend_Image_Pdf extends Mage_Adminhtml_Model_System_Config_Backend_Image_Pdf
{
    /**
     * Maximum size (in pixels).
     */
    const MAX_WIDTH  = 900;
    const MAX_HEIGHT = 100;

    /**
     * If an image was saved, make sure the image is not too large. If it is, resize it.
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        /**
         * If we have no value, no new file was uploaded.
         */
        if (!$this->getValue()) {
            return parent::_afterSave();
        }

        /**
         * Locate the file and make sure it exists.
         */
        $uploadDir = $this->_getBaseUploadDir();
        $fileName = $this->getValue();
        $file = $uploadDir . DS . $fileName;

        if (!file_exists($file)) {
            return parent::_afterSave();
        }

        /**
         * Load the image as a Varien_Image object.
         */
        $image = new Varien_Image($file);
        $image->keepAspectRatio(true);
        $image->keepTransparency(true);

        /**
         * Check the image's size and resize the image if necessary.
         */
        if ($image->getOriginalWidth() > self::MAX_WIDTH) {
            $image->resize(self::MAX_WIDTH, self::MAX_HEIGHT);
            $image->save($file);
        } elseif ($image->getOriginalHeight() > self::MAX_HEIGHT) {
            $image->resize(null, self::MAX_HEIGHT);
            $image->save($file);
        }

        return parent::_afterSave();
    }

    /**
     * Return path to directory for upload file without scope.
     *
     * @return string
     *
     * @throw Mage_Core_Exception
     */
    protected function _getBaseUploadDir()
    {
        /**
         * @var $fieldConfig Varien_Simplexml_Element
         */
        $fieldConfig = $this->getFieldConfig();

        if (empty($fieldConfig->upload_dir)) {
            Mage::throwException(Mage::helper('catalog')->__('The base directory to upload file is not specified.'));
        }

        $uploadDir = (string)$fieldConfig->upload_dir;

        $el = $fieldConfig->descend('upload_dir');

        /**
         * Take root from config
         */
        if (!empty($el['config'])) {
            $uploadRoot = $this->_getUploadRoot((string)$el['config']);
            $uploadDir = $uploadRoot . '/' . $uploadDir;
        }

        return $uploadDir;
    }
}