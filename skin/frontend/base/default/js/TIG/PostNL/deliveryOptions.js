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
 * PostNL delivery options logic class.
 *
 * Uses AJAX to communicate with PostNL and retrieve possible delivery options. This class also manages all available options.
 */
PostnlDeliveryOptions = new Class.create({
    eveningFee : '',
    expressFee : '',

    pgLocation  : false,
    pgeLocation : false,
    paLocation  : false,

    timeframes : false,
    locations  : false,

    selectedOption : false,

    initialize : function(timeframesUrl, locationsUrl, locationsInAreaUrl, postcode, housenumber, deliveryDate, options) {
        this.timeframesUrl      = timeframesUrl;
        this.locationsUrl       = locationsUrl;
        this.locationsInAreaUrl = locationsInAreaUrl;
        this.postcode           = postcode;
        this.housenumber        = housenumber;
        this.deliveryDate       = deliveryDate;

        this.options = {};
        if (options) {
            this.options = options;
        }

        var weekdays = new Array(7);
        weekdays[0] = Translator.translate('Su');
        weekdays[1] = Translator.translate('Mo');
        weekdays[2] = Translator.translate('Tu');
        weekdays[3] = Translator.translate('We');
        weekdays[4] = Translator.translate('Th');
        weekdays[5] = Translator.translate('Fr');
        weekdays[6] = Translator.translate('Sa');

        this.weekdays = weekdays;
        this.datesProcessed = [];

        return this;
    },

    getMapIcon : function() {
        if (this.options.mapIcon) {
            return this.options.mapIcon;
        }

        return '';
    },

    getMapIconSelected : function() {
        if (this.options.mapIconSelected) {
            return this.options.mapIconSelected;
        }

        return '';
    },

    /**
     * Start the delivery options functionality by retrieving possible delivery options from PostNL.
     *
     * @return PostnlDeliveryOptions
     */
    showOptions : function() {
        this.getTimeframes(this.postcode, this.housenumber, this.deliveryDate);
        this.getLocations(this.postcode, this.housenumber, this.deliveryDate);

        this.deliveryOptionsMap = new PostnlDeliveryOptions.Map(this.fullAddress, this);

        return this;
    },

    /**
     * Get all possible delivery timeframes for a specified postcode, housenumber and delivery date.
     *
     * @param postcode
     * @param housenumber
     * @param deliveryDate
     *
     * @return PostnlDeliveryOptions
     */
    getTimeframes : function(postcode, housenumber, deliveryDate) {
        new Ajax.PostnlRequest(this.timeframesUrl,{
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

        return this;
    },

    parseTimeframes : function(timeframes) {
        var parsedTimeframes = [];

        for(var n = 0, o = 0, l = timeframes.length; n < l; n++) {
            if (o >= 1 && !this.options.allowTimeframes) {
                break;
            }

            var currentTimeframe = timeframes[n];

            for (i = 0, m = currentTimeframe.Timeframes.TimeframeTimeFrame.length; i < m ; i++, o++) {
                var currentSubTimeframe = currentTimeframe.Timeframes.TimeframeTimeFrame[i];
                if (this.options.allowEveningTimeframes === false
                    && currentSubTimeframe.TimeframeType == 'Avond'
                ) {
                    continue;
                }

                var postnlTimeframe = new PostnlDeliveryOptions.Timeframe(currentTimeframe.Date, currentSubTimeframe, o, this);

                parsedTimeframes.push(postnlTimeframe);
            }
        }

        this.timeframes = parsedTimeframes;

        return this;
    },

    renderTimeframes : function() {
        $$('#timeframes li.option').each(function(element) {
            element.remove();
        });

        this.timeframes.each(function(timeframe) {
            timeframe.render('timeframes');
        });

        this.timeframes[0].select();

        return this;
    },

    showDefaultTimeframe : function() {
        var fakeTimeframe = {
            From          : '09:00:00',
            To            : '18:00:00',
            TimeframeType : 'Overdag'
        };

        var postnlTimeframe = new PostnlDeliveryOptions.Timeframe(this.deliveryDate, fakeTimeframe, 0, this);
        this.timeframes = new Array(postnlTimeframe);

        this.renderTimeframes();

        return this;
    },

    selectTimeframe : function(element) {
        if (!element) {
            return this;
        }

        var timeframes = this.timeframes;

        timeframes.each(function(timeframe) {
            if (element && timeframe.element.identify() == element.identify()) {
                this.selectedOption = timeframe;
                timeframe.select();
            } else {
                timeframe.unSelect();
            }
        });

        this.unSelectLocation();

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
     * @param postcode
     * @param housenumber
     * @param deliveryDate
     *
     * @return PostnlDeliveryOptions
     */
    getLocations : function(postcode, housenumber, deliveryDate) {
        new Ajax.PostnlRequest(this.locationsUrl,{
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
        if (this.deliveryOptionsMap) {
            this.deliveryOptionsMap.addMarkers(locations);
        }

        /**
         * Parse and render the result.
         */
        this.parseLocations(locations)
            .renderLocations();

        return this;
    },

    /**
     * Parse PostNL delivery locations. We need to filter out unneeded locations so we only end up with the ones closest to the
     * chosen postcode and housenumber.
     *
     * @param locations.
     *
     * @return PostnlDeliveryOptions
     */
    parseLocations : function(locations) {
        var processedPG = false;
        var processedPGE = false;
        var processedPA = false;
        var processedLocations = [];

        var options = this.options;

        for(var n = 0, l = locations.length; n < l; n++) {
            /**
             * If we already have a PakjeGemak, PakjeGemak Express and parcel dispenser location, we're finished and can ignore
             * the remaining locations.
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
            var postnlLocation = new PostnlDeliveryOptions.Location(locations[n], n, this, type);
            processedLocations.push(postnlLocation);

            if (
                (options.allowPg && !processedPG && type.indexOf('PG') != -1)
                && (options.allowPge && !processedPGE && type.indexOf('PGE') != -1)
            ) {

                /**
                 * Register this location as the chosen PGE location.
                 */
                this.pgeLocation = postnlLocation;
                this.pgLocation  = false;

                processedPGE = true;
                processedPG  = true;
                continue;
            }

            /**
             * If we can add a PGE location, we don't already have a PGE location and this is a PGE location; add it as the chosen
             * PGE location.
             */
            if (options.allowPge && !processedPGE && type.indexOf('PGE') != -1) {
                /**
                 * Register this location as the chosen PGE location.
                 */
                this.pgeLocation = postnlLocation;
                processedPGE     = true;
                continue;
            }

            /**
             * If we can add a PG location, we don't already have a PG location and this is a PG location; add it as the chosen
             * PG location.
             */
            if (options.allowPg && !processedPG && type.indexOf('PG') != -1) {
                /**
                 * Register this location as the chosen PG location.
                 */
                this.pgLocation = postnlLocation;
                processedPG     = true;
                continue;
            }

            /**
             * If we can add a PA location, we don't already have a PA location and this is a PA location; add it as the chosen
             * PA location.
             *
             * N.B. that a single location can be used as both PG, PGE and PA.
             */
            if (options.allowPa && !processedPA && type.indexOf('PA') != -1) {
                /**
                 * Register this location as the chosen PA location.
                 */
                this.paLocation = postnlLocation;
                processedPA     = true;
            }
        }

        this.locations = processedLocations;

        return this;
    },

    renderLocations : function() {
        var pickUpList = $('postnl_pickup');
        pickUpList.show();

        $$('#pgelocation li').each(function(element) {
            element.remove();
        });
        $$('#pglocation li').each(function(element) {
            element.remove();
        });
        $$('#palocation li').each(function(element) {
            element.remove();
        });

        if (this.options.allowPge && this.pgeLocation) {
            this.pgeLocation.render('pgelocation');
        }

        if (this.options.allowPg && this.pgLocation) {
            this.pgLocation.render('pglocation');
        }

        if (this.options.allowPa && this.paLocation) {
            this.paLocation.render('palocation');
        }

        if (!this.pgeLocation && !this.pgLocation && !this.paLocation) {
            pickUpList.hide();
        }

        return this;
    },

    hideLocations : function() {
        $('postnl_pickup').hide();

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
                    this.selectedOption = location;
                    location.select(index);
                } else {
                    location.unSelect(index);
                }
            }

            return true;
        });

        this.unSelectTimeframe();

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
    }
});

