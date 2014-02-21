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

        return this;
    },

    registerObservers : function() {
        var postnlDeliveryOptions = this;

        $('custom_location').observe('click', this.openAddLocationWindow.bind(this));
        $('close_popup').observe('click', this.closeAddLocationWindow.bind(this));
        $('search-button').observe('click', this.addressSearch.bind(this));
        $('search-field').observe('keydown', this.addressSearch.bind(this));

        $$('.location-option-checkbox').each(function(element) {
            element.observe('click', function() {
                this.toggleClassName('selected');
            });
        });

        return this;
    },

    initMap : function() {
        this.deliveryOptionsMap = new PostnlDeliveryOptions.Map(this.fullAddress, this);

        return this;
    },

    getAddLocationWindow : function() {
        if (this.options && this.options.addLocationWindow) {
            var addLocationWindow = this.options.addLocationWindow;
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
        this.deliveryOptionsMap.triggerResize();

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

        this.deliveryOptionsMap.panMapToAddress(address);

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
        var PostnlDeliveryOptions = this;

        new Ajax.PostnlRequest(this.timeframesUrl,{
            method : 'post',
            parameters : {
                postcode     : postcode,
                housenumber  : housenumber,
                deliveryDate : deliveryDate,
                isAjax       : true
            },
            onSuccess : function(response) {
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
                    PostnlDeliveryOptions.showDefaultTimeframe();

                    return false;
                }

                /**
                 * Eval the resulting JSON in sanitize mode.
                 */
                var timeframes = responseText.evalJSON(true);

                /**
                 * Parse and render the result.
                 */
                PostnlDeliveryOptions.parseTimeframes(timeframes)
                                     .renderTimeframes();
                return this;
            },
            onFailure : function() {
                PostnlDeliveryOptions.showDefaultTimeframe();

                return false;
            }
        });
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
        var timeframes = this.timeframes;

        timeframes.each(function(timeframe) {
            if (element && timeframe.element.identify() == element.identify()) {
                this.selectedOption = timeframe;
                timeframe.select();
            } else {
                timeframe.unSelect();
            }
        });

        if (element) {
            this.selectLocation(false);
        }

        return false;
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
        var PostnlDeliveryOptions = this;

        new Ajax.PostnlRequest(this.locationsUrl,{
            method : 'post',
            parameters : {
                postcode     : postcode,
                housenumber  : housenumber,
                deliveryDate : deliveryDate,
                isAjax       : true
            },
            onSuccess : function(response) {
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
                    PostnlDeliveryOptions.hideLocations();

                    return false;
                }

                /**
                 * Eval the resulting JSON in sanitize mode.
                 */
                var locations = responseText.evalJSON(true);

                /**
                 * Parse and render the result.
                 */
                PostnlDeliveryOptions.parseLocations(locations)
                                     .renderLocations();
                return this;
            },
            onFailure : function() {
                PostnlDeliveryOptions.hideLocations();

                return false;
            }
        });
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

        var postnlPgeLocation;
        var postnlPgLocation;
        var postnlPaLocation;

        var deliveryOptions = this;
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

            if (
                (options.allowPg && !processedPG && type.indexOf('PG') != -1)
                && (options.allowPge && !processedPGE && type.indexOf('PGE') != -1)
            ) {
                /**
                 * Instantiate a new PostnlDeliveryOptions.Location object with this location's parameters.
                 */
                postnlPgeLocation = new PostnlDeliveryOptions.Location(locations[n], n, deliveryOptions, 'PGE');
                postnlPgLocation = new PostnlDeliveryOptions.Location(locations[n], n+1, deliveryOptions, 'PG');

                postnlPgeLocation.child = postnlPgLocation;
                postnlPgLocation.parent = postnlPgeLocation;

                /**
                 * Register this location as the chosen PGE location.
                 */
                deliveryOptions.pgeLocation = postnlPgeLocation;
                deliveryOptions.pgLocation  = postnlPgLocation;

                processedPGE                = true;
                processedPG                 = true;

                processedLocations.push(postnlPgeLocation);
                processedLocations.push(postnlPgLocation);
                continue;
            }

            /**
             * If we can add a PGE location, we don't already have a PGE location and this is a PGE location; add it as the chosen
             * PGE location.
             */
            if (options.allowPge && !processedPGE && type.indexOf('PGE') != -1) {
                /**
                 * Instantiate a new PostnlDeliveryOptions.Location object with this location's parameters.
                 */
                postnlPgeLocation = new PostnlDeliveryOptions.Location(locations[n], n, deliveryOptions, 'PGE');

                /**
                 * Register this location as the chosen PGE location.
                 */
                deliveryOptions.pgeLocation = postnlPgeLocation;
                processedPGE                = true;

                processedLocations.push(postnlPgeLocation);
                continue;
            }

            /**
             * If we can add a PG location, we don't already have a PG location and this is a PG location; add it as the chosen
             * PG location.
             */
            if (options.allowPg && !processedPG && type.indexOf('PG') != -1) {
                /**
                 * Instantiate a new PostnlDeliveryOptions.Location object with this location's parameters.
                 */
                postnlPgLocation = new PostnlDeliveryOptions.Location(locations[n], n, deliveryOptions, 'PG');

                /**
                 * Register this location as the chosen PG location.
                 */
                deliveryOptions.pgLocation = postnlPgLocation;
                processedPG                = true;

                processedLocations.push(postnlPgLocation);
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
                 * Instantiate a new PostnlDeliveryOptions.Location object with this location's parameters.
                 */
                postnlPaLocation = new PostnlDeliveryOptions.Location(locations[n], n, deliveryOptions, 'PA');

                /**
                 * Register this location as the chosen PA location.
                 */
                deliveryOptions.paLocation = postnlPaLocation;
                processedPA                = true;

                processedLocations.push(postnlPaLocation);
            }
        }

        this.locations = processedLocations;

        return this;
    },

    renderLocations : function() {
        $('postnl_pickup').show();

        $$('#pgelocation li').each(function(element) {
            element.remove();
        });
        $$('#pglocation li').each(function(element) {
            element.remove();
        });
        $$('#palocation li').each(function(element) {
            element.remove();
        });

        if (this.options.allowPge && this.pgeLocation && !this.pgeLocation.isChild()) {
            this.pgeLocation.render('pgelocation');

            if (this.pgeLocation.child) {
                this.pgeLocation.child.render('pgelocation');
            }
        }

        if (this.options.allowPg && this.pgLocation && !this.pgLocation.isChild()) {
            this.pgLocation.render('pglocation');

            if (this.pgLocation.child) {
                this.pgLocation.child.render('pglocation');
            }
        }

        if (this.options.allowPa && this.paLocation && !this.paLocation.isChild()) {
            this.paLocation.render('palocation');

            if (this.paLocation.child) {
                this.paLocation.child.render('palocation');
            }
        }

        if (!this.pgeLocation && !this.pgLocation && !this.paLocation) {
            $('postnl_pickup').hide();
        }

        return this;
    },

    hideLocations : function() {
        $('postnl_pickup').hide();

        return this;
    },

    selectLocation : function(element) {
        var locations = this.locations;

        locations.each(function(location) {
            if (element && location.element.identify() == element.identify()) {
                this.selectedOption = location;
                location.select();
            } else {
                location.unSelect();
            }
        });

        if (element) {
            this.selectTimeframe(false);
        }

        return false;
    }
});

