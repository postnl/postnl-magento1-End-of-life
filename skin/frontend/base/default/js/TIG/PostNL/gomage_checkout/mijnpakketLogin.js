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

var MijnpakketLogin = new Class.create();
MijnpakketLogin.prototype = {
    postnlLogin      : null,
    publicId         : null,
    profileAccessUrl : null,

    elementId        : null,
    debug            : null,
    mijnpakketData   : null,

    checkout         : null,
    failureUrl       : null,

    formPopulate     : null,

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

        this.onProfileAccessCreate   = this.fireGetProfileDataRequestStart.bindAsEventListener(this);
        this.onProfileAccess         = this.updateCheckout.bindAsEventListener(this);
        this.onProfileAccessFailure  = this.ajaxFailure.bindAsEventListener(this);
        this.onProfileAccessComplete = this.fireGetProfileDataRequestEnd.bindAsEventListener(this);
    },

    /**
     * @returns {null|Checkout}
     */
    getCheckout : function() {
        return this.checkout;
    },

    /**
     * @param {Checkout} checkout
     *
     * @returns {MijnpakketLogin}
     */
    setCheckout : function(checkout) {
        this.checkout = checkout;
        return this;
    },

    /**
     * @returns {null|{}}
     */
    getMijnpakketData : function() {
        return this.mijnpakketData;
    },

    /**
     * @param {{}} mijnpakketData
     *
     * @returns {MijnpakketLogin}
     */
    setMijnpakketData : function(mijnpakketData) {
        this.mijnpakketData = mijnpakketData;
        return this;
    },

    /**
     * @returns {null|{}}
     */
    getFormPopulate : function() {
        return this.formPopulate;
    },

    /**
     * @param {{}} mijnpakketData
     *
     * @returns {MijnpakketLogin}
     */
    setFormPopulate : function(formPopulate) {
        this.formPopulate = formPopulate;
        return this;
    },

    /**
     * @param {string} mijnpakketDataJson
     *
     * @returns {MijnpakketLogin}
     */
    setMijnpakketDataJson : function(mijnpakketDataJson) {
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


        if (this.debug) {
            console.info('Starting MijnPakket login...');
        }

        this.registerObservers();

        if (this.getMijnpakketData()) {
            if (this.debug) {
                console.info('Saved MijnPakket data found. Replacing login button with refresh button.');
            }
            this.showRefreshButton();

            return this;
        }

        var loginResponse = this.loginResponse;
        var params = {
            elementId  : elementId,
            pId        : this.publicId,
            onResponse : this.loginResponse.bind(this),
            debug      : this.debug
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
                }
                billingSelect.up().up().hide();
            }

            var shippingSelect = $('shipping-address-select');
            if (shippingSelect) {
                var newShippingAddressOption = $$("#shipping-address-select option[value='']")[0];
                if (!newShippingAddressOption.selected) {
                    newShippingAddressOption.selected = true;
                }
                shippingSelect.up().up().hide();
            }

            if (!$('billing_use_for_shipping_yes').checked) {
                $('billing_use_for_shipping_yes').click();
            }
        }.bind(this));

        return this;
    },

    /**
     * Shows a 'refresh' login button that skips the actual login step and goes immediately to the shipping_method checkout
     * step.
     *
     * @returns {MijnpakketLogin}
     */
    showRefreshButton : function() {
        this.hideButtons();

        var button = $('postnl_mijnpakket_login_button_refresh');

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
        this.hideButtons();

        var button = $('postnl_mijnpakket_login_button_disabled');
        button.show();

        return this;
    },

    /**
     * Hides the MijnPakket login buttons.
     *
     * @returns {MijnpakketLogin}
     */
    hideButtons : function() {
        $$('.postnl_mijnpakket_button').each(function() {
            this.hide();
        });

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
        document.fire('postnl:getProfileDataStart');

        if (this.debug) {
            console.info('Getting MijnPakket data.');
        }

        if (this.getCheckout()) {
            this.getCheckout().setLoadWaiting('billing');
        }

        var params = {
            isAjax : true,
            token  : token
        };
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
            console.info(data);
        }

        this.hideTooltip();

        if ($('checkout-step-login')) {
            this.showDummyButton();
        }

        this.updateMijnpakketLoginMessage();
        this.addMijnpakketDataLoadedMessage();
        this.updateAddressForms(data);

        return this;
    },

    /**
     * @returns {MijnpakketLogin}
     */
    updateCheckoutError : function() {
        if (this.getCheckout()) {
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
            console.info('Updating forms...');
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

        if (this.debug) {
            console.info('Calling FormPopulate');
        }
        // Set current data on FormPopulate and populate all forms
        this.getFormPopulate().setData(data);
        this.getFormPopulate().populateAll();

        if (this.debug) {
            console.info('Finished updating forms.');
        }

        if (this.debug) {
            console.info('Reload form');
        }
        this.getCheckout().submit(this.getCheckout().getFormData(), 'get_methods');

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
        this.getFormPopulate().setData(data);
        this.getFormPopulate().populate(type);

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
        messageContainer = $('gcheckout-step-shipping_method');

        if (!messageContainer) {
            return this;
        }

        messageContainer.insert({
            top : dataLoadedMessage
        });

        return this;
    }
};
