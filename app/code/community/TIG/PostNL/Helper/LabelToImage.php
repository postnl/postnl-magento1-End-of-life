<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Helper_LabelToImage extends Mage_Core_Helper_Abstract
{
    const TMP_IMAGE_NAME = 'TIG_PostNL_temp.jpg';
    const TMP_LABEL_NAME = 'TIG_PostNL_temp.pdf';

    const XPATH_TECHNICAL_IMAGICK_ENABLED = 'postnl/advanced/imagick_label';

    const A6_SIZE = '297:420:';

    const BLACK_THRESHOLD = '#000000';

    /**
     * @var array
     */
    protected $types = array(
        'file',
        'pdf'
    );

    /**
     * @var string
     */
    protected $varDir = '/';

    /**
     * @var string
     */
    protected $tmpImageName;

    /**
     * @var string
     */
    protected $tmpPdfName;

    /**
     * @var null|Zend_Pdf
     */
    protected $label = null;

    /**
     * @param string $type
     *
     * @return null|string|Zend_Pdf
     * @throws Exception
     */
    public function get($type = 'pdf')
    {
        $this->validateGet($type);

        if ($type == 'file') {
            return $this->returnFile();
        }

        return $this->label;
    }

    /**
     * Always converting the image as A6 format.
     *
     * @param     $labelToFormat
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     */
    public function set($labelToFormat, $x1 = 0, $y1 = 0, $x2 = 298, $y2 = 420)
    {
        $this->validateSet($labelToFormat);

        $this->varDir     = Mage::getConfig()->getVarDir('TIG' . DS . 'PostNL' . DS . 'temp_label');
        $this->tmpPdfName = $labelToFormat;

        if ($labelToFormat instanceof Zend_Pdf) {
            $this->tmpPdfName = $this->getTempPdfFile();
            $labelToFormat->save($this->tmpPdfName);
        }

        $this->createImage();

        $this->label = new Zend_Pdf();
        $newPage     = new Zend_Pdf_Page(static::A6_SIZE);
        $zendImage   = Zend_Pdf_Image::imageWithPath($this->tmpImageName);

        $newPage->drawImage($zendImage, $x1, $y1, $x2, $y2);
        $this->remove(true);

        $this->label->pages[] = $newPage;
    }

    /**
     * Removes the temp files.
     * @param $imageOnly
     */
    public function remove($imageOnly = false)
    {
        if (!$imageOnly && file_exists($this->tmpPdfName)) {
            unlink($this->tmpPdfName);
        }

        // image could already have been removed by imagick.
        if (file_exists($this->tmpImageName)) {
            unlink($this->tmpImageName);
        }
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) Mage::getStoreConfig(static::XPATH_TECHNICAL_IMAGICK_ENABLED, $storeId);
    }

    /**
     * @param int $x_resolution
     * @param int $y_resolution
     */
    protected function createImage($x_resolution = 500, $y_resolution = 500)
    {
        $this->tmpImageName = $this->getTempJpgFile();

        $imagick = new Imagick();
        $imagick->setResolution($x_resolution, $y_resolution);
        $imagick->readImage($this->tmpPdfName);
        $imagick->blackThresholdImage(static::BLACK_THRESHOLD);
        $imagick->writeImages($this->tmpImageName, false);
        $imagick->clear();
        $imagick->destroy();
    }

    /**
     * @param $type
     *
     * @throws Exception
     */
    protected function validateGet($type)
    {
        if (!in_array($type, $this->types)) {
            throw new Exception('The type : ' . $type .' is not supported in '. get_class($this));
        }

        if ($this->label == null) {
            throw new Exception(
                'Label is not set yet, you should first use the set method in' . get_class($this)
            );
        }
    }

    /**
     * @param $labelToFormat
     *
     * @throws Exception
     */
    protected function validateSet($labelToFormat)
    {
        if (!extension_loaded('imagick')) {
            throw new Exception('Imagick extension is not loaded');
        }

        if ($labelToFormat instanceof TIG_PostNL_Fpdi) {
            throw new Exception('Current version of ' . get_class($this) . ' does not support Fpdi');
        }
    }

    /**
     * @return string
     */
    protected function returnFile()
    {
        $this->remove();

        $this->tmpPdfName = $this->getTempPdfFile();
        $this->label->save($this->tmpPdfName);

        return $this->tmpPdfName;
    }

    /**
     * @return string
     */
    protected function getTempPdfFile()
    {
        return $this->varDir . DS . md5(rand(0, 10)) . '-' . time() . '-' . static::TMP_LABEL_NAME;
    }

    /**
     * @return string
     */
    protected function getTempJpgFile()
    {
        return $this->varDir . DS . md5(rand(0, 10)) . '-' . time() . '-' . static::TMP_IMAGE_NAME;
    }
}
