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
 */
class TIG_PostNL_Model_Core_Service_PaymentMethodDummy extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_dummy_payment_method';

    /**
     * @var string
     */
    protected $_code = 'postnl_dummy';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway                   = false;
    /**
     * @var bool
     */
    protected $_canOrder                    = false;
    /**
     * @var bool
     */
    protected $_canAuthorize                = false;
    /**
     * @var bool
     */
    protected $_canCapture                  = false;
    /**
     * @var bool
     */
    protected $_canCapturePartial           = false;
    /**
     * @var bool
     */
    protected $_canCaptureOnce              = false;
    /**
     * @var bool
     */
    protected $_canRefund                   = false;
    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial     = false;
    /**
     * @var bool
     */
    protected $_canVoid                     = false;
    /**
     * @var bool
     */
    protected $_canUseInternal              = false;
    /**
     * @var bool
     */
    protected $_canUseCheckout              = false;
    /**
     * @var bool
     */
    protected $_canUseForMultishipping      = false;
    /**
     * @var bool
     */
    protected $_isInitializeNeeded          = false;
    /**
     * @var bool
     */
    protected $_canFetchTransactionInfo     = false;
    /**
     * @var bool
     */
    protected $_canReviewPayment            = false;
    /**
     * @var bool
     */
    protected $_canCreateBillingAgreement   = false;
    /**
     * @var bool
     */
    protected $_canManageRecurringProfiles  = false;
}