PostnlDeliveryOptions.Map = new Class.create({
    map             : false,
    deliveryOptions : false,
    fullAddress     : '',

    isBeingDragged : false,

    markers              : [],
    locations            : [],
    selectedMarker       : false,
    searchLocationMarker : false,

    nearestLocationsRequestObject : false,
    locationsInAreaRequestObject  : false,

    /******************************
     *                            *
     *  GETTER AND SETTER METHODS *
     *                            *
     ******************************/

    getMap : function() {
        return this.map;
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
        if (markers.length > 0) {
            return true;
        }

        return false;
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
        if (locations.length > 0) {
            return true;
        }

        return false;
    },

    getSelectedMarker : function() {
        return this.selectedMarker;
    },

    setSelectedMarker : function(marker) {
        this.selectedMarker = marker;

        return this;
    },

    hasSelectedMarker : function() {
        if (!this.getSelectedMarker()) {
            return false;
        }

        return true;
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

    /**
     * Constructor method.
     * Creates the google maps object and triggers an initial address search based on the user's chosen shipping
     * address.
     *
     * @param fullAddress
     * @param deliveryOptions
     */
    initialize : function(fullAddress, deliveryOptions) {
        this.deliveryOptions = deliveryOptions;
        this.fullAddress = fullAddress;

        var mapOptions = this.getMapOptions();

        this.map = new google.maps.Map($('map-div'), mapOptions);

        if (this.hasMarkers()) {
            this.getMarkers().each(function(marker) {
                marker.setMap(null);
            });

            this.setMarkers([]);
        }

        this.searchAndPanToAddress(this.getFullAddress(), true, false);

        this.registerObservers();
    },

    /**
     * Gets an option object for the google maps object.
     *
     * @returns object
     */
    getMapOptions : function() {
        var myStyles = [
            {
                featureType : 'poi',
                elementType : 'labels',
                stylers     : [
                    { visibility : 'off'}
                ]
            }
        ];

        var zoomControlOptions = {
            style    : google.maps.ZoomControlStyle.SMALL,
            position : google.maps.ControlPosition.LEFT_TOP
        };

        var mapOptions = {
            zoom               : 16,
            minZoom            : 12,
            maxZoom            : 18,
            center             : new google.maps.LatLng(52.3702157, 4.895167899999933), //Amsterdam
            mapTypeId          : google.maps.MapTypeId.ROADMAP,
            styles             : myStyles,
            panControl         : false,
            mapTypeControl     : false,
            scaleControl       : false,
            streetViewControl  : false,
            overviewMapControl : false,
            zoomControl        : true,
            zoomControlOptions : zoomControlOptions
        };

        return mapOptions;
    },

    /**
     * Register observers for the google maps interface window.
     *
     * @returns PostnlDeliveryOptions.Map
     */
    registerObservers : function () {
        var map = this.getMap();

        /**
         * Register observers for the google maps interface window.
         */
        $('custom_location').observe('click', this.openAddLocationWindow.bind(this));
        $('close_popup').observe('click', this.closeAddLocationWindow.bind(this));
        $('search-button').observe('click', this.addressSearch.bind(this));
        $('search-field').observe('keydown', this.addressSearch.bind(this));
        $('location_save').observe('click', this.saveLocation.bind(this));

        $$('.location-option-checkbox').each(function(element) {
            element.observe('click', function() {
                this.toggleClassName('selected');
            });
        });

        /**
         * Register observers specific for the google map.
         */
        google.maps.event.addListener(map, 'zoom_changed', function() {
            this.getLocationsWithinBounds();
        }.bind(this));

        google.maps.event.addListener(map, 'dragstart', function() {
            this.isBeingDragged = true;
        }.bind(this));

        google.maps.event.addListener(map, 'dragend', function() {
            this.isBeingDragged = false;

            this.getLocationsWithinBounds();
        }.bind(this));

        return this;
    },

    /**
     * Get the goolgle maps interface window element.
     *
     * @returns element
     */
    getAddLocationWindow : function() {
        if (this.getDeliveryOptions().options && this.getDeliveryOptions().options.addLocationWindow) {
            var addLocationWindow = this.getDeliveryOptions().options.addLocationWindow;
            if (typeof addLocationWindow == 'string') {
                return $(addLocationWindow);
            }

            return addLocationWindow;
        }

        return $('postnl_add_location');
    },

    /**
     * Open the google maps interface window.
     *
     * @param event
     *
     * @returns PostnlDeliveryOptions.Map
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

        return this;
    },

    /**
     * Close the google maps interface window.
     *
     * @param event
     *
     * @returns PostnlDeliveryOptions.Map
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
     * @param event
     *
     * @returns PostnlDeliveryOptions.Map
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
     * @param address      The address to search for.
     * @param addMarker    Whether to add a marker to the address's position.
     * @param getLocations Whether to search for nearby locations.
     *
     * @returns PostnlDeliveryOptions.Map
     */
    searchAndPanToAddress : function(address, addMarker, getLocations) {
        this.geocode(address, this.panMapToAddress.bind(this, addMarker, getLocations), this.showSearchErrorDiv);

        return this;
    },

    /**
     * Geocode an address and then trigger the success- or failurecallback.
     *
     * @param address
     * @param successCallback
     * @param failureCallback
     *
     * @return void
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
     * @param addMarker
     * @param getLocations
     * @param results
     *
     * @returns PostnlDeliveryOptions.Map
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
            });
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
        if (map.getZoom() < 15) {
            map.setZoom(15);
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
                position: latlng,
                map: map,
                title: selectedResult.formatted_address,
                draggable: false
            });

            this.setSearchLocationMarker(searchLocationMarker);
        }

        return this;
    },

    /**
     * Get the element containing the search error message.
     *
     * @returns element
     */
    getSearchErrorDiv : function() {
        if (this.getDeliveryOptions().options.searchErrorDiv) {
            return this.getDeliveryOptions().options.searchErrorDiv;
        }

        return $('search-field-error');;
    },

    /**
     * Hide the search error message container.
     *
     * @returns PostnlDeliveryOptions.Map
     */
    hideSearchErrorDiv : function() {
        this.getSearchErrorDiv().hide();

        return this;
    },

    /**
     * Show the search error message.
     *
     * @returns PostnlDeliveryOptions.Map
     */
    showSearchErrorDiv : function() {
        this.getSearchErrorDiv().show();

        return this;
    },

    /**
     * Search for nearby locations. Search is based on the current center of the map and the provided delivery date. The
     * result will contain up to 20 locations of varying types.
     *
     * @returns PostnlDeliveryOptions.Map
     */
    getNearestLocations : function() {
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

        /**
         * Send a new getNearestLocations request.
         */
        var nearestLocationsRequestObject = new Ajax.PostnlRequest(this.getDeliveryOptions().locationsUrl, {
            method : 'post',
            parameters : {
                lat          : center.lat(),
                long         : center.lng(),
                deliveryDate : this.getDeliveryOptions().deliveryDate,
                isAjax       : true
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
                this.setNearestLocationsRequestObject(false);
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
     * @returns PostnlDeliveryOptions.Map
     */
    getLocationsWithinBounds : function() {
        var map = this.map;

        /**
         * Get the bounds of the map. These will be a set of NE and SW coordinates.
         */
        var bounds = map.getBounds();
        var northEast = bounds.getNorthEast();
        var southWest = bounds.getSouthWest();

        if (this.getLocationsInAreaRequestObject()) {
            this.getLocationsInAreaRequestObject().transport.abort();
        }

        var locationsInAreaRequestObject = new Ajax.PostnlRequest(this.deliveryOptions.locationsInAreaUrl, {
            method : 'post',
            parameters : {
                northEastLat : northEast.lat(),
                northEastLng : northEast.lng(),
                southWestLat : southWest.lat(),
                southWestLng : southWest.lng(),
                deliveryDate : this.deliveryOptions.deliveryDate,
                isAjax       : true
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
            onFailure : function(response) {
                return false;
            },
            onComplete : function() {
                this.setLocationsInAreaRequestObject(false);
            }.bind(this)
        });

        this.setLocationsInAreaRequestObject(locationsInAreaRequestObject);

        return this;
    },

    /**
     * Add markers for an array of locations.
     *
     * @param locations
     *
     * @returns PostnlDeliveryOptions.Map
     */
    addMarkers : function(locations) {
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
             * Get the position and title of the new marker.
             */
            var markerLatLng = new google.maps.LatLng(location.Latitude, location.Longitude);
            var markerTitle = location.Name + ', ' + location.Address.Street + ' ' + location.Address.HouseNr;
            if (location.Address.HouseNrExt) {
                markerTitle += ' ' + location.Address.HouseNrExt;
            }

            /**
             * Add the new marker.
             */
            var marker = new google.maps.Marker({
                position  : markerLatLng,
                map       : this.map,
                title     : markerTitle,
                animation : google.maps.Animation.DROP,
                draggable : false,
                clickable : true,
                icon      : this.getDeliveryOptions().getMapIcon()
            });

            /**
             * Create a new PostNL location object to associate with this marker.
             */
            var parsedLocation = new PostnlDeliveryOptions.Location(
                location,
                parsedLocations.length + 1,
                this.deliveryOptions,
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

        this.setLocations(parsedLocations);
        this.setMarkers(markers);

        /**
         * Render the locations.
         */
        this.renderLocations(newLocations);

        /**
         * If no marker has been selected, select the first marker.
         */
        if (!this.hasSelectedMarker()) {
            this.selectMarker(markers[0], false, false);
        }

        return this;
    },

    /**
     * Removes all markers.
     *
     * @returns PostnlDeliveryOptions.Map
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
        $$('#map-locations li').each(function(location) {
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
     * @param locations
     *
     * @returns PostnlDeliveryOptions.Map
     */
    renderLocations : function(locations) {
        for (var i = 0; i < locations.length; i++) {
            var location = locations[i];

            location.renderAsMapLocation('map-locations');
        }

        return this;
    },

    /**
     * Checks if a mark already exists for a specified location.
     *
     * @param location
     *
     * @returns boolean
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
     * Trigger the google maps resize event. This prevents sizing errors when the map has been initialized in a hidden
     * div.
     *
     * @returns PostnlDeliveryOptions.Map
     */
    triggerResize : function() {
        var map = this.getMap();

        /**
         * Make sure the map keeps it's previous center.
         */
        var center = map.getCenter();

        google.maps.event.trigger(map, "resize");

        map.setCenter(center);

        /**
         * Look for new locations within the map's new bounds.
         */
        this.getLocationsWithinBounds();

        return this;
    },

    /**
     * Select a marker.
     *
     * @param marker   The marker to select.
     * @param scrollTo Whether the locations list should scroll to the selected marker's location element.
     * @param panTo    Whether the map should pan to the selected marker.
     *
     * @returns PostnlDeliveryOptions.Map
     */
    selectMarker : function(marker, scrollTo, panTo) {
        /**
         * If the marker is already selected, we don't have to do anything.
         */
        if (this.hasSelectedMarker()
            && this.getSelectedMarker().location.mapElement.identify() == marker.location.mapElement.identify()
        ) {
            return this;
        }

        /**
         * Update the marker's icon and the marker's location's classname.
         */
        marker.setIcon(this.getDeliveryOptions().getMapIconSelected());
        if (!marker.location.mapElement.hasClassName('selected')) {
            marker.location.mapElement.addClassName('selected');
        }

        /**
         * If required, scroll to the marker's location in the locations list.
         */
        if (scrollTo) {
            var locationsList = $('map-locations');
            locationsList.scrollTop = marker.location.mapElement.offsetTop - locationsList.offsetTop - 36;
        }

        /**
         * If we already had a selected marker, update it's icon and it's location's class name.
         */
        if (this.hasSelectedMarker()) {
            this.getSelectedMarker().setIcon(this.getDeliveryOptions().getMapIcon());
            this.getSelectedMarker().location.mapElement.removeClassName('selected');
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

        return this;
    },

    /**
     * Update the marker's icon on mouseover.
     *
     * @param marker
     *
     * @returns PostnlDeliveryOptions.Map
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
            || this.getSelectedMarker().location.mapElement.identify() != marker.location.mapElement.identify()
        ) {
            marker.setIcon(this.getDeliveryOptions().getMapIconSelected());
        }

        return this;
    },


    /**
     * Update the marker's icon on mouseout.
     *
     * @param marker
     *
     * @returns PostnlDeliveryOptions.Map
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
            || this.getSelectedMarker().location.mapElement.identify() != marker.location.mapElement.identify()
            ) {
            marker.setIcon(this.getDeliveryOptions().getMapIcon());
        }

        return this;
    },

    /**
     * Save a selected location as a new pickup location.
     *
     * @returns PostnlDeliveryOptions.Map
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
        for(var index in elements) {
            if (!elements.hasOwnProperty(index)) {
                continue;
            }

            var locationElement = elements[index];
            deliveryOptions.selectLocation(locationElement);
            break;
        }

        /**
         * Close the google maps interface window.
         */
        this.closeAddLocationWindow();

        return this;
    }
});

/**
 * A PostNL PakjeGemak, PakjeGemak Express or parcel dispenser location. Contains address information, opening hours,
 * the type of location and any html elements associated to this location.
 */
PostnlDeliveryOptions.Location = new Class.create({
    elements        : [],
    mapElement      : null,

    address         : {},
    distance        : null,
    latitude        : null,
    longitude       : null,
    name            : null,
    openingHours    : null,

    locationIndex   : 0,
    deliveryOptions : null,
    type            : [],

    marker          : false,

    oldCenter       : false,

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

    getLocationIndex : function() {
        return this.locationIndex;
    },

    getDeliveryOptions : function() {
        return this.deliveryOptions;
    },

    getType : function() {
        return this.type;
    },

    getOptions : function() {
        return this.getDeliveryOptions().options;
    },

    getMarker : function() {
        return this.marker;
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

    /**
     * Constructor method.
     *
     * @param location        The PostNL location JSON object returned by PostNL's webservices associated with this
     *                        location.
     * @param locationIndex   This location's index among all locations currently stored by the deliveryOptions object.
     * @param deliveryOptions The current deliveryOptions object with which this location is associated.
     * @param type            An array of PostNL location types. possible options include PE, PGE and PA.
     */
    initialize : function(location, locationIndex, deliveryOptions, type) {
        this.address      = location.Address;
        this.distance     = location.Distance;
        this.latitude     = location.Latitude;
        this.longitude    = location.Longitude;
        this.name         = location.Name;
        this.openingHours = location.OpeningHours;

        this.locationIndex = locationIndex;

        this.deliveryOptions = deliveryOptions;

        this.type = type;
    },

    /**
     * Render the location and attach it to the supplied parent element.
     *
     * @param parent The parent element. May either be an element object or an element's id.
     *
     * @return PostnlDeliveryOptions.Location
     */
    render : function(parent) {
        var elements = {};
        var deliveryDate = this.getDeliveryOptions().deliveryDate;
        var date = new Date(
            deliveryDate.substring(6, 10),
            deliveryDate.substring(3, 5) - 1,
            deliveryDate.substring(0, 2)
        );
        console.log(date);
        var availableDeliveryDate = this.getDeliveryDate(date);
        console.log(availableDeliveryDate);
        /**
         * Get the html for this location's header.
         */
        var headerHtml = '';
        headerHtml += '<li class="location">';
        headerHtml += '<span class="bkg">';
        headerHtml += '<span class="bkg">';
        headerHtml += '<div class="content">';
        headerHtml += '<strong class="location-name overflow-protect">' + this.getName() + '</strong>';

        if (this.getType().indexOf('PA') != -1) {
            headerHtml += '<span class="location-type">' + Translator.translate('Package Dispenser') + '</span>';
        } else {
            headerHtml += '<span class="location-type">' + Translator.translate('Post Office') + '</span>';
        }

        headerHtml += '<a href="javascript:void(0);" class="location-info">';
        headerHtml += '<span>' + Translator.translate('More Info') + '</span>';
        headerHtml += this.getTooltipHtml();
        headerHtml += '</a>';
        headerHtml += '</div>';
        headerHtml += '</span>';
        headerHtml += '</span>';
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
            var id = 'location_' + this.getLocationIndex() + '_' + type;

            var optionHtml = '';
            optionHtml += '<li class="option" id="' + id + '">';
            optionHtml += '<a href="#">';
            optionHtml += '<span class="bkg">';
            optionHtml += '<span class="bkg">';
            optionHtml += '<div class="content">';
            optionHtml += '<span class="option-dd">';

            /**
             * Only the first element will display the delivery date.
             */
            if (n < 1) {
                optionHtml += '<strong class="option-day">'
                            + this.getDeliveryOptions().weekdays[availableDeliveryDate.getDay()]
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
            optionHtml += '</span>';
            optionHtml += '</span>';
            optionHtml += '</a>';
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

        return this;
    },

    /**
     * Gets the comment html for this location. The comment contains any additional fees incurred by choosing this option and, in
     * the case of a parcel dispenser location, the fact that it is available 24/7.
     *
     * @param type
     *
     * @return string
     */
    getCommentHtml : function(type) {
        var commentHtml = '';

        /**
         * Additional fees may only be charged for PakjeGemak Express locations.
         */
        if (type == 'PGE') {
            var extraCosts = this.getOptions().expressFee;
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
     * @param date
     * @param n    The number of tries that have been made to find a valid delivery date.
     *
     * @returns Date
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
     * @return string
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

        var html = '<div class="tooltip">';
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

        /**
         * Add the opening hours for every day of the week.
         */
        var openingHours = this.getOpeningHours();
        var closedText = Translator.translate('Closed');

        /**
         * Monday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Mo') + '</th>';
        if (openingHours.Monday && openingHours.Monday.string && openingHours.Monday.string.join(', ') != '') {
            html += '<td>' + openingHours.Monday.string.join(', ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Tuesday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Tu') + '</th>';
        if (openingHours.Tuesday && openingHours.Tuesday.string && openingHours.Tuesday.string.join(', ') != '') {
            html += '<td>' + openingHours.Tuesday.string.join(', ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Wednesday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('We') + '</th>';
        if (openingHours.Wednesday && openingHours.Wednesday.string && openingHours.Wednesday.string.join(', ') != '') {
            html += '<td>' + openingHours.Wednesday.string.join(', ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Thursday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Th') + '</th>';
        if (openingHours.Thursday && openingHours.Thursday.string && openingHours.Thursday.string.join(', ') != '') {
            html += '<td>' + openingHours.Thursday.string.join(', ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Friday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Fr') + '</th>';
        if (openingHours.Friday && openingHours.Friday.string && openingHours.Friday.string.join(', ') != '') {
            html += '<td>' + openingHours.Friday.string.join(', ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Saturday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Sa') + '</th>';
        if (openingHours.Saturday && openingHours.Saturday.string && openingHours.Saturday.string.join(', ') != '') {
            html += '<td>' + openingHours.Saturday.string.join(', ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

        /**
         * Sunday
         */
        html += '<tr>';
        html += '<th>' + Translator.translate('Su') + '</th>';
        if (openingHours.Sunday && openingHours.Sunday.string && openingHours.Sunday.string.join(', ') != '') {
            html += '<td>' + openingHours.Sunday.string.join(', ') + '</td>';
        } else {
            html += '<td>' + closedText + '</td>';
        }
        html += '</tr>';

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
     * Render this location as a map element. Map elements appear in a list below the google maps interface.
     *
     * @param parent
     *
     * @returns PostnlDeliveryOptions.Location
     */
    renderAsMapLocation : function(parent) {
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
        if (distance < 1000 && distance > 0) {
            distanceText = distance + ' m';
        } else if (distance > 0) {
            distanceText = parseFloat(Math.round(distance / 100) / 10).toFixed(1) + ' km';
        }

        var id = 'map-location_' + this.getLocationIndex();

        /**
         * Build the element's html.
         */
        var html = '<li class="location" id="' + id + '">';
        html += '<div class="content">';

        /**
         * @todo implement the proper image for each element.
         */
        html += '<img src="http://newdev.tigpostnl.nl/skin/frontend/enterprise/default/images/TIG/PostNL/deliveryoptions/tmp_ah.png" class="location-icon" alt="Albert Heijn" />';
        html += '<span class="overflow-protect">';
        html += '<strong class="location-name">' + this.getName() + '</strong>';
        html += '<span class="location-address">' + addressText + '</span>';
        html += '</span>';
        html += '<span class="location-distance">' + distanceText + '</span>';
        html += '<a href="javascript:void(0);" class="location-info">' + Translator.translate('business hours') + '</a>';
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

            if (!this.getMarker()) {
                return false;
            }

            this.setOldCenter(this.getMarker().getPosition());

            this.getDeliveryOptions().deliveryOptionsMap.selectMarker(this.getMarker(), false, true, event);
            return true;
        }.bind(this));

        element.observe('mouseover', function() {
            var map = this.getDeliveryOptions().deliveryOptionsMap;
            if (map.isBeingDragged) {
                return this;
            }

            if (!this.getMarker()) {
                return false;
            }

            if (!map.map.getBounds().contains(this.getMarker().getPosition())) {
                this.setOldCenter(map.map.getCenter());
                map.map.setCenter(this.getMarker().getPosition());
            }

            google.maps.event.trigger(this.getMarker(), 'mouseover');
            return true;
        }.bind(this));

        element.observe('mouseout', function() {
            var map = this.getDeliveryOptions().deliveryOptionsMap;
            if (map.isBeingDragged) {
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

        this.setMapElement(element);

        return this;
    },

    /**
     * Select an element by adding the 'active' class.
     *
     * @return PostnlDeliveryOptions.Option
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
     * @return PostnlDeliveryOptions.Option
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
    element : false,

    initialize : function(date, timeframe, timeframeIndex, deliveryOptions) {
        this.date = date;
        this.from = timeframe.From;
        this.to   = timeframe.To;
        this.type = timeframe.TimeframeType;

        this.timeframeIndex = timeframeIndex;

        this.deliveryOptions = deliveryOptions;
    },

    render : function(parent) {
        var html = '<li class="option" id="timeframe_' + this.timeframeIndex + '">';
        html += '<a href="#">';
        html += '<span class="bkg">';
        html += '<span class="bkg">';
        html += '<div class="content">';
        html += '<span class="option-dd">';
        html += this.getWeekdayHtml();
        html += '</span>';
        html += '<span class="option-radio"></span>';
        html += '<span class="option-time">' + this.from.substring(0, 5) + ' - ' + this.to.substring(0, 5) + '</span>';
        html += this.getCommentHtml();
        html += '</div>';
        html += '</span>';
        html += '</span>';
        html += '</a>';
        html += '</li>';

        $(parent).insert({
            bottom: html
        });

        var element = $('timeframe_' + this.timeframeIndex);
        element.observe('click', function(element, event) {
            event.stop();

            if (element.hasClassName('active')) {
                return true;
            }

            this.deliveryOptions.selectTimeframe(element);
            return true;
        }.bind(this, element));

        this.element = element;

        return this;
    },

    getCommentHtml : function() {
        var comment = '';
        if (this.type == 'Avond') {
            var extraCosts = this.deliveryOptions.options.eveningFee;
            var extraCostHtml = '';

            if (extraCosts) {
                extraCostHtml += ' + ' + extraCosts;
            }

            comment = '<span class="option-comment">' + Translator.translate('evening') + extraCostHtml + '</span>';
        }

        return comment;
    },

    getWeekdayHtml : function() {
        var date = new Date(this.date.substring(6, 10), this.date.substring(3, 5) - 1, this.date.substring(0, 2));

        var datesProcessed = this.deliveryOptions.datesProcessed;
        var weekdayHtml = '';
        if (datesProcessed.indexOf(date.getTime()) == -1) {
            var weekdays = this.deliveryOptions.weekdays;

            this.deliveryOptions.datesProcessed.push(date.getTime());
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
     * @return PostnlDeliveryOptions.Option
     */
    select : function() {
        var element = this.element;
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
     * @return PostnlDeliveryOptions.Option
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