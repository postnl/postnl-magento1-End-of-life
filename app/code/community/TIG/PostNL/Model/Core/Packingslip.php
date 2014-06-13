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
 */
class TIG_PostNL_Model_Core_Packingslip extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * @var int|void
     */
    public $rightColumnY;

    public function getPdf()
    {
        return false;
    }

    /**
     * Create the full packingslip pdf. This will create an initial Zend_Pdf object with all the address and order info
     * and then merge that with the shipping labels using Fpdi.
     *
     * @param array $labels
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return Zend_Pdf
     */
    public function createPdf($labels, $postnlShipment)
    {
        $pdf = $this->_getPackingSlipPdf($postnlShipment);

        $labelModel = Mage::getModel('postnl_core/label')
                          ->setLabelSize('A4')
                          ->setOutputMode('S');

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Label $firstLabel
         */
        $labels = $labelModel->sortLabels($labels);
        $firstLabel = array_shift($labels);

        if (
            $this->y < 421
            || ($firstLabel->getLabelType() != 'Label' && $firstLabel->getLabelType() != 'Label-combi')
        ) {
            $labelsString = $labelModel->createPdf($labels);

            $labelPdf = Zend_Pdf::parse($labelsString);

            foreach ($labelPdf->pages as $page) {
                $pdf->pages[] = clone $page;
            }
        } else {
            $packingSlipString = $pdf->render();
            $labelsString = $labelModel->createPackingSlipLabel($firstLabel, $packingSlipString);

            $pdf = Zend_Pdf::parse($labelsString);

            if (count($labels) > 0) {
                $additionalLabelsString = $labelModel->createPdf($labels);

                $labelPdf = Zend_Pdf::parse($additionalLabelsString);

                foreach ($labelPdf->pages as $page) {
                    $pdf->pages[] = clone $page;
                }
            }
        }

        return $pdf;
    }

    /**
     * Builds the packingslip part of the final pdf.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return Zend_Pdf
     */
    protected function _getPackingSlipPdf($postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();

        /**
         * Create a dummy invoice for the totals at the bottom of the packingslip.
         */
        $invoice  = Mage::getModel('postnl_core/service')->initInvoice($shipment, true);
        $storeId  = $shipment->getStoreId();

        $this->_beforeGetPdf();
        $this->_initRenderer('postnl_packingslip');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->emulate($storeId);
            Mage::app()->setCurrentStore($storeId);
        }
        $page  = $this->newPage();
        $order = $shipment->getOrder();

        /*
         * Add image.
         */
        $this->_insertLogo($page, $storeId);

        /*
         * Add address.
         */
        $this->_insertAddress($page, $storeId);

        /**
         * Add company info.
         */
        $this->_insertCompanyInfo($page, $storeId);

        /**
         * Add customer number.
         */
        $this->_insertCustomerId($page, $order->getCustomerId());

        /**
         * Add order info.
         */
        $this->_insertOrderInfo($page, $order, $shipment);

        /**
         * Add payment info.
         */
        $this->_insertPaymentInfo($page, $order);

        /**
         * Add order addresses.
         */
        $this->_insertOrderAddresses(
             $page,
             $order
        );

        /**
         * Add shipment info.
         */
        $this->_insertShipmentInfo($page, $order, $postnlShipment);

        if ($this->rightColumnY < $this->y) {
            $this->y = $this->rightColumnY;
        }

        /**
         * Add shipment items table.
         */
        $this->_drawItemsHeader($page);

        /**
         * Add table body.
         *
         * @var Mage_Sales_Model_Order_Invoice_Item $item
         */
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
        $items = $invoice->getAllItems();
        foreach ($items as $item) {
            if ($item->getOrderItem()->getParentItem()) {
                continue;
            }
            /**
             * Draw item
             */
            $page->drawLine(15, $this->y - 5, 580, $this->y - 5);
            $this->_drawItem($item, $page, $order);
            $page = end($pdf->pages);
        }
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));

        /**
         * Add shipment comment.
         */
        $this->_insertShipmentComment($page, $shipment);

        /**
         * Add totals.
         */
        $this->_insertTotals($page, $order, $invoice);

        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    /**
     * Set font as regular.
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold.
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic.
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Insert logo to pdf page.
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
        $top         = 827; //top border of the page
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
        $x1 = 15;
        $x2 = $x1 + $width;

        //coordinates after transformation are rounded by Zend
        $page->drawImage($image, $x1, $y1, $x2, $y2);

        $this->y = $y1 - 14;
    }

    /**
     * Insert address to pdf page.
     *
     * @param Zend_Pdf_Page &$page
     * @param null|int      $store
     */
    protected function _insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 8);
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
                     15,
                     $top,
                     'UTF-8'
                );
                $top -= 10;
            }
        }
        $this->y = ($this->y > $top) ? $top : $this->y;
    }

    /**
     * Add company info.
     *
     * @param Zend_Pdf_Page &$page
     * @param int|null      $store
     */
    protected function _insertCompanyInfo(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 8);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 775;

        $page->drawText(
             Mage::helper('postnl')->__('CoC') . ' ' . 'xxxxxxxx',
             165,
             $top,
             'UTF-8'
        );
        $top -= 10;

        $page->drawText(
             Mage::helper('postnl')->__('VAT') . ' ' . 'xxxxxxxx',
             165,
             $top,
             'UTF-8'
        );
        $top -= 10;

        $this->y = ($this->y > $top) ? $top : $this->y;
    }

    /**
     * Add order info.
     *
     * @param Zend_Pdf_Page                   &$page
     * @param Mage_Sales_Model_Order          $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _insertOrderInfo(&$page, $order, $shipment)
    {
        $top = 815;

        $font = $this->_setFontBold($page, 15);
        $text = Mage::helper('postnl')->__('Packingslip');
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 15);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 20;

        $font = $this->_setFontRegular($page, 8);
        $text = Mage::helper('postnl')->__('Order') . ' # ' . $order->getIncrementId();
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $text = Mage::helper('postnl')->__('Shipment') . ' # ' . $shipment->getIncrementId();
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $text = Mage::helper('postnl')->__('Order date')
              . ': '
              . Mage::helper('core')->formatDate($order->getCreatedAtDate(), 'long', false);
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $this->rightColumnY = $top;
    }

    /**
     * @param Zend_Pdf_Page          &$page
     * @param Mage_Sales_Model_Order $order
     */
    protected function _insertPaymentInfo(&$page, $order)
    {
        $this->rightColumnY -= 24;
        $top = $this->rightColumnY;

        $font = $this->_setFontBold($page, 8);
        $text = Mage::helper('postnl')->__('Payment method');
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
                           ->setIsSecureMode(true)
                           ->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        $font = $this->_setFontRegular($page, 8);
        foreach ($payment as $value){
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $text = strip_tags(trim($_value));
                    $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);

                    $page->drawText(strip_tags(trim($_value)), $x, $top, 'UTF-8');
                    $top -= 10;
                }
            }
        }

        $this->rightColumnY = $top;
    }

    /**
     * @param Zend_Pdf_Page                   &$page
     * @param Mage_Sales_Model_Order          $order
     * @param TIG_Postnl_Model_Core_Shipment  $postnlShipment
     */
    protected function _insertShipmentInfo(&$page, $order, $postnlShipment)
    {
        $this->rightColumnY -= 14;
        $top = $this->rightColumnY;

        $font = $this->_setFontBold($page, 8);
        $text = Mage::helper('postnl')->__('Shipping method');
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $shippingMethod = $order->getShippingDescription();

        $font = $this->_setFontRegular($page, 8);
        $text = strip_tags(trim($shippingMethod)) . ' - ' . $order->formatPriceTxt($order->getShippingAmount());
        $x = 584 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText($text, $x, $top, 'UTF-8');

        $top -= 10;

        $deliveryDate = $postnlShipment->getDeliveryDate();
        $text = Mage::helper('core')->formatDate($deliveryDate, 'short', false);
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText($text, $x, $top, 'UTF-8');

        $top -= 24;

        $font = $this->_setFontBold($page, 8);
        $text = Mage::helper('postnl')->__('Ship order on');
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $font = $this->_setFontRegular($page, 8);
        $confirmDate = $postnlShipment->getConfirmDate();
        $text = Mage::helper('core')->formatDate($confirmDate, 'full', false);
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $this->rightColumnY = $top;
    }

    /**
     * @param Zend_Pdf_Page &$page
     * @param int|null      $customerId
     */
    protected function _insertCustomerId(&$page, $customerId)
    {
        if (!$customerId) {
            return;
        }

        $this->_setFontBold($page, 8);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = $this->y - 24;

        $page->drawText(
             Mage::helper('postnl')->__('Customer number'),
             15,
             $top,
             'UTF-8'
        );
        $top -= 10;

        $this->_setFontRegular($page, 8);

        $page->drawText(
             $customerId,
             15,
             $top,
             'UTF-8'
        );

        $this->y = ($this->y > $top) ? $top : $this->y;
    }

    /**
     * Insert order addresses to the pdf page.
     *
     * @param Zend_Pdf_Page          &$page
     * @param Mage_Sales_Model_Order $obj
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _insertOrderAddresses(&$page, $obj)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        } else {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No valid order available for packing slip.').
                'POSTNL-0168'
            );
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y - 14;

        $this->_setFontRegular($page, 8);

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
        /* Shipping Address */
        $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));

        /**
         * PakjeGemak Address
         *
         * @var Mage_Sales_Model_Order_Address $address
         */
        $pakjeGemakAddress = false;
        $addressesCollection = $order->getAddressesCollection();
        foreach ($addressesCollection as $address) {
            if ($address->getAddressType() != 'pakje_gemak') {
                continue;
            }

            $pakjeGemakAddress = $this->_formatAddress($address->format('pdf'));
            break;
        }

        $this->_setFontBold($page, 8);
        $page->drawText(Mage::helper('postnl')->__('Billing address'), 15, ($top - 15), 'UTF-8');
        $page->drawText(Mage::helper('postnl')->__('Shipping address'), 165, ($top - 15), 'UTF-8');
        if ($pakjeGemakAddress) {
            $page->drawText(Mage::helper('postnl')->__('Post office address'), 315, ($top - 15), 'UTF-8');
        }

        $this->_setFontRegular($page, 8);
        $this->y = $top - 25;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 20, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 15, $this->y, 'UTF-8');
                    $this->y -= 10;
                }
            }
        }

        $addressesEndY = $this->y;

        if (isset($shippingAddress)) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $text = array();
                    foreach (Mage::helper('core/string')->str_split($value, 20, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 165, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;
        }

        if ($pakjeGemakAddress) {
            $this->y = $addressesStartY;
            foreach ($pakjeGemakAddress as $value){
                if ($value!=='') {
                    $text = array();
                    foreach (Mage::helper('core/string')->str_split($value, 20, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 315, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;
        }

        $this->y -= 14;
    }

    /**
     * Draw table header for product items.
     *
     * @param  Zend_Pdf_Page &$page
     *
     * @return void
     */
    protected function _drawItemsHeader(Zend_Pdf_Page $page)
    {
        /*
         * Add table head.
         */
        $this->_setFontRegular($page, 8);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->drawRectangle(15, $this->y, 580, $this->y-20);
        $this->y -= 13;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));

        /**
         * Add table columns.
         */
        $lines = array(
            array(
                array(
                    'text'      => Mage::helper('postnl')->__('Products'),
                    'feed'      => 20,
                    'font'      => 'bold',
                    'align'     => 'left',
                    'font_size' => 8,
                ),
                array(
                    'text'      => Mage::helper('postnl')->__('SKU'),
                    'feed'      => 275,
                    'font'      => 'bold',
                    'align'     => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'      => Mage::helper('postnl')->__('Price'),
                    'feed'      => 365,
                    'font'      => 'bold',
                    'align'     => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'      => Mage::helper('postnl')->__('Qty'),
                    'feed'      => 435,
                    'font'      => 'bold',
                    'align'     => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'      => Mage::helper('postnl')->__('VAT'),
                    'feed'      => 495,
                    'font'      => 'bold',
                    'align'     => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'      => Mage::helper('postnl')->__('Subtotal'),
                    'feed'      => 575,
                    'font'      => 'bold',
                    'align'     => 'right',
                    'font_size' => 8,
                ),
            ),
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
    }

    /**
     * Add shipment comment to the page.
     *
     * @param Zend_Pdf_Page                   &$page
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _insertShipmentComment(&$page, $shipment)
    {
        $commentsCollection = $shipment->getCommentsCollection();
        $commentsCollection->getSelect()
                           ->limit(1);

        /**
         * @var Mage_Sales_Model_Order_Shipment_Comment $comment
         */
        $comment = $commentsCollection->getFirstItem();
        if (!$comment || !$comment->getId()) {
            return;
        }

        $top = $this->y;

        $commentText = $comment->getComment();
        $commentTextParts = Mage::helper('core/string')->str_split($commentText, 70, true, true);
        $height = 12 * count($commentTextParts) + 10;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->drawRectangle(15, $top, 300, $top - $height);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0));

        $top -= 14;
        $this->_setFontItalic($page, 8);

        foreach ($commentTextParts as $text) {
            $page->drawText(
                $text,
                20,
                $top
            );
            $top -= 10;
        }

        $this->_setFontRegular($page, 8);
    }

    /**
     * Adds totals to the page.
     *
     * @param Zend_Pdf_Page                  &$page
     * @param Mage_Sales_Model_Order         $order
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    protected function _insertTotals(&$page, $order, $invoice)
    {
        $totals = $this->_getTotalsList($order);
        $lineBlock = array(
            'lines'  => array(),
            'height' => 15
        );

        /**
         * @var Mage_Sales_Model_Order_Pdf_Total_Default $total
         */
        foreach ($totals as $total) {
            $total->setOrder($order)
                  ->setSource($invoice);

            if ($total->canDisplay()) {
                $total->setFontSize(10);
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    $label = array(
                        'text'      => $totalData['label'],
                        'feed'      => 495,
                        'align'     => 'right',
                        'font_size' => 8,
                        'height'    => 15,
                    );

                    $value = array(
                        'text'      => $totalData['amount'],
                        'feed'      => 580,
                        'align'     => 'right',
                        'font_size' => 8,
                        'height'    => 15,
                    );

                    if ($total->getSourceField() == 'grand_total') {
                        $label['font'] = 'bold';
                        $value['font'] = 'bold';
                    }

                    $line = array($label, $value);

                    $lineBlock['lines'][] = $line;
                }
            }
        }

        $this->y -= 4;
        $page = $this->drawLineBlocks($page, array($lineBlock));
    }
}