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
 *
 * @method TIG_PostNL_Model_Core_Label setLabelSize(string $value)
 */
class TIG_PostNL_Model_Core_Label extends Varien_Object
{
    /**
     * base name of temporary pdf files. An md5 hash will be prepended to this name in order to make each filename unique
     */
    const TEMP_LABEL_FILENAME = 'TIG_PostNL_temp.pdf';

    /**
     * XML path to label size setting
     *
     * This setting is ignored for GlobalPack labels and single Dutch or EPS labels
     */
    const XML_PATH_LABEL_SIZE = 'postnl/cif_labels_and_confirming/label_size';

    /**
     * An array of temporary files that have been created. these files will be destroyed at the end of the script.
     *
     * @var array
     */
    protected $_tempFilesSaved = array();

    /**
     * Counter to determine position of labels
     *
     * @var null | int
     */
    protected $_labelCounter = null;

    /**
     * Flag if the current label is the first of a set of labels.
     *
     * @var bool
     */
    protected $_isFirstLabel = false;

    /**
     * @param boolean $isFirstLabel
     *
     * @return TIG_PostNL_Model_Core_Label
     */
    public function setIsFirstLabel($isFirstLabel)
    {
        $this->_isFirstLabel = $isFirstLabel;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFirstLabel()
    {
        return $this->_isFirstLabel;
    }

    /**
     * Get the array of saved temporary labels
     *
     * @return array
     */
    public function getTempFilesSaved()
    {
        return $this->_tempFilesSaved;
    }

    /**
     * Set the array of saved temporary labels
     *
     * @param array $tempFilesSaved
     *
     * @return TIG_PostNL_Model_Core_Label
     */
    public function setTempFilesSaved($tempFilesSaved)
    {
        $this->_tempFilesSaved = $tempFilesSaved;

        return $this;
    }

    /**
     * Get the current label counter
     *
     * @return null | int
     */
    public function getLabelCounter()
    {
        return $this->_labelCounter;
    }

    /**
     * Set the current label counter
     *
     * @param int $counter
     *
     * @return TIG_PostNL_Model_Core_Label
     */
    public function setLabelCounter($counter)
    {
        $this->_labelCounter = $counter;

        return $this;
    }

    /**
     * get the configured label size
     *
     * @return string
     */
    public function getLabelSize()
    {
        if ($this->getData('label_size')) {
            return $this->getData('label_size');
        }

        $labelSize = Mage::getStoreConfig(self::XML_PATH_LABEL_SIZE, Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->setLabelSize($labelSize);
        return $labelSize;
    }

    /**
     * Reset the counter to 0
     *
     * @return TIG_PostNL_Model_Core_Label
     */
    public function resetLabelCounter()
    {
        $this->setLabelCounter(1);

        return $this;
    }

    /**
     * increase the label counter by a given amount
     *
     * @param int $increase
     *
     * @return TIG_PostNL_Model_Core_Label
     */
    public function increaseLabelCounter($increase = 1)
    {
        $counter = $this->getLabelCounter();
        $newCounter = $counter + $increase;

        $this->setLabelCounter($newCounter);

        return $this;
    }

    /**
     * Add a temporary pdf filename to the array so we can destroy it later
     *
     * @param string $tempFile
     *
     * @return TIG_PostNL_Model_Core_Label
     */
    public function addTempFileSaved($tempFile)
    {
        $tempFilesSaved = $this->getTempFilesSaved();
        $tempFilesSaved[] = $tempFile;

        $this->setTempFilesSaved($tempFilesSaved);

        return $this;
    }

    /**
     * Creates a pdf containing shipping labels using FPDF and FPDI libraries.
     * Four labels will be printed on each page in a vertical position. All labels will be rotated 90 degrees counter-clockwise
     *
     * @param mixed $labels May be an array of labels or a single label string
     *
     * @return TIG_PostNL_Model_Core_Label
     *
     * @see TIG_PostNL_Fpdf
     * @see TIG_PostNL_Fpdi
     *
     * @link http://www.fpdf.org/ Fpdf library documentation
     * @link http://www.setasign.de/products/pdf-php-solutions/fpdi/ Fpdi library
     */
    public function createPdf($labels)
    {
        Varien_Profiler::start('tig::postnl::core::label_createpdf');

        /**
         * Open a new pdf object and assign some basic values
         */
        $pdf = new TIG_PostNL_Fpdi(); //lib/TIG/PostNL/Fpdi
        $pdf->open();
        $pdf->SetFont('Arial', 'I', 40);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetTitle('PostNL Shipping Labels');
        $pdf->SetAuthor('PostNL');
        $pdf->SetCreator('PostNL');

        if (!is_array($labels)) {
            $labels = array($labels);
        }
        /**
         * Create a pdf containing multiple labels
         */
        $pdf = $this->_createMultiLabelPdf($pdf, $labels);

        /**
         * Destroy the temporary labels as they are no longer needed
         */
        $this->_destroyTempLabels();

        /**
         * Get the final label.
         */
        $label = $pdf->Output('PostNL Shipping Labels.pdf', 'I');

        Varien_Profiler::stop('tig::postnl::core::label_createpdf');

        return $label;
    }

    /**
     * Adds multiple labels to the pdf
     *
     * @param TIG_PostNL_Fpdi $pdf
     * @param array $labels
     *
     * @return TIG_PostNL_Fpdi $pdf
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _createMultiLabelPdf($pdf, $labels)
    {
        /**
         * Check if printing the required number of labels is allowed.
         *
         * This is limited to 200 by default to prevent out of memory errors.
         * On a clean Magento install with 256 MB of memory, several thousands of
         * labels can be printed at once. However, for safety reasons a limit
         * of 200 is used. By default you shouldn't be able to select more than 200
         * in the shipment grid.
         */
        if(count($labels) > 200 && !Mage::helper('postnl/cif')->allowInfinitePrinting()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Maximum amount of labels exceeded. Maximum allowed: 200. Requested: %s',
                    count($labels)
                ),
                'POSTNL-0064'
            );
        }

