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
 * @method TIG_PostNL_Model_Core_Label setLabelSize(string $value)
 */
class TIG_PostNL_Model_Core_Label extends Varien_Object
{
    /**
     * Base name of temporary pdf files. An md5 hash and several other parameters will be prepended to this file name to
     * make it unique.
     */
    const TEMP_LABEL_FILENAME       = 'TIG_PostNL_temp.pdf';
    const TEMP_PACKINGSLIP_FILENAME = 'TIG_PostNL_temp_packingslip.pdf';

    /**
     * XML path to label size setting
     *
     * This setting is ignored for GlobalPack labels and single Dutch or EPS labels
     */
    const XPATH_LABEL_SIZE = 'postnl/cif_labels_and_confirming/label_size';

    /**
     * Maximum number of labels that may be printed at once.
     */
    const MAX_LABEL_COUNT = 200;

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
     * Flag if the current label is the first CODCard label processed.
     *
     * @var bool
     */
    protected $_isFirstCodCardLabel = false;

    /**
     * Output mode. Currently 2 modes are supported: I and S.
     *
     * @see FPDF::Output()
     *
     * @var string
     */
    protected $_outputMode = 'I';

    /**
     * An array of label position coordinates per position per label type.
     *
     * @var array
     */
    protected $_labelPositions = array(
        'Label' => array(
            1 => array(
                'x' => 152.4,
                'y' => 3.9,
                'w' => 141.6,
            ),
            2 => array(
                'x' => 152.4,
                'y' => 108.9,
                'w' => 141.6,
            ),
            3 => array(
                'x' => 3.9,
                'y' => 3.9,
                'w' => 141.6,
            ),
            4 => array(
                'x' => 3.9,
                'y' => 108.9,
                'w' => 141.6,
            ),
        ),
        'Return Label' => array(
            1 => array(
                'x' => 152.4,
                'y' => 3.9,
                'w' => 141.6,
            ),
            2 => array(
                'x' => 152.4,
                'y' => 108.9,
                'w' => 141.6,
            ),
            3 => array(
                'x' => 3.9,
                'y' => 3.9,
                'w' => 141.6,
            ),
            4 => array(
                'x' => 3.9,
                'y' => 108.9,
                'w' => 141.6,
            ),
        ),
        'BusPakje' => array(
            1 => array(
                'x' => 152.4,
                'y' => 3.9,
                'w' => 141.6,
            ),
            2 => array(
                'x' => 152.4,
                'y' => 108.9,
                'w' => 141.6,
            ),
            3 => array(
                'x' => 3.9,
                'y' => 3.9,
                'w' => 141.6,
            ),
            4 => array(
                'x' => 3.9,
                'y' => 108.9,
                'w' => 141.6,
            ),
        ),
        'BusPakjeExtra' => array(
            1 => array(
                'x' => 152.4,
                'y' => 3.9,
                'w' => 141.6,
            ),
            2 => array(
                'x' => 152.4,
                'y' => 108.9,
                'w' => 141.6,
            ),
            3 => array(
                'x' => 3.9,
                'y' => 3.9,
                'w' => 141.6,
            ),
            4 => array(
                'x' => 3.9,
                'y' => 108.9,
                'w' => 141.6,
            ),
        ),
        'Label-combi' => array(
            1 => array(
                'x' => 0.5,
                'y' => -277.6,
                'w' => 105.3,
            ),
            2 => array(
                'x' => 105.3,
                'y' => -277.6,
                'w' => 105.3,
            ),
            3 => array(
                'x' => 0.5,
                'y' => -127.1,
                'w' => 105.3,
            ),
            4 => array(
                'x' => 105.3,
                'y' => -127.1,
                'w' => 105.3,
            ),
        ),
        'CODcard' => array(
            array(
                'x' => 2,
                'y' => -39,
                'w' => 103,
            ),
        ),
        'CN23' => array(
            array(
                'x' => 3.9,
                'y' => 4.5,
                'w' => 204.2,
            ),
        ),
        'CommercialInvoice' => array(
            array(
                'x' => 3.9,
                'y' => 4.5,
                'w' => 204.2,
            ),
        ),
        'CP71' => array(
            array(
                'x' => 3.9,
                'y' => 152.1,
                'w' => 204.2,
            ),
        ),
    );

