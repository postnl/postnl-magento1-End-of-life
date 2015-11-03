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

/**
 * Make sure the magento translator is available. If not, create a dummy function.
 */
if (typeof Translator == 'undefined' && typeof Translate === 'function') {
    Translator = new Translate();
} else if (typeof Translator == 'undefined') {
    var Translate = Class.create();
    Translate.prototype = {
        initialize : function() {},

        translate : function(text) {
            return text;
        }
    };

    Translator = new Translate();
}

/**
 * Add the option to trigger HTML events on elements.
 */
if (typeof Element.triggerEvent == 'undefined') {
    Element.prototype.triggerEvent = function (eventName) {
        if (document.createEvent) {
            var evt = document.createEvent('HTMLEvents');
            evt.initEvent(eventName, true, true);

            return this.dispatchEvent(evt);
        }

        if (this.fireEvent)
            return this.fireEvent('on' + eventName);
    };
}

/**
 * Add the 'trim' method to strings for browsers that do not natively support this method.
 */
if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, '');
    }
}

/**
 * Add a 'indexOf' method to arrays.
 */
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(obj, start) {
        for (var i = (start || 0), j = this.length; i < j; i++) {
            if (this[i] === obj) { return i; }
        }
        return -1;
    }
}

/**
 * Add a 'formatMoney' method to numbers.
 */
if (!Number.prototype.formatMoney) {
    Number.prototype.formatMoney = function(c, d, t){
        c = isNaN(c = Math.abs(c)) ? 2 : c;
        d = d == undefined ? "." : d;
        t = t == undefined ? "," : t;
        var n = this,
            s = n < 0 ? "-" : "",
            i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    }
}

/**
 * PostNL delivery options logic class.
 *
 * Uses AJAX to communicate with PostNL and retrieve possible delivery options. This class also manages all available
 * options.
 */