PostnlDeliveryOptions.Map = new Class.create({
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
            zoom             : 15,
            center           : new google.maps.LatLng(52.3702157, 4.895167899999933), //Amsterdam
            mapTypeId        : google.maps.MapTypeId.ROADMAP,
            styles           : myStyles,
            disableDefaultUI : true
        };

        this.map = new google.maps.Map($('map-div'), mapOptions);

        this.panMapToAddress(this.fullAddress);

        this.registerMapObservers();
    },

    registerMapObservers : function () {
        var map = this.map;

        google.maps.event.addListener(map, 'dragend', function() {
            this.getLocationsWithinBounds();
        }.bind(this));

        google.maps.event.addListener(map, 'zoom_changed', function() {
            this.getLocationsWithinBounds();
        }.bind(this));

        return this;
    },

    panMapToAddress : function(address) {
        var map = this.map;
        var errorDiv = $('search-field-error');
        errorDiv.hide();

        var geocoder = new google.maps.Geocoder();
        geocoder.geocode(
            {
                'address' : address,
                'region'  : 'nl'
            },
            function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var latlng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                    map.panTo(latlng);
                    map.setZoom(15);

                    this.getLocationsWithinBounds();
                } else {
                    errorDiv.show();
                }
            }.bind(this)
        );

        return this;
    },

    getLocationsWithinBounds : function() {
        var map = this.map;

        if (map.getZoom() < 13) {
            return this;
        }

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
            var marker = new google.maps.Marker({
                position  : markerLatLng,
                map       : this.map,
                title     : location.Name,
                animation : google.maps.Animation.DROP,
                draggable : false,
                icon      : this.deliveryOptions.getMapIcon()
            });

            var parsedLocation = new PostnlDeliveryOptions.Location(location, parsedLocations.length + 1, this.deliveryOptions, 'custom');
            parsedLocation.marker = marker;

            marker.locationCode = location.LocationCode;
            marker.location = parsedLocation;

            google.maps.event.addListener(marker, "click", this.selectMarker.bind(this, marker));

            markers.push(marker);
            parsedLocations.push(parsedLocation);
            newLocations.push(parsedLocation);
        }

        this.locations = parsedLocations;
        this.markers = markers;

        this.renderLocations(newLocations);

        if (!this.selectedMarker) {
            google.maps.event.trigger(markers[0], 'click');
        }

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

    selectMarker : function(marker, event) {
        if (this.selectedMarker
            && this.selectedMarker.location.mapElement.identify() == marker.location.mapElement.identify()
        ) {
            return this;
        }

        marker.setIcon(this.deliveryOptions.getMapIconSelected());
        marker.location.mapElement.addClassName('selected');

        if (this.selectedMarker) {
            this.selectedMarker.setIcon(this.deliveryOptions.getMapIcon());
            this.selectedMarker.location.mapElement.removeClassName('selected');
        }
        this.selectedMarker = marker;

        this.map.panTo(marker.getPosition());

        return this;
    }
});

