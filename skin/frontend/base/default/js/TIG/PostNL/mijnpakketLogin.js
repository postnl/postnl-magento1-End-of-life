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

var MijnpakketLogin = new Class.create();
MijnpakketLogin.prototype = {
    postnlLogin      : null,
    publicId         : null,
    profileAccessUrl : null,
    isOsc            : false,

    elementId        : null,
    debug            : null,
    mijnpakketData   : null,

    checkout         : null,
    billing          : null,
    shipping         : null,
    failureUrl       : null,

    /**
     * @constructor
     *
     * @param {string}  publicId
     * @param {string}  profileAccessUrl
     * @param {boolean} isOsc
     */
    initialize : function(publicId, profileAccessUrl, isOsc) {
        this.postnlLogin      = PostNL.Login;
        this.publicId         = publicId;
        this.profileAccessUrl = profileAccessUrl;
        this.isOsc            = isOsc;

        this.onProfileAccessCreate   = this.fireGetProfileDataRequestStart.bindAsEventListener(this);
        this.onProfileAccess         = this.updateCheckout.bindAsEventListener(this);
        this.onProfileAccessFailure  = this.ajaxFailure.bindAsEventListener(this);
        this.onProfileAccessComplete = this.fireGetProfileDataRequestEnd.bindAsEventListener(this);
    },

    /**
     * @returns {null|Checkout}
     */
    getCheckout : function()
    {
        return this.checkout;
    },

    /**
     * @param {Checkout} checkout
     *
     * @returns {MijnpakketLogin}
     */
    setCheckout : function(checkout)
    {
        this.checkout = checkout;

        return this;
    },

    /**
     * @returns {null|Billing}
     */
    getBilling : function()
    {
        return this.billing;
    },

    /**
     * @param {Billing} billing
     *
     * @returns {MijnpakketLogin}
     */
    setBilling : function(billing)
    {
        this.billing = billing;

        return this;
    },

    /**
     * @returns {null|Shipping}
     */
    getShipping : function()
    {
        return this.shipping;
    },

    /**
     * @param {Shipping} shipping
     * @returns {MijnpakketLogin}
     */
    setShipping : function(shipping)
    {
        this.shipping = shipping;

        return this;
    },

    /**
     * @returns {null|{}}
     */
    getMijnpakketData : function()
    {
        return this.mijnpakketData;
    },

    /**
     * @param {{}} mijnpakketData
     *
     * @returns {MijnpakketLogin}
     */
    setMijnpakketData : function(mijnpakketData)
    {
        this.mijnpakketData = mijnpakketData;

        return this;
    },

    /**
     * @param {string} mijnpakketDataJson
     *
     * @returns {MijnpakketLogin}
     */
    setMijnpakketDataJson : function(mijnpakketDataJson)
    {
        var mijnpakketData = mijnpakketDataJson.evalJSON(true);
        this.setMijnpakketData(mijnpakketData);

        return this;
    },

    /**
     * @param {string} url
     *
     * @returns {MijnpakketLogin}
     */
    setFailureUrl : function(url) {
        this.failureUrl = url;

        return this;
    },

    /**
     * @param {string}  elementId
     * @param {boolean} debug
     *
     * @returns {MijnpakketLogin}
     */
    init : function(elementId, debug) {
        this.elementId = elementId;
        this.debug = debug;

        if (debug) {
            console.info('Starting MijnPakket login...');
        }

        this.registerObservers();

        if (this.getMijnpakketData()) {
            if (debug) {
                console.info('Saved MijnPakket data found. Replacing login button with dummy.');
            }
            this.showDummyButton();

            return this;
        }

        var params = {
            elementId  : elementId,
            pId        : this.publicId,
            onResponse : this.loginResponse.bind(this),
            debug      : debug
        };

        this.postnlLogin.init(params);

        return this;
    },

    /**
     * Register observers.
     *
     * @returns {MijnpakketLogin}
     */
    registerObservers : function() {
        document.observe('postnl:getProfileDataRequestStart', function() {
            $$('#checkout-step-login .button').each(function(button) {
                button.disabled = true;

                if (!button.hasClassName('disabled')) {
                    button.addClassName('disabled');
                }
            });

            $$('#checkout-step-billing .button').each(function(button) {
                button.disabled = true;

                if (!button.hasClassName('disabled')) {
                    button.addClassName('disabled');
                }
            });

            this.getCheckout().setLoadWaiting('login');
        }.bind(this));

        document.observe('postnl:getProfileDataRequestEnd', function() {
            $$('#checkout-step-login .button').each(function(button) {
                button.disabled = false;

                if (button.hasClassName('disabled')) {
                    button.removeClassName('disabled');
                }
            });

            $$('#checkout-step-billing .button').each(function(button) {
                button.disabled = false;

                if (button.hasClassName('disabled')) {
                    button.removeClassName('disabled');
                }
            });

            this.getCheckout().setLoadWaiting(false);
        }.bind(this));

        document.observe('postnl:updateAddressFormsStart', function() {
            var billingSelect = $('billing-address-select');
            if (billingSelect) {
                var newBillingAddressOption = $$("#billing-address-select option[value='']")[0];
                if (!newBillingAddressOption.selected) {
                    newBillingAddressOption.selected = true;
                    billing.newAddress(true);
                }
                billingSelect.up().up().hide();
            }

            var shippingSelect = $('shipping-address-select');
            if (shippingSelect) {
                var newShippingAddressOption = $$("#shipping-address-select option[value='']")[0];
                if (!newShippingAddressOption.selected) {
                    newShippingAddressOption.selected = true;
                    shipping.newAddress(true);
                }
                shippingSelect.up().up().hide();
            }

            $('billing:use_for_shipping_yes').checked = true;
            if (this.isOsc) {
                $('shipping_address').hide();
                $('shipping_address_list').hide();
            }
        }.bind(this));

        return this;
    },

    /**
     * Shows a dummy login button that skips the actual login step and goes immediately to the shipping_method checkout
     * step.
     *
     * @returns {MijnpakketLogin}
     */
    showDummyButton : function() {
        $('postnl_mijnpakket_login').hide();
        $('postnl_mijnpakket_login_btn_disabled').hide();

        var button = $('postnl_mijnpakket_login_btn');

        button.show();
        button.observe('click', this.getProfileData.bind(this, ''));

        return this;
    },

    /**
     * Shows a disabled login button.
     *
     * @returns {MijnpakketLogin}
     */
    showDisabledButton : function() {
        $('postnl_mijnpakket_login').hide();
        $('postnl_mijnpakket_login_btn').hide();

        var button = $('postnl_mijnpakket_login_btn_disabled');

        button.show();

        return this;
    },

    /**
     * Hides the MijnPakket login buttons.
     *
     * @returns {MijnpakketLogin}
     */
    hideButton : function() {
        $('postnl_mijnpakket_login').hide();
        $('postnl_mijnpakket_login_btn').hide();
        $('postnl_mijnpakket_login_btn_disabled').hide();

        return this;
    },

    /**
     * @returns {MijnpakketLogin}
     */
    hideTooltip : function() {
        $('postnl_mijnpakket_tooltip').hide();

        return this;
    },

    /**
     * @param {{}} data
     *
     * @returns {MijnpakketLogin}
     */
    loginResponse : function(data) {
        if (this.debug) {
            console.log(data);
        }

        if (!data.Type || data.Type != 'Transfer' || !data.Token) {
            return this;
        }
        this.getProfileData(data.Token);

        return this;
    },

    /**
     * @param token
     *
     * @returns {MijnpakketLogin}
     */
    getProfileData : function(token) {
        if (this.getCheckout() && this.getCheckout().loadWaiting != false) {
            return this;
        }
        document.fire('postnl:getProfileDataStart');

        if (this.debug) {
            console.info('Getting MijnPakket data.');
        }

        if (!this.isOsc && this.getCheckout()) {
            this.getCheckout().setLoadWaiting('billing');
        } else {
            $('postnl_login_spinner').show();
        }

        var params = {
            isAjax : true,
            token  : token
        };

        if (this.isOsc) {
            params['isOsc'] = true;
        }

        this.getProfileDataRequest = new Ajax.PostnlRequest(this.profileAccessUrl, {
            method     : 'post',
            parameters : params,
            onCreate   : this.onProfileAccessCreate,
            onSuccess  : this.onProfileAccess,
            onFailure  : this.onProfileAccessFailure,
            onComplete : this.onProfileAccessComplete
        });

        return this;
    },

    /**
     * @param {Ajax.Response} response
     *
     * @returns {MijnpakketLogin}
     */
    updateCheckout : function(response) {
        if (response.responseText == 'error'
            || response.responseText == 'not_allowed'
            || response.responseText == 'invalid_data'
        ) {
            if (this.debug) {
                console.error('Invalid response received:', response.responseText);
            }

            this.updateCheckoutError();

            return this;
        }

        document.fire('postnl:getProfileDataSuccess');

        var responseData = response.responseText.evalJSON(true);
        if (!responseData) {
            if (this.debug) {
                console.error('Response data received:', responseData);
            }

            this.updateCheckoutError();

            return this;
        }

        var data = responseData.origData;

        if (this.debug) {
            console.log(data);
        }

        if (!this.isOsc && this.getBilling()) {
            this.getBilling().onSave(response);
        }

        this.hideTooltip();

        if (!this.isOsc && $('checkout-step-login')) {
            this.showDummyButton();
        } else if (this.isOsc) {
            this.hideButton();
        }

        this.updateMijnpakketLoginMessage();
        if (!this.isOsc) {
            this.addMijnpakketDataLoadedMessage();
        }

        this.updateAddressForms(data);

        return this;
    },

    /**
     * @returns {MijnpakketLogin}
     */
    updateCheckoutError : function() {
        if (!this.isOsc && this.getCheckout()) {
            this.getCheckout().setLoadWaiting(false);
        } else {
            $('postnl_login_spinner').hide();
        }

        this.showDisabledButton();
        alert(
            Translator.translate(
                'Unfortunately MijnPakket login is currently not available. Please use a different checkout method.'
            )
        );

        return this;
    },

    ajaxFailure : function(response) {
        if (this.failureUrl) {
            window.location.href = this.failureUrl;
        } else if (this.getCheckout() && this.getCheckout().failureUrl) {
            window.location.href = this.getCheckout().failureUrl;
        }
    },

    fireGetProfileDataRequestStart : function() {
        document.fire('postnl:getProfileDataRequestStart');
    },

    fireGetProfileDataRequestEnd : function() {
        document.fire('postnl:getProfileDataRequestEnd');
    },

    /**
     * Update existing billing and shipping forms so customers can change their address.
     *
     * @param {{}} data
     *
     * @returns {MijnpakketLogin}
     */
    updateAddressForms : function(data) {
        document.fire('postnl:updateAddressFormsStart');

        if (this.debug) {
            console.info('Updating forms.');
        }

        /**
         * If guest checkout is allowed, set it as the chosen checkout method.
         */
        var guestLoginCheckbox = $('login:guest');
        if (guestLoginCheckbox && this.getCheckout()) {
            guestLoginCheckbox.checked = true;
            this.getCheckout().method = 'guest';
            new Ajax.Request(
                this.getCheckout().saveMethodUrl,
                {
                    method: 'post',
                    parameters: {
                        method:'guest'
                    },
                    onFailure: this.getCheckout().ajaxFailure.bind(this)
                }
            );
            Element.hide('register-customer-password');
        }

        this.updateFormData('billing', data);

        /**
         * Sync billing and shipping address forms.
         */
        if (!this.isOsc && this.getShipping()) {
            this.getShipping().syncWithBilling();
        } else {
            this.updateFormData('shipping', data);
        }

        /**
         * Copy PostNL postcode check fields from billing to shipping.
         */
        var virtualStreet1 = $('virtual:billing:street1');
        var virtualStreet2 = $('virtual:billing:street2');
        var virtualStreet3 = $('virtual:billing:street3');
        if (virtualStreet1) {
            $('virtual:shipping:street1').setValue(virtualStreet1.getValue());
        }
        if (virtualStreet2) {
            $('virtual:shipping:street2').setValue(virtualStreet2.getValue());
        }
        if (virtualStreet3) {
            $('virtual:shipping:street3').setValue(virtualStreet3.getValue());
        }

        /**
         * Update region updaters.
         */
        if (window.billingRegionUpdater) {
            billingRegionUpdater.update();
        }

        if (window.shippingRegionUpdater) {
            shippingRegionUpdater.update();
        }

        if (this.debug) {
            console.info('Finished updating forms.');
        }

        document.fire('postnl:updateAddressFormsEnd');

        return this;
    },

    /**
     * Updates form data for either the billing or shipping address form.
     *
     * @param {string} type
     * @param {{}}     data
     *
     * @returns {MijnpakketLogin}
     */
    updateFormData : function (type, data) {
        var field;
        var virtualField;

        /**
         * Copy all data to the billing address form.
         */
        for (var index in data) {
            if (!data.hasOwnProperty(index)) {
                continue;
            }

            /**
             * If the value is an array, loop through the array's contents.
             */
            if (data[index] instanceof Array) {
                var dataArray = data[index];

                for (var n = 0; n < dataArray.length; n++) {
                    field = $(type + ':' + index + (n + 1));
                    virtualField = $('virtual:' + type + ':' + index + (n + 1));

                    if (field) {
                        field.setValue(dataArray[n]);
                    }

                    if (virtualField) {
                        virtualField.setValue(dataArray[n]);
                    }
                }

                continue;
            }

            field = $(type + ':' + index);

            if (field) {
                field.setValue(data[index]);
            }
        }

        var saveInAddressBook = $(type + ':save_in_address_book');
        if(saveInAddressBook) {
            saveInAddressBook.checked = false;
        }

        return this;
    },

    /**
     * Updates the login with MijnPakket message to indicate your address has been loaded.
     *
     * @returns {MijnpakketLogin}
     */
    updateMijnpakketLoginMessage : function() {
        var loginMessage = $$('#mijnpakket_text p')[0];
        if (!loginMessage) {
            return this;
        }

        loginMessage.update(
            Translator.translate(
                'Your preferred address has been loaded from your MijnPakket account and set as your '
                    + 'billing and shipping address. You may now choose a shipping method and complete your order.'
            )
        );

        return this;
    },

    /**
     * Add a success message to the shipping method step.
     *
     * @returns {MijnpakketLogin}
     */
    addMijnpakketDataLoadedMessage : function() {
        var dataLoadedMessage = $('mijnpakket_data_loaded');
        if (dataLoadedMessage) {
            dataLoadedMessage.show();

            return this;
        }

        dataLoadedMessage = new Element('div', {id : 'mijnpakket_data_loaded'});

        var dataLoadedContent = new Element('p');
        dataLoadedContent.update(
            Translator.translate(
                'Your preferred address has been loaded from your MijnPakket account and set as your '
                + 'billing and shipping address. You may now choose a shipping method and complete your order.'
            )
        );

        dataLoadedMessage.insert(dataLoadedContent);

        var messageContainer;
        if (!this.isOsc) {
            messageContainer = $('checkout-step-shipping_method')
        } else {
            messageContainer = $('billing_address_list');
        }

        if (!messageContainer) {
            return this;
        }

        messageContainer.insert({
            top : dataLoadedMessage
        });

        return this;
    }
};