var PostnlDeliveryOptions = Class.create();
PostnlDeliveryOptions.prototype = {
    isActive                 : false,

    options                  : {},
    weekdays                 : [],
    datesProcessed           : [],

    saveUrl                  : null,
    timeframesUrl            : null,
    locationsUrl             : null,
    locationsInAreaUrl       : null,

    postcode                 : null,
    housenumber              : null,
    country                  : null,
    fullAddress              : null,
    deliveryDate             : null,
    imageBaseUrl             : null,

    pgLocation               : false,
    pgeLocation              : false,
    paLocation               : false,

    timeframes               : false,
    locations                : [],
    parsedTimeframes         : false,
    parsedLocations          : false,

    selectedOption           : false,
    selectedType             : false,
    lastSelectedOption       : false,
    lastSelectedType         : false,
    paPhoneCheckPassed       : false,

    deliveryOptionsMap       : false,

    extraCosts               : 0,

    timeframeRequest         : false,
    locationsRequest         : false,
    saveOptionCostsRequest   : false,
    savePaPhoneNumberRequest : false,

    /**
     * Constructor method.
     *
     * @constructor
     *
     * @param {{}}      params
     * @param {{}}      options
     * @param {boolean} debug
     *
     * @returns {void}
     */
    initialize : function(params, options, debug) {
        if (!params.saveUrl
            || !params.timeframesUrl
            || !params.locationsUrl
            || !params.locationsInAreaUrl
            || !params.postcode
            || !params.housenumber
            || !params.country
            || !params.deliveryDate
            || !params.imageBaseUrl
            || !params.fullAddress
        ) {
            throw 'Missing parameters.';
        }

        this.reset();

        this.saveUrl            = params.saveUrl;
        this.timeframesUrl      = params.timeframesUrl;
        this.locationsUrl       = params.locationsUrl;
        this.locationsInAreaUrl = params.locationsInAreaUrl;
        this.postcode           = params.postcode;
        this.housenumber        = params.housenumber;
        this.country            = params.country;
        this.deliveryDate       = params.deliveryDate;
        this.imageBaseUrl       = params.imageBaseUrl;
        this.fullAddress        = params.fullAddress;

        this.options = Object.extend({
            isOsc                     : false,
            oscSaveButton             : 'close_options_popup_btn',
            oscOptionsPopup           : 'postnl_delivery_options',
            disableCufon              : false,
            allowDeliveryDays         : true,
            allowTimeframes           : true,
            allowEveningTimeframes    : false,
            allowPg                   : true,
            allowPge                  : false,
            allowPa                   : true,
            allowSundaySorting        : false,
            isBuspakje                : false,
            taxDisplayType            : 1,
            eveningFeeIncl            : 0,
            eveningFeeExcl            : 0,
            sundayFeeIncl             : 0,
            sundayFeeExcl             : 0,
            expressFeeIncl            : 0,
            expressFeeExcl            : 0,
            eveningFeeText            : '',
            sundayFeeText             : '',
            expressFeeText            : '',
            allowStreetview           : true,
            scrollbarContainer        : 'scrollbar_content',
            scrollbarTrack            : 'scrollbar_track',
            loaderDiv                 : 'initial_loader',
            locationsLoader           : 'locations_loader',
            responsiveLocationsLoader : 'responsive_locations_loader',
            searchField               : 'search_field',
            searchErrorDiv            : 'search_error_message',
            optionsContainer          : 'postnl_delivery_options',
            pgLocationContainer       : 'pglocation',
            pgeLocationContainer      : 'pgelocation',
            paLocationContainer       : 'palocation',
            timeframesContainer       : 'timeframes',
            addPhoneContainer         : 'postnl_add_phonenumber',
            currencySymbol            : '€',
            shippingMethodName        : 's_method_postnl_flatrate',
            postnlShippingMethods     : [
                's_method_postnl_tablerate', 's_method_postnl_flatrate'
            ],
            extraOptions              : {}
        }, options || {});

        this.debug = debug;

        var weekdays = new Array(7);
        weekdays[0] = Translator.translate('Su');
        weekdays[1] = Translator.translate('Mo');
        weekdays[2] = Translator.translate('Tu');
        weekdays[3] = Translator.translate('We');
        weekdays[4] = Translator.translate('Th');
        weekdays[5] = Translator.translate('Fr');
        weekdays[6] = Translator.translate('Sa');

        this.weekdays = weekdays;

        this.registerObservers();
    },

    /******************************
     *                            *
     *  GETTER AND SETTER METHODS *
     *                            *
     ******************************/

    getOptions : function() {
        return this.options;
    },

    getWeekdays : function() {
        return this.weekdays;
    },

    getDatesProcessed : function() {
        return this.datesProcessed;
    },

    getSaveUrl : function() {
        return this.saveUrl;
    },

    getTimeframesUrl : function() {
        return this.timeframesUrl;
    },

    getLocationsUrl : function() {
        return this.locationsUrl;
    },

    getLocationsInAreaUrl : function() {
        return this.locationsInAreaUrl;
    },

    getPostcode : function() {
        return this.postcode;
    },

    getHousenumber : function() {
        return this.housenumber;
    },

    getCountry : function() {
        return this.country;
    },


    getFullAddress : function() {
        return this.fullAddress;
    },

    getDeliveryDate : function() {
        return this.deliveryDate;
    },

    getImageBasUrl : function() {
        return this.imageBaseUrl;
    },

    getPgLocation : function() {
        return this.pgLocation;
    },

    setPgLocation : function(location) {
        this.pgLocation = location;

        return this;
    },

    getPgeLocation : function() {
        return this.pgeLocation;
    },

    setPgeLocation : function(location) {
        this.pgeLocation = location;

        return this;
    },

    getPaLocation : function() {
        return this.paLocation;
    },

    setPaLocation : function(location) {
        this.paLocation = location;

        return this;
    },

    setTimeframes : function(timeframes) {
        this.timeframes = timeframes;

        return this;
    },

    setLocations : function(locations) {
        this.locations = locations;

        return this;
    },

    getParsedLocations : function() {
        return this.parsedLocations;
    },

    setParsedTimeframes : function(parsedTimeframes) {
        this.parsedTimeframes = parsedTimeframes;

        return this;
    },

    getParsedTimeframes : function() {
        return this.parsedTimeframes;
    },

    setParsedLocations : function(parsedLocations) {
        this.parsedLocations = parsedLocations;

        return this;
    },

    getSelectedOption : function() {
        return this.selectedOption;
    },

    setSelectedOption : function(option) {
        var fire = false;
        if (this.getSelectedOption() !== option) {
            fire = true;
        }

        this.selectedOption = option;

        if (fire) {
            document.fire('postnl:selectedOptionChange');
        }

        return this;
    },

    getSelectedType : function() {
        return this.selectedType;
    },

    setSelectedType : function(type) {
        var fire = false;
        if (this.getSelectedType() !== type) {
            fire = true;
        }

        this.selectedType = type;

        if (fire) {
            document.fire('postnl:selectedTypeChange');
        }
        return this;
    },

    getLastSelectedOption : function() {
        return this.lastSelectedOption;
    },

    setLastSelectedOption : function(option) {
        this.lastSelectedOption = option;

        return this;
    },

    getLastSelectedType : function() {
        return this.lastSelectedType;
    },

    setLastSelectedType : function(type) {
        this.lastSelectedType = type;

        return this;
    },

    getPaPhoneCheckPassed : function() {
       return this.paPhoneCheckPassed;
    },

    setPaPhoneCheckPassed : function(passed) {
        this.paPhoneCheckPassed = passed;

        return this;
    },

    getDeliveryOptionsMap : function() {
        return this.deliveryOptionsMap;
    },

    getIsBuspakje : function() {
        return this.options.isBuspakje;
    },

    /*
     * Get the name of an image file for the specified location name.
     *
     * @param {string} name
     *
     * @returns {string}
     */
    getImageName : function(name) {
        var imageName = '';
        switch(name) {
            case 'Albert Heijn':
                imageName = 'albertheijn';
                break;
            case 'Bruna':
                imageName = 'bruna';
                break;
            case 'C1000':
                imageName = 'c1000';
                break;
            case 'Coop':
            case 'CoopCompact':
                imageName = 'coop';
                break;
            case 'PostNL':
                imageName = 'default';
                break;
            case 'Emté supermarkt':
                imageName = 'emte';
                break;
            case 'Jumbo':
                imageName = 'jumbo';
                break;
            case 'Plus':
                imageName = 'plus';
                break;
            case 'Primera':
                imageName = 'primera';
                break;
            case 'The Read Shop':
                imageName = 'readshop';
                break;
            case 'Spar':
                imageName = 'spar';
                break;
            case 'Staples Office Centre':
                imageName = 'staples';
                break;
            case 'Gamma':
                imageName = 'gamma';
                break;
            case 'Karwei':
                imageName = 'karwei';
                break;
            case 'automaat':
                imageName = 'automaat';
                break;
            default:
                imageName = 'default';
                break;
        }

        return imageName;
    },

    /**
     * Reset all parameters to their default values.
     *
     * @returns {PostnlDeliveryOptions}
     */
    reset : function() {
        this.isActive = false;

        if (this.debug) {
            console.info('Resetting delivery options.');
        }

        this.datesProcessed   = [];
        this.pgLocation       = false;
        this.pgeLocation      = false;
        this.paLocation       = false;
        this.timeframes       = false;
        this.locations        = [];
        this.parsedTimeframes = false;
        this.parsedLocations  = false;
        this.selectedOption   = false;

        document.stopObserving('postnl:saveDeliveryOptions');
        document.stopObserving('postnl:domModified');
        document.stopObserving('postnl:selectedTypeChange');

        if (this.getOptions().isOsc && this.getOptions().oscSaveButton && $(this.getOptions().oscSaveButton)) {
            var saveButton = $(this.getOptions().oscSaveButton);
            saveButton.stopObserving('click');
        }

        return this;
    },

    /**
     * Register observers.
     *
     * @returns {PostnlDeliveryOptions}
     */
    registerObservers : function() {
        $$('#checkout-shipping-method-load input[type="radio"]').each(function(element) {
            element.observe('click', function(element) {
                var shippingMethods = this.getOptions().postnlShippingMethods;

                for (var i = 0; i < shippingMethods.length; i++) {
                    if (element.identify() == shippingMethods[i]) {
                        if (!this.getSelectedOption() && this.getParsedTimeframes()) {
                            if (this.getLastSelectedType() == 'Avond' || this.getLastSelectedType() == 'Overdag') {
                                this.selectTimeframe(this.getLastSelectedOption().getElement());
                            } else if (this.getLastSelectedType()) {
                                this.selectLocation(
                                    this.getLastSelectedOption().getElements()[this.getLastSelectedType()]
                                );
                            } else {
                                this.timeframes[0].select();
                            }
                        }
                        return;
                    }
                }

                this.unSelectOptions();
                this.updateShippingPrice();
            }.bind(this, element));
        }.bind(this));

        document.observe('postnl:domModified', this.reinitCufon.bind(this));

        if (this.getOptions().isOsc && this.getOptions().oscSaveButton) {
            var saveButton = $(this.getOptions().oscSaveButton);
            saveButton.observe('click', this.saveOscOptions.bind(this));
        }

        document.observe('postnl:selectedTypeChange', function() {
            var extraOptions = this.options.extraOptions;
            if (!extraOptions) {
                return;
            }

            $H(extraOptions).each(function(option) {
                var params = option.value;
                var selectedType = this.getSelectedType();

                if (params.allowedTypes.indexOf(selectedType) < 0) {
                    params.element.checked = false;
                    params.element.disabled = true;
                } else {
                    params.element.disabled = false;
                }
            }.bind(this));
        }.bind(this));

        return this;
    },

    /**
     * Checks if a specified location type is allowed.
     *
     * @param {string} type
     *
     * @returns {boolean}
     */
    isTypeAllowed : function(type) {
        var isAllowed = false;
        switch (type) {
            case 'PG':
                isAllowed = this.isPgAllowed();
                break;
            case 'PGE':
                isAllowed = this.isPgeAllowed();
                break;
            case 'PA':
                isAllowed = this.isPaAllowed();
                break;
            case 'timeframes':
                isAllowed = this.isTimeframesAllowed();
                break;
            case 'EveningTimeframes':
                isAllowed = this.isEveningTimeframesAllowed();
                break;
        }

        return isAllowed;
    },

    /**
     * Check if PGE locations are allowed.
     *
     * @returns {boolean}
     */
    isPgeAllowed : function() {
        return this.getOptions().allowPge !== false;
    },

    /**
     * Check if PG locations are allowed.
     *
     * @returns {boolean}
     */
    isPgAllowed : function() {
        return this.getOptions().allowPg !== false;
    },

    /**
     * Check if PA locations are allowed.
     *
     * @returns {boolean}
     */
    isPaAllowed : function() {
        return this.getOptions().allowPa !== false;
    },

    /**
     * Check if delivery days are allowed.
     *
     * @returns {boolean}
     */
    isDeliveryDaysAllowed : function() {
        return this.getOptions().allowDeliveryDays !== false;
    },

    /**
     * Check if timeframes are allowed.
     *
     * @returns {boolean}
     */
    isTimeframesAllowed : function() {
        return this.getOptions().allowTimeframes !== false;
    },

    /**
     * Check if evening timeframes are allowed.
     *
     * @returns {boolean}
     */
    isEveningTimeframesAllowed : function() {
        return this.getOptions().allowEveningTimeframes !== false;
    },

    /**
     * Start the delivery options functionality by retrieving possible delivery options from PostNL.
     *
     * @returns {PostnlDeliveryOptions}
     */
    showOptions : function() {
        this.isActive = true;

        if (this.debug) {
            console.info('Delivery options starting...');
        }

        this.deliveryOptionsMap = new PostnlDeliveryOptions.Map(this.getFullAddress(), this, this.debug);

        if (this.isDeliveryDaysAllowed()) {
            this.getTimeframes(this.getPostcode(), this.getHousenumber(), this.getCountry(), this.getDeliveryDate());
        } else {
            if (this.debug) {
                console.info('Showing default timeframe.');
            }
            this.showDefaultTimeframe()
                .setParsedTimeframes(true)
                .hideSpinner();
        }
        this.getLocations(this.getPostcode(), this.getHousenumber(), this.getCountry(), this.getDeliveryDate());

        return this;
    },

    /**
     * Get all possible delivery timeframes for a specified postcode, housenumber and delivery date.
     *
     * @param {string} postcode
     * @param {number} housenumber
     * @param {string} country
     * @param {string} deliveryDate
     *
     * @returns {boolean|Array|PostnlDeliveryOptions}
     */
    getTimeframes : function(postcode, housenumber, country, deliveryDate) {
        if (this.debug) {
            console.info('Getting available timeframes.');
        }

        /**
         * @type {Array|boolean}
         */
        var timeframes = this.timeframes;
        if (timeframes) {
            return timeframes;
        }

        if (this.timeframeRequest !== false) {
            try {
                this.timeframeRequest.transport.abort();
            } catch (e) {
                console.error(e);
            }
        }

        if (!postcode) {
            postcode = this.getPostcode();
        }

        if (!housenumber) {
            housenumber = this.getHousenumber();
        }

        if (!country) {
            country = this.getCountry();
        }

        if (!deliveryDate) {
            deliveryDate = this.getDeliveryDate();
        }

        this.timeframeRequest = new Ajax.PostnlRequest(this.getTimeframesUrl(), {
            method : 'post',
            parameters : {
                postcode     : postcode,
                housenumber  : housenumber,
                deliveryDate : deliveryDate,
                country      : country,
                isAjax       : true
            },
            onSuccess : this.processGetTimeframesSuccess.bind(this),
            onFailure : this.showDefaultTimeframe.bind(this),
            onComplete : function() {
                this.timeframeRequest = false;
            }.bind(this)
        });

        return this;
    },

    /**
     * Process a succesful GetTimeframes request.
     *
     * @param response
     *
     * @returns {boolean|PostnlDeliveryOptions}
     */
    processGetTimeframesSuccess : function(response) {
        /**
         * Check that the response is valid.
         *
         * @todo expand error handling.
         */
        var responseText = response.responseText;
        if (responseText == 'not_allowed'
            || responseText == 'invalid_data'
            || responseText == 'error'
            ) {
            this.showDefaultTimeframe();

            return false;
        }

        /**
         * Eval the resulting JSON in sanitize mode.
         */
        var timeframes = responseText.evalJSON(true);

        var shippingMethodName = this.getOptions().shippingMethodName;
        var checkbox = $(shippingMethodName);
        var selectPostnlShippingMethod = false;

        if (checkbox.checked) {
            selectPostnlShippingMethod = true;
        } else if (this.getOptions().isOsc) {
            checkbox.checked = true;
            selectPostnlShippingMethod = true;
        }

        /**
         * Parse and render the result.
         */
        this.parseTimeframes(timeframes)
            .renderTimeframes(selectPostnlShippingMethod);

        this.setParsedTimeframes(true)
            .hideSpinner();

        return this;
    },

    /**
     * Parse a list of PostNL timeframe objects.
     *
     * @param {Array} timeframes
     *
     * @returns {PostnlDeliveryOptions}
     */
    parseTimeframes : function(timeframes) {
        var parsedTimeframes = [];

        for(var n = 0, o = 0, l = timeframes.length; n < l; n++) {
            if (o >= 1 && this.isDeliveryDaysAllowed() === false) {
                break;
            }

            var currentTimeframe = timeframes[n];

            for (var i = 0, m = currentTimeframe.Timeframes.TimeframeTimeFrame.length; i < m ; i++, o++) {
                var currentSubTimeframe = currentTimeframe.Timeframes.TimeframeTimeFrame[i];
                if (this.isEveningTimeframesAllowed() === false
                    && currentSubTimeframe.Options.string[0] == 'Evening'
                ) {
                    continue;
                }

                var postnlTimeframe = new PostnlDeliveryOptions.Timeframe(
                    currentTimeframe.Date,
                    currentSubTimeframe,
                    o,
                    this
                );

                parsedTimeframes.push(postnlTimeframe);
            }
        }

        this.setTimeframes(parsedTimeframes);

        if (this.debug) {
            console.log('Timeframes parsed:', parsedTimeframes);
        }

        return this;
    },

    /**
     * Render all timeframes.
     *
     * @param {boolean}  selectTimeframe
     *
     * @returns {PostnlDeliveryOptions}
     */
    renderTimeframes : function(selectTimeframe) {
        $$('#' + this.getOptions().timeframesContainer + ' li.option').each(function(element) {
            element.remove();
        });

        this.timeframes.each(function(timeframe) {
            timeframe.render(this.getOptions().timeframesContainer);
        }.bind(this));

        if (selectTimeframe) {
            this.selectTimeframe(this.timeframes[0].getElement());
        }

        if (this.getOptions().isOsc) {
            this.timeframes[0].renderAsOsc();

            if (selectTimeframe) {
                this.saveSelectedOption();
            }
        }

        if (this.debug) {
            console.info('Timeframes rendered.');
        }

        return this;
    },

    showDefaultTimeframe : function() {
        if (this.debug) {
            console.info('Showing default timeframe option.');
        }

        var fakeTimeframe = {
            From          : '09:00:00',
            To            : '18:00:00',
            TimeframeType : 'Overdag',
            Options       : {
                string : []
            }
        };

        var postnlTimeframe = new PostnlDeliveryOptions.Timeframe(this.getDeliveryDate(), fakeTimeframe, 0, this);
        this.setTimeframes(new Array(postnlTimeframe));

        this.renderTimeframes(true);

        this.setParsedTimeframes(true)
            .hideSpinner();

        return this;
    },

    /**
     * Get all possible delivery locations for a specified postcode, housenumber and delivery date.
     *
     * The result may contain up to 20 locations, however we will end up using a maximum of 3.
     *
     * @param {string} postcode
     * @param {int}    housenumber
     * @param {string} country
     * @param {string} deliveryDate
     *
     * @return {PostnlDeliveryOptions}
     */
    getLocations : function(postcode, housenumber, country, deliveryDate) {
        if (this.debug) {
            console.info('Getting available delivery locations.');
        }

        if (this.locationsRequest !== false) {
            try {
                this.locationsRequest.transport.abort();
            } catch (e) {
                console.error(e);
            }
        }

        this.locationsRequest = new Ajax.PostnlRequest(this.getLocationsUrl(),{
            method : 'post',
            parameters : {
                postcode     : postcode,
                housenumber  : housenumber,
                deliveryDate : deliveryDate,
                country      : country,
                isAjax       : true
            },
            onSuccess : this.processGetLocationsSuccess.bind(this),
            onFailure : this.hideLocations.bind(this),
            onComplete : function() {
                this.locationsRequest = false;
            }.bind(this)
        });

        return this;
    },

    /**
     * @param response
     * @returns {*}
     */
    processGetLocationsSuccess : function(response) {
        /**
         * Check that the response is valid.
         *
         * @todo expand error handling.
         */
        var responseText = response.responseText;
        if (responseText == 'not_allowed'
            || responseText == 'invalid_data'
            || responseText == 'error'
        ) {
            this.hideLocations();

            return false;
        }

        /**
         * Eval the resulting JSON in sanitize mode.
         */
        var locations = responseText.evalJSON(true);

        /**
         * Add the location to the map interface as markers.
         */
        if (this.getDeliveryOptionsMap()) {
            this.getDeliveryOptionsMap().addMarkers(locations);
        }

        /**
         * Parse and render the result.
         */
        this.parseLocations(locations);
        try {
            this.renderLocations();
        } catch (e) {
            console.log(ed)
        }

        this.setParsedLocations(true)
            .hideSpinner();

        return this;
    },

    /**
     * Parse PostNL delivery locations. We need to filter out unneeded locations so we only end up with the ones closest
     * to the chosen postcode and housenumber.
     *
     * @param {Array} locations.
     *
     * @return {PostnlDeliveryOptions}
     */
    parseLocations : function(locations) {
        var processedPG = false;
        var processedPGE = false;
        var processedPA = false;
        var processedLocations = [];

        for(var n = 0, l = locations.length; n < l; n++) {
            /**
             * If we already have a PakjeGemak, PakjeGemak Express and parcel dispenser location, we're finished and
             * can ignore the remaining locations.
             */
            if (processedPG && processedPGE && processedPA) {
                break;
            }

            /**
             * Get the type of location. Can be PG, PGE or PA.
             */
            var type = locations[n].DeliveryOptions.string;

            /**
             * Instantiate a new PostnlDeliveryOptions.Location object with this location's parameters.
             */
            var postnlLocation = new PostnlDeliveryOptions.Location(
                locations[n],
                this,
                type
            );
            processedLocations.push(postnlLocation);

            if (
                (this.isPgAllowed() && !processedPG && type.indexOf('PG') != -1)
                && (this.isPgeAllowed() && !processedPGE && type.indexOf('PGE') != -1)
            ) {
                postnlLocation.setTooltipClassName('first');

                /**
                 * Register this location as the chosen PGE location.
                 */
                this.setPgeLocation(postnlLocation);
                this.setPgLocation(false);

                processedPGE = true;
                processedPG  = true;
                continue;
            }

            /**
             * If we can add a PGE location, we don't already have a PGE location and this is a PGE location; add it as the chosen
             * PGE location.
             */
            if (this.isPgeAllowed() && !processedPGE && type.indexOf('PGE') != -1) {
                postnlLocation.setTooltipClassName('first');

                /**
                 * Register this location as the chosen PGE location.
                 */
                this.setPgeLocation(postnlLocation);
                processedPGE     = true;
                continue;
            }

            /**
             * If we can add a PG location, we don't already have a PG location and this is a PG location; add it as the chosen
             * PG location.
             */
            if (this.isPgAllowed() && !processedPG && type.indexOf('PG') != -1) {
                postnlLocation.setTooltipClassName('second');

                /**
                 * Register this location as the chosen PG location.
                 */
                this.setPgLocation(postnlLocation);
                processedPG     = true;
                continue;
            }

            /**
             * If we can add a PA location, we don't already have a PA location and this is a PA location; add it as the chosen
             * PA location.
             *
             * N.B. that a single location can be used as both PG, PGE and PA.
             */
            if (this.isPaAllowed() && !processedPA && type.indexOf('PA') != -1) {
                postnlLocation.setTooltipClassName('third');

                /**
                 * Register this location as the chosen PA location.
                 */
                this.setPaLocation(postnlLocation);
                processedPA     = true;
            }
        }

        this.setLocations(processedLocations);
        if (this.getDeliveryOptionsMap()) {
            this.getDeliveryOptionsMap().addMarkers(locations);
        }

        if (this.debug) {
            console.log('Delivery locations parsed:', processedLocations);
        }

        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions}
     */
    renderLocations : function() {
        var pickUpList = $('postnl_pickup');
        pickUpList.show();
        $('responsive_switch').show();

        $$('#' + this.getOptions().pgeLocationContainer + ' li').each(function(element) {
            element.remove();
        });
        $$('#' + this.getOptions().pgLocationContainer + ' li').each(function(element) {
            element.remove();
        });
        $$('#' + this.getOptions().paLocationContainer + ' li').each(function(element) {
            element.remove();
        });

        if (!this.isPgeAllowed() && !this.isPgAllowed() && !this.isPaAllowed()) {
            this.hideLocations();
            return this;
        }

        if (this.isPgeAllowed() && this.getPgeLocation()) {
            this.getPgeLocation().render(this.getOptions().pgeLocationContainer);
        }

        if (this.isPgAllowed() && this.getPgLocation()) {
            this.getPgLocation().render(this.getOptions().pgLocationContainer);
        }

        if (this.isPaAllowed() && this.getPaLocation()) {
            this.getPaLocation().render(this.getOptions().paLocationContainer);
        }

        if (this.debug) {
            console.info('Delivery locations rendered.');
        }

        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions}
     */
    hideLocations : function() {
        this.setParsedLocations(true)
            .hideSpinner();

        $('postnl_pickup').hide();
        $('responsive_switch').hide();

        if (this.debug) {
            console.info('Delivery locations are hidden.');
        }

        return this;
    },

    /**
     * @param element
     * @returns {*}
     */
    selectTimeframe : function(element) {
        if (!element) {
            return this;
        }

        var timeframes = this.timeframes;

        timeframes.each(function(timeframe) {
            if (element && timeframe.element.identify() == element.identify()) {
                this.setLastSelectedOption(timeframe);
                this.setSelectedOption(timeframe);
                this.setLastSelectedType(timeframe.getType());
                this.setSelectedType(timeframe.getType());

                if (this.debug) {
                    console.log('Timeframe selected:', timeframe);
                }

                document.fire('postnl:selectTimeframe');
                document.fire('postnl:selectDeliveryOption');
                timeframe.select();
            } else {
                timeframe.unSelect();
            }
        }.bind(this));

        this.unSelectLocation();
        this.selectPostnlShippingMethod();

        this.updateShippingPrice();

        return false;
    },

    /**
     * @returns {PostnlDeliveryOptions}
     */
    unSelectTimeframe : function() {
        var timeframes = this.timeframes;
        if (!timeframes) {
            return this;
        }

        timeframes.each(function(timeframe) {
            timeframe.unSelect();
        });

        return this;
    },

    /**
     * @param element
     * @returns {PostnlDeliveryOptions}
     */
    selectLocation : function(element) {
        if (!element) {
            return this;
        }

        var locations = this.locations;

        locations.each(function(location) {
            if (!location.elements) {
                return false;
            }

            var elements = location.elements;
            for(var index in elements) {
                if (!elements.hasOwnProperty(index)) {
                    continue;
                }

                var locationElement = elements[index];
                if (element && locationElement.identify() == element.identify()) {
                    this.setLastSelectedOption(location);
                    this.setSelectedOption(location);
                    this.setLastSelectedType(index);
                    this.setSelectedType(index);
                    location.select(index);

                    var selectedMarker = this.getDeliveryOptionsMap().getSelectedMarker();
                    if (location.getMarker() != selectedMarker) {
                        this.getDeliveryOptionsMap().selectMarker(location.getMarker(), true, true);
                    }

                    document.fire('postnl:selectLocation');
                    document.fire('postnl:selectDeliveryOption');

                    if (this.debug) {
                        console.log('Delivery location selected:', location);
                    }
                } else {
                    location.unSelect(index);
                }
            }

            return true;
        }.bind(this));

        this.unSelectTimeframe();
        this.selectPostnlShippingMethod();

        this.updateShippingPrice();

        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions}
     */
    unSelectLocation : function() {
        var locations = this.locations;

        locations.each(function(location) {
            if (!location.elements) {
                return false;
            }

            var elements = location.elements;
            for(var index in elements) {
                if (!elements.hasOwnProperty(index)) {
                    continue;
                }

                location.unSelect(index);
            }

            return true;
        });

        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions}
     */
    unSelectOptions : function() {
        this.unSelectLocation();
        this.unSelectTimeframe();
        this.setSelectedType(null);
        this.setSelectedOption(null);

        return this;
    },

    /**
     * Hides the initial AJAX spinner and shows the delivery options.
     *
     * @returns {PostnlDeliveryOptions}
     */
    hideSpinner : function() {
        if (!this.getOptions().loaderDiv) {
            return this;
        }

        if (!this.getParsedLocations() || !this.getParsedTimeframes()) {
            return this;
        }

        $(this.getOptions().loaderDiv).hide();
        $(this.getOptions().optionsContainer).show();

        document.fire('postnl:loadingFinished');

        return this;
    },

    /**
     * Select the PostNL shipping method radio button.
     *
     * @returns {PostnlDeliveryOptions}
     */
    selectPostnlShippingMethod : function() {
        var shippingMethodName = this.getOptions().shippingMethodName;
        var checkbox = $(shippingMethodName);

        if (checkbox && !this.getOptions().isOsc) {
            checkbox.checked = true;

            return this;
        }

        return this;
    },

    /**
     * Save the selected option for OneStepCheckout.
     *
     * @returns {boolean}
     */
    saveOscOptions : function() {
        if (!this.getSelectedOption()) {
            return false;
        }
        var selectedType   = this.getSelectedType();

        if (selectedType == 'PA' && !this.getPaPhoneCheckPassed()) {
            this.openAddPhoneWindow();
            return false;
        }

        var selectedOption = this.getSelectedOption();
        var isTimeframe    = true;

        if (selectedType == 'PG' || selectedType == 'PGE' || selectedType == 'PA') {
            isTimeframe = false;
        }

        $$('#postnl_add_moment .option').each(function(element) {
            element.remove();
        });

        var n = 0;
        $$('#postnl_add_moment .location').each(function(element) {
            if (n == 0 && isTimeframe) {
                element.show();
            } else if (n == 0) {
                element.hide();
            } else {
                element.remove();
            }
            n++;
        });

        selectedOption.renderAsOsc(selectedType);

        $(this.getOptions().oscOptionsPopup).hide();

        this.saveSelectedOption();

        var body = $$('body')[0];
        if (body.hasClassName('responsive-noscroll')) {
            body.removeClassName('responsive-noscroll');
        }

        this.getOptions().postnlShippingMethods.each(function(shippingMethod) {
            if ($(shippingMethod)) {
                $(shippingMethod).checked = true;
            }
        });

        $('postnl_delivery_options').hide();

        document.fire('postnl:domModified');

        if (this.debug) {
            console.info('Saved option for OSC.');
        }
        return true;
    },

    /**
     * Saves the selected option.
     *
     * @returns {boolean}
     */
    saveSelectedOption : function() {
        if (!this.isActive) {
            return true;
        }

        if (typeof(this.timeframes[0]) == "undefined") {
            return true;
        }

        if (!this.getSelectedOption()) {
            this.selectTimeframe(this.timeframes[0].getElement());
        }

        if (this.debug) {
            console.info('Saving selected option...');
        }

        var selectedType = this.getSelectedType();

        if (!this.getOptions().isOsc && selectedType == 'PA' && !this.getPaPhoneCheckPassed()) {
            this.openAddPhoneWindow();
            return false;
        }

        var selectedOption = this.getSelectedOption();

        var extraCosts = {
            incl : this.getExtraCosts(true),
            excl : this.getExtraCosts(false)
        };

        var from = selectedOption.from;
        if (selectedType == 'PG' || selectedType == 'PA') {
            from = '15:00:00';
        } else if (selectedType == 'PGE') {
            from = '08:30:00'
        }

        var params = {
            isAjax : true,
            type   : selectedType,
            date   : selectedOption.getDate(),
            from   : from,
            to     : selectedOption.to,
            costs  : Object.toJSON(extraCosts)
        };

        if (selectedType == 'PG' || selectedType == 'PGE' || selectedType == 'PA') {
            var address            = selectedOption.getAddress();
            address['Name']        = selectedOption.getName();
            address['PhoneNumber'] = selectedOption.getPhoneNumber();
            params['address']      = Object.toJSON(address);
        }

        if (selectedType == 'PA') {
            params['number'] = $('add_phone_input').getValue();
        }

        if (this.getOptions().isOsc) {
            params['isOsc'] = true;
        }

        if (this.saveOptionCostsRequest) {
            try {
                this.saveOptionCostsRequest.transport.abort();
            } catch (e) {
                console.error(e);
            }
        }

        this.saveOptionCostsRequest = new Ajax.PostnlRequest(this.getSaveUrl(), {
            method     : 'post',
            parameters : params,
            onCreate   : function() {
                document.fire('postnl:selectOptionSaveStart');
            },
            onSuccess  : function(response) {
                var responseText = response.responseText.trim();
                if (responseText != 'OK') {
                    console.error('Invalid response received: ' + responseText);
                }

                document.fire('postnl:selectOptionSaved');
            }
        });

        return true;
    },

    /**
     * Calculate optional extra costs for currently selected option.
     *
     * @returns {Number}
     */
    getExtraCosts : function(inclTax) {
        var selectedType = this.getSelectedType();
        var extraCosts = 0;

        if (!selectedType) {
            return extraCosts;
        }

        if (inclTax) {
            if (selectedType == 'PGE') {
                extraCosts = this.getOptions().expressFeeIncl;
            } else if (selectedType == 'Avond') {
                extraCosts = this.getOptions().eveningFeeIncl;
            } else if (selectedType == 'Sunday') {
                extraCosts = this.getOptions().sundayFeeIncl;
            }

            if (this.debug) {
                console.log('Extra costs incl. VAT:', extraCosts);
            }

            return parseFloat(extraCosts);
        }

        if (selectedType == 'PGE') {
            extraCosts = this.getOptions().expressFeeExcl;
        } else if (selectedType == 'Avond') {
            extraCosts = this.getOptions().eveningFeeExcl;
        } else if (selectedType == 'Sunday') {
            extraCosts = this.getOptions().sundayFeeExcl;
        }

        if (this.debug) {
            console.log('Extra costs excl. VAT:', extraCosts);
        }

        return parseFloat(extraCosts);
    },

    /**
     * Update the displayed shipping price.
     *
     * @returns {PostnlDeliveryOptions}
     */
    updateShippingPrice : function()
    {
        var taxDisplayType = this.getOptions().taxDisplayType;
        if (taxDisplayType == 1) {
            this.updateShippingPriceExclTax(0);
        } else if (taxDisplayType == 2) {
            this.updateShippingPriceInclTax(0);
        } else {
            this.updateShippingPriceExclTax(0);
            this.updateShippingPriceInclTax(1);
        }

        return this;
    },

    /**
     * @param {int} spanNumber
     *
     * @returns {PostnlDeliveryOptions}
     */
    updateShippingPriceExclTax : function(spanNumber) {
        var shippingMethodLabel = $$('label[for="' + this.getOptions().shippingMethodName + '"]')[0];
        var priceContainerExcl = $$('label[for="' + this.getOptions().shippingMethodName + '"] span.price')[spanNumber];

        if (!priceContainerExcl) {
            return this;
        }

        var extraCostsExcl   = this.getExtraCosts(false);
        var defaultCostsExcl = parseFloat(shippingMethodLabel.readAttribute('data-price'));

        var defaultCurrencyExcl = (defaultCostsExcl).formatMoney(2, ',', '.');
        var currencyExcl        = (extraCostsExcl).formatMoney(2, ',', '.');

        var updateText = this.getOptions().currencySymbol
            + ' '
            + defaultCurrencyExcl;

        if (extraCostsExcl) {
            updateText += ' + '
                + this.getOptions().currencySymbol
                + ' '
                + currencyExcl;
        }

        priceContainerExcl.update(updateText);

        return this;
    },

    /**
     * @param {int} spanNumber
     *
     * @returns {PostnlDeliveryOptions}
     */
    updateShippingPriceInclTax : function(spanNumber) {
        var shippingMethodLabel = $$('label[for="' + this.getOptions().shippingMethodName + '"]')[0];
        var priceContainerIncl = $$('label[for="' + this.getOptions().shippingMethodName + '"] span.price')[spanNumber];

        if (!priceContainerIncl) {
            return this;
        }

        var extraCostsIncl   = this.getExtraCosts(true);
        var defaultCostsIncl = parseFloat(shippingMethodLabel.readAttribute('data-price-incl'));

        var defaultCurrencyIncl = (defaultCostsIncl).formatMoney(2, ',', '.');
        var currencyIncl        = (extraCostsIncl).formatMoney(2, ',', '.');

        var updateText   = this.getOptions().currencySymbol
                         + ' '
                         + defaultCurrencyIncl;

        if (extraCostsIncl) {
            updateText += ' + '
                       + this.getOptions().currencySymbol
                       + ' '
                       + currencyIncl;
        }

        priceContainerIncl.update(updateText);

        return this;
    },

    /**
     * Opens the add phone window for PA delivery options.
     *
     * @returns {PostnlDeliveryOptions}
     */
    openAddPhoneWindow : function() {
        var phoneWindow = $(this.getOptions().addPhoneContainer);
        if (!phoneWindow) {
            return this;
        }

        var body = $$('body')[0];
        if (!body.hasClassName('responsive-noscroll')) {
            body.addClassName('responsive-noscroll');
        }

        phoneWindow.show();
        $('add_phone_input').focus();

        if (typeof validateShippingMethod != 'undefined') {
            validateShippingMethod();
        }
        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions}
     */
    closeAddPhoneWindow : function() {
        var phoneWindow = $(this.getOptions().addPhoneContainer);
        if (!phoneWindow) {
            return this;
        }

        var body = $$('body')[0];
        if (body.hasClassName('responsive-noscroll')) {
            body.removeClassName('responsive-noscroll');
        }

        phoneWindow.hide();
        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions}
     */
    reinitCufon : function() {
        if (this.getOptions().disableCufon) {
            return this;
        }

        if (typeof initCufon != 'function') {
            return this;
        }

        if (this.debug) {
            console.info('Refreshing cufon...');
        }

        initCufon();

        return this;
    }
};

