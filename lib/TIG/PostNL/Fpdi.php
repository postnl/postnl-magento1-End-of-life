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
    
    // overloaded to allow rotation of pages right before finishing the document
    public function _putpages()
    {
        $nb=$this->page;
        if(!empty($this->AliasNbPages))
        {
            //Replace number of pages
            for($n=1;$n<=$nb;$n++)
                $this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
        }
        if($this->DefOrientation=='P')
        {
            $wPt=$this->DefPageFormat[0]*$this->k;
            $hPt=$this->DefPageFormat[1]*$this->k;
        }
        else
        {
            $wPt=$this->DefPageFormat[1]*$this->k;
            $hPt=$this->DefPageFormat[0]*$this->k;
        }
        $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
        for($n=1;$n<=$nb;$n++)
        {
            //Page
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            if(isset($this->PageSizes[$n]))
                $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));

            // START MOD PAGE ROTATION
            if (isset($this->rotatedPage[$n])) {
                $this->_out('/Rotate '.$this->rotatedPage[$n]);
            }
            // END MOD PAGE ROTATION

            $this->_out('/Resources 2 0 R');
            if(isset($this->PageLinks[$n]))
            {
                //Links
                $annots='/Annots [';
                foreach($this->PageLinks[$n] as $pl)
                {
                    $rect=sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
                    $annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
                    if(is_string($pl[4]))
                        $annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
                    else
                    {
                        $l=$this->links[$pl[4]];
                        $h=isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
                        $annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
                    }
                }
                $this->_out($annots.']');
            }
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            //Page content
            $p=($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
            $this->_newobj();
            $this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }
        //Pages root
        $this->offsets[1]=strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Pages');
        $kids='/Kids [';
        for($i=0;$i<$nb;$i++)
            $kids.=(3+2*$i).' 0 R ';
        $this->_out($kids.']');
        $this->_out('/Count '.$nb);
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
        $this->_out('>>');
        $this->_out('endobj');
    }
}
