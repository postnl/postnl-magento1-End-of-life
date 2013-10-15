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
class TIG_PostNL_Model_Core_Label extends Varien_Object
{
    /**
     * base name of temporary pdf files. An md5 hash will be prepended to this name in order to make each filename unique
     * 
     * @var string
     */
    const TEMP_LABEL_FILENAME = 'TIG_PostNL_temp.pdf';
    
    /**
     * An array of temporary files that have been created. these files will be destroyed at the end of the script.
     * 
     * @var array
     */
    protected $_tempFilesSaved = array();
    
    public function getTempFilesSaved()
    {
        return $this->_tempFilesUsed;
    }
    
    public function setTempFilesSaved($tempFilesUsed)
    {
        $this->_tempFilesUsed = $tempFilesUsed;
        
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
        /**
         * Open a new pdf object and assign some basic values
         */
        $pdf = new TIG_PostNL_Fpdi(); //lib/TIG/PostNL/Fpdi
        $pdf->open();
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFillColor(255,255,255);
        $pdf->SetTitle('PostNL Shipping Labels');
        $pdf->SetAuthor('PostNL');
        $pdf->SetCreator('PostNL');
        
        if (is_array($labels)) {
            $pdf->addOrientedPage('L', 'A4'); //landscape A4
            $pdf = $this->_createMultiLabelPdf($pdf, $labels);
        } else {
            $pdf->addOrientedPage('L', 'A6'); //landscape A6
            $pdf = $this->_addPdfTemplate($pdf, $labels);
        }
        
        /**
         * Destroy the temporary labels as they are no longer needed
         */
        $this->_destroyTempLabels();
        
        /**
         * Output the label as a download response
         */
        $pdf->Output('PostNL Shipping Labels.pdf', 'D');
        
        return $this;
    }

    /**
     * Adds multiple labels to the pdf
     * 
     * @param TIG_PostNL_Fpdi $pdf
     * @param array $labels
     * 
     * @return TIG_PostNL_Fpdi $pdf
     */
    protected function _createMultiLabelPdf($pdf, $labels)
    {
        $n = 0;
        foreach ($labels as $label) {
            /**
             * Every 4 labels result in a single page
             */
            if (++$n > 4) {
                $pdf->addOrientedPage('L', 'A4');
                $n = 1;
            }
            
            $pdf = $this->_addPdfTemplate($pdf, $label, $n);
        }

        return $pdf;
    }
    
    /**
     * Adds a lebl to the pdf by storing it in a temporary pdf file and then adding it to the master pdf object
     * 
     * @param TIG_PostNL_Fpdi $pdf
     * @param string $label
     * @param int $labelCounter A counter used to determine the position of the next label to be added.
     * 
     * @return TIG_PostNL_Fpdi $pdf
     */
    protected function _addPdfTemplate($pdf, $label, $labelCounter = false)
    {
        /**
         * Fpdi requires labels to be provided as files. Therefore the label will be saved as a temporary file in var/TIG/PostNL/temp_labels/
         */
        $tempFilename = $this->_saveTempLabel($label);
        
        /**
         * Calculate the position of the next label to be printed
         */
        $position = $this->_getPosition($labelCounter);
        /**
         * Add the next label to the pdf
         */
        $pdf->insertTemplate($tempFilename, $position['x'], $position['y'], $this->pix2pt(538));
        
        return $pdf;
    }
    
    /**
     * Save a label to a temporary pdf file. Temporary pdf files are stored in var/TIG/PostNL/temp_label/
     * 
     * @param string $label
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
                      . self::TEMP_LABEL_FILENAME;
        
        /**
         * Add the base64 decoded label to the file
         */
        file_put_contents($tempFilePath, base64_decode($label));
        
        /**
         * Save the name of the temp file so itcan be destroyed later
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
     * Calculates the position of the requested label using a counter system.
     * The labels will be positioned accordingly:
     * first: top left
     * second: top right
     * third: bottom left
     * fourth: bottom right
     * 
     * @param int $counter
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _getPosition($counter = false)
    {
        if ($counter === false) {
            $position = array('x' => 0, 'y' => 0);
            
            return $position;
        }
        
        switch($counter)
        {
            case 1: 
                $position = array('x' => $this->pix2pt(579), 'y' => $this->pix2pt(0));  
                break;
            case 2: 
                $position = array('x' => $this->pix2pt(579), 'y' => $this->pix2pt(379)); 
                break;
            case 3: 
                $position = array('x' => $this->pix2pt(15),  'y' => $this->pix2pt(0));  
                break; // also used for A6
            case 4: 
                $position = array('x' => $this->pix2pt(15),  'y' => $this->pix2pt(379)); 
                break;
            default: 
                throw Mage::exception('TIG_PostNL', 'Invalid counter: ' . $counter);
        }
        
        return $position;
    }
    
    /**
     * Converts pixels to points. 3.8 pixels is 1 pt in pdfs
     * 
     * @param float $input
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