PostnlDeliveryOptions.Map = new Class.create({
    map                           : false,
    scrollbar                     : false,
    autocomplete                  : false,

    deliveryOptions               : false,
    fullAddress                   : '',

    isBeingDragged                : false,
    isInfoWindowOpen              : false,

    markers                       : [],
    locations                     : [],
    selectedMarker                : false,
    searchLocationMarker          : false,

    nearestLocationsRequestObject : false,
    locationsInAreaRequestObject  : false,

    filterEarly                   : false,
    filterEvening                 : false,
    filterPA                      : false,

    /**
     * Constructor method.
     * Creates the google maps object and triggers an initial address search based on the user's chosen shipping
     * address.
     *
     * @constructor
     *
     * @param {string} fullAddress
     * @param {PostnlDeliveryOptions} deliveryOptions
     * @param {boolean} debug
     *
     * @returns {void}
     */
    initialize : function(fullAddress, deliveryOptions, debug) {
        if (typeof google.maps == 'undefined') {
            throw 'Google maps is required.';
        }

        this.deliveryOptions = deliveryOptions;
        this.fullAddress = fullAddress;
        this.debug = debug;

        var mapOptions = this.getMapOptions();

        this.map = new google.maps.Map($('map-div'), mapOptions);

        this.scrollbar = new Control.ScrollBar(
            this.getOptions().scrollbarContainer,
            this.getOptions().scrollbarTrack
        );

        this.searchAndPanToAddress(this.getFullAddress(), true, false);

        /**
         * Add autocomplete functionality to the address search field. Results will be located in the Netherlands and
         * may contain only addresses.
         */
        this.autocomplete = new google.maps.places.Autocomplete(
            $('search_field'),
            {
                componentRestrictions : {
                    country : 'nl'
                },
                types : [
                    'establishment',
                    'geocode'
                ]
            }
        );
        this.autocomplete.bindTo('bounds', this.map);

        this.registerObservers();
    },

    /******************************
     *                            *
     *  GETTER AND SETTER METHODS *
     *                            *
     ******************************/

    getMap : function() {
        return this.map;
    },

    getScrollbar : function() {
        return this.scrollbar;
    },

    getAutoComplete : function() {
        return this.autocomplete;
    },

    getDeliveryOptions : function() {
        return this.deliveryOptions;
    },

    getFullAddress : function() {
        return this.fullAddress;
    },

    getIsBeingDragged : function() {
        return this.isBeingDragged;
    },

    setIsBeingDragged : function(isBeingDragged) {
        this.isBeingDragged = isBeingDragged;

        return this;
    },

    getIsInfoWindowOpen : function() {
        return this.isInfoWindowOpen;
    },

    setIsInfoWindowOpen : function(isInfoWindowOpen) {
        this.isInfoWindowOpen = isInfoWindowOpen;

        return this;
    },

    getMarkers : function() {
        return this.markers;
    },

    setMarkers : function(markers) {
        this.markers = markers;

        return this;
    },

    hasMarkers : function() {
        var markers = this.getMarkers();

        return markers.length > 0;
    },

    getLocations : function() {
        return this.locations;
    },

    setLocations : function(locations) {
        this.locations = locations;

        return this;
    },

    hasLocations : function() {
        var locations = this.getLocations();

        return locations.length > 0;
    },

    getSelectedMarker : function() {
        return this.selectedMarker;
    },

    setSelectedMarker : function(marker) {
        this.selectedMarker = marker;

        return this;
    },

    hasSelectedMarker : function() {
        return this.getSelectedMarker() ? true : false;
    },

    getSearchLocationMarker : function() {
        return this.searchLocationMarker;
    },

    setSearchLocationMarker : function(marker) {
        this.searchLocationMarker = marker;

        return this;
    },

    getNearestLocationsRequestObject : function() {
        return this.nearestLocationsRequestObject;
    },

    setNearestLocationsRequestObject : function(requestObject) {
        this.nearestLocationsRequestObject = requestObject;

        return this;
    },

    getLocationsInAreaRequestObject : function() {
        return this.locationsInAreaRequestObject;
    },

    setLocationsInAreaRequestObject : function(requestObject) {
        this.locationsInAreaRequestObject = requestObject;

        return this;
    },

    getFilterEarly : function() {
        return this.filterEarly;
    },

    setFilterEarly : function(filter) {
        this.filterEarly = filter;

        return this;
    },

    getFilterEvening : function() {
        return this.filterEvening;
    },

    setFilterEvening : function(filter) {
        this.filterEvening = filter;

        return this;
    },

    getFilterPa : function() {
        return this.filterPa;
    },

    setFilterPa : function(filter) {
        this.filterPa = filter;

        return this;
    },

    getOptions : function() {
        return this.getDeliveryOptions().getOptions();
    },

    /**
     * Get the map icon for unselected markers.
     *
     * @param {object} location
     *
     * @returns {{anchor: Ext.lib.Point, url: string}}
     */
    getMapIcon : function(location) {
        var name = location.Name;
        if (!name) {
            name = location.getName();
        }

        if (typeof location.DeliveryOptions != 'undefined'
            && location.DeliveryOptions.string.indexOf('PA') > -1
        ) {
            name = 'automaat';
        } else if (typeof location.type != 'undefined'
            && location.getType().indexOf('PA') > -1
        ) {
            name = 'automaat';
        }

        var imageName = this.getDeliveryOptions().getImageName(name);
        var imageBase = this.getDeliveryOptions().getImageBasUrl();
        var image = imageBase + '/crc_' + imageName + '.png';

        return {
            anchor : new google.maps.Point(13, 27),
            url    : image
        };
    },

    /**
     * Get the icon for selected markers.
     *
     * @param {object} location
     *
     * @returns {{anchor: Ext.lib.Point, url: string}}
     */
    getMapIconSelected : function(location) {
        var name = location.getName();
        if (!name) {
            name = location.Name;
        }

        if (typeof location.DeliveryOptions != 'undefined'
            && location.DeliveryOptions.string.indexOf('PA') > -1
            ) {
            name = 'automaat';
        } else if (typeof location.type != 'undefined'
            && location.getType().indexOf('PA') > -1
            ) {
            name = 'automaat';
        }

        var imageName = this.getDeliveryOptions().getImageName(name);
        var imageBase = this.getDeliveryOptions().getImageBasUrl();
        var image = imageBase + '/drp_' + imageName + '.png';

        return {
            anchor : new google.maps.Point(17, 46),
            url    : image
        };
    },

    getSaveButton : function() {
        return $('location_save');
    },

    /**
     * Get an options object for the map's markers.
     *
     * @param {object} location
     * @param {*} markerLatLng
     * @param {string} markerTitle
     *
     * @returns {object}
     */
    getMarkerOptions : function(location, markerLatLng, markerTitle) {
        return {
            position  : markerLatLng,
            map       : null,
            title     : markerTitle,
            animation : google.maps.Animation.DROP,
            draggable : false,
            clickable : true,
            icon      : this.getMapIcon(location)
        };
    },

    /**
     * Get the shape of a marker.
     *
     * @param {boolean} isPa Whether or not this is for a pakket automaat locations
     *
     * @returns {{coords: number[], type: string}}
     */
    getMarkerShape : function(isPa) {
        var coords = [];
        if (isPa) {
            coords = [
                10, 31, 6, 29, 4, 27, 3, 26, 1, 24, 0, 21, 0, 14, 1, 11, 3, 9, 4, 8, 6, 6, 10, 4, 10, 0, 31, 0, 31, 12,
                27, 12, 27, 21, 26, 24, 24, 26, 23, 27, 21, 29, 17, 31
            ];
        } else {
            coords = [
                10, 27, 6, 25, 4, 23, 3, 22, 1, 20, 0, 17, 0, 10, 1, 7, 3, 5, 4, 4, 6, 2, 10, 0, 17, 0, 21, 2, 23, 4,
                24, 5, 26, 7, 27, 10, 27, 17, 26, 20, 24, 22, 23, 23, 21, 25, 17, 27
            ];
        }

        return {
            coords : coords,
            type   : 'poly'
        };
    },

    /**
     * Get the shape of a selected marker.
     *
     * @param {boolean} isPa Whether or not this is for a pakket automaat locations
     *
     * @returns {{coords: number[], type: string}}
     */
    getSelectedMarkerShape : function(isPa) {
        var coords = [];
        if (isPa) {
            coords = [
                17, 46, 13, 41, 8, 34, 3, 28, 1, 24, 0, 21, 0, 13, 1, 10, 3, 7, 5, 5, 7, 3, 10, 1, 13, 0, 35,0, 35, 21,
                34, 24, 32, 28, 27, 34, 22, 41, 18, 46
            ];
        } else {
            coords = [
                17, 46, 13, 41, 8, 34,3, 28, 1, 24, 0, 21, 0, 13, 1, 10, 3, 7, 5, 5, 7, 3, 10, 1, 13, 0, 22, 0, 25, 1,
                28, 3, 30, 5, 32, 7, 34, 10, 35, 13, 35, 21, 34, 24, 32, 28, 27, 34, 22, 41, 18, 46
            ];
        }

        return {
            coords : coords,
            type   : 'poly'
        };
    },

    /**
     * Get the goolgle maps interface window element.
     *
     * @returns {Element}
     */
    getAddLocationWindow : function() {
        if (this.getDeliveryOptions().getOptions() && this.getDeliveryOptions().getOptions().addLocationWindow) {
            var addLocationWindow = this.getDeliveryOptions().getOptions().addLocationWindow;
            if (typeof addLocationWindow == 'string') {
                return $(addLocationWindow);
            }

            return addLocationWindow;
        }

        return $('postnl_add_location_container');
    },

    /**
     * Gets an option object for the google maps object.
     *
     * @returns {object}
     */
    getMapOptions : function() {
        /**
         * Google map styles.
         * All POI icons are hidden. Road icons (directions, etc.) are also hidden.
         */
        var styles = [
            {
                "featureType" : "poi",
                "elementType" : "labels",
                "stylers"     : [
                    {
                        "visibility": "off"
                    }
                ]
            },
            {
                "featureType" : "road",
                "elementType" : "labels.icon",
                "stylers"     : [
                    {
                        "visibility": "off"
                    }
                ]
            }
        ];

        var zoomControlOptions = {
            style    : google.maps.ZoomControlStyle.SMALL,
            position : google.maps.ControlPosition.LEFT_TOP
        };

        return {
            zoom                   : 14,
            minZoom                : 13,
            maxZoom                : 18,
            center                 : new google.maps.LatLng(52.3702157, 4.895167899999933), //Amsterdam
            mapTypeId              : google.maps.MapTypeId.ROADMAP,
            styles                 : styles,
            draggable              : true,
            panControl             : false,
            mapTypeControl         : false,
            scaleControl           : false,
            overviewMapControl     : false,
            streetViewControl      : this.getOptions().allowStreetview,
            zoomControl            : true,
            zoomControlOptions     : zoomControlOptions,
            disableDoubleClickZoom : false,
            scrollwheel            : true,
            keyboardShortcuts      : false
        };
    },

    /**
     * Register observers for the google maps interface window.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    registerObservers : function () {
        var map = this.getMap();

        /**
         * Register observers for the google maps interface window.
         */
        $('add_location').observe('click', this.openAddLocationWindow.bind(this));
        $('close_popup').observe('click', this.closeAddLocationWindow.bind(this));
        $('postnl_back_link').observe('click', this.closeAddLocationWindow.bind(this));
        $('search_button').observe('click', this.addressSearch.bindAsEventListener(this, 'search_field'));
        $('search_field').observe('keydown', this.addressSearch.bindAsEventListener(this, 'search_field'));
        $('responsive_search_button').observe('click', this.addressSearch.bindAsEventListener(this, 'responsive_search_field'));
        $('responsive_search_field').observe('keydown', this.addressSearch.bindAsEventListener(this, 'responsive_search_field'));
        $('location-details-close').observe('click', this.closeLocationInfoWindow.bind(this));
        this.getSaveButton().observe('click', this.saveLocation.bind(this));
        Event.observe(this.getOptions().scrollbarTrack, 'mouse:wheel', this.scrollbar.boundMouseWheelEvent);

        /**
         * Register filter observers.
         */
        var earlyPickupFilter = $('early-filter');
        earlyPickupFilter.observe('click', function() {
            if (earlyPickupFilter.hasClassName('selected')) {
                this.setFilterEarly(false);
                earlyPickupFilter.removeClassName('selected');
            } else {
                this.setFilterEarly(true);
                earlyPickupFilter.addClassName('selected');
            }

            this.filter();
        }.bind(this));

        var earlyPickupFilterResp = $('early-filter-responsive');
        earlyPickupFilterResp.observe('click', function() {
            if (earlyPickupFilterResp.hasClassName('selected')) {
                this.setFilterEarly(false);
                earlyPickupFilterResp.removeClassName('selected');
            } else {
                this.setFilterEarly(true);
                earlyPickupFilterResp.addClassName('selected');
            }

            this.filter();
        }.bind(this));

        var eveningPickupFilter = $('evening-filter');
        eveningPickupFilter.observe('click', function() {
            if (eveningPickupFilter.hasClassName('selected')) {
                this.setFilterEvening(false);
                eveningPickupFilter.removeClassName('selected');
            } else {
                this.setFilterEvening(true);
                eveningPickupFilter.addClassName('selected');
            }
            this.filter();
        }.bind(this));

        var eveningPickupFilterResp = $('evening-filter-responsive');
        eveningPickupFilterResp.observe('click', function() {
            if (eveningPickupFilterResp.hasClassName('selected')) {
                this.setFilterEvening(false);
                eveningPickupFilterResp.removeClassName('selected');
            } else {
                this.setFilterEvening(true);
                eveningPickupFilterResp.addClassName('selected');
            }
            this.filter();
        }.bind(this));

        var paPickupFilter = $('pa-filter');
        paPickupFilter.observe('click', function() {
            if (paPickupFilter.hasClassName('selected')) {
                this.setFilterPa(false);
                paPickupFilter.removeClassName('selected');
            } else {
                this.setFilterPa(true);
                paPickupFilter.addClassName('selected');
            }
            this.filter();
        }.bind(this));

        var paPickupFilterResp = $('pa-filter-responsive');
        paPickupFilterResp.observe('click', function() {
            if (paPickupFilterResp.hasClassName('selected')) {
                this.setFilterPa(false);
                paPickupFilterResp.removeClassName('selected');
            } else {
                this.setFilterPa(true);
                paPickupFilterResp.addClassName('selected');
            }
            this.filter();
        }.bind(this));

        /**
         * Register observers specific for the google map.
         */
        google.maps.event.addListener(map, 'zoom_changed', function() {
            if (this.getIsInfoWindowOpen()) {
                return;
            }

            if (map.getZoom() < 14) {
                this.getNearestLocations(true);
            } else {
                this.getLocationsWithinBounds();
            }
        }.bind(this));

        google.maps.event.addListener(map, 'dragstart', function() {
            this.setIsBeingDragged(true);
        }.bind(this));

        google.maps.event.addListener(map, 'dragend', function() {
            this.setIsBeingDragged(false);

            if (map.getZoom() < 14) {
                this.getNearestLocations(true);
            } else {
                this.getLocationsWithinBounds();
            }
        }.bind(this));

        google.maps.event.addListener(this.autocomplete, 'place_changed', this.placeSearch.bind(this));

        return this;
    },

    /**
     * Trigger the google maps resize event. This prevents sizing errors when the map has been initialized in a hidden
     * div.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    triggerResize : function() {
        var map = this.getMap();

        /**
         * Make sure the map keeps it's previous center.
         */
        var center = map.getCenter();

        google.maps.event.trigger(map, "resize");

        map.setCenter(center);

        if (!this.hasMarkers()) {
            this.getLocationsWithinBounds();
        }

        return this;
    },

    /**
     * Open the google maps interface window.
     *
     * @param {Event} event
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    openAddLocationWindow : function(event) {
        if (this.debug) {
            console.info('Opening add location window...');
        }

        /**
         * Stop event propagation and the default action from triggering.
         */
        if (event) {
            event.stop();
        }

        var body = $$('body')[0];
        if (!this.getOptions().isOsc) {
            body.addClassName('responsive-noscroll');
        }

        $$('#postnl_delivery_options .responsive-protector')[0].addClassName('responsive-hidden');
        $$('#postnl_delivery_options .responsive-switch-wrapper ul')[0].addClassName('responsive-hidden');
        $('postnl_back_link').show();

        this.getAddLocationWindow().show();

        /**
         * This causes the map to resize according to the now visible window's viewport.
         */
        this.triggerResize();
        this.recalculateScrollbar();

        document.fire('postnl:domModified');

        return this;
    },

    /**
     * Close the google maps interface window.
     *
     * @param {Event} event
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    closeAddLocationWindow : function(event) {
        if (this.debug) {
            console.info('Closing add location window...');
        }

        /**
         * Stop event propagation and the default action from triggering.
         */
        if (event) {
            event.stop();
        }

        var body = $$('body')[0];
        if (!this.getOptions().isOsc) {
            body.removeClassName('responsive-noscroll');
        }

        $$('#postnl_delivery_options .responsive-protector')[0].removeClassName('responsive-hidden');
        $$('#postnl_delivery_options .responsive-switch-wrapper ul')[0].removeClassName('responsive-hidden');
        $('postnl_back_link').hide();

        this.getAddLocationWindow().hide();

        return this;
    },

    /**
     * Search for an address. The address can be any value, but a postcode or street name is recommended.
     *
     * @param {Event} event
     * @param {String} searchField
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    addressSearch : function(event, searchField) {
        /**
         * If this event was triggered by a keypress, we want to ignore any except the return key.
         */
        if (event && event.keyCode && event.keyCode != Event.KEY_RETURN) {
            return this;
        }

        /**
         * If this event was triggered by the return key and a pac-item was selected, ignore it. The google maps
         * place-changed event will handle it instead.
         */
        if (event
            && event.keyCode
            && event.keyCode == Event.KEY_RETURN
            && $$('.pac-item.pac-item-selected').length > 0
        ) {
            return this;
        }

        /**
         * Stop event propagation and the default action from triggering.
         */
        if (event) {
            event.stop();
        }

        var address = $(searchField).getValue();
        if (!address) {
            return this;
        }

        if (this.debug) {
            console.log('Searching for address:', address);
        }

        /**
         * Search for an address, pan the map to the new location and search for locations nearby.
         */
        this.searchAndPanToAddress(address, true, true);

        return this;
    },

    /**
     * Search for a place. The place will contain an address for google's geocode service to search for.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    placeSearch : function() {
        var searchField = $(this.getOptions().searchField);

        /**
         * Get the currently selected place.
         */
        var place = this.getAutoComplete().getPlace();
        var address = place.formatted_address;

        /**
         * Fix for some locations returning 'Netherlands' as the address. Appears to be a bug in google's autocomplete
         * service.
         */
        if (address == 'Netherlands') {
            address = searchField.getValue();
        }

        if (this.debug) {
            console.log('Searching for address:', address);
        }

        /**
         * Search for the place's address, pan the map to the new location and search for locations nearby.
         */
        this.searchAndPanToAddress(address, true, true);

        /**
         * Hack to force the input element to contain the address of the selected place, rather than the name.
         */
        var input = searchField;
        input.blur();
        setTimeout(function() {
            input.setValue(address);
        }, 1);

        return this;
    },

    /**
     * Search for an address and pan to the new location. Can optionally add a marker to the searched address's location
     * and search for new locations nearby.
     *
     * @param {string}  address      The address to search for.
     * @param {boolean} addMarker    Whether to add a marker to the address's position.
     * @param {boolean} getLocations Whether to search for nearby locations.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    searchAndPanToAddress : function(address, addMarker, getLocations) {
        if (!address) {
            return this;
        }

        this.unselectMarker();
        this.disableSaveButton();

        this.geocode(address, this.panMapToAddress.bind(this, addMarker, getLocations), this.showSearchErrorDiv);

        return this;
    },

    /**
     * Geocode an address and then trigger the success- or failurecallback.
     *
     * @param {string} address
     * @param {function} successCallback
     * @param {function} failureCallback
     *
     * @returns {void}
     */
    geocode : function(address, successCallback, failureCallback) {
        var geocoder = new google.maps.Geocoder();
        var country = this.getDeliveryOptions().getCountry();
        geocoder.geocode(
            {
                address                : address,
                bounds                 : this.map.getBounds(),
                componentRestrictions  : {
                    country : country
                }
            },
            function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    successCallback.call(this, results, status);
                } else {
                    failureCallback.call(this, results, status);
                }
            }.bind(this)
        );
    },

    /**
     * Pan the map to a set of geocode results. May optionally add a marker to the selected result and search for
     * locations nearby.
     *
     * @param {boolean} addMarker
     * @param {boolean} getLocations
     * @param {Array} results
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    panMapToAddress : function(addMarker, getLocations, results) {
        /**
         * Hide the search error div.
         */
        this.hideSearchErrorDiv();
        var selectedResult = false;

        var country = this.getDeliveryOptions().getCountry();

        /**
         * Loop through all results and validate each to find a suitable result to use.
         */
        results.each(function(result) {
            if (selectedResult !== false) {
                return false;
            }

            /**
             * These are the results that google returns when it actually can't find the address.
             */
            if (result.formatted_address === 'Nederland'
                || result.formatted_address === '8362 Nederland'
                || result.formatted_address === 'The Netherlands'
            ) {
                return false;
            }

            /**
             * Make sure the result is located in the Netherlands.
             */
            var resultIsDomestic = false;
            var components = result.address_components;
            components.each(function(component) {
                if (selectedResult !== false) {
                    return false;
                }

                if (component.short_name != country) {
                    return false;
                }

                resultIsDomestic = true;
                return true;
            });

            if (!resultIsDomestic) {
                return false;
            }

            selectedResult = result;
            return true;
        });

        /**
         * If no result was validated, show the error div.
         */
        if (selectedResult === false) {
            this.showSearchErrorDiv();

            if (this.debug) {
                console.log('No geocoding result found.');
            }

            return this;
        }

        if (this.debug) {
            console.log('Geocoding result:', selectedResult);
        }

        /**
         * Pan the map and zoom to the location.
         */
        var map = this.map;
        var latlng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
        map.panTo(latlng);
        if (map.getZoom() < 13) {
            map.setZoom(13);
        }

        /**
         * If we need to search for nearby locations, do so. All existing markers will be removed.
         */
        if (getLocations) {
            this.removeMarkers();
            this.getNearestLocations();
        }

        /**
         * We may need to add a special (actually, it's the default google maps marker) marker to the resulting
         * location.
         */
        if (addMarker) {
            var searchLocationMarker;

            /**
             * Remove any existing searchLocationMarker from the map.
             */
            if (this.getSearchLocationMarker()) {
                this.getSearchLocationMarker().setMap(null);
            }

            /**
             * Create a new marker.
             */
            searchLocationMarker = new google.maps.Marker({
                position  : latlng,
                map       : map,
                title     : selectedResult.formatted_address,
                draggable : false,
                zIndex    : 0
            });

            this.setSearchLocationMarker(searchLocationMarker);
        }

        return this;
    },

    /**
     * Get the element containing the search error message.
     *
     * @returns {Element|boolean}
     */
    getSearchErrorDiv : function() {
        if (this.getDeliveryOptions().getOptions().searchErrorDiv) {
            return $(this.getDeliveryOptions().getOptions().searchErrorDiv);
        }

        return false;
    },

    /**
     * Hide the search error message container.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    hideSearchErrorDiv : function() {
        if (this.getSearchErrorDiv()) {
            this.getSearchErrorDiv().hide();
        }

        return this;
    },

    /**
     * Show the search error message.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    showSearchErrorDiv : function() {
        if (this.getSearchErrorDiv()) {
            this.getSearchErrorDiv().show();
        }

        return this;
    },

    /**
     * Search for nearby locations. Search is based on the current center of the map and the provided delivery date. The
     * result will contain up to 20 locations of varying types.
     *
     * @param {boolean} checkBounds
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    getNearestLocations : function(checkBounds) {
        if (this.debug) {
            console.info('Getting nearest locations...');
        }

        var locationsLoader            = $(this.getOptions().locationsLoader);
        var responsiveLocationsLoader = $(this.getOptions().responsiveLocationsLoader);

        if (checkBounds !== true) {
            checkBounds = false;
        }

        /**
         * Get the map's center.
         */
        var map = this.map;
        var center = map.getCenter();

        /**
         * Abort any in-progress requests.
         */
        if (this.getNearestLocationsRequestObject()) {
            try {
                this.getNearestLocationsRequestObject().transport.abort();
            } catch (e) {
                console.error(e);
            }
        }

        if (this.getLocationsInAreaRequestObject()) {
            try {
                this.getLocationsInAreaRequestObject().transport.abort();
            } catch (e) {
                console.error(e);
            }
        }

        var country = this.getDeliveryOptions().getCountry();

        /**
         * Send a new getNearestLocations request.
         */
        var nearestLocationsRequestObject = new Ajax.PostnlRequest(this.getDeliveryOptions().getLocationsUrl(), {
            method : 'post',
            parameters : {
                lat          : center.lat(),
                'long'       : center.lng(),
                country      : country,
                deliveryDate : this.getDeliveryOptions().getDeliveryDate(),
                isAjax       : true
            },
            onCreate : function() {
                locationsLoader.show();
                responsiveLocationsLoader.show();
            },
            onSuccess : function(response) {
                var responseText = response.responseText;
                if (responseText == 'not_allowed'
                    || responseText == 'invalid_data'
                    || responseText == 'error'
                    || responseText == 'no_result'
                ) {
                    return this;
                }

                var locations = responseText.evalJSON(true);

                /**
                 * Add new markers for the locations we found.
                 */
                this.addMarkers(locations, checkBounds);

                return this;
            }.bind(this),
            onFailure : function() {
                return false;
            },
            onComplete : function() {
                this.setNearestLocationsRequestObject(false);
                locationsLoader.hide();
                responsiveLocationsLoader.hide();
            }.bind(this)
        });

        /**
         * Store the request. That way we can abort it if we need to send another request before this one is done.
         */
        this.setNearestLocationsRequestObject(nearestLocationsRequestObject);

        return this;
    },

    /**
     * Search for locations inside the maps' viewport. Results will contain up to 20 locations of varying types.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    getLocationsWithinBounds : function() {
        if (this.debug) {
            console.info('Getting locations within map bounds...');
        }
        var locationsLoader           = $(this.getOptions().locationsLoader);
        var responsiveLocationsLoader = $(this.getOptions().responsiveLocationsLoader);
        var map = this.map;

        /**
         * Get the bounds of the map. These will be a set of NE and SW coordinates.
         */
        var bounds = map.getBounds();
        var northEast = bounds.getNorthEast();
        var southWest = bounds.getSouthWest();

        /**
         * Abort any in-progress requests.
         */
        if (this.getLocationsInAreaRequestObject()) {
            try {
                this.getLocationsInAreaRequestObject().transport.abort();
            } catch (e) {
                console.error(e);
            }
        }

        if (this.getNearestLocationsRequestObject()) {
            try {
                this.getNearestLocationsRequestObject().transport.abort();
            } catch (e) {
                console.error(e);
            }
        }

        var country = this.getDeliveryOptions().getCountry();

        var locationsInAreaRequestObject = new Ajax.PostnlRequest(this.deliveryOptions.getLocationsInAreaUrl(), {
            method : 'post',
            parameters : {
                northEastLat : northEast.lat(),
                northEastLng : northEast.lng(),
                southWestLat : southWest.lat(),
                southWestLng : southWest.lng(),
                country      : country,
                deliveryDate : this.getDeliveryOptions().getDeliveryDate(),
                isAjax       : true
            },
            onCreate : function() {
                locationsLoader.show();
                responsiveLocationsLoader.show();
            },
            onSuccess : function(response) {
                var responseText = response.responseText;
                if (responseText == 'not_allowed'
                    || responseText == 'invalid_data'
                    || responseText == 'error'
                    || responseText == 'no_result'
                ) {
                    return this;
                }

                var locations = responseText.evalJSON(true);

                /**
                 * Add new markers for the locations we found.
                 */
                this.addMarkers(locations);

                return this;
            }.bind(this),
            onFailure : function() {
                return false;
            },
            onComplete : function() {
                this.setLocationsInAreaRequestObject(false);
                locationsLoader.hide();
                responsiveLocationsLoader.hide();
            }.bind(this)
        });

        this.setLocationsInAreaRequestObject(locationsInAreaRequestObject);

        return this;
    },

    /**
     * Add markers for an array of locations.
     *
     * @param {Array} locations
     *
     * @param {boolean|null} filterBounds
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    addMarkers : function(locations, filterBounds) {
        var markers = [];

        /**
         * If we have existing markers, get those as we will be adding (not replacing) markers.
         */
        if (this.hasMarkers()) {
            markers = this.getMarkers();
        }

        var parsedLocations = [];
        var newLocations = [];

        /**
         * If we have existing locations, get those as we will be adding (not replacing) locations.
         */
        if (this.hasLocations()) {
            parsedLocations = this.getLocations();
        }

        /**
         * Loop through each location to add a marker.
         */
        for (var i = 0; i < locations.length; i++) {
            var location = locations[i];

            /**
             * Check that this location's types are allowed. Only if all of the location's types are disallowed is the
             * location skipped.
             */
            var type = location.DeliveryOptions.string;
            var isTypeAllowed = false;
            type.each(function(type) {
                if (this.getDeliveryOptions().isTypeAllowed(type)) {
                    isTypeAllowed = true;
                }
            }.bind(this));

            if (!isTypeAllowed) {
                continue;
            }

            /**
             * Check that this marker doesn't already exist.
             */
            if (this.markerExists(location.LocationCode)) {
                continue;
            }

            /**
             * Get the position of the new marker.
             */
            var markerLatLng = new google.maps.LatLng(location.Latitude, location.Longitude);
            if (filterBounds === true && !this.getMap().getBounds().contains(markerLatLng)) {
                continue;
            }

            /**
             * Format the location's address for the marker's title.
             */
            var markerTitle = location.Name + ', ' + location.Address.Street + ' ' + location.Address.HouseNr;
            if (location.Address.HouseNrExt) {
                markerTitle += ' ' + location.Address.HouseNrExt;
            }

            /**
             * Add the new marker.
             */
            var markerOptions = this.getMarkerOptions(location, markerLatLng, markerTitle);
            var marker = new google.maps.Marker(markerOptions);

            var isPa = false;
            if (type.indexOf('PA') > -1) {
                isPa = true;
            }
            marker.setShape(this.getMarkerShape(isPa));
            marker.setZIndex(markers.length + 1);

            /**
             * Create a new PostNL location object to associate with this marker.
             */
            var parsedLocation = new PostnlDeliveryOptions.Location(
                location,
                this.getDeliveryOptions(),
                type
            );

            /**
             * Attach the marker to the location.
             */
            parsedLocation.setMarker(marker);

            /**
             * Attach the location to the marker.
             */
            marker.locationCode = location.LocationCode;
            marker.location = parsedLocation;

            /**
             * Register some observers for the marker. These will allow the marker to be selected and will change it's
             * icon on hover.
             */
            google.maps.event.addListener(marker, "click", this.markerOnClick.bind(this, marker));
            google.maps.event.addListener(marker, "dblclick", this.markerOnDblClick.bind(this, marker));
            google.maps.event.addListener(marker, "mouseover", this.markerOnMouseOver.bind(this, marker));
            google.maps.event.addListener(marker, "mousedown", this.markerOnMouseDown.bind(this));
            google.maps.event.addListener(marker, "mouseup", this.markerOnMouseUp.bind(this));
            google.maps.event.addListener(marker, "mouseout", this.markerOnMouseOut.bind(this, marker));

            /**
             * Add the marker and the location to the marker and location lists.
             */
            markers.push(marker);
            parsedLocations.push(parsedLocation);
            newLocations.push(parsedLocation);
        }

        if (this.debug) {
            console.log('Processed new locations:', newLocations);
        }

        /**
         * Render the locations.
         */
        this.renderLocations(newLocations);

        this.setLocations(parsedLocations);
        this.setMarkers(markers);

        /**
         * If no marker has been selected, select the first marker.
         */
        if (!this.hasSelectedMarker()) {
            this.selectMarker(markers[0], true, false);
        }

        /**
         * Have the marker's drop sequentially, rather than all at once.
         */
        for (var o = 0, n = 0; n < markers.length; n++) {
            marker = markers[n];
            if (marker.getMap()) {
                continue;
            }

            setTimeout(function(marker) {
                marker.setMap(this.getMap());
            }.bind(this, marker), o * 50);

            o++;
        }

        this.filter();

        return this;
    },

    /**
     * Removes all markers.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    removeMarkers : function() {
        var markers = this.getMarkers();

        /**
         * Remove each marker from the map and unset it.
         */
        markers.each(function(marker) {
            marker.setMap(null);
            marker = null;
        });

        /**
         * Remove all location elements.
         */
        $$('#scrollbar_content li.location').each(function(location) {
            location.remove();
        });

        $$('#responsive_location_list li.location-details').each(function(location) {
            location.remove();
        });

        /**
         * Reset the markers array.
         */
        this.setMarkers([]);

        this.getScrollbar().reset();
        this.recalculateScrollbar();
        return this;
    },

    /**
     * Render location elements.
     *
     * @param {Array} locations
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    renderLocations : function(locations) {
        if (locations.length < 1) {
            return this;
        }

        for (var i = 0; i < locations.length; i++) {
            var location = locations[i];

            /**
             * Only the first request can render it's distance to the searched address. Future requests will no longer
             * be accurate enough to display.
             */
            var renderDistance = true;
            if (this.hasMarkers()) {
                renderDistance = false;
            }

            location.renderAsMapLocation('scrollbar_content', 'responsive_location_list', renderDistance);
        }

        this.recalculateScrollbar();

        document.fire('postnl:domModified');

        return this;
    },

    /**
     * Checks if a marker already exists for a specified location.
     *
     * @param {string} location
     *
     * @returns {boolean}
     */
    markerExists : function(location) {
        var markers = this.getMarkers();

        for (var i = 0; i < markers.length; i++) {
            if (markers[i].locationCode == location) {
                return true;
            }
        }

        return false;
    },

    /**
     * Select a marker.
     *
     * @param {*}       marker   The marker to select.
     * @param {boolean} scrollTo Whether the locations list should scroll to the selected marker's location element.
     * @param {boolean} panTo    Whether the map should pan to the selected marker.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    selectMarker : function(marker, scrollTo, panTo) {
        /**
         * If the marker is already selected, we don't have to do anything.
         */
        if (this.hasSelectedMarker()
            && this.getSelectedMarker().location
            && marker.location
            && this.getSelectedMarker().location.getMapElement().identify()
                == marker.location.getMapElement().identify()
        ) {
            if (panTo) {
                this.panToMarker(marker);
            }

            if (scrollTo) {
                this.scrollToMarker(marker);
            }

            return this;
        }

        if (!marker.location) {
            return this;
        }

        var element = false;
        if (marker.location.getMapElement()) {
            element = marker.location.getMapElement();
        }

        var responsiveElement = false;
        if (marker.location.getResponsiveMapElement()) {
            responsiveElement = marker.location.getResponsiveMapElement();
        }

        var isPa = false;
        if (marker.location.getType().indexOf('PA') > -1) {
            isPa = true;
        }

        /**
         * Update the marker's icon and the marker's location's classname.
         */
        marker.setIcon(this.getMapIconSelected(marker.location));
        marker.setShape(this.getSelectedMarkerShape(isPa));

        if (!marker.oldZIndex) {
            marker.oldZIndex = marker.getZIndex();
        }
        marker.setZIndex(this.getMarkers().length + 1);

        if (element) {
            element.addClassName('selected');
        }

        if (responsiveElement) {
            responsiveElement.select('.radio')[0].addClassName('selected');
        }

        this.unselectMarker();

        /**
         * If required, scroll to the marker's location in the locations list.
         */
        if (scrollTo && element) {
            this.getScrollbar().scrollTo(
                element.offsetTop - $('scrollbar_content').offsetTop - 36, true
            );
        }

        if (this.debug) {
            console.log('Marker selected:', marker);
        }

        /**
         * Set this marker as the selected marker.
         */
        this.setSelectedMarker(marker);

        /**
         * Pan the map to the marker's position if required.
         */
        if (panTo) {
            this.panToMarker(marker);
        }

        this.enableSaveButton();

        return this;
    },

    /**
     * Pans the map to a specified marker's position.
     *
     * @param {*} marker
     * @returns {PostnlDeliveryOptions.Map}
     */
    panToMarker : function(marker) {
        this.getMap().panTo(marker.getPosition());

        var streetView = this.getMap().getStreetView();
        if (streetView.getVisible()) {
            streetView.setPosition(marker.getPosition());
        }

        return this;
    },

    /**
     * @param marker
     * @returns {PostnlDeliveryOptions.Map}
     */
    scrollToMarker : function(marker) {
        element = marker.location.getMapElement();

        if (!element) {
            return this;
        }

        this.getScrollbar().scrollTo(
            element.offsetTop - $('scrollbar_content').offsetTop - 36, true
        );

        return this;
    },

    /**
     * Unselect the currently selected marker.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    unselectMarker : function() {
        if (!this.hasSelectedMarker()) {
            return this;
        }

        var marker = this.getSelectedMarker();
        marker.setIcon(this.getMapIcon(marker.location));

        if (marker.location && marker.location.getMapElement()) {
            marker.location.getMapElement().removeClassName('selected');
        }

        if (marker.location && marker.location.getResponsiveMapElement()) {
            marker.location.getResponsiveMapElement().select('.radio')[0].removeClassName('selected');
        }

        var isPa = false;
        if (typeof location.type != 'undefined'
            && location.getType().indexOf('PA') > -1
        ) {
            isPa = true;
        }
        marker.setShape(this.getMarkerShape(isPa));
        marker.setZIndex(marker.oldZIndex ? marker.oldZIndex : 0);
        marker.oldZIndex = false;

        this.setSelectedMarker(false);

        return this;
    },

    /**
     * @param {*} marker
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    markerOnClick : function(marker) {
        if (this.getIsInfoWindowOpen()) {
            return this;
        }

        this.selectMarker(marker, true, true);

        return this;
    },

    /**
     * @param {*} marker
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    markerOnDblClick : function(marker) {
        if (this.getIsInfoWindowOpen()) {
            return this;
        }

        this.selectMarker(marker, true, true);
        this.saveLocation();

        return this;
    },

    /**
     * Update the marker's icon on mouseover.
     *
     * @param {*} marker
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    markerOnMouseOver : function(marker) {
        /**
         * Don't do anything if the map is currently being dragged.
         */
        if (this.getIsBeingDragged() || this.getIsInfoWindowOpen()) {
            return this;
        }

        /**
         * Only update the marker is it's not the currently selected marker.
         */
        if (!this.getSelectedMarker()
            || this.getSelectedMarker().location.getMapElement().identify()
                != marker.location.getMapElement().identify()
        ) {
            if (!marker.oldZIndex) {
                marker.oldZIndex = marker.getZIndex();
            }
            marker.setZIndex(this.getMarkers().length + 2);
            marker.setIcon(this.getMapIconSelected(marker.location));
            marker.setShape(false); //remove any shape, as the new icon has a different shape. This could cause
                                    //flickering.
        }

        marker.location.getMapElement().setStyle({
            backgroundColor : '#f2f2f2'
        });

        return this;
    },

    markerOnMouseDown : function()
    {
        this.setIsBeingDragged(true);
    },

    markerOnMouseUp : function()
    {
        this.setIsBeingDragged(false);
    },


    /**
     * Update the marker's icon on mouseout.
     *
     * @param {*} marker
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    markerOnMouseOut : function(marker) {
        /**
         * Don't do anything if the map is currently being dragged.
         */
        if (this.getIsBeingDragged() || this.getIsInfoWindowOpen()) {
            return this;
        }

        /**
         * Only update the marker is it's not the currently selected marker.
         */
        if (!this.getSelectedMarker()
            || this.getSelectedMarker().location.getMapElement().identify() != marker.location.getMapElement().identify()
        ) {
            var isPa = false;
            if (marker.location.getType().indexOf('PA') > -1) {
                isPa = true;
            }

            marker.setZIndex(marker.oldZIndex ? marker.oldZIndex : 0);
            marker.setIcon(this.getMapIcon(marker.location));
            marker.setShape(this.getMarkerShape(isPa));
            marker.oldZIndex = false;
        }

        marker.location.getMapElement().writeAttribute('style', '');

        return this;
    },

    /**
     * Save a selected location as a new pickup location.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    saveLocation : function() {
        if (this.debug) {
            console.info('Saving selected location...');
        }
        var deliveryOptions = this.getDeliveryOptions();

        /**
         * Get the selected location.
         */
        var customLocation = this.getSelectedMarker().location;
        if (!customLocation) {
            return this;
        }

        /**
         * Remove any previously saved locations.
         */
        $$('#customlocation li').each(function(element) {
            element.remove();
        });

        /**
         * Remove the previously saved location from the stored locations list.
         */
        var currentSelectedLocationIndex = deliveryOptions.locations.indexOf(
            deliveryOptions.customLocation
        );

        if (currentSelectedLocationIndex > -1) {
            deliveryOptions.locations.splice(currentSelectedLocationIndex, 1);
        }


        if (!this.locationExists(customLocation, true)) {
            /**
             * Set this location as the (new) selected location.
             */
            deliveryOptions.customLocation = customLocation;

            /**
             * Add the location to the stored locations list and render it.
             */
            deliveryOptions.locations.push(customLocation);
            customLocation.render('customlocation');

            /**
             * Select the new element.
             */
            var elements = customLocation.getElements();
            if (elements.PA) {
                deliveryOptions.selectLocation(elements.PA);
            } else if (elements.PGE && this.getFilterEarly()) {
                deliveryOptions.selectLocation(elements.PGE);
            } else {
                deliveryOptions.selectLocation(elements.PG);
            }
        }

        /**
         * Close the google maps interface window.
         */
        this.closeAddLocationWindow();

        document.fire('postnl:domModified');

        return this;
    },

    /**
     * Check if a new custom location already exists.
     *
     * @param {PostnlDeliveryOptions.Location} customLocation
     * @param {boolean}                        select
     *
     * @returns {boolean}
     */
    locationExists : function(customLocation, select) {
        var deliveryOptions = this.getDeliveryOptions();

        /**
         * Check if this location is already available as the default PGE location. If so, select it and close the
         * window.
         */
        if (deliveryOptions.getPgeLocation() &&
            customLocation.getLocationCode() == deliveryOptions.getPgeLocation().getLocationCode()
        ) {
            if (select) {
                deliveryOptions.selectLocation(deliveryOptions.getPgeLocation().getElements().PGE);
            }

            return true;
        }


        /**
         * Check if this location is already available as the default PGE location. If so, select it and close the
         * window.
         */
        if (deliveryOptions.getPgLocation() &&
            customLocation.getLocationCode() == deliveryOptions.getPgLocation().getLocationCode()
        ) {
            if (select) {
                deliveryOptions.selectLocation(deliveryOptions.getPgLocation().getElements().PG);
            }

            return true;
        }

        /**
         * Check if this location is already available as the default PG location. If so, select it and close the
         * window.
         */
        if (deliveryOptions.getPaLocation() &&
            customLocation.getLocationCode() == deliveryOptions.getPaLocation().getLocationCode()
        ) {
            if (select) {
                deliveryOptions.selectLocation(deliveryOptions.getPaLocation().getElements().PA);
            }

            return true;
        }

        return false;
    },

    /**
     * Open the location info window.
     *
     * @param {string} content
     * @param {string} code
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    openLocationInfoWindow : function(content, code) {
        if (this.getMap().getStreetView().getVisible()) {
            return this;
        }

        this.setIsInfoWindowOpen(true);

        var locationInfoWindow = $('location-info-window');
        var map                = this.getMap();
        var mapOptions         = this.getMapOptions();

        mapOptions.draggable              = false;
        mapOptions.scrollwheel            = false;
        mapOptions.zoomControl            = false;
        mapOptions.streetViewControl      = false;
        mapOptions.disableDoubleClickZoom = true;
        mapOptions.center                 = map.getCenter();

        map.setOptions(mapOptions);

        $$('#location-info-window div').each(function(element) {
            element.remove();
        });

        locationInfoWindow.insert({
            top: content
        });

        if (code) {
            locationInfoWindow.setAttribute('data-locationcode', code);
        } else {
            locationInfoWindow.removeAttribute('data-locationcode');
        }

        locationInfoWindow.show();

        document.fire('postnl:domModified');

        return this;
    },

    /**
     * Close the location info window.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    closeLocationInfoWindow : function() {

        var locationInfoWindow = $('location-info-window');
        var map = this.getMap();
        var mapOptions = this.getMapOptions();

        mapOptions.draggable = true;
        mapOptions.center = map.getCenter();

        map.setOptions(mapOptions);

        $$('#location-info-window div').each(function(element) {
            element.remove();
        });

        locationInfoWindow.removeAttribute('data-locationcode');
        locationInfoWindow.hide();

        this.setIsInfoWindowOpen(false);

        return this;
    },

    /**
     * Filter visible markers and locations based on currently applied filters.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    filter : function() {
        var filterEarly       = this.getFilterEarly();
        var filterEvening     = this.getFilterEvening();
        var filterPa          = this.getFilterPa();
        var locations         = this.getLocations();
        var hasVisibleMarkers = false;

        locations.each(function(location) {
            var type = location.getType();
            if (filterEarly) {
                if (type.indexOf('PGE') < 0 && type.indexOf('PA') < 0) {
                    location.getMapElement().hide();
                    location.getResponsiveMapElement().hide();
                    location.getMarker().setVisible(false);

                    return false;
                }
            }

            if (filterEvening) {
                if (!location.getIsEveningLocation()) {
                    location.getMapElement().hide();
                    location.getResponsiveMapElement().hide();
                    location.getMarker().setVisible(false);

                    return false;
                }
            }

            if (filterPa) {
                if (type.indexOf('PA') < 0) {
                    location.getMapElement().hide();
                    location.getResponsiveMapElement().hide();
                    location.getMarker().setVisible(false);

                    return false;
                }
            }

            location.getMapElement().show();
            location.getResponsiveMapElement().show();
            location.getMarker().setVisible(true);

            hasVisibleMarkers = true;

            return true;
        }.bind(this));

        if (this.hasSelectedMarker()) {
            var selectedMarker = this.getSelectedMarker();
            if (!selectedMarker.getVisible()) {
                this.unselectMarker();
            }
        }

        if (hasVisibleMarkers === true) {
            $('no_locations_error').hide();
            $('no_locations_error_responsive').hide();
            if (this.hasSelectedMarker()) {
                this.enableSaveButton();
            } else {
                this.disableSaveButton();
            }
        } else {
            $('no_locations_error').show();
            $('no_locations_error_responsive').show();
            this.disableSaveButton();
        }

        this.recalculateScrollbar();

        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions.Map}
     */
    disableSaveButton : function() {
        if (this.getSaveButton().disabled) {
            return this;
        }

        this.getSaveButton().disabled = true;
        if (!this.getSaveButton().hasClassName('disabled')) {
            this.getSaveButton().addClassName('disabled');
        }

        document.fire('postnl:domModified');

        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions.Map}
     */
    enableSaveButton : function() {
        if (!this.getSaveButton().disabled) {
            return this;
        }

        this.getSaveButton().disabled = false;
        if (this.getSaveButton().hasClassName('disabled')) {
            this.getSaveButton().removeClassName('disabled');
        }

        document.fire('postnl:domModified');

        return this;
    },

    /**
     * Recalculate the scrollbar after the scrollbar contents were changed and make sure the scrollbar stays in the
     * same position.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    recalculateScrollbar : function() {
        var scrollbar = this.getScrollbar();
        var scrollbarOffset = scrollbar.slider.value;

        scrollbar.recalculateLayout();
        scrollbar.scrollTo(scrollbarOffset * scrollbar.getCurrentMaximumDelta());

        return this;
    }
});

/**
 * A PostNL PakjeGemak, PakjeGemak Express or parcel dispenser location. Contains address information, opening hours,
 * the type of location and any html elements associated to this location.
 */
