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
PostnlPostcodecheck = new Class.create({
    errorCounter : 0,
    errorMax     : 3,
    timeoutDelay : 3,

    inProgressRequest: false,

    initialize: function(postcodecheckUrl, addressType, countryField, postcodeField, streetnameField, housenumberField,
                         housenumberExtensionField, cityField, virtualPrefix) {
        this.postcodecheckUrl          = postcodecheckUrl;
        this.addressType               = addressType;
        this.countryField              = countryField;
        this.postcodeField             = postcodeField;
        this.streetnameField           = streetnameField;
        this.housenumberField          = housenumberField;
        this.housenumberExtensionField = housenumberExtensionField;
        this.cityField                 = cityField;
        this.virtualPrefix             = virtualPrefix;
    },

    init: function() {
        var addressType               = this.addressType;
        var countryField              = this.countryField;
        var postcodeField             = this.postcodeField;
        var streetnameField           = this.streetnameField;
        var housenumberField          = this.housenumberField;
        var housenumberExtensionField = this.housenumberExtensionField;
        var virtualPrefix             = this.virtualPrefix;

        if (!$(streetnameField).getValue() && $(virtualPrefix + streetnameField).getValue()) {
            $(streetnameField).setValue(
                $(virtualPrefix + streetnameField).getValue()
            );
        } else if ($(streetnameField).getValue() && !$(virtualPrefix + streetnameField).getValue()) {
            $(virtualPrefix + streetnameField).setValue(
                $(streetnameField).getValue()
            );
        }

        if (!$(housenumberField).getValue() && $(virtualPrefix + housenumberField).getValue()) {
            $(housenumberField).setValue(
                $(virtualPrefix + housenumberField).getValue()
            );
        } else if ($(housenumberField).getValue() && !$(virtualPrefix + housenumberField).getValue()) {
            $(virtualPrefix + housenumberField).setValue(
                $(housenumberField).getValue()
            );
        }

        if (!$(housenumberExtensionField).getValue() && $(virtualPrefix + housenumberExtensionField).getValue()) {
            $(housenumberExtensionField).setValue(
                $(virtualPrefix + housenumberExtensionField).getValue()
            );
        } else if ($(housenumberExtensionField).getValue() && !$(virtualPrefix + housenumberExtensionField).getValue()) {
            $(virtualPrefix + housenumberExtensionField).setValue(
                $(housenumberExtensionField).getValue()
            );
        }

        var postcodeCheck = this;

        if($(countryField) === null || $(countryField).hasClassName('country_hidden') == false) {

            $(countryField).observe('change', function() {
                countryId = this.getValue();

                $('postnl_address_error_' + addressType).hide();
                $('postnl_address_missing_' + addressType).hide();
                $('postnl_address_invalid_' + addressType).hide();

                postcodeCheck.changePostcodeCheckDisabledFields(countryId);

                if (countryId == 'NL') {
                    postcode = $(postcodeField).getValue();
                    housenumber = $(virtualPrefix + housenumberField).getValue();

                    postcodeCheck.checkPostcode(postcode, housenumber);
                }
            });
        } else {
            countryField = 'billing:country_id';
        }

        $(postcodeField).observe('change', function() {
            var postcode = this.getValue();
            var housenumber = $(virtualPrefix + housenumberField).getValue();

            postcodeCheck.checkPostcode(postcode, housenumber);
        });

        $(virtualPrefix + streetnameField).observe('change', function() {
            var value = this.getValue();

            $(streetnameField).setValue(value);
        });

        $(virtualPrefix + housenumberField).observe('change', function() {
            var value = this.getValue();

            $(housenumberField).setValue(value);

            var postcode = $(postcodeField).getValue();
            var housenumber = this.getValue();

            postcodeCheck.checkPostcode(postcode, housenumber);
        });

        $(virtualPrefix + housenumberExtensionField).observe('change', function() {
            var value = this.getValue();

            $(housenumberExtensionField).setValue(value);
        });

        var countryId   = $(countryField).getValue();
        var postcode    = $(postcodeField).getValue();
        var housenumber = $(virtualPrefix + housenumberField).getValue();

        this.changePostcodeCheckDisabledFields(countryId);

        document.observe('postnl:updateAddressFormsEnd', function() {
            this.changePostcodeCheckDisabledFields($(countryField).getValue());
        }.bind(this));
    },

    checkPostcode: function(postcode, housenumber) {
        var postcodecheckUrl = this.postcodecheckUrl;
        var addressType = this.addressType;

        if (typeof postcodecheckUrl === 'undefined') {
            return false;
        }

        /**
         * Only the billing and shipping address types are currently supported
         */
        if (addressType && (addressType != 'billing' && addressType != 'shipping')) {
            return false;
        }

        if (!postcode.length || !housenumber.length) {
            return false;
        }

        if ($(this.countryField).getValue() != 'NL') {
            return false;
        }

        var inputValid = true;

        if (!Validation.validate($(this.postcodeField))) {
            inputValid = false;
        }

        if (!Validation.validate($(this.virtualPrefix + this.housenumberField))) {
            inputValid = false;
        }

        if (!inputValid) {
            return false;
        }

        $(this.cityField).readOnly = true;
        $(this.cityField).addClassName('postnl-readonly');
        $(this.virtualPrefix + this.streetnameField).readOnly = true;
        $(this.virtualPrefix + this.streetnameField).addClassName('postnl-readonly');

        $('postnl_address_error_' + addressType).hide();
        $('postnl_address_missing_' + addressType).hide();
        $('postnl_address_invalid_' + addressType).hide();
        $('postnl_postcodecheck_spinner_' + addressType).show();

        var postcodeCheck = this;

        if (this.inProgressRequest) {
            this.inProgressAborted = true;
            this.inProgressRequest.transport.abort();
            this.inProgressRequest = false;
            this.inProgressAborted = false;
        }

        var request = new Ajax.PostnlRequest(postcodecheckUrl,{
            method: 'post',
            parameters: {
                postcode    : postcode,
                housenumber : housenumber,
                isAjax      : true
            },
            onCreate : function() {
                document.fire('postnl:postcodeCheckStart');
            },
            onSuccess: function(response) {
                var spinner = $('postnl_postcodecheck_spinner_' + addressType);

                if (response.responseText == 'error') {
                    $('postnl_address_error_' + addressType).show();
                    postcodeCheck.changePostcodeCheckDisabledFields(false);

                    $(postcodeCheck.virtualPrefix + postcodeCheck.streetnameField).setValue('');
                    $(postcodeCheck.streetnameField).setValue('');
                    $(postcodeCheck.cityField).setValue('');

                    postcodeCheck.inProgressRequest = false;
                    spinner.hide();

                    return;
                }

                if (response.responseText == 'missing_data') {
                    $('postnl_address_missing_' + addressType).show();

                    postcodeCheck.inProgressRequest = false;
                    spinner.hide();

                    return;
                }

                if (response.responseText == 'invalid_data') {
                    postcodeCheck.errorCounter = postcodeCheck.errorCounter + 1;

                    if (postcodeCheck.errorMax && postcodeCheck.errorCounter >= postcodeCheck.errorMax) {
                        $('postnl_address_error_' + addressType).show();
                        postcodeCheck.changePostcodeCheckDisabledFields(false, addressType);
                    } else {
                        $('postnl_address_invalid_' + addressType).show();
                    }

                    $(postcodeCheck.virtualPrefix + postcodeCheck.streetnameField).setValue('');
                    $(postcodeCheck.streetnameField).setValue('');
                    $(postcodeCheck.cityField).setValue('');

                    postcodeCheck.inProgressRequest = false;
                    $('postnl_postcodecheck_spinner_' + addressType).hide();

                    return;
                }

                postcodeCheck.errorCounter = 0;

                var data = eval('(' + response.responseText + ')');

                $(postcodeCheck.virtualPrefix + postcodeCheck.streetnameField).setValue(data['streetname']);
                $(postcodeCheck.streetnameField).setValue(data['streetname']);
                $(postcodeCheck.cityField).setValue(data['city']);

                postcodeCheck.inProgressRequest = false;
                spinner.hide();

                document.fire('postnl:postcodeCheckSuccess');
            },
            onFailure: function() {
                if (postcodeCheck.inProgressAborted) {
                    return;
                }

                $('postnl_address_error_' + addressType).show();
                postcodeCheck.changePostcodeCheckDisabledFields(false);

                $(postcodeCheck.virtualPrefix + postcodeCheck.streetnameField).setValue('');
                $(postcodeCheck.streetnameField).setValue('');
                $(postcodeCheck.cityField).setValue('');

                postcodeCheck.inProgressRequest = false;
                $('postnl_postcodecheck_spinner_' + addressType).hide();
            },
            onTimeout: function() {
                $('postnl_address_error_' + addressType).show();
                postcodeCheck.changePostcodeCheckDisabledFields(false);

                postcodeCheck.inProgressRequest = false;
                $('postnl_postcodecheck_spinner_' + addressType).hide();
            },
            timeoutDelay: postcodeCheck.timeoutDelay
        });
        this.inProgressRequest = request;

        return true;
    },

    changePostcodeCheckDisabledFields: function(countryId) {
        var addressType = this.addressType;

        /**
         * Only the billing and shipping address types are currently supported
         */
        if (addressType && (addressType != 'billing' && addressType != 'shipping')) {
            return;
        }

        var streetLine = $(this.virtualPrefix + this.streetnameField);
        var city = $(this.cityField);

        /**
         * For the Netherlands we need to disable the streetline and city fields.
         */
        if (countryId == 'NL') {
            streetLine.readOnly = true;
            streetLine.addClassName('postnl-readonly');

            city.readOnly = true;
            city.addClassName('postnl-readonly');

            if (!$(this.postcodeField).hasClassName('postnl-validate-postcode')) {
                $(this.postcodeField).addClassName('postnl-validate-postcode')
            }

            if (!$(this.virtualPrefix + this.housenumberField).hasClassName('postnl-validate-housenumber')) {
                $(this.virtualPrefix + this.housenumberField).addClassName('postnl-validate-housenumber')
            }

            return;
        }

        /**
         * For all other countries we need to make sure they're enabled instead.
         */
        streetLine.readOnly = false;
        streetLine.removeClassName('postnl-readonly');

        city.readOnly = false;
        city.removeClassName('postnl-readonly');

        if ($(this.postcodeField).hasClassName('postnl-validate-postcode')) {
            $(this.postcodeField).removeClassName('postnl-validate-postcode')
        }

        if ($(this.virtualPrefix + this.housenumberField).hasClassName('postnl-validate-housenumber')) {
            $(this.virtualPrefix + this.housenumberField).removeClassName('postnl-validate-housenumber')
        }
    }
});
