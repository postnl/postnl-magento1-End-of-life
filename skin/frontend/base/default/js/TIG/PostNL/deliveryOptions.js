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

/**
 * Make sure the magento translator is available. If not, create a dummy function.
 */
if (typeof Translator == 'undefined' && typeof Translate === 'function') {
    Translator = new Translate();
} else if (typeof Translator == 'undefined') {
    var Translate = new Class.create();
    Translate.prototype = {
        translate : function(text) {
            return text;
        }
    };

    Translator = new Translate();
}

/**
 * PostNL delivery options logic class.
 *
 * Uses AJAX to communicate with PostNL and retrieve possible delivery options. This class also manages all available options.
 */
var PostnlDeliveryOptions = new Class.create();
PostnlDeliveryOptions.prototype = {
    options            : {},
    weekdays           : [],
    datesProcessed     : [],

    timeframesUrl      : null,
    locationsUrl       : null,
    locationsInAreaUrl : null,

    postcode           : null,
    housenumber        : null,
    fullAddress        : null,
    deliveryDate       : null,
    imageBaseUrl       : null,

    pgLocation         : false,
    pgeLocation        : false,
    paLocation         : false,

    timeframes         : false,
    locations          : [],
    parsedTimeframes   : false,
    parsedLocations    : false,

    selectedOption     : false,

    deliveryOptionsMap : false,

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
        this.selectedOption = option;

        return this;
    },

    getDeliveryOptionsMap : function() {
        return this.deliveryOptionsMap;
    },

    /*
     * Get the name of an imagefile for the specified location name.
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
        }

        if (imageName == '') {
            if (name.indexOf('GAMMA') > -1) {
                return 'gamma';
            }

            if (name.indexOf('KARWEI') > -1) {
                return 'karwei';
            }
        }

        if (imageName == '') {
            return 'default';
        }

        return imageName;
    },

    /**
     * Constructor method.
     *
     * @constructor
     *
     * @param {object} params
     * @param {object} options
     *
     * @returns {void}
     */
    initialize : function(params, options) {
        if (!params.timeframesUrl
            || !params.locationsUrl
            || !params.locationsInAreaUrl
            || !params.postcode
            || !params.housenumber
            || !params.deliveryDate
            || !params.imageBaseUrl
            || !params.fullAddress
        ) {
            throw 'Missing parameters.';
        }

        this.reset();

        this.timeframesUrl      = params.timeframesUrl;
        this.locationsUrl       = params.locationsUrl;
        this.locationsInAreaUrl = params.locationsInAreaUrl;
        this.postcode           = params.postcode;
        this.housenumber        = params.housenumber;
        this.deliveryDate       = params.deliveryDate;
        this.imageBaseUrl       = params.imageBaseUrl;
        this.fullAddress        = params.fullAddress;

        this.options = Object.extend({
            allowTimeframes        : true,
            allowEveningTimeframes : false,
            allowPg                : true,
            allowPge               : false,
            allowPa                : true,
            eveningFee             : 0,
            expressFee             : 0,
            eveningFeeText         : '',
            expressFeeText         : '',
            allowStreetview        : true,
            scrollbarContainer     : 'scrollbar_content',
            scrollbarTrack         : 'scrollbar_track',
            pgLocationContainer    : 'pglocation',
            pgeLocationContainer   : 'pgelocation',
            paLocationContainer    : 'palocation',
            timeframesContainer    : 'timeframes',
            postnlShippingMethods  : [
                's_method_postnl_tablerate', 's_method_postnl_flatrate'
            ]
        }, options || {});

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

    /**
     * Reset all parameters to their defaul values.
     *
     * @returns {PostnlDeliveryOptions}
     */
    reset : function() {
        this.datesProcessed   = [];
        this.pgLocation       = false;
        this.pgeLocation      = false;
        this.paLocation       = false;
        this.timeframes       = false;
        this.locations        = [];
        this.parsedTimeframes = false;
        this.parsedLocations  = false;
        this.selectedOption   = false;

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
                        return;
                    }
                }

                this.unSelectLocation();
                this.unSelectTimeframe();
            }.bind(this, element));
        }.bind(this));

        return this;
    },

    /**
     * Check if PGE locations are allowed.
     *
     * @returns {boolean}
     */
    isPgeAllowed : function() {
        if (this.getOptions().allowPge === false) {
            return false;
        }

        return true;
    },

    /**
     * Check if PG locations are allowed.
     *
     * @returns {boolean}
     */
    isPgAllowed : function() {
        if (this.getOptions().allowPg === false) {
            return false;
        }

        return true;
    },

    /**
     * Check if PA locations are allowed.
     *
     * @returns {boolean}
     */
    isPaAllowed : function() {
        if (this.getOptions().allowPa === false) {
            return false;
        }

        return true;
    },

    /**
     * Check if timeframes are allowed.
     *
     * @returns {boolean}
     */
    isTimeframesAllowed : function() {
        if (this.getOptions().allowTimeframes === false) {
            return false;
        }

        return true;
    },

    /**
     * Check if evening timeframes are allowed.
     *
     * @returns {boolean}
     */
    isEveningTimeframesAllowed : function() {
        if (this.getOptions().allowEveningTimeframes === false) {
            return false;
        }

        return true;
    },

    /**
     * Start the delivery options functionality by retrieving possible delivery options from PostNL.
     *
     * @returns {PostnlDeliveryOptions}
     */
    showOptions : function() {
        this.deliveryOptionsMap = new PostnlDeliveryOptions.Map(this.getFullAddress(), this);

        this.getTimeframes(this.getPostcode(), this.getHousenumber(), this.getDeliveryDate());
        this.getLocations(this.getPostcode(), this.getHousenumber(), this.getDeliveryDate());

        return this;
    },

    /**
     * Get all possible delivery timeframes for a specified postcode, housenumber and delivery date.
     *
     * @param {string} postcode
     * @param {number} housenumber
     * @param {string} deliveryDate
     *
     * @returns {boolean|Array|PostnlDeliveryOptions}
     */
    getTimeframes : function(postcode, housenumber, deliveryDate) {
        /**
         * @type {Array|boolean}
         */
        var timeframes = this.timeframes;
        if (timeframes) {
            return timeframes;
        }

        if (!postcode) {
            postcode = this.getPostcode();
        }

        if (!housenumber) {
            housenumber = this.getHousenumber();
        }

        if (!deliveryDate) {
            deliveryDate = this.getDeliveryDate();
        }

        new Ajax.PostnlRequest(this.getTimeframesUrl(),{
            method : 'post',
            parameters : {
                postcode     : postcode,
                housenumber  : housenumber,
                deliveryDate : deliveryDate,
                isAjax       : true
            },
            onSuccess : this.processGetTimeframesSuccess.bind(this),
            onFailure : this.showDefaultTimeframe.bind(this)
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

        /**
         * Parse and render the result.
         */
        this.parseTimeframes(timeframes)
            .renderTimeframes();

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
            if (o >= 1 && this.isTimeframesAllowed() === false) {
                break;
            }

            var currentTimeframe = timeframes[n];

            for (i = 0, m = currentTimeframe.Timeframes.TimeframeTimeFrame.length; i < m ; i++, o++) {
                var currentSubTimeframe = currentTimeframe.Timeframes.TimeframeTimeFrame[i];
                if (this.isEveningTimeframesAllowed() === false
                    && currentSubTimeframe.TimeframeType == 'Avond'
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

        return this;
    },

    renderTimeframes : function() {
        $$('#' + this.getOptions().timeframesContainer + ' li.option').each(function(element) {
            element.remove();
        });

        this.timeframes.each(function(timeframe) {
            timeframe.render(this.getOptions().timeframesContainer);
        }.bind(this));

        this.timeframes[0].select();

        return this;
    },

    showDefaultTimeframe : function() {
        var fakeTimeframe = {
            From          : '09:00:00',
            To            : '18:00:00',
            TimeframeType : 'Overdag'
        };

        var postnlTimeframe = new PostnlDeliveryOptions.Timeframe(this.getDeliveryDate(), fakeTimeframe, 0, this);
        this.setTimeframes(new Array(postnlTimeframe));

        this.renderTimeframes();

        this.setParsedTimeframes(true)
            .hideSpinner();

        return this;
    },

    selectTimeframe : function(element) {
        if (!element) {
            return this;
        }

        var timeframes = this.timeframes;

        timeframes.each(function(timeframe) {
            if (element && timeframe.element.identify() == element.identify()) {
                this.setSelectedOption(timeframe);
                timeframe.select();
            } else {
                timeframe.unSelect();
            }
        }.bind(this));

        this.unSelectLocation();
        this.selectPostnlShippingMethod();

        return false;
    },

    unSelectTimeframe : function() {
        var timeframes = this.timeframes;

        timeframes.each(function(timeframe) {
            timeframe.unSelect();
        });

        return this;
    },

    /**
     * Get all possible delivery locations for a specified postcode, housenumber and delivery date.
     *
     * The result may contain up to 20 locations, however we will end up using a maximum of 3.
     *
     * @param {string} postcode
     * @param {int}    housenumber
     * @param {string} deliveryDate
     *
     * @return {PostnlDeliveryOptions}
     */
    getLocations : function(postcode, housenumber, deliveryDate) {
        new Ajax.PostnlRequest(this.getLocationsUrl(),{
            method : 'post',
            parameters : {
                postcode     : postcode,
                housenumber  : housenumber,
                deliveryDate : deliveryDate,
                isAjax       : true
            },
            onSuccess : this.processGetLocationsSuccess.bind(this),
            onFailure : this.hideLocations.bind(this)
        });

        return this;
    },

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
        this.parseLocations(locations)
            .renderLocations();

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

        return this;
    },

    renderLocations : function() {
        var pickUpList = $('postnl_pickup');
        pickUpList.show();

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
            pickUpList.hide();
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

        return this;
    },

    hideLocations : function() {
        this.setParsedLocations(true)
            .hideSpinner();

        return this;
    },

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
                    this.setSelectedOption(location);
                    location.select(index);

                    var selectedMarker = this.getDeliveryOptionsMap().getSelectedMarker();
                    if (location.getMarker() != selectedMarker) {
                        this.getDeliveryOptionsMap().selectMarker(location.getMarker(), true, true);
                    }
                } else {
                    location.unSelect(index);
                }
            }

            return true;
        }.bind(this));

        this.unSelectTimeframe();
        this.selectPostnlShippingMethod();

        return this;
    },

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
     * Hides the initial AJAX spinner and shows the delivery options.
     *
     * @returns {PostnlDeliveryOptions}
     */
    hideSpinner : function() {
        if (!this.getParsedLocations() || !this.getParsedTimeframes()) {
            return this;
        }

        $('initial_loader').hide();
        $('postnl_delivery_options').show();

        return this;
    },

    /**
     * Select the PostNL shiopping method radio button.
     *
     * @returns {PostnlDeliveryOptions}
     */
    selectPostnlShippingMethod : function() {
        var flatrate = $('s_method_postnl_flatrate');
        var tablerate = $('s_method_postnl_tablerate');

        if (flatrate) {
            flatrate.checked = true;

            return this;
        }

        if (tablerate) {
            tablerate.checked = true;

            return this;
        }

        return this;
    }
};

