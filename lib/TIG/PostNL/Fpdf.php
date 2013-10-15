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
include('Fpdf/fpdf.php');
class TIG_PostNL_Fpdf extends FPDF
{
    // public static function pix2pt($input)
    // {
        // if($input>0) {
            // return round($input/3.8,1); // 3.8 pixels = 1pt in pdf
        // } else {
            // return 0;
        // }
    // }
// 
    // public function writeRotie($x, $y, $txt, $text_angle = 90, $font_angle = 0)
    // {
        // if ($x < 0) {
            // $x += $this->w;
        // }
        // if ($y < 0) {
            // $y += $this->h;
        // }
// 
        // /* Escape text. */
        // $text = $this->_escape($txt);
// 
        // $font_angle += 90 + $text_angle;
        // $text_angle *= M_PI / 180;
        // $font_angle *= M_PI / 180;
// 
        // $text_dx = cos($text_angle);
        // $text_dy = sin($text_angle);
        // $font_dx = cos($font_angle);
        // $font_dy = sin($font_angle);
// 
        // $s = sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (', $text_dx, $text_dy, $font_dx, $font_dy, $x * $this->k, ($this->h - $y) * $this->k);
        // $s = str_replace(',', '.', $s); // fix for Dutch locale formatting
        // $s .= $text . ') Tj ET';
// 
        // if($this->underline && $txt!='')
        // {
            // $s .= ' ' . $this->_dounderline($x, $y, $txt);
        // }
        // if($this->ColorFlag)
        // {
            // $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        // }
        // $this->_out($s);
    // }
}
