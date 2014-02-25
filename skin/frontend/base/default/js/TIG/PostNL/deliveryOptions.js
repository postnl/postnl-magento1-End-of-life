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
    beingDragged : false,
    markers : [],

    selectedMarker : false,

    initialize : function(fullAddress, deliveryOptions) {
        this.deliveryOptions = deliveryOptions;
        this.fullAddress = fullAddress;

        var myStyles = [
            {
                featureType : 'poi',
                elementType : 'labels',
                stylers     : [
                    { visibility : 'off'}
                ]
            }
        ];
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
            zoomControlOptions : {
                style    : google.maps.ZoomControlStyle.SMALL,
                position : google.maps.ControlPosition.LEFT_TOP
            }
        };

        this.map = new google.maps.Map($('map-div'), mapOptions);

        if (this.markers) {
            this.markers.each(function(marker) {
                marker.setMap(null);
            });

            this.markers = [];
        }

        this.searchAndPanToAddress(this.fullAddress, true, false);

        this.registerObservers();
    },

    registerObservers : function () {
        var map = this.map;

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

        google.maps.event.addListener(map, 'zoom_changed', function() {
            this.getLocationsWithinBounds();
        }.bind(this));

        google.maps.event.addListener(map, 'dragstart', function() {
            this.beingDragged = true;
        }.bind(this));

        google.maps.event.addListener(map, 'dragend', function() {
            this.beingDragged = false;

            this.getLocationsWithinBounds();
        }.bind(this));

        return this;
    },

    getAddLocationWindow : function() {
        if (this.deliveryOptions.options && this.deliveryOptions.options.addLocationWindow) {
            var addLocationWindow = this.deliveryOptions.options.addLocationWindow;
            if (typeof addLocationWindow == 'string') {
                return $(addLocationWindow);
            }

            return addLocationWindow;
        }

        return $('postnl_add_location');
    },

    openAddLocationWindow : function(event) {
        if (event) {
            event.stop();
        }

        this.getAddLocationWindow().show();
        this.triggerResize();

        return this;
    },

    closeAddLocationWindow : function(event) {
        if (event) {
            event.stop();
        }

        this.getAddLocationWindow().hide();

        return this;
    },

    addressSearch : function(event) {
        if (event.keyCode && event.keyCode != Event.KEY_RETURN) {
            return this;
        } else if (event) {
            event.stop();
        }

        var address = $('search-field').getValue();
        if (!address) {
            return this;
        }

        this.searchAndPanToAddress(address, true, true);

        return this;
    },

    saveLocation : function() {
        var customLocation = this.selectedMarker.location;
        if (!customLocation) {
            return this;
        }

        $$('#customlocation li').each(function(element) {
            element.remove();
        });

        this.deliveryOptions.customLocation = customLocation;

        this.deliveryOptions.locations.push(customLocation);
        customLocation.render('customlocation');

        this.deliveryOptions.selectLocation(customLocation.element);
        this.closeAddLocationWindow();

        return this;
    },

    searchAndPanToAddress : function(address, addMarker, getLocations) {
        this.geocode(address, this.panMapToAddress.bind(this, addMarker, getLocations), this.showSearchErrorDiv);

        return this;
    },

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

    panMapToAddress : function(addMarker, getLocations, results) {
        this.hideSearchErrorDiv();
        var selectedResult = false;

        results.each(function(result) {
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

        if (selectedResult === false) {
            this.showSearchErrorDiv();

            return this;
        }

        var map = this.map;
        var latlng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
        map.panTo(latlng);
        if (map.getZoom() < 15) {
            map.setZoom(15);
        }

        if (getLocations) {
            this.removeMarkers();
            this.getNearestLocations();
        }

        if (addMarker) {
            var searchLocationMarker;

            if (this.searchLocationMarker) {
                this.searchLocationMarker.setMap(null);
            }

            searchLocationMarker = new google.maps.Marker({
                position: latlng,
                map: map,
                title: selectedResult.formatted_address,
                draggable: false
            });

            this.searchLocationMarker = searchLocationMarker;
        }

        return this;
    },

    getSearchErrorDiv : function() {
        if (this.deliveryOptions.options.searchErrorDiv) {
            return this.deliveryOptions.options.searchErrorDiv;
        }

        return $('search-field-error');;
    },

    hideSearchErrorDiv : function() {
        this.getSearchErrorDiv().hide();

        return this;
    },

    showSearchErrorDiv : function() {
        this.getSearchErrorDiv().show();

        return this;
    },

    getNearestLocations : function() {
        var map = this.map;

        var center = map.getCenter();

        if (this.getNearestLocationsRequest) {
            this.getNearestLocationsRequest.transport.abort();
        }

        this.getNearestLocationsRequest = new Ajax.PostnlRequest(this.deliveryOptions.locationsUrl, {
            method : 'post',
            parameters : {
                lat          : center.lat(),
                long         : center.lng(),
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

                this.addMarkers(locations);

                return this;
            }.bind(this),
            onFailure : function() {
                return false;
            },
            onComplete : function() {
                this.getLocationsInAreaRequest = false;
            }.bind(this)
        });

        return this;
    },

    getLocationsWithinBounds : function() {
        var map = this.map;

        var bounds = map.getBounds();
        var northEast = bounds.getNorthEast();
        var southWest = bounds.getSouthWest();

        if (this.getLocationsInAreaRequest) {
            this.getLocationsInAreaRequest.transport.abort();
        }

        this.getLocationsInAreaRequest = new Ajax.PostnlRequest(this.deliveryOptions.locationsInAreaUrl, {
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

                this.addMarkers(locations);

                return this;
            }.bind(this),
            onFailure : function(response) {
                return false;
            },
            onComplete : function() {
                this.getLocationsInAreaRequest = false;
            }.bind(this)
        });

        return this;
    },

    addMarkers : function(locations) {
        var markers = [];
        if (this.markers) {
            markers = this.markers;
        }

        var parsedLocations = [];
        var newLocations = [];
        if (this.locations) {
            parsedLocations = this.locations;
        }

        for (var i = 0; i < locations.length; i++) {
            var location = locations[i];

            if (this.markerExists(location.LocationCode)) {
                continue;
            }

            var markerLatLng = new google.maps.LatLng(location.Latitude, location.Longitude);
            var markerTitle = location.Name + ', ' + location.Address.Street + ' ' + location.Address.HouseNr;
            if (location.Address.HouseNrExt) {
                markerTitle += ' ' + location.Address.HouseNrExt;
            }

            var marker = new google.maps.Marker({
                position  : markerLatLng,
                map       : this.map,
                title     : markerTitle,
                animation : google.maps.Animation.DROP,
                draggable : false,
                clickable : true,
                icon      : this.deliveryOptions.getMapIcon()
            });

            var parsedLocation = new PostnlDeliveryOptions.Location(
                location,
                parsedLocations.length + 1,
                this.deliveryOptions,
                location.DeliveryOptions.string
            );
            parsedLocation.marker = marker;

            marker.locationCode = location.LocationCode;
            marker.location = parsedLocation;

            google.maps.event.addListener(marker, "click", this.selectMarker.bind(this, marker, true, true));
            google.maps.event.addListener(marker, "mouseover", this.markerOnMouseOver.bind(this, marker));
            google.maps.event.addListener(marker, "mouseout", this.markerOnMouseOut.bind(this, marker));

            markers.push(marker);
            parsedLocations.push(parsedLocation);
            newLocations.push(parsedLocation);
        }

        this.locations = parsedLocations;
        this.markers = markers;

        this.renderLocations(newLocations);

        if (!this.selectedMarker) {
            this.selectMarker(markers[0], false, false);
        }

        return this;
    },

    removeMarkers : function() {
        var markers = this.markers;

        markers.each(function(marker) {
            marker.setMap(null);
            marker = null;
        });

        $$('#map-locations li').each(function(location) {
            location.remove();
        });

        this.markers = [];
        return this;
    },

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
        var markers = this.markers;

        for (var i = 0; i < markers.length; i++) {
            if (markers[i].locationCode == location) {
                return true;
            }
        }

        return false;
    },

    triggerResize : function() {
        var center = this.map.getCenter();
        google.maps.event.trigger(this.map, "resize");
        this.map.setCenter(center);

        this.getLocationsWithinBounds();

        return this;
    },

    selectMarker : function(marker, scrollTo, panTo) {
        if (this.selectedMarker
            && this.selectedMarker.location.mapElement.identify() == marker.location.mapElement.identify()
        ) {
            return this;
        }

        marker.setIcon(this.deliveryOptions.getMapIconSelected());
        marker.location.mapElement.addClassName('selected');

        if (scrollTo) {
            var locationsList = $('map-locations');
            locationsList.scrollTop = marker.location.mapElement.offsetTop - locationsList.offsetTop - 36;
        }

        if (this.selectedMarker) {
            this.selectedMarker.setIcon(this.deliveryOptions.getMapIcon());
            this.selectedMarker.location.mapElement.removeClassName('selected');
        }
        this.selectedMarker = marker;

        if (panTo) {
            this.map.panTo(marker.getPosition());
        }

        return this;
    },

    markerOnMouseOver : function(marker) {
        if (this.beingDragged) {
            return this;
        }

        if (!this.selectedMarker
            || this.selectedMarker.location.mapElement.identify() != marker.location.mapElement.identify()
            ) {
            marker.setIcon(this.deliveryOptions.getMapIconSelected());
        }

        return this;
    },

    markerOnMouseOut : function(marker) {
        if (this.beingDragged) {
            return this;
        }

        if (!this.selectedMarker
            || this.selectedMarker.location.mapElement.identify() != marker.location.mapElement.identify()
            ) {
            marker.setIcon(this.deliveryOptions.getMapIcon());
        }

        return this;
    }
});

