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

$includePath = '';
if (defined('COMPILER_INCLUDE_PATH')) {
    $includePath = 'TIG/PostNL/';
}

require_once($includePath . 'Fpdf/fpdf.php');
require_once($includePath . 'Fpdi/fpdf_tpl.php');
require_once($includePath . 'Fpdi/fpdi.php');
require_once($includePath . 'Fpdi/fpdi_pdf_parser.php');

class TIG_PostNL_Fpdi extends FPDI
{
    var $angle=0;
    
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
        $templateIndex = $this->ImportPage(1);
        $this->useTemplate($templateIndex, $x, $y, $w);
        
        return $this;
    }
    
    function Rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }
    
    function _endpage()
    {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}
