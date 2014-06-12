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
class TIG_PostNL_Model_Core_Packingslip extends Mage_Sales_Model_Order_Pdf_Shipment
{
    /**
     * @param array $labels
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return Zend_Pdf
     */
    public function createPdf($labels, $postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();
        $storeId  = $shipment->getStoreId();

        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();

        $this->_setFontBold($style, 7);
        $this->_setFontBold($style, 10);

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->emulate($storeId);
            Mage::app()->setCurrentStore($storeId);
        }
        $page  = $this->newPage();
        $order = $shipment->getOrder();

        /*
         * Add image
         */
        $this->_insertLogo($page, $storeId);

        /*
         * Add address
         */
        $this->_insertAddress($page, $storeId);

        /**
         * Add company info
         */
        $this->_insertCompanyInfo($page, $storeId);
//        /* Add head */
//        $this->insertOrder(
//             $page,
//             $shipment,
//             Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId())
//        );
//        /* Add document text and number */
//        $this->insertDocumentNumber(
//             $page,
//             Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId()
//        );
//        /* Add table */
//        $this->_drawHeader($page);
//        /* Add body */
//        foreach ($shipment->getAllItems() as $item) {
//            if ($item->getOrderItem()->getParentItem()) {
//                continue;
//            }
//            /* Draw item */
//            $this->_drawItem($item, $page, $order);
//            $page = end($pdf->pages);
//        }

        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Insert logo to pdf page
     *
     * @param Zend_Pdf_Page &$page
     * @param null|int      $store
     *
     * @return void
     */
    protected function _insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if (!$image) {
            return;
        }

        $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
        if (!is_file($image)) {
            return;
        }

        $image       = Zend_Pdf_Image::imageWithPath($image);
        $top         = 830; //top border of the page
        $widthLimit  = 200; //half of the page width
        $heightLimit = 34; //assuming the image is not a "skyscraper"
        $width       = $image->getPixelWidth();
        $height      = $image->getPixelHeight();

        //preserving aspect ratio (proportions)
        $ratio = $width / $height;

        if ($width > $widthLimit) {
            $width  = $widthLimit;
            $height = $width / $ratio;
        }

        if ($height > $heightLimit) {
            $height = $heightLimit;
            $width  = $height * $ratio;
        }

        if ($ratio == 1 && $height > $heightLimit) {
            $height = $heightLimit;
            $width  = $widthLimit;
        }

        $y1 = $top - $height;
        $y2 = $top;
        $x1 = 25;
        $x2 = $x1 + $width;

        //coordinates after transformation are rounded by Zend
        $page->drawImage($image, $x1, $y1, $x2, $y2);

        $this->y = $y1 - 10;
    }

    /**
     * Insert address to pdf page
     *
     * @param Zend_Pdf_Page &$page
     * @param null|int      $store
     */
    protected function _insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 775;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if (empty($value)) {
                continue;
            }

            $value = preg_replace('/<br[^>]*>/i', "\n", $value);
            $splitString = Mage::helper('core/string')->str_split($value, 45, true, true);
            foreach ($splitString as $_value) {
                $page->drawText(
                     trim(
                         strip_tags($_value)
                     ),
                     25,
                     $top,
                     'UTF-8'
                );
                $top -= 10;
            }
        }
        $this->y = ($this->y > $top) ? $top : $this->y;
    }

    protected function _insertCompanyInfo(&$page, $store = null)
    {

    }

}