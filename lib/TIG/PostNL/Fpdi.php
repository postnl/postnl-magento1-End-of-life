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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
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

    function _putpages()
    {
        $nb = $this->page;
        if(!empty($this->AliasNbPages))
        {
            // Replace number of pages
            for($n=1;$n<=$nb;$n++)
                $this->pages[$n] = str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
        }
        if($this->DefOrientation=='P')
        {
            $wPt = $this->DefPageSize[0]*$this->k;
            $hPt = $this->DefPageSize[1]*$this->k;
        }
        else
        {
            $wPt = $this->DefPageSize[1]*$this->k;
            $hPt = $this->DefPageSize[0]*$this->k;
        }
        $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
        for($n=1;$n<=$nb;$n++)
        {
            // Page
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
                // Links
                $annots = '/Annots [';
                foreach($this->PageLinks[$n] as $pl)
                {
                    $rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
                    $annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
                    if(is_string($pl[4]))
                        $annots .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
                    else
                    {
                        $l = $this->links[$pl[4]];
                        $h = isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
                        $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
                    }
                }
                $this->_out($annots.']');
            }
            if($this->PDFVersion>'1.3')
                $this->_out('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            // Page content
            $p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
            $this->_newobj();
            $this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }
        // Pages root
        $this->offsets[1] = strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Pages');
        $kids = '/Kids [';
        for($i=0;$i<$nb;$i++)
            $kids .= (3+2*$i).' 0 R ';
        $this->_out($kids.']');
        $this->_out('/Count '.$nb);
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
        $this->_out('>>');
        $this->_out('endobj');
    }

    function Output($name='', $dest='')
    {
        // Output PDF to some destination
        if($this->state<3)
            $this->Close();
        $dest = strtoupper($dest);
        if($dest=='')
        {
            if($name=='')
            {
                $name = 'doc.pdf';
                $dest = 'I';
            }
            else
                $dest = 'F';
        }
        switch($dest)
        {
            case 'I':
                // Send to standard output
                $this->_checkoutput();

                return $this->buffer;
                // if(PHP_SAPI!='cli')
                // {
                    // // We send to a browser
                    // header('Content-Type: application/pdf');
                    // header('Content-Disposition: inline; filename=test.pdf');
                    // header('Cache-Control: private, max-age=0, must-revalidate');
                    // header('Pragma: public');
                // }
                // echo $this->buffer;
                // break;
            case 'D':
                // Download file
                $this->_checkoutput();
                header('Content-Type: application/x-download');
                header('Content-Disposition: attachment; filename="'.$name.'"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $this->buffer;
                break;
            case 'F':
                // Save to local file
                $f = fopen($name,'wb');
                if(!$f)
                    $this->Error('Unable to create output file: '.$name);
                fwrite($f,$this->buffer,strlen($this->buffer));
                fclose($f);
                break;
            case 'S':
                // Return as a string
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: '.$dest);
        }
        return '';
    }
}