/**
 * A PostNL PakjeGemak, PakjeGemak Express or parcel dispenser location. Contains address information, opening hours, the type
 * of location and any html elements associated to this location.
 */
PostnlDeliveryOptions.Location = new Class.create({
    elements : [],

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
        var deliveryDate = deliveryOptions.deliveryDate;
        var date = new Date(deliveryDate.substring(6, 10), deliveryDate.substring(3, 5) - 1, deliveryDate.substring(0, 2));

        /**
         * Get the html for this location.
         */
        var headerHtml = '';
        headerHtml += '<li class="location">';
        headerHtml += '<span class="bkg">';
        headerHtml += '<span class="bkg">';
        headerHtml += '<div class="content">';
        headerHtml += '<strong class="location-name">' + this.name + '</strong>';

        if (this.type.indexOf('PA') != -1) {
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
         * Attach the location to the bottom of the parent element.
         */
        $(parent).insert({
            bottom: headerHtml
        });

        var n = 0;
        this.type.each(function(type) {
            var id = 'location_' + this.locationIndex + '_' + type;
            var optionHtml = '';
            optionHtml += '<li class="option" id="' + id + '">';
            optionHtml += '<a href="#">';
            optionHtml += '<span class="bkg">';
            optionHtml += '<span class="bkg">';
            optionHtml += '<div class="content">';
            optionHtml += '<span class="option-dd">';

            if (n < 1) {
                optionHtml += '<strong class="option-day">' + this.deliveryOptions.weekdays[date.getDay()] + '</strong>';
                optionHtml += '<span class="option-date">'
                           + ('0' + date.getDate()).slice(-2)
                           + '-'
                           + ('0' + (date.getMonth() + 1)).slice(-2)
                           + '</span>';
            }

            optionHtml += '</span>';
            optionHtml += '<span class="option-radio"></span>';

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

            $(parent).insert({
                bottom: optionHtml
            });

            var element = $(id);

            element.observe('click', function(element, event) {
                event.stop();

                if (element.hasClassName('active')) {
                    return false;
                }

                this.deliveryOptions.selectLocation(element);
                return true;
            }.bind(this, element));

            elements[type] = element;
            n++;
        }.bind(this));

        this.elements = elements;

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
            var extraCosts = this.deliveryOptions.options.expressFee;
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
     * Create the html for this location's tooltip. The tooltip contains address information as well as information regarding
     * the opening hours of this location.
     *
     * @return string
     */
    getTooltipHtml : function() {
        /**
         * Get the base tooltip html and the address info.
         */
        var address = this.address;
        var addressText = address.Street + ' ' + address.HouseNr;
        if (address.houseNrExt) {
            addressText += ' ' + address.houseNrExt;
        }
        addressText += '  ' + Translator.translate('in') + ' ' + address.City;

        var html = '<div class="tooltip">';
        html += '<div class="tooltip-header">';
        html += '<strong class="location-name">' + this.name + '</strong>';
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
        var openingHours = this.openingHours;
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

    renderAsMapLocation : function(parent) {
        var addressText = this.address.Street + ' ' + this.address.HouseNr;
        if (this.address.HouseNrExt) {
            addressText += ' ' + this.address.HouseNrExt;
        }
        addressText += ', ' + this.address.City;

        var distance = parseInt(this.distance);
        var distanceText = '';

        if (distance < 1000 && distance > 0) {
            distanceText = distance + ' m';
        } else if (distance > 0) {
            distanceText = parseFloat(Math.round(distance / 100) / 10).toFixed(1) + ' km';
        }

        var id = 'map-location_' + this.locationIndex;

        var html = '<li class="location" id="' + id + '">';
        html += '<a href="javascript:void(0);">';
        html += '<div class="content">';
        html += '<img src="http://newdev.tigpostnl.nl/skin/frontend/enterprise/default/images/TIG/PostNL/deliveryoptions/tmp_ah.png" class="location-icon" alt="Albert Heijn" />';
        html += '<strong class="location-name">' + this.name + '</strong>';
        html += '<span class="location-address">' + addressText + '</span>';
        html += '<span class="location-distance">' + distanceText + '</span>';
        html += '<a href="javascript:void(0);" class="location-info">' + Translator.translate('business hours') + '</a>';
        html += '</div>';
        html += '</a>';
        html += '</li>';

        /**
         * If an element's id was supplied, get the parent element.
         */
        if (typeof parent == 'string') {
            parent = $(parent);
        }

        /**
         * Attach the location to the bottom of the parent element.
         */
        parent.insert({
            bottom: html
        });

        var element = $(id);
        element.observe('click', function(event) {
            event.stop();

            this.oldCenter = this.marker.getPosition();

            this.deliveryOptions.deliveryOptionsMap.selectMarker(this.marker, false, true, event);
        }.bind(this));

        element.observe('mouseover', function() {
            var map = this.deliveryOptions.deliveryOptionsMap;
            if (map.beingDragged) {
                return this;
            }

            if (!map.map.getBounds().contains(this.marker.getPosition())) {
                this.oldCenter = map.map.getCenter();
                map.map.setCenter(this.marker.getPosition());
            }

            google.maps.event.trigger(this.marker, 'mouseover');
        }.bind(this));

        element.observe('mouseout', function() {
            var map = this.deliveryOptions.deliveryOptionsMap;
            if (this.deliveryOptions.deliveryOptionsMap.beingDragged) {
                return this;
            }

            if (this.oldCenter) {
                map.map.setCenter(this.oldCenter);
                this.oldCenter = false;
            }

            google.maps.event.trigger(this.marker, 'mouseout');
        }.bind(this));

        this.mapElement = element;

        return this;
    },

    /**
     * Select an element by adding the 'active' class.
     *
     * @return PostnlDeliveryOptions.Option
     */
    select : function(type) {
        var elements = this.elements;
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
        var elements = this.elements;
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