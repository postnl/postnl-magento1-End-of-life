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
var PostNLCheckout = new Class.create();
PostNLCheckout.prototype = {
    initialize: function(url, successUrl, environment) {
        this.url = url;
        this.createIframe();
        this.overlay = $('postnl-overlay');

        PostNL_OP_Checkout.initCheckout(
            function() {
                var overlay = this.overlay;
                if (overlay) {
                    overlay.show();
                }
                window.location = successUrl;
            },
            function() {
                // Cancel Action
            },
            function() {
                // Login Action
            },
            environment
        );
    },
    createIframe: function() {
        var iframe = document.createElement("div");
        iframe.id = "iframe";
        iframe.setAttribute("style", "z-index:9999999;");
        document.body.appendChild(iframe);
    },
    startCheckout: function(elem) {
        document.body.style.cursor = 'wait';
        this.overlay.show();
        this.prepareOrder();
        return false;
    },
    prepareOrder: function() {
        new Ajax.Request(this.url,{
            method: 'post',
            parameters: null,
            onComplete: this.redirectToCheckout.bind(this)
        });
    },
    redirectToCheckout: function(transport) {
        if (transport.responseText == 'error') {
            alert(Translator.translate('An error occurred. Please use our regular checkout instead.'));
            document.body.style.cursor = 'default';
            this.overlay.hide();

            return false;
        }

        var data = eval('(' + transport.responseText + ')');
        PostNL_OP_Checkout.showCheckout(data[0].orderToken);
        document.body.style.cursor = 'default';
        this.overlay.hide();
        return true;
    }
};