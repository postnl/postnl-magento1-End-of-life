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
 *
 * @method TIG_PostNL_Model_Core_PackingSlip setStoreId(int $value)
 * @method TIG_PostNL_Model_Core_PackingSlip setItemColumns(array $value)
 *
 * @method int                               getStoreId()
 *
 * @method boolean                           hasItemColumns()
 */
class TIG_PostNL_Model_Core_PackingSlip extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Xpath to packing slip configuration settings.
     */
    const XPATH_PACKING_SLIP_SETTINGS = 'postnl/packing_slip';

    /**
     * Xpath to the 'item_columns' configuration setting.
     */
    const XPATH_ITEM_COLUMNS = 'postnl/packing_slip/item_columns';

    /**
     * The height of a page's top and bottom margins.
     */
    const PAGE_TOP_HEIGHT    = 815;
    const PAGE_BOTTOM_HEIGHT = 15;

    /**
     * Y coordinate for right column elements.
     *
     * @var int|void
     */
    public $rightColumnY;

    /**
     * @var TIG_PostNL_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_coreHelper;

    /**
     * @var Mage_Core_Helper_String
     */
    protected $_stringHelper;

    /**
     * @var array
     */
    protected $_config;

    /**
     * @param TIG_PostNL_Helper_Data $helper
     *
     * @return $this
     */
    public function setHelper($helper)
    {
        $this->_helper = $helper;

        return $this;
    }

    /**
     * @return TIG_PostNL_Helper_Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @param Mage_Core_Helper_Data $coreHelper
     *
     * @return $this
     */
    public function setCoreHelper($coreHelper)
    {
        $this->_coreHelper = $coreHelper;

        return $this;
    }

    /**
     * @return Mage_Core_Helper_Data
     */
    public function getCoreHelper()
    {
        return $this->_coreHelper;
    }

    /**
     * @param Mage_Core_Helper_String $stringHelper
     *
     * @return $this
     */
    public function setStringHelper($stringHelper)
    {
        $this->_stringHelper = $stringHelper;

        return $this;
    }

    /**
     * @return Mage_Core_Helper_String
     */
    public function getStringHelper()
    {
        return $this->_stringHelper;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->_config = $config;

        return $this;
    }

    /**
     * @param string|null $key
     *
     * @return array
     */
    public function getConfig($key = null)
    {
        $config = $this->_config;

        if (!is_null($key) && array_key_exists($key, $config)) {
            return $config[$key];
        }

        return $config;
    }

    /**
     * Gets the configured item columns sorted by position.
     *
     * @return mixed
     */
    public function getItemColumns()
    {
        if ($this->hasItemColumns()) {
            return $this->_getData('item_columns');
        }

        $columns = Mage::getStoreConfig(self::XPATH_ITEM_COLUMNS, $this->getStoreId());
        $columns = unserialize($columns);

        $position = array();
        foreach ($columns as $key => $row) {
            $position[$key] = $row['position'];
        }

        array_multisort($position, SORT_ASC, $columns);

        $this->setItemColumns($columns);
        return $columns;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setHelper(Mage::helper('postnl'))
             ->setCoreHelper(Mage::helper('core'))
             ->setStringHelper(Mage::helper('core/string'));
    }

    /**
     * Alias for createPdf().
     *
     * @param array                                  $labels
     * @param TIG_PostNL_Model_Core_Shipment|boolean $postnlShipment
     * @param Zend_Pdf|boolean                       $mainPdf
     *
     * @return bool|Zend_Pdf
     */
    public function getPdf($labels = array(), $postnlShipment = false, $mainPdf = false)
    {
        if (!$postnlShipment || !$mainPdf) {
            return false;
        }

        return $this->createPdf($labels, $postnlShipment, $mainPdf);
    }

    /**
     * Create the full packing slip pdf. This will create an initial Zend_Pdf object with all the address and order info
     * and then merge that with the shipping labels using Fpdi.
     *
     * @param array $labels
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     * @param Zend_Pdf                       $mainPdf
     *
     * @return Zend_Pdf
     */
    public function createPdf($labels, $postnlShipment, &$mainPdf)
    {
        $pdf = $this->_getPackingSlipPdf($postnlShipment);

        $labelModel = Mage::getSingleton('postnl_core/label')
                          ->setLabelSize('A4')
                          ->setOutputMode('S')
                          ->setLabelCounter(0);

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Label $firstLabel
         */
        $labels     = $labelModel->sortLabels($labels);
        $firstLabel = current($labels);

        if (!$firstLabel) {
            return $pdf;
        } elseif (
            !$this->getConfig('show_label')
            || $this->y < 421
            || ($firstLabel->getLabelType() != 'Label'
                && $firstLabel->getLabelType() != 'Label-combi'
                && $firstLabel->getLabelType() != 'BusPakje'
                && $firstLabel->getLabelType() != 'BusPakjeExtra'
            )
        ) {
            foreach($pdf->pages as $page) {
                $mainPdf->pages[] = clone $page;
            }

            $labelsString = $labelModel->createPdf($labels);

            $labelPdf = Zend_Pdf::parse($labelsString);

            foreach ($labelPdf->pages as $page) {
                $mainPdf->pages[] = clone $page;
            }
        } else {
            $packingSlipString = $pdf->render();
            $labelsString = $labelModel->createPackingSlipLabel(array_shift($labels), $packingSlipString);

            $pdf = Zend_Pdf::parse($labelsString);
            foreach($pdf->pages as $page) {
                $mainPdf->pages[] = clone $page;
            }

            if (count($labels) > 0) {
                $labelModel->resetLabelCounter();
                $additionalLabelsString = $labelModel->createPdf($labels);

                $labelPdf = Zend_Pdf::parse($additionalLabelsString);

                foreach ($labelPdf->pages as $page) {
                    $mainPdf->pages[] = clone $page;
                }
            }
        }
        return $mainPdf;
    }

    /**
     * Builds the packing slip part of the final pdf.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return Zend_Pdf
     */
    protected function _getPackingSlipPdf($postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();

        /**
         * Create a dummy invoice for the totals at the bottom of the packing slip.
         */
        $invoice  = Mage::getModel('postnl_core/service')->initInvoice($shipment, true);
        $storeId  = $shipment->getStoreId();

        /**
         * Set the store ID and configuration settings.
         */
        $this->setStoreId($storeId);
        $this->setConfig(Mage::getStoreConfig(self::XPATH_PACKING_SLIP_SETTINGS, $storeId));

        $this->_beforeGetPdf();
        $this->_initRenderer('postnl_packingslip');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);

        $this->y            = self::PAGE_TOP_HEIGHT;
        $this->rightColumnY = self::PAGE_TOP_HEIGHT;

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->emulate($storeId);
            Mage::app()->setCurrentStore($storeId);
        }
        $page  = $this->newPage();
        $order = $shipment->getOrder();

        /*
         * Add upper pdf contents.
         */
        $this->_insertLogo($page)
             ->_insertCompanyInfo($page, $storeId)
             ->_insertCustomerId($page, $order->getCustomerId())
             ->_insertOrderInfo($page, $order, $shipment)
             ->_insertPaymentInfo($page, $order)
             ->_insertOrderAddresses($page, $order)
             ->_insertShipmentInfo($page, $order, $postnlShipment);

        /**
         * Make sure the lowest y coordinate is used to prevent elements from overlapping.
         */
        if ($this->rightColumnY < $this->y) {
            $this->y = $this->rightColumnY;
        }

        /**
         * Add shipment items table.
         */
        $this->_drawItemsHeader($page);

        /**
         * Add shipment items table body.
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
            $this->_drawItem($item, $page, $order);
            $page->drawLine(15, $this->y + 15, 580, $this->y + 15);
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
     *
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
     *
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
     *
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
     *
     * @return $this
     */
    protected function _insertLogo(&$page)
    {
        if (!$this->y) {
            $this->y = self::PAGE_TOP_HEIGHT;
        }

        $image = $this->getConfig('logo');
        if (!$image) {
            return $this;
        }

        $image = Mage::getBaseDir('media')
               . DS
               . 'TIG'
               . DS
               . 'PostNL'
               . DS
               . 'core'
               . DS
               . 'packing_slip_logo'
               . DS
               . $image;

        if (!is_file($image)) {
            return $this;
        }

        $image       = Zend_Pdf_Image::imageWithPath($image);
        $top         = 827;
        $widthLimit  = 200;
        $heightLimit = 34;
        $width       = $image->getPixelWidth();
        $height      = $image->getPixelHeight();

        /**
         * Calculate the image's height and width ratio and resize the image if needed.
         */
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

        /**
         * Draw the image.
         */
        $page->drawImage($image, $x1, $y1, $x2, $y2);

        $this->y = $y1 - 14;

        return $this;
    }

    /**
     * Add company information to the pdf page. Company info is the company's contact address, CoC number and VAT
     * number.
     *
     * @param Zend_Pdf_Page &$page
     * @param null|int      $store
     *
     * @return $this
     */
    protected function _insertCompanyInfo(&$page, $store = null)
    {
        if (!$this->getConfig('show_webshop_info')) {
            return $this;
        }

        if (!$this->y) {
            $this->y = self::PAGE_TOP_HEIGHT;
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 8);
        $page->setLineWidth(0);

        $top     = $this->y;
        $rightTop = $this->y;

        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if (empty($value)) {
                continue;
            }

            $value = preg_replace('/<br[^>]*>/i', "\n", $value);
            $splitString = $this->getStringHelper()->str_split($value, 45, true, true);
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

        if ($this->getConfig('coc_number')) {
            $page->drawText(
                 $this->getHelper()->__('CoC') . ' ' . $this->getConfig('coc_number'),
                 165,
                 $rightTop,
                 'UTF-8'
            );
            $rightTop -= 10;
        }

        if ($this->getConfig('vat_number')) {
            $page->drawText(
                 $this->getHelper()->__('VAT') . ' ' . $this->getConfig('vat_number'),
                 165,
                 $rightTop,
                 'UTF-8'
            );
            $rightTop -= 10;
        }

        $top = ($top > $rightTop) ? $rightTop : $top;

        $this->y = ($this->y > $top) ? $top : $this->y;

        return $this;
    }

    /**
     * Add order info.
     *
     * @param Zend_Pdf_Page                   &$page
     * @param Mage_Sales_Model_Order          $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return $this
     */
    protected function _insertOrderInfo(&$page, $order, $shipment)
    {
        $top = $this->rightColumnY;

        $font = $this->_setFontBold($page, 15);
        $text = $this->getHelper()->__('Packing slip');
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 15);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 20;

        $this->rightColumnY = $top;

        if (!$this->getConfig('show_order_info')) {
            return $this;
        }

        $font = $this->_setFontRegular($page, 8);
        $text = $this->getHelper()->__('Order') . ' # ' . $order->getIncrementId();
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $text = $this->getHelper()->__('Shipment') . ' # ' . $shipment->getIncrementId();
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $text = $this->getHelper()->__('Order date')
              . ': '
              . $this->getCoreHelper()->formatDate($order->getCreatedAtDate(), 'long', false);
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        $this->rightColumnY = $top;

        return $this;
    }

    /**
     * @param Zend_Pdf_Page          &$page
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    protected function _insertPaymentInfo(&$page, $order)
    {
        if (!$this->getConfig('show_payment_method')) {
            return $this;
        }

        $this->rightColumnY -= 24;
        $top = $this->rightColumnY;

        $font = $this->_setFontBold($page, 8);
        $text = $this->getHelper()->__('Payment method');
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        /**
         * Payment info.
         */
        $paymentInfo = Mage::helper('payment')
                           ->getInfoBlock($order->getPayment())
                           ->setIsSecureMode(true)
                           ->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value){
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
                foreach ($this->getStringHelper()->str_split($value, 45, true, true) as $_value) {
                    $text = strip_tags(trim($_value));
                    $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);

                    $page->drawText(strip_tags(trim($_value)), $x, $top, 'UTF-8');
                    $top -= 10;
                }
            }
        }

        $this->rightColumnY = $top;

        return $this;
    }

    /**
     * @param Zend_Pdf_Page                   &$page
     * @param Mage_Sales_Model_Order          $order
     * @param TIG_Postnl_Model_Core_Shipment  $postnlShipment
     *
     * @return $this
     */
    protected function _insertShipmentInfo(&$page, $order, $postnlShipment)
    {
        if (!$this->getConfig('show_shipping_method')) {
            return $this;
        }

        $this->rightColumnY -= 14;
        $top = $this->rightColumnY;

        $font = $this->_setFontBold($page, 8);
        $text = $this->getHelper()->__('Shipping method');
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
        $text = strip_tags(trim($shippingMethod))
              . ' - '
              . $order->formatPriceTxt($order->getShippingAmount() + $order->getShippingTaxAmount());
        $x = 584 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText($text, $x, $top, 'UTF-8');

        $top -= 10;

        $deliveryDate = $postnlShipment->getDeliveryDate();
        $text = $this->getCoreHelper()->formatDate($deliveryDate, 'full', false);
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText($text, $x, $top, 'UTF-8');

        $top -= 24;

        $font = $this->_setFontBold($page, 8);
        $text = $this->getHelper()->__('Ship order on');
        $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
        $page->drawText(
             $text,
             $x,
             $top,
             'UTF-8'
        );

        $top -= 10;

        if ($this->getConfig('show_shipping_date')) {
            $font = $this->_setFontRegular($page, 8);
            $confirmDate = $postnlShipment->getConfirmDate();
            $text = $this->getCoreHelper()->formatDate($confirmDate, 'full', false);
            $x = 580 - $this->widthForStringUsingFontSize($text, $font, 8);
            $page->drawText(
                 $text,
                 $x,
                 $top,
                 'UTF-8'
            );

            $top -= 10;
        }

        $this->rightColumnY = $top - 14;

        return $this;
    }

    /**
     * @param Zend_Pdf_Page &$page
     * @param int|null      $customerId
     *
     * @return $this
     */
    protected function _insertCustomerId(&$page, $customerId)
    {
        if (!$this->getConfig('show_customer_number') || !$customerId) {
            return $this;
        }

        if (!$this->y) {
            $this->y = self::PAGE_TOP_HEIGHT;
        }

        $this->_setFontBold($page, 8);
        $page->setLineWidth(0);
        $top = $this->y - 24;

        $page->drawText(
             $this->getHelper()->__('Customer number'),
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

        return $this;
    }

    /**
     * Add address info to the pdf page. A maximum of 3 addresses will be added: the billing address, shipping address
     * and PakjeGemak address. Each can be enabled and disabled in the extension configuration.
     *
     * @param Zend_Pdf_Page          &$page
     * @param Mage_Sales_Model_Order $obj
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _insertOrderAddresses(&$page, $obj)
    {
        $showBillingAddress    = $this->getConfig('show_billing_address');
        $showShippingAddress   = $this->getConfig('show_shipping_address');
        $showPakjegemakAddress = $this->getConfig('show_pakjegemak_address');

        if (!$showBillingAddress && !$showShippingAddress && !$showPakjegemakAddress) {
            return $this;
        }

        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        } else {
            throw new TIG_PostNL_Exception(
                $this->getHelper()->__('No valid order available for packing slip.').
                'POSTNL-0168'
            );
        }

        if (!$this->y) {
            $this->y = self::PAGE_TOP_HEIGHT;
        }

        $top = $this->y - 14;

        $this->_setFontBold($page, 8);

        $addressX = 15;

        $billingAddress    = false;
        $shippingAddress   = false;
        $pakjeGemakAddress = false;

        if ($showBillingAddress) {
            $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

            $page->drawText($this->getHelper()->__('Billing address'), $addressX, ($top - 15), 'UTF-8');
            $addressX += 150;
        }

        if ($showShippingAddress) {
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));

            $page->drawText($this->getHelper()->__('Shipping address'), $addressX, ($top - 15), 'UTF-8');
            $addressX += 150;
        }

        if ($showPakjegemakAddress) {
            /**
             * @var Mage_Sales_Model_Order_Address $address
             */
            $addressesCollection = $order->getAddressesCollection();
            foreach ($addressesCollection as $address) {
                if ($address->getAddressType() != 'pakje_gemak') {
                    continue;
                }

                $pakjeGemakAddress = $this->_formatAddress($address->format('pdf'));

                $page->drawText($this->getHelper()->__('Post office address'), $addressX, ($top - 15), 'UTF-8');
            }
        }

        $this->_setFontRegular($page, 8);
        $this->y = $top - 25;
        $addressesStartY = $this->y;
        $addressX = 15;

        if ($showBillingAddress && $billingAddress) {
            foreach ($billingAddress as $value){
                if ($value !== '') {
                    foreach ($this->getStringHelper()->str_split($value, 20, true, true) as $part) {
                        $page->drawText(strip_tags(ltrim($part)), $addressX, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }

            $addressX += 150;
        }

        $addressesEndY = $this->y;

        if ($showShippingAddress && $shippingAddress) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    foreach ($this->getStringHelper()->str_split($value, 20, true, true) as $part) {
                        $page->drawText(strip_tags(ltrim($part)), $addressX, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $addressX += 150;
        }

        if ($showPakjegemakAddress && $pakjeGemakAddress) {
            $this->y = $addressesStartY;
            foreach ($pakjeGemakAddress as $value){
                if ($value!=='') {
                    $text = array();
                    foreach ($this->getStringHelper()->str_split($value, 20, true, true) as $part) {
                        $page->drawText(strip_tags(ltrim($part)), $addressX, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;
        }

        $this->y -= 14;

        return $this;
    }

    /**
     * Draw table header for product items.
     *
     * @param  Zend_Pdf_Page &$page
     *
     * @return $this
     */
    protected function _drawItemsHeader(Zend_Pdf_Page &$page)
    {
        if (!$this->y) {
            $this->y = self::PAGE_TOP_HEIGHT;
        }

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

        $columns = $this->getItemColumns();

        $lines = array(
            array()
        );

        $i = 0;
        $feed = 20;
        $previousFeed = 0;
        foreach ($columns as $column) {
            if ($i > 0) {
                $align = 'right';
            } else {
                $align = 'left';
            }

            $feed += $previousFeed;
            $previousFeed = $column['width'];

            $lines[0][] = array(
                'text'      => $this->getHelper()->__($column['title']),
                'feed'      => $feed,
                'font'      => 'bold',
                'align'     => $align,
                'font_size' => 8,
            );

            $i++;
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        return $this;
    }

    /**
     * Draw Item process
     *
     * @param  Varien_Object|Mage_Sales_Model_Order_Shipment_item $item
     * @param  Zend_Pdf_Page                                      $page
     * @param  Mage_Sales_Model_Order                             $order
     * @return Zend_Pdf_Page
     */
    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order)
    {
        $orderItem = $item->getOrderItem();
        $type = $orderItem->getProductType();
        $renderer = $this->_getRenderer($type);

        $this->renderItem($item, $page, $order, $renderer);

        $transportObject = new Varien_Object(array('renderer_type_list' => array()));
        Mage::dispatchEvent(
            'pdf_item_draw_after',
            array(
                'transport_object' => $transportObject,
                'entity_item'      => $item
            )
        );

        foreach ($transportObject->getData('renderer_type_list') as $type) {
            $renderer = $this->_getRenderer($type);
            if ($renderer) {
                $this->renderItem($orderItem, $page, $order, $renderer);
            }
        }

        return $renderer->getPage();
    }

    /**
     * Render item
     *
     * @param Varien_Object $item
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Pdf_Items_Abstract $renderer
     *
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    public function renderItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order, $renderer)
    {
        $renderer->setOrder($order)
                 ->setItem($item)
                 ->setPdf($this)
                 ->setPage($page)
                 ->setItemColumns($this->getItemColumns())
                 ->setRenderedModel($this)
                 ->draw();

        return $this;
    }

    /**
     * Add shipment comment to the page.
     *
     * @param Zend_Pdf_Page                   &$page
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return $this
     */
    protected function _insertShipmentComment(&$page, $shipment)
    {
        if (!$this->getConfig('show_comment')) {
            return $this;
        }

        $commentType = $this->getConfig('comment_type');

        if ($commentType == 'static') {
            $commentText = $this->getStringHelper()->stripTags($this->getConfig('comment_text'));
        } else {
            $commentsCollection = $shipment->getCommentsCollection()
                                           ->addFieldToFilter('is_visible_on_front', array('eq' => 1));

            $commentsCollection->getSelect()
                               ->limit(1);

            /**
             * @var Mage_Sales_Model_Order_Shipment_Comment $comment
             */
            $comment = $commentsCollection->getFirstItem();
            if (!$comment || !$comment->getId()) {
                return $this;
            }

            $commentText = $comment->getComment();
        }

        $top = $this->y;

        $commentTextParts = $this->getStringHelper()->str_split($commentText, 70, true, true);
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

        return $this;
    }

    /**
     * Adds totals to the page.
     *
     * @param Zend_Pdf_Page                  &$page
     * @param Mage_Sales_Model_Order         $order
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return $this
     */
    protected function _insertTotals(&$page, $order, $invoice)
    {
        if (!$this->getConfig('show_totals')) {
            return $this;
        }

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

        return $this;
    }

    /**
     * Draw lines.
     *
     * draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parameter), optional left, right
     * height       int;line spacing (default 10)
     * shift        int;Vertical indentation. In addition to the line spacing
     *
     * @param  Zend_Pdf_Page $page
     * @param  array         $draw
     * @param  array         $pageSettings
     *
     * @throws Mage_Core_Exception
     *
     * @return Zend_Pdf_Page
     */
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(
                    Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.')
                );
            }

            $this->_drawLineBlock($page, $pageSettings, $itemsProp);
        }

        return $page;
    }

    /**
     * Draw a single line.
     *
     * @param Zend_Pdf_Page $page
     * @param array         $pageSettings
     * @param array         $itemsProp
     *
     * @return $this
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _drawLineBlock($page, $pageSettings, $itemsProp)
    {
        $lines  = $itemsProp['lines'];
        $height = 10;

        if (isset($itemsProp['height'])) {
            $height = $itemsProp['height'];
        }

        if (!isset($itemsProp['shift']) || empty($itemsProp['shift'])) {
            $itemsProp['shift'] = $this->_getItemShift($lines, $height);
        }

        if ($this->y - $itemsProp['shift'] < self::PAGE_BOTTOM_HEIGHT) {
            $page = $this->newPage($pageSettings);
        }

        foreach ($lines as $line) {
            $maxHeight = 0;
            foreach ($line as $column) {
                $maxHeight = $this->_drawLineBlockColumn($page, $height, $maxHeight, $pageSettings, $column);
            }
            $this->y -= $maxHeight;
        }

        return $this;
    }

    /**
     * Calculates the shift of an item.
     *
     * @param array $lines
     * @param int   $height
     *
     * @return int
     */
    protected function _getItemShift($lines, $height)
    {
        $shift = 0;
        foreach ($lines as $line) {
            $maxHeight = 0;
            foreach ($line as $column) {
                $lineSpacing = $height;
                if (isset($column['height']) && $column['height']) {
                    $lineSpacing = $column['height'];
                }

                if (isset($column['text']) && !is_array($column['text'])) {
                    $column['text'] = array($column['text']);
                }

                $top = 0;
                $textCount = count($column['text']);
                $top += ($lineSpacing * $textCount);

                if ($top > $maxHeight) {
                    $maxHeight = $top;
                }
            }

            $shift += $maxHeight;
        }

        return $shift;
    }

    /**
     * Draw a column of a line block.
     *
     * @param Zend_Pdf_Page &$page
     * @param int           $height
     * @param int           $maxHeight
     * @param array         $pageSettings
     * @param array         $column
     *
     * @return int
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _drawLineBlockColumn(&$page, $height, $maxHeight, $pageSettings, $column)
    {
        list($font, $fontSize) = $this->_setLineBlockColumnFont($page, $column);

        if (!is_array($column['text'])) {
            $column['text'] = array($column['text']);
        }

        $lineSpacing = $height;
        if (isset($column['height']) && !empty($column['height'])) {
            $lineSpacing = $column['height'];
        }

        $top = 0;
        foreach ($column['text'] as $part) {
            $feed      = $column['feed'];
            $shift     = 0;
            $textAlign = 'left';
            $width     = 0;

            if (array_key_exists('shift', $column)) {
                $shift = $column['shift'];
            }

            if (isset($column['align']) && !empty($column['align'])) {
                $textAlign = $column['align'];
            }

            if (isset($column['width']) && !empty($column['width'])) {
                $width = $column['width'];
            }

            $top += $shift;

            if ($this->y - $lineSpacing - $shift < self::PAGE_BOTTOM_HEIGHT) {
                $page = $this->newPage($pageSettings);
            }

            /**
             * If the text align is not the default 'left', we need to modify the feed parameter to match the
             * text's alignment.
             */
            if ($textAlign == 'right' && $width) {
                $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
            } elseif ($textAlign == 'right') {
                $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
            } elseif ($textAlign == 'center') {
                $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
            }

            $page->drawText($part, $feed, ($this->y - $top), 'UTF-8');
            $top += $lineSpacing;
        }

        if ($top > $maxHeight) {
            $maxHeight = $top;
        }

        return $maxHeight;
    }

    /**
     * Sets the page's font for a given column.
     *
     * @param Zend_Pdf_Page &$page
     * @param array         $column
     *
     * @return array
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _setLineBlockColumnFont(&$page, $column)
    {
        $fontSize = 8;
        if (isset($column['font_size']) && !empty($column['font_size'])) {
            $fontSize = $column['font_size'];
        }

        if (isset($column['font_file']) && !empty($column['font_file'])) {
            $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
            $page->setFont($font, $fontSize);
        } else {
            $fontStyle = 'regular';
            if (isset($column['font']) && !empty($column['font'])) {
                $fontStyle = $column['font'];
            }

            if ($fontStyle == 'bold') {
                $font = $this->_setFontBold($page, $fontSize);
            } elseif ($fontStyle == 'italic') {
                $font = $this->_setFontItalic($page, $fontSize);
            } else {
                $font = $this->_setFontRegular($page, $fontSize);
            }
        }

        $fontData = array($font, $fontSize);
        return $fontData;
    }

    /**
     * Create new page and assign to PDF object.
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $pageSize = Zend_Pdf_Page::SIZE_A4;
        if (isset($column['text']) && !empty($settings['page_size'])) {
            $pageSize = $settings['page_size'];
        }

        $page = $this->_getPdf()->newPage($pageSize);

        $this->_getPdf()->pages[] = $page;
        $this->y = self::PAGE_TOP_HEIGHT;

        return $page;
    }
}