PostnlDeliveryOptions.Map = new Class.create({
    map                           : false,
    scrollbar                     : false,

    deliveryOptions               : false,
    fullAddress                   : '',

    isBeingDragged                : false,

    markers                       : [],
    locations                     : [],
    selectedMarker                : false,
    searchLocationMarker          : false,

    nearestLocationsRequestObject : false,
    locationsInAreaRequestObject  : false,

    filterEarly                   : false,
    filterEvening                 : false,

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

        var imageName = this.getDeliveryOptions().getImageName(name);
        var imageBase = this.getDeliveryOptions().getImageBasUrl();
        var image = imageBase + '/crc_' + imageName + '.png';

        var icon = {
            anchor : new google.maps.Point(13, 27),
            url    : image
        };

        return icon;
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

        var imageName = this.getDeliveryOptions().getImageName(name);
        var imageBase = this.getDeliveryOptions().getImageBasUrl();
        var image = imageBase + '/drp_' + imageName + '.png';

        var icon = {
            anchor : new google.maps.Point(17, 46),
            url    : image
        };

        return icon;
    },

    getSaveButton : function() {
        return $('location_save');
    },

    /**
     * Get an options object for the map's markers.
     *
     * @param {object} location
     * @param {google.maps.LatLng} markerLatLng
     * @param {string} markerTitle
     *
     * @returns {object}
     */
    getMarkerOptions : function(location, markerLatLng, markerTitle) {
        var markerOptions = {
            position  : markerLatLng,
            map       : null,
            title     : markerTitle,
            animation : google.maps.Animation.DROP,
            draggable : false,
            clickable : true,
            icon      : this.getMapIcon(location)
        };

        return markerOptions;
    },

    /**
     * Get the shape of a marker.
     *
     * @returns {{coords: number[], type: string}}
     */
    getMarkerShape : function() {
        var coords = [
            10, 27, 6, 25, 4, 23, 3, 22, 1, 20, 0, 17, 0, 10, 1, 7, 3, 5, 4, 4, 6, 2, 10, 0, 17, 0, 21, 2, 23, 4, 24,
            5, 26, 7, 27, 10, 27, 17, 26, 20, 24, 22, 23, 23, 21, 25, 17, 27
        ];

        var shape = {
            coords : coords,
            type   : 'poly'
        };

        return shape;
    },

    /**
     * Get the shape of a selected marker.
     *
     * @returns {{coords: number[], type: string}}
     */
    getSelectedMarkerShape : function() {
        var coords = [
            17, 46, 13, 41, 8, 34,3, 28, 1, 24, 0, 21, 0, 13, 1, 10, 3, 7, 5, 5, 7, 3, 10, 1, 13, 0, 22, 0, 25, 1, 28,
            3, 30, 5, 32, 7, 34, 10, 35, 13, 35, 21, 34, 24, 32, 28, 27, 34, 22, 41, 18, 46
        ];

        var shape = {
            coords : coords,
            type   : 'poly'
        };

        return shape;
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

        return $('postnl_add_location');
    },

    /**
     * Constructor method.
     * Creates the google maps object and triggers an initial address search based on the user's chosen shipping
     * address.
     *
     * @constructor
     *
     * @param {string} fullAddress
     * @param {PostnlDeliveryOptions} deliveryOptions
     *
     * @returns {void}
     */
    initialize : function(fullAddress, deliveryOptions) {
        if (typeof google.maps == 'undefined') {
            throw 'Google maps is required.';
        }


        this.deliveryOptions = deliveryOptions;
        this.fullAddress = fullAddress;

        var mapOptions = this.getMapOptions();

        this.map = new google.maps.Map($('map-div'), mapOptions);

        this.scrollbar = new Control.ScrollBar(
            this.getOptions().scrollbarContainer,
            this.getOptions().scrollbarTrack
        );

        this.searchAndPanToAddress(this.getFullAddress(), true, false);

        this.registerObservers();
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
        var myStyles = [
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

        var mapOptions = {
            zoom                   : 14,
            minZoom                : 13,
            maxZoom                : 18,
            center                 : new google.maps.LatLng(52.3702157, 4.895167899999933), //Amsterdam
            mapTypeId              : google.maps.MapTypeId.ROADMAP,
            styles                 : myStyles,
            draggable              : true,
            panControl             : false,
            mapTypeControl         : false,
            scaleControl           : false,
            overviewMapControl     : false,
            streetViewControl      : this.getOptions().allowStreetview,
            zoomControl            : true,
            zoomControlOptions     : zoomControlOptions,
            disableDoubleClickZoom : false,
            scrollwheel            : true
        };

        return mapOptions;
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
        $('search-button').observe('click', this.addressSearch.bind(this));
        $('search-field').observe('keydown', this.addressSearch.bind(this));
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

        /**
         * Register observers specific for the google map.
         */
        google.maps.event.addListener(map, 'zoom_changed', function() {
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
        /**
         * Stop event propagation and the default action from triggering.
         */
        if (event) {
            event.stop();
        }

        this.getAddLocationWindow().show();

        /**
         * This causes the map to resize according to the now visible window's viewport.
         */
        this.triggerResize();
        this.recalculateScrollbar();

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
        /**
         * Stop event propagation and the default action from triggering.
         */
        if (event) {
            event.stop();
        }

        this.getAddLocationWindow().hide();

        return this;
    },

    /**
     * Search for an address. The address can be any value, but a postcode or streetname is recommended.
     *
     * @param {Event} event
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    addressSearch : function(event) {
        /**
         * If this event was triggered by a keypress, we want to ignore any except the return key.
         */
        if (event.keyCode && event.keyCode != Event.KEY_RETURN) {
            return this;
        } else if (event) {
            /**
             * Stop event propagation and the default action from triggering.
             */
            event.stop();
        }

        var address = $('search-field').getValue();
        if (!address) {
            return this;
        }

        /**
         * Search for an address, pan the map to the new location and search for locations nearby.
         */
        this.searchAndPanToAddress(address, true, true);

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
        this.unselectMarker();
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
        geocoder.geocode(
            {
                address                : address,
                bounds                 : this.map.getBounds(),
                componentRestrictions  : {
                    country : 'NL'
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

        /**
         * Loop through all results and validate each to find a suitable result to use.
         */
        results.each(function(result) {
            if (selectedResult !== false) {
                return false;
            }

            /**
             * Make sure the result is located in the Netherlands.
             */
            var components = result.address_components;
            components.each(function(component) {
                if (selectedResult !== false) {
                    return false;
                }

                if (component.short_name == 'NL') {
                    if (selectedResult !== false) {
                        return false;
                    }

                    selectedResult = result;

                    return true;
                }

                return false;
            });
            return true;
        });

        /**
         * If no result was validated, show the error div.
         */
        if (selectedResult === false) {
            this.showSearchErrorDiv();

            return this;
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
     * @returns {Element}
     */
    getSearchErrorDiv : function() {
        if (this.getDeliveryOptions().getOptions().searchErrorDiv) {
            return this.getDeliveryOptions().getOptions().searchErrorDiv;
        }

        return $('search-field-error');
    },

    /**
     * Hide the search error message container.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    hideSearchErrorDiv : function() {
        this.getSearchErrorDiv().hide();

        return this;
    },

    /**
     * Show the search error message.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    showSearchErrorDiv : function() {
        this.getSearchErrorDiv().show();

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
            this.getNearestLocationsRequestObject().transport.abort();
        }

        if (this.getLocationsInAreaRequestObject()) {
            this.getLocationsInAreaRequestObject().transport.abort();
        }

        /**
         * Send a new getNearestLocations request.
         */
        var nearestLocationsRequestObject = new Ajax.PostnlRequest(this.getDeliveryOptions().getLocationsUrl(), {
            method : 'post',
            parameters : {
                lat          : center.lat(),
                long         : center.lng(),
                deliveryDate : this.getDeliveryOptions().getDeliveryDate(),
                isAjax       : true
            },
            onCreate : function() {
                $('locations_loader').show();
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
                $('locations_loader').hide();
            }.bind(this)
        });

        /**
         * Store the request. That way we can abort it if we need to send another request before this one is done.
         */
        this.setNearestLocationsRequestObject(nearestLocationsRequestObject);

        return this;
    },

    /**
     * Search for lolcations inside the maps' viewport. Results will contain up to 20 locations of varying types.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    getLocationsWithinBounds : function() {
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
            this.getLocationsInAreaRequestObject().transport.abort();
        }

        if (this.getNearestLocationsRequestObject()) {
            this.getNearestLocationsRequestObject().transport.abort();
        }

        var locationsInAreaRequestObject = new Ajax.PostnlRequest(this.deliveryOptions.getLocationsInAreaUrl(), {
            method : 'post',
            parameters : {
                northEastLat : northEast.lat(),
                northEastLng : northEast.lng(),
                southWestLat : southWest.lat(),
                southWestLng : southWest.lng(),
                deliveryDate : this.getDeliveryOptions().getDeliveryDate(),
                isAjax       : true
            },
            onCreate : function() {
                $('locations_loader').show();
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
                $('locations_loader').hide();
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
            marker.setShape(this.getMarkerShape());
            marker.setZIndex(markers.length + 1);

            /**
             * Create a new PostNL location object to associate with this marker.
             */
            var parsedLocation = new PostnlDeliveryOptions.Location(
                location,
                this.getDeliveryOptions(),
                location.DeliveryOptions.string
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
            google.maps.event.addListener(marker, "click", this.selectMarker.bind(this, marker, true, true));
            google.maps.event.addListener(marker, "mouseover", this.markerOnMouseOver.bind(this, marker));
            google.maps.event.addListener(marker, "mouseout", this.markerOnMouseOut.bind(this, marker));

            /**
             * Add the marker and the location to the marker and location lists.
             */
            markers.push(marker);
            parsedLocations.push(parsedLocation);
            newLocations.push(parsedLocation);
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
        $$('#scrollbar_content li').each(function(location) {
            location.remove();
        });

        /**
         * Reset the markers array.
         */
        this.setMarkers([]);
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

            location.renderAsMapLocation('scrollbar_content', renderDistance);
        }

        this.recalculateScrollbar();

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
     * @param {google.maps.Marker} marker   The marker to select.
     * @param {boolean}            scrollTo Whether the locations list should scroll to the selected marker's location element.
     * @param {boolean}            panTo    Whether the map should pan to the selected marker.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    selectMarker : function(marker, scrollTo, panTo) {

        /**
         * If the marker is already selected, we don't have to do anything.
         */
        if (this.hasSelectedMarker()
            && this.getSelectedMarker().location
            && this.getSelectedMarker().location.getMapElement().identify() == marker.location.getMapElement().identify()
        ) {
            return this;
        }

        var element = false;
        if (marker.location && marker.location.getMapElement()) {
            element = marker.location.getMapElement();
        }

        /**
         * Update the marker's icon and the marker's location's classname.
         */
        marker.setIcon(this.getMapIconSelected(marker.location));
        marker.setShape(this.getSelectedMarkerShape());

        if (!marker.oldZIndex) {
            marker.oldZIndex = marker.getZIndex();
        }
        marker.setZIndex(this.getMarkers().length + 1);

        if (element && !element.hasClassName('selected')) {
            element.addClassName('selected');
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

        /**
         * Set this marker as the selected marker.
         */
        this.setSelectedMarker(marker);

        /**
         * Pan the map to the marker's position if required.
         */
        if (panTo) {
            this.getMap().panTo(marker.getPosition());
        }

        /**
         * Enable the 'save' button.
         */
        this.getSaveButton().disabled = false;
        if (this.getSaveButton().hasClassName('disabled')) {
            this.getSaveButton().removeClassName('disabled');
        }

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
        marker.location.getMapElement().removeClassName('selected');
        marker.setShape(this.getMarkerShape());
        marker.setZIndex(marker.oldZIndex);
        marker.oldZIndex = false;

        this.setSelectedMarker(false);

        /**
         * Disable the 'save' button.
         */
        this.getSaveButton().disabled = true;
        if (!this.getSaveButton().hasClassName('disabled')) {
            this.getSaveButton().addClassName('disabled');
        }

        return this;
    },

    /**
     * Update the marker's icon on mouseover.
     *
     * @param {google.maps.Marker} marker
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    markerOnMouseOver : function(marker) {
        /**
         * Don't do anything if the map is currently being dragged.
         */
        if (this.getIsBeingDragged()) {
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


    /**
     * Update the marker's icon on mouseout.
     *
     * @param {google.maps.Marker} marker
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    markerOnMouseOut : function(marker) {
        /**
         * Don't do anything if the map is currently being dragged.
         */
        if (this.getIsBeingDragged()) {
            return this;
        }

        /**
         * Only update the marker is it's not the currently selected marker.
         */
        if (!this.getSelectedMarker()
            || this.getSelectedMarker().location.getMapElement().identify() != marker.location.getMapElement().identify()
        ) {
            marker.setZIndex(marker.oldZIndex);
            marker.setIcon(this.getMapIcon(marker.location));
            marker.setShape(this.getMarkerShape());
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
            } else if (elements.PGE) {
                deliveryOptions.selectLocation(elements.PGE);
            } else {
                deliveryOptions.selectLocation(elements.PG);
            }
        }

        /**
         * Close the google maps interface window.
         */
        this.closeAddLocationWindow();

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
        var locationInfoWindow = $('location-info-window');
        var map = this.getMap();
        var mapOptions = this.getMapOptions();

        mapOptions.draggable              = false;
        mapOptions.minZoom                = map.getZoom();
        mapOptions.maxZoom                = map.getZoom();
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

        return this;
    },

    /**
     * Filter visible markers and locations based on currently applied filters.
     *
     * @returns {PostnlDeliveryOptions.Map}
     */
    filter : function() {
        var filterEarly   = this.getFilterEarly();
        var filterEvening = this.getFilterEvening();
        var locations     = this.getLocations();

        locations.each(function(location) {
            if (filterEarly) {
                var type = location.getType();
                if (type.indexOf('PGE') < 0) {
                    location.getMapElement().hide();
                    location.getMarker().setVisible(false);

                    return false;
                }
            }

            if (filterEvening) {
                if (!location.getIsEveningLocation()) {
                    location.getMapElement().hide();
                    location.getMarker().setVisible(false);

                    return false;
                }
            }

            location.getMapElement().show();
            location.getMarker().setVisible(true);
            return true;
        }.bind(this));

        if (this.hasSelectedMarker()) {
            var selectedMarker = this.getSelectedMarker();
            if (!selectedMarker.getVisible()) {
                this.unselectMarker();
            }
        }

        return this;
    },

    /**
     * Recalculcate the scrollbar after the scrollbar contents were changed and make sure the scrollbar stays in the
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
    elements          : [],
    tooltipElement    : null,
    mapElement        : null,

    address           : {},
    distance          : null,
    latitude          : null,
    longitude         : null,
    name              : null,
    openingHours      : null,
    locationCode      : null,

    deliveryOptions   : null,
    type              : [],
    isEveningLocation : false,

    marker            : false,

    oldCenter         : false,

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

    getTooltipElement : function() {
        return this.tooltipElement;
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

    getAddress : function() {
        return this.address;
    },

    getDistance : function() {
        return this.distance;
    },

    getLatitude : function() {
        return this.latitude;
    },

    getLongitude : function() {
        return this.longitude;
    },

    getName : function() {
        return this.name;
    },

    getOpeningHours : function() {
        return this.openingHours;
    },

    getLocationCode : function() {
        return this.locationCode;
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

        var markers = this.getDeliveryOptions().getDeliveryOptionsMap().getMarkers();
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

    setIsEveningLocation : function(isEveningLocation) {
        this.isEveningLocation = isEveningLocation;

        return this;
    },

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
        this.openingHours      = location.OpeningHours;
        this.locationCode      = location.LocationCode.replace(/\s+/g, ''); //remove whitespace from the location code
        this.isEveningLocation = location.isEvening;

        this.deliveryOptions   = deliveryOptions;

        this.type = type;
    },

    /**
     * Render the location and attach it to the supplied parent element.
     *
     * @param {string} parent The parent element's ID.
     *
     * @return {PostnlDeliveryOptions.Location}
     */
    render : function(parent) {
        var elements = {};
        var deliveryDate = this.getDeliveryOptions().getDeliveryDate();
        var date = new Date(
            deliveryDate.substring(6, 10),
            deliveryDate.substring(3, 5) - 1,
            deliveryDate.substring(0, 2)
        );
        var availableDeliveryDate = this.getDeliveryDate(date);

        /**
         * Get the html for this location's header.
         */
        var headerHtml = '';
        headerHtml += '<li class="location">';
        headerHtml += '<div class="bkg">';
        headerHtml += '<div class="bkg">';
        headerHtml += '<div class="content">';
        headerHtml += '<strong class="location-name overflow-protect">' + this.getName() + '</strong>';

        if (this.getType().indexOf('PA') != -1) {
            headerHtml += '<span class="location-type">' + Translator.translate('Package Dispenser') + '</span>';
        } else {
            headerHtml += '<span class="location-type">' + Translator.translate('Post Office') + '</span>';
        }

        headerHtml += '<a class="location-info" id="tooltip_anchor_'
                    + this.getLocationCode()
                    + '">';
        headerHtml += '<span>' + Translator.translate('More Info') + '</span>';
        headerHtml += '</a>';

        headerHtml += this.getTooltipHtml();

        headerHtml += '</div>';
        headerHtml += '</div>';
        headerHtml += '</div>';
        headerHtml += '</li>';

        /**
         * Attach the header to the bottom of the parent element.
         */
        $(parent).insert({
            bottom: headerHtml
        });

        /**
         * Add an element for each of this location's types. Most often this will be a a single element or a PE and PGE
         * element.
         */
        var n = 0;
        this.getType().each(function(type) {
            var id = 'location_' + this.getLocationCode() + '_' + type;

            var optionHtml = '';
            optionHtml += '<li class="option" id="' + id + '">';
            optionHtml += '<div class="bkg">';
            optionHtml += '<div class="bkg">';
            optionHtml += '<div class="content">';
            optionHtml += '<span class="option-dd">';

            /**
             * Only the first element will display the delivery date.
             */
            if (n < 1) {
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
                optionHtml += '<span class="option-time">' + Translator.translate('from') + ' 16:00</span>';
            }

            optionHtml += '<span class="option-comment">' + this.getCommentHtml(type) + '</span>';
            optionHtml += '</div>';
            optionHtml += '</div>';
            optionHtml += '</div>';
            optionHtml += '</li>';

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

            elements[type] = element;
            n++;
        }.bind(this));

        /**
         * Save all newly created elements.
         */
        this.setElements(elements);

        /**
         * Add observers to display the tooltip on mouseover.
         */
        var tooltipElement = $('location_tooltip_' + this.getLocationCode());
        var tooltipAnchor = $('tooltip_anchor_' + this.getLocationCode());

        tooltipAnchor.observe('mouseover', function() {
            tooltipElement.show();
        }.bind(this));

        tooltipAnchor.observe('mouseout', function() {
            tooltipElement.hide();
        }.bind(this));

        this.setTooltipElement(tooltipElement);

        return this;
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

            if (extraCosts) {
                extraCostHtml += ' + ' + extraCosts;
            }

            commentHtml = Translator.translate('early delivery') + extraCostHtml;
        } else if (type == 'PA') {
            commentHtml = '24/7 ' + Translator.translate('available');
        }

        return commentHtml;
    },

    /**
     * Get an available delivery date. This method checks the opening times of this location to make sure the location
     * is open when the order is delivered. If not it will check the day after, and the day after that, and so on.
     *
     * Note that this method is recursive and uses the optional parameter n to prevent infinite loops.
     *
     * @param {Date}   date
     * @param {number} n    The number of tries that have been made to find a valid delivery date.
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
                openingHours = openingDays.Sunday.string;
                break;
            case 1:
                openingHours = openingDays.Monday.string;
                break;
            case 2:
                openingHours = openingDays.Tuesday.string;
                break;
            case 3:
                openingHours = openingDays.Wednesday.string;
                break;
            case 4:
                openingHours = openingDays.Thursday.string;
                break;
            case 5:
                openingHours = openingDays.Friday.string;
                break;
            case 6:
                openingHours = openingDays.Saturday.string;
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

            return this.getDeliveryDate(nextDay, n + 1);
        }

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
        if (address.houseNrExt) {
            addressText += ' ' + address.houseNrExt;
        }
        addressText += '  ' + Translator.translate('in') + ' ' + address.City;

        var html = '<div class="tooltip" id="location_tooltip_' + this.getLocationCode() + '" style="display:none;">';
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
     * Gets html for this location's business hours.
     *
     * Html will be formatted as tr and td elements. This method expects the calling method to provide a container
     * table.
     *
     * @returns {string}
     */
    getOpeningHoursHtml : function() {
        /**
         * Add the opening hours for every day of the week.
         */
        var openingHours = this.getOpeningHours();
        var closedText = Translator.translate('Closed');

        /**
         * Monday
         */
        var html = '<tr>';
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
     * @param {string} parent
     *
     * @returns {PostnlDeliveryOptions.Location}
     */
    renderAsMapLocation : function(parent, renderDistance) {
        var address = this.getAddress();

        /**
         * Format the address.
         */
        var addressText = address.Street + ' ' + address.HouseNr;
        if (address.HouseNrExt) {
            addressText += ' ' + address.HouseNrExt;
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
        html += '<a class="location-info" id="'
              + id
              + '-info">'
              + Translator.translate('business hours')
              + '</a>';
        html += '</div>';
        html += '</li>';

        /**
         * Attach the location to the bottom of the parent element.
         */
        $(parent).insert({
            bottom: html
        });

        var element = $(id);

        /**
         * Add observers to this element.
         */
        element.observe('click', function(event) {
            event.stop();

            if (Event.element(event).hasClassName('location-info')) {
                return this;
            }

            if (!this.getMarker()) {
                return false;
            }

            this.setOldCenter(this.getMarker().getPosition());

            this.getDeliveryOptions().getDeliveryOptionsMap().selectMarker(this.getMarker(), false, true, event);
            return true;
        }.bind(this));

        element.observe('mouseover', function() {
            var map = this.getDeliveryOptions().getDeliveryOptionsMap();
            if (map.getIsBeingDragged()) {
                return this;
            }

            if (!this.getMarker()) {
                return false;
            }

            this.setOldCenter(map.map.getCenter());
            map.map.panTo(this.getMarker().getPosition());

            google.maps.event.trigger(this.getMarker(), 'mouseover');
            return true;
        }.bind(this));

        element.observe('mouseout', function() {
            var map = this.getDeliveryOptions().getDeliveryOptionsMap();
            if (map.getIsBeingDragged()) {
                return this;
            }

            if (!this.getMarker()) {
                return false;
            }

            if (this.getOldCenter()) {
                map.map.setCenter(this.getOldCenter());
                this.setOldCenter(false);
            }

            google.maps.event.trigger(this.getMarker(), 'mouseout');
            return true;
        }.bind(this));

        var infoElement = $(id + '-info');
        infoElement.observe('click', function() {
            /**
             * If the location info window already has this location's info, close it instead.
             */
            var infoWindow = $('location-info-window');
            if (infoWindow.getAttribute('data-locationcode')
                && infoWindow.getAttribute('data-locationcode') == this.getLocationCode()
            ) {
                this.deliveryOptions.getDeliveryOptionsMap().closeLocationInfoWindow();

                return this;
            }


            this.deliveryOptions.getDeliveryOptionsMap().openLocationInfoWindow(
                this.getMapTooltipHtml(),
                this.getLocationCode()
            )
        }.bind(this));

        this.setMapElement(element);

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
        if (address.houseNrExt) {
            addressText += ' ' + address.houseNrExt;
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
        this.type = timeframe.TimeframeType;

        this.timeframeIndex = timeframeIndex;

        this.deliveryOptions = deliveryOptions;
    },

    /**
     * Render this timeframe as a new html element.
     *
     * @param {string} parent The parent element's ID to which we will attach this element.
     *
     * @returns {PostnlDeliveryOptions.Timeframe}
     */
    render : function(parent) {
        /**
         * Build the element's html.
         */
        var html = '<li class="option" id="timeframe_' + this.getTimeframeIndex() + '">';
        html += '<div class="bkg">';
        html += '<div class="bkg">';
        html += '<div class="content">';
        html += '<span class="option-dd">';

        /**
         * Add the day of the week on which this timeframe is available.
         */
        html += this.getWeekdayHtml();

        html += '</span>';
        html += '<span class="option-radio"></span>';
        html += '<span class="option-time">'
              + this.getFrom().substring(0, 5)
              + ' - '
              + this.getTo().substring(0, 5)
              + '</span>';

        /**
         * Add an optional comment to the timeframe.
         */
        html += this.getCommentHtml();

        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</li>';

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
     * Get an optional comment for this timeframe.
     *
     * @returns {string}
     */
    getCommentHtml : function() {
        var comment = '';
        if (this.type == 'Avond') {
            var extraCosts = this.getDeliveryOptions().getOptions().eveningFeeText;
            var extraCostHtml = '';

            if (extraCosts) {
                extraCostHtml += ' + ' + extraCosts;
            }

            comment = '<span class="option-comment">' + Translator.translate('evening') + extraCostHtml + '</span>';
        }

        return comment;
    },

    /**
     * Get the day of the week on which this timeframe is available.
     *
     * @returns {string}
     */
    getWeekdayHtml : function() {
        var date = new Date(this.date.substring(6, 10), this.date.substring(3, 5) - 1, this.date.substring(0, 2));

        var datesProcessed = this.getDeliveryOptions().getDatesProcessed();
        var weekdayHtml = '';
        if (datesProcessed.indexOf(date.getTime()) == -1) {
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

if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(obj, start) {
         for (var i = (start || 0), j = this.length; i < j; i++) {
             if (this[i] === obj) { return i; }
         }
         return -1;
    }
}