        $this->setIsFirstLabel(true);
        $labels = $this->_sortLabels($labels);
        foreach ($labels as $label) {
            $pdf = $this->_addPdfTemplate($pdf, $label);
        }

        return $pdf;
    }

    /**
     * Adds a label to the pdf by storing it in a temporary pdf file and then adding it to the master pdf object
     *
     * @param TIG_PostNL_Fpdi                      $pdf
     * @param TIG_PostNL_Model_Core_Shipment_Label $label
     *
     * @throws TIG_PostNL_Exception
     *
     * @return TIG_PostNL_Fpdi $pdf
     */
    protected function _addPdfTemplate($pdf, $label)
    {
        /**
         * Fpdi requires labels to be provided as files. Therefore the label will be saved as a temporary file in
         * var/TIG/PostNL/temp_labels/
         */
        $tempFilename = $this->_saveTempLabel($label->getLabel());

        $rotate = false;

        /**
         * First we need to add pages to the pdf for certain label types under certain conditions.
         */
        $labelType = $label->getLabelType();
        if ($labelType == 'Label' || $labelType == 'Label-combi') {
            if ($this->getLabelSize() == 'A4'
                && (!$this->getLabelCounter() || $this->getLabelCounter() > 4)
            ) {
                $pdf->addOrientedPage('L', 'A4');
                $this->resetLabelCounter();
            } elseif ($this->getLabelSize() == 'A4' && $this->getIsFirstLabel()) {
                $pdf->addOrientedPage('L', 'A4');
                $this->setIsFirstLabel(false);
            }

            /**
             * If the configured label size is A6, add a new page every label
             */
            if($this->getLabelSize() == 'A6') {
                $this->setLabelCounter(3); //used to calculate the top left position
                $pdf->addOrientedPage('L', 'A6');
            }
        } else if ($labelType == 'CN23'
            || $labelType == 'CommercialInvoice'
            || $labelType == 'CODcard'
        ) {
            $pdf->addOrientedPage('P', 'A4');
        }

        switch ($labelType) {
            case 'Label-combi':
                /**
                 * Rotate the pdf to accommodate the rotated combi-label.
                 */
                $pdf->Rotate('-90');

                /**
                 * Calculate the position of the next label to be printed
                 */
                $position = $this->_getRotatedPosition($this->getLabelCounter());
                $position['w'] = $this->pix2pt(400);

                $this->increaseLabelCounter();

                $rotate = true;
                break;
            case 'Label':
                /**
                 * Calculate the position of the next label to be printed
                 */
                $position = $this->_getPosition($this->getLabelCounter());
                $position['w'] = $this->pix2pt(538);

                $this->increaseLabelCounter();
                break;
            case 'CN23':
            case 'CommercialInvoice':
                /**
                 * Calculate the position of the next label to be printed
                 */
                $position = array(
                    'x' => $this->pix2pt(15),
                    'y' => $this->pix2pt(17),
                    'w' => $this->pix2pt(776)
                );

                /**
                 * increase the label counter to above 4. This will prompt the creation of a new page
                 */
                $this->setLabelCounter(5);
                break;
            case 'CP71':
                /**
                 * Calculate the position of the next label to be printed
                 */
                $position = array(
                    'x' => $this->pix2pt(15),
                    'y' => $this->pix2pt(578),
                    'w' => $this->pix2pt(776)
                );

                /**
                 * increase the label counter to above 4. This will prompt the creation of a new page
                 */
                $this->setLabelCounter(5);
                break;
            case 'CODcard':
                /**
                 * Calculate the position of the next label to be printed
                 */
                $position = array(
                    'x' => $this->pix2pt(15),
                    'y' => $this->pix2pt(17),
                    'w' => $this->pix2pt(776)
                );

                /**
                 * increase the label counter to above 4. This will prompt the creation of a new page
                 */
                $this->setLabelCounter(5);
                break;
            default:
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Invalid label type supplied: %s', $label->getLabelType()),
                    'POSTNL-0065'
                );
        }

        /**
         * Add the next label to the pdf
         */
        $pdf->insertTemplate($tempFilename, $position['x'], $position['y'], $position['w']);

        /**
         * If a rotated pdf was added, rotate the main pdf back to it's previous orientation.
         */
        if ($rotate) {
            $pdf->Rotate('0');
        }

        return $pdf;
    }

    /**
     * Save a label to a temporary pdf file. Temporary pdf files are stored in var/TIG/PostNL/temp_label/
     *
     * @param string $label
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _saveTempLabel($label)
    {
        /**
         * construct the path to the temporary file
         */
        $tempFilePath = Mage::getConfig()->getVarDir('TIG' . DS . 'PostNL' . DS . 'temp_label')
                      . DS
                      . md5($label)
                      . '-'
                      . time()
                      . '-'
                      . self::TEMP_LABEL_FILENAME;

        if (file_exists($tempFilePath)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Temporary template file already exists: %s', $tempFilePath),
                'POSTNL-0066'
            );
        }

        /**
         * Add the base64 decoded label to the file
         */
        file_put_contents($tempFilePath, base64_decode($label));

        /**
         * Save the name of the temp file so it can be destroyed later
         */
        $this->addTempFileSaved($tempFilePath);

        return $tempFilePath;
    }

    /**
     * Destroy all temporary pdf files
     *
     * @return TIG_PostNL_Model_Core_Label
     */
    protected function _destroyTempLabels()
    {
        $tempFilesSaved = $this->getTempFilesSaved();
        foreach ($tempFilesSaved as $tempFile) {
            unlink($tempFile);
        }

        return $this;
    }

    /**
     * Sorts labels by label type. First all labels of the 'Label' type. Then all other labels in the
     * order of 'CN23' > 'CP71' > 'CommercialInvoice' grouped by shipments
     *
     * @param array $labels
     *
     * @return array
     *
     * @todo expand with cod labels
     */
    protected function _sortLabels($labels)
    {
        $generalLabels = array();
        $globalLabels  = array();
        $codCards      = array();

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Label $label
         */
        foreach ($labels as $label) {
            /**
             * Separate general labels from the rest
             */
            if ($label->getLabelType() == 'Label' || $label->getLabelType() == 'Label-combi') {
                $generalLabels[] = $label;
                continue;
            }

            /**
             * Separate COD cards
             */
            if ($label->getLabelType() == 'CODcard') {
                $codCards[] = $label;
                continue;
            }

            /**
             * Group other labels by shipment id (parent_id attribute)
             */
            if (isset($globalLabels[$label->getParentId()])) {
                $globalLabels[$label->getParentId()][$label->getLabelType()] = $label;
            } else {
                $globalLabels[$label->getParentId()] = array($label->getlabelType() => $label);
            }
        }

        /**
         * Sort all non-standard labels
         */
        $sortedGlobalLabels = array();
        foreach ($globalLabels as $shipmentLabels) {
            if (isset($shipmentLabels['CN23'])) {
                $sortedGlobalLabels[] = $shipmentLabels['CN23'];
            }

            if (isset($shipmentLabels['CP71'])) {
                $sortedGlobalLabels[] = $shipmentLabels['CP71'];
            }

            if (isset($shipmentLabels['CommercialInvoice'])) {
                $sortedGlobalLabels[] = $shipmentLabels['CommercialInvoice'];
            }
        }

        /**
         * merge all labels back into a single array
         */
        $labels = array_merge($generalLabels, $sortedGlobalLabels, $codCards);
        return $labels;
    }

    /**
     * Calculates the position of the requested label using a counter system.
     * The labels will be positioned accordingly:
     * first: top left
     * second: top right
     * third: bottom left
     * fourth: bottom right
     *
     * @param bool|int $counter
     *
     * @throws TIG_PostNL_Exception
     *
     * @return array
     */
    protected function _getPosition($counter = false)
    {
        if ($counter === false) {
            $position = array('x' => 0, 'y' => 0);

            return $position;
        }

        switch($counter) {
            case 1:
                $position = array('x' => $this->pix2pt(579), 'y' => $this->pix2pt(15));
                break;
            case 2:
                $position = array('x' => $this->pix2pt(579), 'y' => $this->pix2pt(414));
                break;
            case 3:
                $position = array('x' => $this->pix2pt(15),  'y' => $this->pix2pt(15));
                break;
            case 4:
                $position = array('x' => $this->pix2pt(15),  'y' => $this->pix2pt(414));
                break;
            default:
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Invalid counter: %s', $counter),
                    'POSTNL-0067'
                );
        }

        return $position;
    }

    /**
     * Calculates the position of the requested label using a counter system. This method is for labels which are
     * rotated by 90 degrees. Currently this is only used for EPS combi-labels.
     * The labels will be positioned accordingly:
     * first: top left
     * second: top right
     * third: bottom left
     * fourth: bottom right
     *
     * @param bool|int $counter
     *
     * @throws TIG_PostNL_Exception
     *
     * @return array
     */
    protected function _getRotatedPosition($counter = false)
    {
        if ($counter === false) {
            $position = array('x' => 0, 'y' => 0);

            return $position;
        }

        switch($counter) {
            case 1:
                $position = array('x' => $this->pix2pt(2), 'y' => $this->pix2pt(-1055));
                break;
            case 2:
                $position = array('x' => $this->pix2pt(400), 'y' => $this->pix2pt(-1055));
                break;
            case 3:
                $position = array('x' => $this->pix2pt(2),  'y' => $this->pix2pt(-483));
                break;
            case 4:
                $position = array('x' => $this->pix2pt(400),  'y' => $this->pix2pt(-483));
                break;
            default:
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Invalid counter: %s', $counter),
                    'POSTNL-0067'
                );
        }

        return $position;
    }

    /**
     * Converts pixels to points. 3.8 pixels is 1 pt in pdfs.
     *
     * @param int $pixels
     *
     * @return int
     */
    public function pix2pt($pixels = 0)
    {
        if($pixels != 0) {
            $points =  round($pixels / 3.8, 1);
            return $points;
        }

        return 0;
    }
}