PostnlDeliveryOptions.Location = new Class.create({
    elements             : [],
    tooltipElement       : null,
    mapElement           : null,
    responsiveMapElement : null,
    tooltipClassName     : null,

    address              : {},
    distance             : null,
    latitude             : null,
    longitude            : null,
    name                 : null,
    phoneNumber          : null,
    openingHours         : null,
    locationCode         : null,
    date                 : null,

    deliveryOptions      : null,
    type                 : [],
    isEveningLocation    : false,

    marker               : false,

    oldCenter            : false,

    /**
     * Constructor method.
     *
     * @constructor
     *
     * @param {object}                location        The PostNL location JSON object returned by PostNL's webservices
     *                                                associated with this location.
     * @param {PostnlDeliveryOptions} deliveryOptions The current deliveryOptions object with which this location is
     *                                                associated.
     * @param {Array}                 type            An array of PostNL location types. possible options include PE,
     *                                                PGE and PA.
     *
     * @returns {void}
     */
    initialize : function(location, deliveryOptions, type) {
        this.address           = location.Address;
        this.distance          = location.Distance;
        this.latitude          = location.Latitude;
        this.longitude         = location.Longitude;
        this.name              = location.Name;
        this.phoneNumber       = location.PhoneNumber;
        this.openingHours      = location.OpeningHours;
        this.locationCode      = location.LocationCode.replace(/\s+/g, ''); //remove whitespace from the location code
        this.date              = deliveryOptions.getDeliveryDate();
        this.isEveningLocation = location.isEvening;

        this.deliveryOptions   = deliveryOptions;

        this.type = type;
    },

    /******************************
     *                            *
     *  GETTER AND SETTER METHODS *
     *                            *
     ******************************/

    getElements : function() {
        return this.elements;
    },

    setElements : function(elements) {
        this.elements = elements;

        return this;
    },

    setTooltipElement : function(element) {
        this.tooltipElement = element;

        return this;
    },

    getMapElement : function() {
        return this.mapElement;
    },

    setMapElement : function(mapElement) {
        this.mapElement = mapElement;

        return this;
    },

    getResponsiveMapElement : function() {
        return this.responsiveMapElement;
    },

    setResponsiveMapElement : function(mapElement) {
        this.responsiveMapElement = mapElement;

        return this;
    },

    getTooltipClassName : function() {
        var className = this.tooltipClassName;

        if (className) {
            return className;
        }

        if ($$('.tooltip.first').length < 1) {
            this.setTooltipClassName('first');
            return 'first';
        }

        if ($$('.tooltip.second').length < 1) {
            this.setTooltipClassName('first');
            return 'second';
        }

        if ($$('.tooltip.third').length < 1) {
            this.setTooltipClassName('first');
            return 'third';
        }

        return 'fourth';
    },

    setTooltipClassName : function(className) {
        this.tooltipClassName = className;

        return this;
    },

    getAddress : function() {
        return this.address;
    },

    getDistance : function() {
        return this.distance;
    },

    getName : function() {
        return this.name;
    },

    getPhoneNumber : function() {
        return this.phoneNumber;
    },

    getOpeningHours : function() {
        return this.openingHours;
    },

    getLocationCode : function() {
        return this.locationCode;
    },

    getDate : function() {
        return this.date;
    },

    setDate : function(date) {
        this.date = date;

        return this;
    },

    getDeliveryOptions : function() {
        return this.deliveryOptions;
    },

    getType : function() {
        return this.type;
    },

    getOptions : function() {
        return this.getDeliveryOptions().getOptions();
    },

    getMarker : function() {
        if (this.marker) {
            return this.marker;
        }

        var markers = this.getMap().getMarkers();
        for (var i = 0; i < markers.length; i++) {
            var marker = markers[i];
            if (marker.locationCode == this.getLocationCode()) {
                this.setMarker(marker);
                return marker;
            }
        }

        return false;
    },

    setMarker : function(marker) {
        this.marker = marker;

        return this;
    },

    getOldCenter : function() {
        return this.oldCenter;
    },

    setOldCenter : function(oldCenter) {
        this.oldCenter = oldCenter;

        return this;
    },

    getIsEveningLocation : function() {
        return this.isEveningLocation;
    },

    getMap : function() {
        return this.getDeliveryOptions().getDeliveryOptionsMap();
    },

    /**
     * Render the location and attach it to the supplied parent element.
     *
     * @param {string|boolean} parent       The parent element's ID.
     * @param {string|null}    typeToRender
     * @param {boolean|null}   noTooltip
     *
     * @return {PostnlDeliveryOptions.Location|string}
     */
    render : function(parent, typeToRender, noTooltip) {
        var elements = {};
        var element;
        var deliveryDate = this.getDate();
        var date = new Date(
            deliveryDate.substring(6, 10),
            deliveryDate.substring(3, 5) - 1,
            deliveryDate.substring(0, 2)
        );
        var availableDeliveryDate = this.getDeliveryDate(date);
        var paClassName = '';

        if (this.getType().indexOf('PA') > -1) {
            paClassName = 'pa-location';
        }

        this.counter = 0;

        /**
         * Get the html for this location's header.
         */
        var headerHtml = '';
        headerHtml += '<li id="location_header_' + this.getLocationCode() + '" class="location ' + paClassName + '">';
        headerHtml += '<div class="bkg">';
        headerHtml += '<div class="bkg">';
        headerHtml += '<div class="content">';
        headerHtml += '<a href="#" title="'
                    + Translator.translate('Show on the map')
                    + '" class="show-map" id="show_map_'
                    + this.getLocationCode()
                    + '">';
        headerHtml += '<strong class="location-name overflow-protect">' + this.getName() + '</strong>';

        if (this.getType().indexOf('PA') == -1) {
            headerHtml += '<span class="location-type">' + Translator.translate('Post Office') + '</span>';
        }
        headerHtml += '</a>';

        if (!noTooltip) {
            headerHtml += '<div class="tooltip-container">';
            headerHtml += '<a class="location-info" id="tooltip_anchor_'
                        + this.getLocationCode()
                        + '">';
            headerHtml += '<span>' + Translator.translate('More Info') + '</span>';
            headerHtml += '</a>';

            headerHtml += this.getTooltipHtml();
            headerHtml += '</div>';
        }

        headerHtml += '<a class="responsive-tooltip-open" id="location_tooltip_'
                    + this.getLocationCode()
                    + '_responsive_open">'
                    + Translator.translate('More Info')
                    + '</a>';
        headerHtml += '</div>';
        headerHtml += '</div>';
        headerHtml += '</div>';
        headerHtml += '</li>';

        if (!noTooltip) {
            headerHtml += '<li class="responsive-tooltip" id="location_tooltip_'
                        + this.getLocationCode()
                        + '_responsive" style="display:none;">';
            headerHtml += '<div class="content">';
            headerHtml += this.getResponsiveTooltipHtml();
            headerHtml += '<div class="close-wrapper" id="location_tooltip_'
                        + this.getLocationCode()
                        + '_responsive_close">';
            headerHtml += '<a class="responsive-tooltip-close">' + Translator.translate('Close') + '</a>';
            headerHtml += '</div>';
            headerHtml += '</div>';
            headerHtml += '</li>';
        }

        /**
         * Attach the header to the bottom of the parent element.
         */
        if (parent) {
            $(parent).insert({
                bottom: headerHtml
            });
        }

        if (typeToRender) {
            element = this.renderOption(typeToRender, availableDeliveryDate, false, true);
            return headerHtml + element;
        }

        /**
         * Add an element for each of this location's types. Most often this will be a a single element or a PE and PGE
         * element.
         */
        this.getType().each(function(type) {
            element = this.renderOption(type, availableDeliveryDate, parent, false);
            if (element) {
                elements[type] = element;
            }
        }.bind(this));

        /**
         * Save all newly created elements.
         */
        this.setElements(elements);

        /**
         * Add observers to display the tooltip on mouse over and the responsive tooltip on click.
         */
        var locationHeader          = $('location_header_' + this.getLocationCode());
        var tooltipElement          = $('location_tooltip_' + this.getLocationCode());
        var showOnMapAnchor         = $('show_map_' + this.getLocationCode());
        var responsiveTooltipAnchor = $('location_tooltip_' + this.getLocationCode() + '_responsive_open');
        var responsiveTooltip       = $('location_tooltip_' + this.getLocationCode() + '_responsive');
        var responsiveTooltipClose  = $('location_tooltip_' + this.getLocationCode() + '_responsive_close');

        locationHeader.observe('click', function() {
            var responsiveSwitch = $('responsive_switch');
            if (!responsiveSwitch || getComputedStyle(responsiveSwitch).display == 'none') {
                return;
            }

            responsiveTooltipAnchor.triggerEvent('click');
        });

        showOnMapAnchor.observe('click', function(event) {
            event.stop();

            /**
             * If the responsive switcher is shown, modify the observer so it shows the tooltip instead.
             */
            var responsiveSwitch = $('responsive_switch');
            if (responsiveSwitch && getComputedStyle(responsiveSwitch).display != 'none') {
                responsiveTooltipAnchor.triggerEvent('click');

                return;
            }

            this.getMap().openAddLocationWindow();

            if (this.getMarker() !== false) {
                this.getMap().selectMarker(this.getMarker(), true, true);
            }
        }.bind(this));

        responsiveTooltipAnchor.observe('click', function(event) {
            event.stop();

            var tooltipShown = (getComputedStyle(responsiveTooltip).display != 'none');

            $$('.responsive-tooltip').invoke('hide');

            if (!tooltipShown) {
                responsiveTooltip.show();
            }
        }.bind(this));

        responsiveTooltipClose.observe('click', function(event) {
            event.stop();

            $$('.responsive-tooltip').invoke('hide');
        }.bind(this));

        this.setTooltipElement(tooltipElement);

        return this;
    },

    /**
     * @param {string}         type
     * @param {Date}           availableDeliveryDate
     * @param {string|boolean} parent
     * @param {boolean|null}   toHtml
     *
     * @returns {Element|string|boolean}
     */
    renderOption : function(type, availableDeliveryDate, parent, toHtml) {
        if (!this.getDeliveryOptions().isTypeAllowed(type)) {
            return false;
        }

        var id = 'location_' + this.getLocationCode() + '_' + type;

        var optionHtml = '';
        optionHtml += '<li class="option" id="' + id + '">';
        optionHtml += '<div class="bkg">';
        optionHtml += '<div class="bkg">';
        optionHtml += '<div class="content">';

        var spanClass = 'option-dd';
        if (!this.getDeliveryOptions().isDeliveryDaysAllowed()) {
            spanClass += ' no-display';
        }
        optionHtml += '<span class="' + spanClass + '">';

        /**
         * Only the first element will display the delivery date.
         */
        if (this.counter < 1) {
            optionHtml += '<strong class="option-day">'
                + this.getDeliveryOptions().getWeekdays()[availableDeliveryDate.getDay()]
                + '</strong>';
            optionHtml += '<span class="option-date">'
                + ('0' + availableDeliveryDate.getDate()).slice(-2)
                + '-'
                + ('0' + (availableDeliveryDate.getMonth() + 1)).slice(-2)
                + '</span>';
        }

        optionHtml += '</span>';
        optionHtml += '<span class="option-radio"></span>';

        /*
         * Opening times are hardoded as 8:30 A.M. for PGE locations and 4:00 P.M. for other loations.
         */
        if (type == 'PGE') {
            optionHtml += '<span class="option-time">' + Translator.translate('from') + ' 8:30</span>';
        } else {
            optionHtml += '<span class="option-time">' + Translator.translate('from') + ' 15:00</span>';
        }

        optionHtml += '<span class="option-comment">' + this.getCommentHtml(type) + '</span>';
        optionHtml += '</div>';
        optionHtml += '</div>';
        optionHtml += '</div>';
        optionHtml += '</li>';
        if (toHtml) {
            return optionHtml;
        }

        /**
         * Attach the element to the bottom of the parent element.
         */
        $(parent).insert({
            bottom: optionHtml
        });

        var element = $(id);

        /**
         * Add an onclick observer that will select the location.
         */
        element.observe('click', function(element, event) {
            event.stop();

            if (element.hasClassName('active')) {
                return false;
            }

            this.getDeliveryOptions().selectLocation(element);
            return true;
        }.bind(this, element));

        this.counter++;
        return element;
    },

    /**
     * Gets the comment html for this location. The comment contains any additional fees incurred by choosing this option and, in
     * the case of a parcel dispenser location, the fact that it is available 24/7.
     *
     * @param {string} type
     *
     * @return {string}
     */
    getCommentHtml : function(type) {
        var commentHtml = '';

        /**
         * Additional fees may only be charged for PakjeGemak Express locations.
         */
        if (type == 'PGE') {
            var extraCosts = this.getOptions().expressFeeText;
            var extraCostHtml = '';

            if (this.getOptions().expressFeeIncl) {
                extraCostHtml += ' + ' + extraCosts;
            }

            commentHtml = Translator.translate('early delivery') + extraCostHtml;
        }

        return commentHtml;
    },

    /**
     * Get an available delivery date. This method checks the opening times of this location to make sure the location
     * is open when the order is delivered. If not it will check the day after, and the day after that, and so on.
     *
     * Note that this method is recursive and uses the optional parameter n to prevent infinite loops.
     *
     * @param {Date}        date
     * @param {number|void} n    The number of tries that have been made to find a valid delivery date.
     *
     * @returns {Date}
     */
    getDeliveryDate : function(date, n) {
        /**
         * If this is the first attempt, set n to 0
         */
        if (typeof n == 'undefined') {
            n = 0;
        }
        /**
         * If over 7 attempts have been made, return the current date (it should be 1 week after the first attempt).
         */
        if (n > 7) {
            return date;
        }
        var openingDays = this.getOpeningHours();
        /**
         * Check if the location is open on the specified day of the week.
         */
        var openingHours = false;
        switch (date.getDay()) {
            case 0:
                openingHours = false;
                break;
            case 1:
                openingHours = false;
                break;
            case 2:
                if (openingDays.Tuesday) {
                    openingHours = openingDays.Tuesday.string;
                }
                break;
            case 3:
                if (openingDays.Wednesday) {
                    openingHours = openingDays.Wednesday.string;
                }
                break;
            case 4:
                if (openingDays.Thursday) {
                    openingHours = openingDays.Thursday.string;
                }
                break;
            case 5:
                if (openingDays.Friday) {
                    openingHours = openingDays.Friday.string;
                }
                break;
            case 6:
                if (openingDays.Saturday) {
                    openingHours = openingDays.Saturday.string;
                }
                break;
        }

        /**
         * If no openinghours are found for this day, or if the location is closed; check the next day.
         */
        if (!openingHours
            || openingHours.length < 1
            || openingHours[0] == ''
        ) {
            var nextDay = new Date(date);
            nextDay.setDate(date.getDate() + 1);

            /**
             * If the next day is Monday, get Tuesday as next day.
             */
            if (nextDay.getDay() == 1) {
                nextDay.setDate(date.getDate() + 2);
            }
            return this.getDeliveryDate(nextDay, n + 1);
        }

        var formattedDate = ('0' + date.getDate()).slice(-2)
                          + '-'
                          + ('0' + (date.getMonth() + 1)).slice(-2)
                          + '-'
                          + date.getFullYear();
        this.setDate(formattedDate);

        return date;
    },

    /**
     * Create the html for this location's tooltip. The tooltip contains address information as well as information regarding
     * the opening hours of this location.
     *
     * @return {string}
     */
    getTooltipHtml : function() {
        /**
         * Get the base tooltip html and the address info.
         */
        var address = this.getAddress();
        var addressText = address.Street + ' ' + address.HouseNr;
        if (address.HouseNrExt) {
            addressText += address.HouseNrExt;
        }
        addressText += '  ' + Translator.translate('in') + ' ' + address.City;

        var html = '<div class="tooltip '
                 + this.getTooltipClassName()
                 + '" id="location_tooltip_'
                 + this.getLocationCode()
                 + '">';
        html += '<div class="tooltip-header">';
        html += '<strong class="location-name">' + this.getName() + '</strong>';
        html += '<strong class="location-address">' + addressText + '</strong>';
        html += '</div>';
        html += '<hr class="tooltip-divider" />';
        html += '<div class="tooltip-content">';
        html += '<table class="business-hours">';
        html += '<thead>';
        html += '<tr>';
        html += '<th colspan="2">' + Translator.translate('Business Hours') + '</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        html += this.getOpeningHoursHtml();

        /**
         * Close all elements and return the result.
         */
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        return html;
    },

    /**
     * Create the responsive html for this location's tooltip. The tooltip contains address information as well as
     * information regarding the opening hours of this location.
     *
     * @return {string}
     */
    getResponsiveTooltipHtml : function() {
        /**
         * Get the base tooltip html and the address info.
         */
        var address = this.getAddress();
        var addressText = address.Street + ' ' + address.HouseNr;
        if (address.HouseNrExt) {
            addressText += address.HouseNrExt;
        }
        addressText += '  ' + Translator.translate('in') + ' ' + address.City;

        html = '<strong class="location-address">' + addressText + '</strong>';
        html += '<hr class="divider" />';
        html += '<table class="business-hours">';
        html += '<thead>';
        html += '<tr>';
        html += '<th colspan="2">' + Translator.translate('Business Hours') + '</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        html += this.getOpeningHoursHtml();

        /**
         * Close all elements and return the result.
         */
        html += '</tbody>';
        html += '</table>';

        return html;
    },

    /**
     * Gets html for this location's business hours.
     *
     * Html will be formatted as tr and td elements. This method expects the calling method to provide a container
     * table.
     *
     * @returns {string}
     */
    getOpeningHoursHtml : function() {
        var html;

        /**
         * Add the opening hours for every day of the week.
         */
        var openingHours = this.getOpeningHours();
        var closedText = Translator.translate('Closed');

        /**
         * Monday
         */
        html = '<tr>';
        html += '<th>' + Translator.translate('Mo') + '</th>';
        if (openingHours.Monday && openingHours.Monday.string && openingHours.Monday.string.join() != '') {
            html += '<td>' + (openingHours.Monday.string.join('<br />')).replace('-', ' - ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Tuesday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Tu') + '</th>';
        if (openingHours.Tuesday && openingHours.Tuesday.string && openingHours.Tuesday.string.join() != '') {
            html += '<td>' + (openingHours.Tuesday.string.join('<br />')).replace('-', ' - ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Wednesday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('We') + '</th>';
        if (openingHours.Wednesday && openingHours.Wednesday.string && openingHours.Wednesday.string.join() != '') {
            html += '<td>' + (openingHours.Wednesday.string.join('<br />')).replace('-', ' - ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Thursday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Th') + '</th>';
        if (openingHours.Thursday && openingHours.Thursday.string && openingHours.Thursday.string.join() != '') {
            html += '<td>' + (openingHours.Thursday.string.join('<br />')).replace('-', ' - ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Friday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Fr') + '</th>';
        if (openingHours.Friday && openingHours.Friday.string && openingHours.Friday.string.join() != '') {
            html += '<td>' + (openingHours.Friday.string.join('<br />')).replace('-', ' - ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Saturday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Sa') + '</th>';
        if (openingHours.Saturday && openingHours.Saturday.string && openingHours.Saturday.string.join() != '') {
            html += '<td>' + (openingHours.Saturday.string.join('<br />')).replace('-', ' - ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Sunday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Su') + '</th>';
        if (openingHours.Sunday && openingHours.Sunday.string && openingHours.Sunday.string.join() != '') {
            html += '<td>' + (openingHours.Sunday.string.join('<br />')).replace('-', ' - ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        return html;
    },

    /**
     * Render this location as a map element. Map elements appear in a list below the google maps interface.
     *
     * @param {string}  parent
     * @param {string}  responsiveParent
     * @param {boolean} renderDistance
     *
     * @returns {PostnlDeliveryOptions.Location}
     */
    renderAsMapLocation : function(parent, responsiveParent, renderDistance) {
        var address = this.getAddress();

        /**
         * Format the address.
         */
        var addressText = address.Street + ' ' + address.HouseNr;
        if (address.HouseNrExt) {
            addressText += address.HouseNrExt;
        }
        addressText += ', ' + address.City;

        /**
         * Format the distance to the last searched address.
         */
        var distance = parseInt(this.getDistance());
        var distanceText = '';

        /**
         * Render distances below 1000 in meters and above 1000 in kilometers.
         */
        if (renderDistance && distance < 1000 && distance > 0) {
            distanceText = distance + ' m';
        } else if (renderDistance && distance > 0) {
            distanceText = parseFloat(Math.round(distance / 100) / 10).toFixed(1) + ' km';
        }

        var businessHoursText = '';
        if (this.getType().indexOf('PA') > -1) {
            businessHoursText = Translator.translate('parcel dispenser');
        } else {
            businessHoursText = Translator.translate('business hours');
        }

        var id = 'map-location_' + this.getLocationCode();

        /**
         * Build the element's html.
         */
        var html = '<li class="location" id="' + id + '">';
        html += '<div class="content">';

        var image = this.getDeliveryOptions().getImageBasUrl()
                  + '/tmb_'
                  + this.getDeliveryOptions().getImageName(this.getName())
                  + '.png';
        html += '<img src="' + image + '" class="location-icon" alt="' + this.getName() + '" />';
        html += '<span class="overflow-protect">';
        html += '<strong class="location-name">' + this.getName() + '</strong>';
        html += '<span class="location-address">' + addressText + '</span>';
        html += '</span>';
        html += '<span class="location-distance">' + distanceText + '</span>';
        html += '<a class="location-info" id="' + id + '-info">' + businessHoursText + '</a>';
        html += '</div>';
        html += '</li>';

        /**
         * Attach the location to the bottom of the parent element.
         */
        $(parent).insert({
            bottom: html
        });

        var element           = $(id);

        /**
         * Add observers to this element.
         */
        element.observe('click', function(event) {
            var map = this.getMap();

            event.stop();

            if (Event.element(event).hasClassName('location-info')) {
                return false;
            }

            if (!this.getMarker()) {
                return false;
            }

            if (map.getSelectedMarker() == this.getMarker()) {
                return false;
            }

            this.setOldCenter(this.getMarker().getPosition());

            map.selectMarker(this.getMarker(), false, true);

            if (map.getIsInfoWindowOpen()) {
                map.openLocationInfoWindow(
                    this.getMapTooltipHtml(),
                    this.getLocationCode()
                );
            }
            return true;
        }.bind(this));

        element.observe('dblclick', function(event) {
            var map = this.getMap();

            event.stop();

            if (Event.element(event).hasClassName('location-info')) {
                return false;
            }

            if (!this.getMarker()) {
                return false;
            }

            if (map.getSelectedMarker() == this.getMarker()) {
                map.saveLocation();
                return false;
            }

            this.setOldCenter(this.getMarker().getPosition());

            map.selectMarker(this.getMarker(), false, true);

            if (map.getIsInfoWindowOpen()) {
                map.openLocationInfoWindow(
                    this.getMapTooltipHtml(),
                    this.getLocationCode()
                );
            }

            map.saveLocation();
            return true;
        }.bind(this));

        element.observe('mouseover', function() {
            this.mouseOver = true;

            var map = this.getMap();
            if (map.getIsInfoWindowOpen() || map.getIsBeingDragged()) {
                return this;
            }

            if (!this.getMarker()) {
                return false;
            }

            google.maps.event.trigger(this.getMarker(), 'mouseover');

            setTimeout(function() {
                if (!this.mouseOver) {
                    return false;
                }

                this.setOldCenter(map.map.getCenter());
                map.map.panTo(this.getMarker().getPosition());
                return true;
            }.bind(this), 250);

            return true;
        }.bind(this));

        element.observe('mouseout', function() {
            var map = this.getMap();
            if (map.getIsInfoWindowOpen() || map.getIsBeingDragged()) {
                return this;
            }

            if (!this.getMarker()) {
                return false;
            }

            google.maps.event.trigger(this.getMarker(), 'mouseout');
            this.mouseOver = false;
            return true;
        }.bind(this));

        var infoElement = $(id + '-info');
        infoElement.observe('click', function() {
            var map = this.getMap();

            /**
             * If the location info window already has this location's info, close it instead.
             */
            var infoWindow = $('location-info-window');
            if (infoWindow.getAttribute('data-locationcode')
                && infoWindow.getAttribute('data-locationcode') == this.getLocationCode()
            ) {
                map.closeLocationInfoWindow();

                return this;
            }

            if (this.getOldCenter()) {
                map.map.setCenter(this.getOldCenter());
                this.setOldCenter(false);
            }

            google.maps.event.trigger(this.getMarker(), 'mouseout');

            map.openLocationInfoWindow(
                this.getMapTooltipHtml(),
                this.getLocationCode()
            );

            return this;
        }.bind(this));

        this.setMapElement(element);

        /**
         * Format the address.
         */
        var responsiveAddressText = '<strong>' + this.getName() + '</strong><br />';
        responsiveAddressText += address.Street + ' ' + address.HouseNr + '<br />';
        if (responsiveAddressText.HouseNrExt) {
            responsiveAddressText += address.HouseNrExt;
        }
        responsiveAddressText += address.Zipcode + ' ' + address.City;

        /**
         * Get the responsive map location html.
         */
        var responsiveId = 'map_location_' + this.getLocationCode() + '_responsive';
        var responsiveHtml = '<li class="location-details" id="' + responsiveId + '">';
        responsiveHtml += '<div class="content">';
        responsiveHtml += '<div class="location-info">';
        responsiveHtml += '<span class="radio"></span>';
        responsiveHtml += '<span class="address">';
        responsiveHtml += responsiveAddressText;
        responsiveHtml += '</span>';
        responsiveHtml += '<span class="distance"><strong>' + distanceText + '</strong></span>';
        responsiveHtml += '</div>';
        responsiveHtml += '<a href="#" class="info-link" id="'
                        + responsiveId
                        + '_info_open">'
                        + Translator.translate('More info')
                        + '</a>';
        responsiveHtml += '</div>';
        responsiveHtml += '<div class="more-info" id="' + responsiveId + '_info" style="display:none;">';
        responsiveHtml += '<table class="business-hours">';
        responsiveHtml += '<thead>';
        responsiveHtml += '<tr>';
        responsiveHtml += '<th colspan="2">' + businessHoursText + '</th>';
        responsiveHtml += '</tr>';
        responsiveHtml += '</thead>';
        responsiveHtml += '<tbody>';
        responsiveHtml += this.getOpeningHoursHtml();
        responsiveHtml += '</tbody>';
        responsiveHtml += '</table>';
        responsiveHtml += '<div class="actions">';
        responsiveHtml += '<button class="postnl-button" id="'
                        + responsiveId
                        + '_select">'
                        + Translator.translate('Select location')
                        + '</button>';
        responsiveHtml += '<button class="postnl-button white" id="'
                        + responsiveId
                        + '_map">'
                        + Translator.translate('Show map')
                        + '</button>';
        responsiveHtml += '</div>';
        responsiveHtml += '<div class="close-wrapper">';
        responsiveHtml += '<a class="more-info-close" id="'
                        + responsiveId
                        + '_info_close">'
                        + Translator.translate('Close')
                        + '</a>';
        responsiveHtml += '</div>';
        responsiveHtml += '</div>';
        responsiveHtml += '</li>';

        /**
         * Attach the location to the bottom of the parent element.
         */
        $(responsiveParent).insert({
            bottom: responsiveHtml
        });

        var responsiveElement             = $(responsiveId);
        var responsiveElementInfo         = $(responsiveId + '_info');
        var responsiveElementInfoAnchor   = $(responsiveId + '_info_open');
        var responsiveElementInfoClose    = $(responsiveId + '_info_close');
        var responsiveElementSelectButton = $(responsiveId + '_select');
        var responsiveElementMapButton    = $(responsiveId + '_map');

        responsiveElement.select('.location-info').invoke('observe', 'click', function(event) {
            var map = this.getMap();

            event.stop();

            if (!this.getMarker()) {
                return false;
            }

            this.setOldCenter(this.getMarker().getPosition());

            map.selectMarker(this.getMarker(), false, true);

            map.saveLocation();

            return true;
        }.bind(this));

        responsiveElementInfoAnchor.observe('click', function(event) {
            event.stop();

            if (getComputedStyle(responsiveElementInfo).display != 'none') {
                responsiveElementInfo.hide();
                return;
            }

            responsiveElementInfo.show();
        });

        responsiveElementInfoClose.observe('click', function(event) {
            event.stop();

            responsiveElementInfo.hide();
        });

        responsiveElementSelectButton.observe('click', function(event) {
            var map = this.getMap();

            event.stop();

            if (!this.getMarker()) {
                return false;
            }

            this.setOldCenter(this.getMarker().getPosition());

            map.selectMarker(this.getMarker(), false, true);

            map.saveLocation();

            return true;
        }.bind(this));

        responsiveElementMapButton.observe('click', function(event) {
            event.stop();

            var markerPosition = this.getMarker().getPosition();
            var mapsUrl = 'http://maps.google.com/?q=';

            mapsUrl += encodeURIComponent(
                this.getName()
                + ' '
                + address.Street
                + ' '
                + address.HouseNr
                + ' '
                + address.City
            );

            mapsUrl += '&f=d';

            mapsUrl += '&saddr=' + encodeURIComponent(this.getDeliveryOptions().getFullAddress());

            mapsUrl += '&daddr='
                     + markerPosition.lat()
                     + ','
                     + markerPosition.lng();

            if (this.getDeliveryOptions().debug) {
                console.info('Opening google maps with url: ' + mapsUrl);
            }

            window.open(mapsUrl);
        }.bind(this));

        this.setResponsiveMapElement(responsiveElement);

        return this;
    },

    /**
     * Gets contents for the location info tooltip.
     *
     * @returns {string}
     */
    getMapTooltipHtml : function() {
        /**
         * Get the base tooltip html and the address info.
         */
        var address = this.getAddress();
        var addressText = address.Street + ' ' + address.HouseNr;
        if (address.HouseNrExt) {
            addressText += address.HouseNrExt;
        }
        addressText += '  ' + Translator.translate('in') + ' ' + address.City;

        var html = '<div class="left">';
        html += '<strong class="location-name">' + this.getName() + '</strong>';
        html += '<strong class="location-address">' + addressText + '</strong>';
        html += '<span class="location-info">' + this.getLocationInfoText() + '</span>';
        html += '</div>';
        html += '<div class="right">';
        html += '<table class="business-hours">';
        html += '<thead>';
        html += '<tr>';
        html += '<th colspan="2">'+ Translator.translate('Business Hours') + ':</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        html += this.getOpeningHoursHtml();

        html += '</tbody>';
        html += '</table>';
        html += '</div>';

        return html;
    },

    /**
     * Gets info about this location for the location-info tooltip.
     *
     * @returns {string}
     */
    getLocationInfoText : function() {
        var type = this.getType();

        var locationInfo = [];
        if (type.indexOf('PGE') > -1) {
            locationInfo.push(Translator.translate('Early pickup available'));
        }

        if (type.indexOf('PA') > -1) {
            locationInfo.push('24/7 ' + Translator.translate('available'));
        }

        if (this.getIsEveningLocation()) {
            locationInfo.push(Translator.translate('Evening pickup available'));
        }

        return locationInfo.join('<br />');
    },

    /**
     * @param {string} type
     *
     * @returns {PostnlDeliveryOptions.Location}
     */
    renderAsOsc : function(type) {
        var html = this.render(false, type, true);

        $$('#postnl_add_moment .option-list')[0].insert({
            bottom : html
        });

        return this;
    },

    /**
     * Select an element by adding the 'active' class.
     *
     * @return {PostnlDeliveryOptions.Location}
     */
    select : function(type) {
        var elements = this.getElements();
        if (!elements) {
            return this;
        }

        if (!elements[type].hasClassName('active')) {
            elements[type].addClassName('active');
        }

        return this;
    },

    /**
     * Unselect an option by removing the 'active' class.
     *
     * @return {PostnlDeliveryOptions.Location}
     */
    unSelect : function(type) {
        var elements = this.getElements();
        if (!elements) {
            return this;
        }

        if (elements[type].hasClassName('active')) {
            elements[type].removeClassName('active');
        }

        return this;
    }
});

PostnlDeliveryOptions.Timeframe = new Class.create({
    element         : false,

    date            : null,
    from            : null,
    to              : null,
    type            : null,

    timeframeIndex  : 0,
    deliveryOptions : null,

    /**
     * Constructor method.
     *
     * @constructor
     *
     * @param {string}                date
     * @param {object}                timeframe
     * @param {number}                timeframeIndex
     * @param {PostnlDeliveryOptions} deliveryOptions
     *
     * @returns {void}
     */
    initialize : function(date, timeframe, timeframeIndex, deliveryOptions) {
        this.date = date;
        this.from = timeframe.From;
        this.to   = timeframe.To;

        var type =  timeframe.Options.string[0];
        switch (type) {
            case 'Evening' :
                this.type = 'Avond';
                break;
            case 'Sunday' :
                this.type = 'Sunday';
                break;
            default :
                this.type = 'Overdag';
                break;
        }

        this.timeframeIndex = timeframeIndex;

        this.deliveryOptions = deliveryOptions;
    },

    /******************************
     *                            *
     *  GETTER AND SETTER METHODS *
     *                            *
     ******************************/

    getElement : function() {
        return this.element;
    },

    setElement : function(element) {
        this.element = element;

        return this;
    },

    getDate : function() {
        return this.date;
    },

    getFrom : function() {
        return this.from;
    },

    getTo : function() {
        return this.to;
    },

    getType : function() {
        return this.type;
    },

    getTimeframeIndex : function() {
        return this.timeframeIndex;
    },

    getDeliveryOptions : function() {
        return this.deliveryOptions;
    },

    getOptions : function() {
        return this.getDeliveryOptions().getOptions();
    },

    /**
     * Render this time frame as a new html element.
     *
     * @param {string}  parent The parent element's ID to which we will attach this element.
     * @param {boolean} forceDate
     *
     * @returns {PostnlDeliveryOptions.Timeframe}
     */
    render : function(parent, forceDate) {
        /**
         * Build the element's html.
         */
        var html = '<li class="option" id="timeframe_' + this.getTimeframeIndex() + '">';
        html += '<div class="bkg">';
        html += '<div class="bkg">';
        html += '<div class="content">';

        var spanClass = 'option-dd';
        if (!this.getDeliveryOptions().isDeliveryDaysAllowed()) {
            spanClass += ' no-display';
        }
        html += '<span class="' + spanClass + '">';

        /**
         * Add the day of the week on which this time frame is available.
         */
        html += this.getWeekdayHtml(forceDate);

        html += '</span>';
        html += '<span class="option-radio"></span>';

        spanClass = 'option-time';
        var openingHours = '';
        if (!this.getDeliveryOptions().isTimeframesAllowed() && this.getDeliveryOptions().getIsBuspakje()) {
            spanClass    += ' no-timeframe-buspakje';
            openingHours += Translator.translate('Fits through the mailslot');
        } else if (!this.getDeliveryOptions().isTimeframesAllowed()
            && !this.getDeliveryOptions().isDeliveryDaysAllowed()
        ) {
            spanClass    += ' no-timeframe-buspakje';
            openingHours += Translator.translate('As soon as possible');
        } else if (!this.getDeliveryOptions().isTimeframesAllowed()) {
            spanClass    += ' no-timeframe-buspakje';
            openingHours += '09:00 - 18:00';
        } else {
            openingHours += this.getFrom().substring(0, 5)
                          + ' - '
                          + this.getTo().substring(0, 5);
        }
        html += '<span class="' + spanClass + '">'
              + openingHours
              + '</span>';

        /**
         * Add an optional comment to the timeframe.
         */
        html += this.getCommentHtml();

        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</li>';

        if (!parent) {
            return html;
        }

        /**
         * Add the element to the DOM.
         */
        $(parent).insert({
            bottom: html
        });

        /**
         * Observe the new element's click event.
         */
        var element = $('timeframe_' + this.getTimeframeIndex());
        element.observe('click', function(element, event) {
            event.stop();

            if (element.hasClassName('active')) {
                return true;
            }

            this.getDeliveryOptions().selectTimeframe(element);
            return true;
        }.bind(this, element));

        this.setElement(element);

        return this;
    },

    /**
     * @returns {PostnlDeliveryOptions.Timeframe}
     */
    renderAsOsc : function() {
        var html = this.render(false, true);

        /**
         * Remove existing timeframes and locations.
         */
        $$('#postnl_add_moment .option-list li.option').invoke('remove');

        /**
         * Render the selected timeframe or location.
         */
        $$('#postnl_add_moment .option-list')[0].insert({
            bottom : html
        });

        return this;
    },

    /**
     * Get an optional comment for this timeframe.
     *
     * @returns {string}
     */
    getCommentHtml : function() {
        var comment = '';
        if (this.type == 'Avond') {
            var extraCosts = this.getOptions().eveningFeeText;
            var extraCostHtml = '';

            if (this.getOptions().eveningFeeIncl) {
                extraCostHtml += ' + ' + extraCosts;
            }

            comment = '<span class="option-comment">' + Translator.translate('evening') + extraCostHtml + '</span>';
        }

        if (this.type == 'Sunday') {
            var sundayCosts = this.getOptions().sundayFeeText;
            var sundayCostHtml = '';

            if (this.getOptions().sundayFeeIncl) {
                sundayCostHtml += ' + ' + sundayCosts;
            }

            comment = '<span class="option-comment">' + Translator.translate('sunday') + sundayCostHtml + '</span>';
        }

        return comment;
    },

    /**
     * Get the day of the week on which this timeframe is available.
     *
     * @param {boolean} skipCheck
     *
     * @returns {string}
     */
    getWeekdayHtml : function(skipCheck) {
        var date = new Date(this.date.substring(6, 10), this.date.substring(3, 5) - 1, this.date.substring(0, 2));

        var datesProcessed = this.getDeliveryOptions().getDatesProcessed();
        var weekdayHtml = '';
        if (skipCheck || datesProcessed.indexOf(date.getTime()) == -1) {
            var weekdays = this.getDeliveryOptions().getWeekdays();

            this.getDeliveryOptions().getDatesProcessed().push(date.getTime());
            weekdayHtml = '<strong class="option-day">' + weekdays[date.getDay()] + '</strong>';
            weekdayHtml += '<span class="option-date">'
                         + ('0' + date.getDate()).slice(-2)
                         + '-'
                         + ('0' + (date.getMonth() + 1)).slice(-2)
                         + '</span>';
        }

        return weekdayHtml;
    },

    /**
     * Select an element by adding the 'active' class.
     *
     * @return {PostnlDeliveryOptions.Timeframe}
     */
    select : function() {
        var element = this.getElement();
        if (!element) {
            return this;
        }

        if (!element.hasClassName('active')) {
            element.addClassName('active');
        }

        return this;
    },

    /**
     * Unselect an option by removing the 'active' class.
     *
     * @return {PostnlDeliveryOptions.Timeframe}
     */
    unSelect : function() {
        var element = this.element;
        if (!element) {
            return this;
        }

        if (element.hasClassName('active')) {
            element.removeClassName('active');
        }

        return this;
    }
});