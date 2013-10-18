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
require_once('Fpdf/fpdf.php');
require_once('Fpdi/fpdf_tpl.php');
require_once('Fpdi/fpdi.php');
require_once('Fpdi/fpdi_pdf_parser.php');

class TIG_PostNL_Fpdi extends FPDI
{
    protected $_pageCount = 0;
    
    public function setPageCount($count)
    {
        $this->_pageCount = $count;
        
        return $this;
    }
    
    public function getPageCount()
    {
        return $this->_pageCount;
    }
    
    public function increasePageCount()
    {
        $currentCount = $this->getPageCount();
        $this->setPageCount($currentCount + 1);
        
        return $this;
    }
    
    public function addOrientedPage($orientation = '', $format = '')
    {
        $this->AddPage($orientation, $format); // create landscape
        
        $this->increasePageCount();
        if($orientation == 'L')
        {
            $this->rotatedPage[$this->getPageCount()] = -90; // set to portrait before output
        }
        
        return $this;
    }
    
    public function insertTemplate($filename, $x = null, $y = null, $w = 0)
    {
        $this->setSourceFile($filename);
        $tplidx = $this->ImportPage(1);
        $this->useTemplate($tplidx, $x, $y, $w);
        
        return $this;
    }
}