/**
 * PostNL delivery option base class. A delivery option can either be a specific timeframe or a delivery location (such as a post office).
 *
 * Contains functionality to select and unselect delivery options.
 */
PostnlDeliveryOptions.Option = new Class.create({
    element : false,

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

/**
 * A PostNL PakjeGemak, PakjeGemak Express or parcel dispenser location. Contains address information, opening hours, the type
 * of location and any html elements associated to this location.
 */
PostnlDeliveryOptions.Location = new Class.create(PostnlDeliveryOptions.Option, {
    renderHeader : true,
    child        : false,
    parent       : false,

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
        var deliveryDate = deliveryOptions.deliveryDate;
        var date = new Date(deliveryDate.substring(6, 10), deliveryDate.substring(3, 5), deliveryDate.substring(0, 2));
        var id = 'location_' + this.locationIndex;
        if (this.isChild()) {
            id += '_child';
        }

        /**
         * Get the html for this location.
         */
        var html = '';

        if (!this.isChild()) {
            html += '<li class="location">';
            html += '<span class="bkg">';
            html += '<span class="bkg">';
            html += '<div class="content">';
            html += '<strong class="location-name">' + this.name + '</strong>';

            if (this.type == 'PG' || this.type == 'PGE') {
                html += '<span class="location-type">' + Translator.translate('Post Office') + '</span>';
            } else {
                html += '<span class="location-type">' + Translator.translate('Package Dispenser') + '</span>';
            }

            html += '<a href="javascript:void(0);" class="location-info">';
            html += '<span>' + Translator.translate('More Info') + '</span>';
            html += this.getTooltipHtml();
            html += '</a>';
            html += '</div>';
            html += '</span>';
            html += '</span>';
            html += '</li>';
        }

        html += '<li class="option" id="' + id + '">';
        html += '<a href="#">';
        html += '<span class="bkg">';
        html += '<span class="bkg">';
        html += '<div class="content">';
        html += '<span class="option-dd">';

        if (!this.isChild()) {
            html += '<strong class="option-day">' + this.deliveryOptions.weekdays[date.getDay()] + '</strong>';
            html += '<span class="option-date">' + date.getDate() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '</span>';
        }

        html += '</span>';
        html += '<span class="option-radio"></span>';

        if (this.type == 'PGE') {
            html += '<span class="option-time">' + Translator.translate('from') + ' 8:30</span>';
        } else {
            html += '<span class="option-time">' + Translator.translate('from') + ' 16:00</span>';
        }

        html += '<span class="option-comment">' + this.getCommentHtml() + '</span>';
        html += '</div>';
        html += '</span>';
        html += '</span>';
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

        /**
         * Get the newly created element and observe it's 'click' event.
         */
        var element = $(id);
        element.observe('click', function(event) {
            event.stop();

            if (this.hasClassName('active')) {
                return true;
            }

            deliveryOptions.selectLocation(this);
            return true;
        });

        /**
         * Add the newly created element to this location, so we can retreive it later.
         */
        this.element = element;

        return this;
    },

    renderAsMapLocation : function(parent) {
        var addressText = this.address.Street + ' ' + this.address.HouseNr;
        if (this.address.HouseNrExt) {
            addressText += ' ' + this.address.HouseNrExt;
        }
        addressText += ', ' + this.address.City;

        var id = 'map-location_' + this.locationIndex;

        var html = '<li class="location" id="' + id + '">';
        html += '<a href="javascript:void(0);">';
        html += '<div class="content">';
        html += '<img src="http://newdev.tigpostnl.nl/skin/frontend/enterprise/default/images/TIG/PostNL/deliveryoptions/tmp_ah.png" class="location-icon" alt="Albert Heijn" />';
        html += '<strong class="location-name">' + this.name + '</strong>';
        html += '<span class="location-address">' + addressText + '</span>';
        html += '<span class="location-distance">' + this.distance + ' km</span>';
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

            google.maps.event.trigger(this.marker, 'click');
        }.bind(this));

        this.mapElement = element;

        return this;
    },

    /**
     * Gets the comment html for this location. The comment contains any additional fees incurred by choosing this option and, in
     * the case of a parcel dispenser location, the fact that it is available 24/7.
     *
     * @return string
     */
    getCommentHtml : function() {
        var commentHtml = '';
        var type = this.type;

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

    isChild : function() {
        if (this.parent !== false) {
            return true;
        }

        return false;
    }
});

PostnlDeliveryOptions.Timeframe = new Class.create(PostnlDeliveryOptions.Option, {
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
        var date = new Date(this.date.substring(6, 10), this.date.substring(3, 5), this.date.substring(0, 2));

        var datesProcessed = this.deliveryOptions.datesProcessed;
        var weekdayHtml = '';
        if (datesProcessed.indexOf(date.getTime()) == -1) {
            var weekdays = this.deliveryOptions.weekdays;

            this.deliveryOptions.datesProcessed.push(date.getTime());
            weekdayHtml = '<strong class="option-day">' + weekdays[date.getDay()] + '</strong>';
            weekdayHtml += '<span class="option-date">' + this.date.substring(0, 5) + '</span>';
        }

        return weekdayHtml;
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