    /**
     * @param string $outputMode
     *
     * @return $this
     */
    public function setOutputMode($outputMode)
    {
        $this->_outputMode = $outputMode;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutputMode()
    {
        return $this->_outputMode;
    }

    /**
     * @param boolean $isFirstLabel
     *
     * @return $this
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
     * @param boolean $isFirstCodCardLabel
     *
     * @return $this
     */
    public function setIsFirstCodCardLabel($isFirstCodCardLabel)
    {
        $this->_isFirstCodCardLabel = $isFirstCodCardLabel;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFirstCodCardLabel()
    {
        return $this->_isFirstCodCardLabel;
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
     * @return $this
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
     * @return $this
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

        $labelSize = Mage::getStoreConfig(self::XPATH_LABEL_SIZE, Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->setLabelSize($labelSize);
        return $labelSize;
    }

    /**
     * @return array
     */
    public function getLabelPositions()
    {
        return $this->_labelPositions;
    }

    /**
     * Gets position coordinates for a given label type and an optional counter.
     *
     * @param string      $type
     * @param boolean|int $counter
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    public function _getLabelPosition($type, $counter = false)
    {
        $labelPositions = $this->getLabelPositions();

        if (!array_key_exists($type, $labelPositions)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid label type supplied: %s', $type),
                'POSTNL-0065'
            );
        }

        $positions = $labelPositions[$type];
        if (count($positions) === 1) {
            return current($positions);
        }

        if (!$counter || !array_key_exists($counter, $positions)) {
            return array(
                'x' => 0,
                'y' => 0,
                'w' => 0,
            );
        }

        return $positions[$counter];
    }

    /**
     * Reset the counter to 0
     *
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * Four labels will be printed on each page in a vertical position. All labels will be rotated 90 degrees
     * counter-clockwise.
     *
     * @param array|TIG_PostNL_Model_Core_Shipment_Label $labels May be an array of labels or a single
     *                                                           TIG_PostNL_Model_Core_Shipment_Label label.
     *
     * @return string
     *
     * @see TIG_PostNL_Fpdf
     * @see TIG_PostNL_Fpdi
     *
     * @link http://www.fpdf.org/                                    Fpdf library documentation.
     * @link http://www.setasign.de/products/pdf-php-solutions/fpdi/ Fpdi library documentation.
     */
    public function createPdf($labels)
    {
        Varien_Profiler::start('tig::postnl::core::label_createpdf');

        /**
         * Open a new pdf object and assign some basic values.
         */
        $pdf = new TIG_PostNL_Fpdi(); //lib/TIG/PostNL/Fpdi
        $pdf->open();
        $pdf->SetTitle('PostNL Shipping Labels');
        $pdf->SetAuthor('PostNL');
        $pdf->SetCreator('PostNL');

        if (!is_array($labels)
            && !(is_object($labels) && $labels instanceof TIG_PostNL_Model_Core_Resource_Shipment_Label_Collection)
        ) {
            $labels = array($labels);
        }
        /**
         * Create a pdf containing multiple labels.
         */
        $pdf = $this->_createMultiLabelPdf($pdf, $labels);

        /**
         * Destroy the temporary labels as they are no longer needed.
         */
        $this->_destroyTempLabels();

        /**
         * Get the final label.
         */
        $label = $pdf->Output('PostNL Shipping Labels.pdf', $this->getOutputMode());

        Varien_Profiler::stop('tig::postnl::core::label_createpdf');

        return $label;
    }

    /**
     * Creates a pdf containing both the packing slip and a shipping label. The shipping label must be of the 'Label' or
     * 'Label-combi' type.
     *
     * @param TIG_PostNL_Model_Core_Shipment_Label $label
     * @param string                               $packingSlip
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function createPackingSlipLabel($label, $packingSlip)
    {
        Varien_Profiler::start('tig::postnl::core::label_createpackingslippdf');

        /**
         * Open a new pdf object and assign some basic values.
         */
        $pdf = new TIG_PostNL_Fpdi(); //lib/TIG/PostNL/Fpdi
        $pdf->open();
        $pdf->SetTitle('PostNL Packingslip');
        $pdf->SetAuthor('PostNL');
        $pdf->SetCreator('PostNL');

        $pdf->addOrientedPage('P', 'A4');

        $tempPackingslip = $this->_saveTempPackingslip($label, $packingSlip);
        $tempLabel       = $this->_saveTempLabel($label);

        $pdf->insertTemplate($tempPackingslip, 0, 0);

        if ($label->getLabelType() == 'Label'
            || $label->getLabelType() == 'BusPakje'
            || $label->getLabelType() == 'BusPakjeExtra'
        ) {
            $pdf->Rotate(90);
            $pdf->insertTemplate($tempLabel, $this->pix2pt(-1037), $this->pix2pt(413), $this->pix2pt(538));
            $pdf->Rotate(0);
        } elseif ($label->getLabelType() == 'Label-combi') {
            $pdf->insertTemplate($tempLabel, $this->pix2pt(400), $this->pix2pt(569), $this->pix2pt(400));
        } else {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Invalid label type supplied for packing slip label pdf: %s.', $label->getLabelType()
                ),
                'POSTNL-0169'
            );
        }

        /**
         * Destroy the temporary labels as they are no longer needed.
         */
        $this->_destroyTempLabels();

        /**
         * Get the final label.
         */
        $label = $pdf->Output('PostNL Shipping Labels.pdf', $this->getOutputMode());

        Varien_Profiler::stop('tig::postnl::core::label_createpackingslippdf');

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
         * On a clean Magento install with 256 MB of memory, several thousands of labels can be printed at once.
         * However, for safety reasons a limit of 200 is used. By default you shouldn't be able to select more than 200
         * in the shipment grid.
         */
        if(count($labels) > self::MAX_LABEL_COUNT && !Mage::helper('postnl/cif')->allowInfinitePrinting()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Maximum amount of labels exceeded. Maximum allowed: 200. Requested: %s',
                    count($labels)
                ),
                'POSTNL-0064'
            );
        }

        $this->setIsFirstLabel(true);
        $this->setIsFirstCodCardLabel(true);
        $labels = $this->sortLabels($labels);
        foreach ($labels as $label) {
            $pdf = $this->_addPdfTemplate($pdf, $label);
        }

        return $pdf;
    }

    /**
     * Adds a label to the pdf by storing it in a temporary pdf file and then adding it to the master pdf object. Each
     * label type has it's own properties and size. For some we need to add another A4-size page first, for others we
     * need to rotate the pdf. This method calculates this before actually adding the pdf template.
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
         * var/TIG/PostNL/temp_label/
         */
        $tempFilename = $this->_saveTempLabel($label);

        $rotate = false;

        /**
         * First we need to add pages to the pdf for certain label types under certain conditions.
         */
        $labelType = $label->getLabelType();
        if ($labelType == 'Label'
            || $labelType == 'Label-combi'
            || $labelType == 'BusPakje'
            || $labelType == 'BusPakjeExtra'
            || $labelType == 'Return Label'
        ) {
            if ($this->getLabelSize() == 'A4' && $this->getIsFirstLabel()) {
                $pdf->addOrientedPage('L', 'A4');
                $this->setIsFirstLabel(false);
                if (!$this->getLabelCounter()) {
                    $this->resetLabelCounter();
                }
            } elseif ($this->getLabelSize() == 'A4'
                && (
                    !$this->getLabelCounter() || $this->getLabelCounter() > 4
                )
            ) {
                $pdf->addOrientedPage('L', 'A4');
                $this->resetLabelCounter();
            }

            /**
             * If the configured label size is A6, add a new page every label.
             */
            if($this->getLabelSize() == 'A6') {
                $this->setLabelCounter(3); //used to calculate the top left position
                $pdf->addOrientedPage('L', 'A6');
            }
        } elseif ($labelType == 'CODcard') {
            $pdf->addOrientedPage('P', array(156.65, 73.85));
        } elseif ($labelType == 'CN23'
            || $labelType == 'CommercialInvoice'
            || $labelType == 'CODcard'
        ) {
            $pdf->addOrientedPage('P', 'A4');
        }

        /**
         * Calculate the position of each label type and whether or not it should be rotated by a certain number of
         * degrees.
         */
        switch ($labelType) {
            case 'Label-combi':
                /**
                 * Rotate the pdf to accommodate the rotated combi-label.
                 */
                $pdf->Rotate('-90');

                $position = $this->_getLabelPosition($labelType, $this->getLabelCounter());

                $this->increaseLabelCounter();

                $rotate = true;
                break;
            case 'Label':
            case 'BusPakje':
            case 'BusPakjeExtra':
            case 'Return Label':
                $position = $this->_getLabelPosition($labelType, $this->getLabelCounter());

                $this->increaseLabelCounter();
                break;
            case 'CN23':
            case 'CommercialInvoice':
                $position = $this->_getLabelPosition($labelType);

                /**
                 * increase the label counter to above 4. This will prompt the creation of a new page.
                 */
                $this->setLabelCounter(5);
                break;
            case 'CP71':
                $position = $this->_getLabelPosition($labelType);

                /**
                 * increase the label counter to above 4. This will prompt the creation of a new page.
                 */
                $this->setLabelCounter(5);
                break;
            case 'CODcard':
                $pdf->Rotate('-90');

                $position = $this->_getLabelPosition($labelType);

                $this->increaseLabelCounter();

                $rotate = true;
                break;
            default:
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Invalid label type supplied: %s', $label->getLabelType()),
                    'POSTNL-0065'
                );
        }

        /**
         * Add the next label to the pdf.
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
     * @param TIG_PostNL_Model_Core_Shipment_Label $label
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _saveTempLabel(TIG_PostNL_Model_Core_Shipment_Label $label)
    {
        /**
         * construct the path to the temporary file.
         */
        $tempFilePath = Mage::getConfig()->getVarDir('TIG' . DS . 'PostNL' . DS . 'temp_label')
                      . DS
                      . md5($label->getLabel() . $label->getId())
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
         * Add the base64 decoded label to the file.
         */
        file_put_contents($tempFilePath, base64_decode($label->getLabel()));

        /**
         * Save the name of the temp file so it can be destroyed later.
         */
        $this->addTempFileSaved($tempFilePath);

        return $tempFilePath;
    }

    /**
     * Save a packing slip to a temporary pdf file. Temporary pdf files are stored in var/TIG/PostNL/temp_label/
     *
     * @param TIG_PostNL_Model_Core_Shipment_Label $label
     * @param string                               $packingSlip
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _saveTempPackingslip(TIG_PostNL_Model_Core_Shipment_Label $label, $packingSlip)
    {
        /**
         * construct the path to the temporary file.
         */
        $tempFilePath = Mage::getConfig()->getVarDir('TIG' . DS . 'PostNL' . DS . 'temp_label')
            . DS
            . md5($label->getLabel() . $packingSlip)
            . '-'
            . time()
            . '-'
            . self::TEMP_PACKINGSLIP_FILENAME;

        if (file_exists($tempFilePath)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Temporary template file already exists: %s', $tempFilePath),
                'POSTNL-0066'
            );
        }

        /**
         * Add the packing slip to the file.
         */
        file_put_contents($tempFilePath, $packingSlip);

        /**
         * Save the name of the temp file so it can be destroyed later.
         */
        $this->addTempFileSaved($tempFilePath);

        return $tempFilePath;
    }

    /**
     * Destroy all temporary pdf files
     *
     * @return $this
     */
    protected function _destroyTempLabels()
    {
        $tempFilesSaved = $this->getTempFilesSaved();
        foreach ($tempFilesSaved as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return $this;
    }

    /**
     * Sorts labels by label type. First all labels of the 'Label', 'Label-combi', 'BusPakje' and 'BusPakjeExtra' type.
     * Then all other labels in the order of 'CODcard' > 'CN23' > 'CP71' > 'CommercialInvoice' grouped by shipments.
     *
     * @param array $labels
     *
     * @return array
     */
    public function sortLabels($labels)
    {
        $generalLabels = array();
        $globalLabels  = array();
        $codCards      = array();

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Label $label
         */
        foreach ($labels as $label) {
            /**
             * Separate general labels from the rest.
             */
            if ($label->getLabelType() == 'Label'
                || $label->getLabelType() == 'Label-combi'
                || $label->getLabelType() == 'BusPakje'
                || $label->getLabelType() == 'BusPakjeExtra'
                || $label->getLabelType() == 'Return Label'
            ) {
                $generalLabels[] = $label;
                continue;
            }

            /**
             * Separate COD cards.
             */
            if ($label->getLabelType() == 'CODcard') {
                $codCards[] = $label;
                continue;
            }

            /**
             * Group other labels by shipment id (parent_id attribute).
             */
            if (isset($globalLabels[$label->getParentId()])) {
                $globalLabels[$label->getParentId()][$label->getLabelType()] = $label;
            } else {
                $globalLabels[$label->getParentId()] = array($label->getlabelType() => $label);
            }
        }

        /**
         * Sort all GlobalPack labels.
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
         * Merge all labels back into a single array.
         */
        $labels = array_merge($generalLabels, $sortedGlobalLabels, $codCards);
        return $labels;
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
            $points = round($pixels / 3.8, 1);
            return $points;
        }

        return 0;
    }
}
