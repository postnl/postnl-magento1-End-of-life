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
PostnlDeliveryOptions = new Class.create({
    eveningExtraCosts : 1,
    expressExtraCosts : 1,

    pgLocation  : false,
    pgeLocation : false,
    paLocation  : false,

    timeframes : false,
    locations  : false,

    selectedOption : false,

    initialize : function(timeframesUrl, locationsUrl, postcode, housenumber, deliveryDate, options) {
        this.timeframesUrl = timeframesUrl;
        this.locationsUrl  = locationsUrl;
        this.postcode      = postcode;
        this.housenumber   = housenumber;
        this.deliveryDate  = deliveryDate;

        if (options) {
            this.options = options;
        } else {
            this.options = {};
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
        this.weekdaysProcessed = new Array();
        console.log(this);
    },

    init : function() {
        this.getTimeframes(this.postcode, this.deliveryDate);
        this.getLocations(this.postcode, this.housenumber, this.deliveryDate);

        return this;
    },

    getTimeframes : function(postcode, deliveryDate) {
        var PostnlDeliveryOptions = this;

        new Ajax.PostnlRequest(PostnlDeliveryOptions.timeframesUrl,{
            method: 'post',
            parameters: {
                postcode     : postcode,
                deliveryDate : deliveryDate,
                isAjax       : true
            },
            onSuccess: function(response) {
                var responseText = response.responseText;
                if (responseText == 'not_allowed' || responseText == 'invalid_data' || responseText == 'error') {
                    alert(responseText);
                    return;
                }

                var timeframes = responseText.evalJSON(true);

                PostnlDeliveryOptions.parseTimeframes(timeframes)
                                     .renderTimeframes();
                return;
            }
        });
        return this;
    },

    getLocations : function(postcode, housenumber, deliveryDate) {
        var PostnlDeliveryOptions = this;

        new Ajax.PostnlRequest(PostnlDeliveryOptions.locationsUrl,{
            method: 'post',
            parameters: {
                postcode     : postcode,
                housenumber  : housenumber,
                deliveryDate : deliveryDate,
                isAjax       : true
            },
            onSuccess: function(response) {
                var responseText = response.responseText;
                if (responseText == 'not_allowed' || responseText == 'invalid_data' || responseText == 'error') {
                    alert(responseText);
                    return;
                }

                var locations = responseText.evalJSON(true);

                PostnlDeliveryOptions.parseLocations(locations)
                                     .renderLocations();
                return;
            }
        });
        return this;
    },

    parseLocations : function(locations) {
    	var processedPG = false;
    	var processedPGE = false;
    	var processedPA = false;
    	var processedLocations = new Array();

    	var deliveryOptions = this;
    	var options = this.options;

    	var n = 0;
    	var max = locations.length;
    	$H(locations).each(function(location) {
    		if (n++ >= max) {
    			return;
    		}

    		if (processedPG && processedPGE && processedPA) {
    			return;
    		}

    		var type = location.value.DeliveryOptions.string;

    		if (options.allowPg && !processedPG && type.indexOf('PG') != -1) {
    			var postnlLocation = new PostnlDeliveryOptions.Location(location.value, location.key, deliveryOptions, 'PG');
    			deliveryOptions.pgLocation = postnlLocation;

    			processedLocations[location.key] = postnlLocation;
    			processedPG = true;
    		}

    		if (options.allowPge && !processedPGE && type.indexOf('PGE') != -1) {
    			var postnlLocation = new PostnlDeliveryOptions.Location(location.value, location.key, deliveryOptions, 'PGE');
    			deliveryOptions.pgeLocation = postnlLocation;

    			processedLocations[location.key] = postnlLocation;
    			processedPGE = true;
    		}

    		if (options.allowPa && !processedPA && type.indexOf('PA') != -1) {
    			var postnlLocation = new PostnlDeliveryOptions.Location(location.value, location.key, deliveryOptions, 'PGA');
    			deliveryOptions.paLocation = postnlLocation;

    			processedLocations[location.key] = postnlLocation;
    			processedPA = true;
    		}
    	});

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

        if (this.options.allowPge) {
        	if (this.pgeLocation) {
        		this.pgeLocation.render('pgelocation');
        	}
    	}

        if (this.options.allowPg) {
        	if (this.pgLocation) {
        		this.pgLocation.render('pglocation');
        	}
    	}

        if (this.options.allowPa) {
        	if (this.paLocation) {
        		this.paLocation.render('palocation');
        	}
    	}

    	if (!this.pgeLocation && !this.pgLocation && !this.paLocation) {
    	    $('postnl_pickup').hide();
    	}

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
    },

    parseTimeframes : function(timeframes) {
    	var parsedTimeframes = new Array();
    	var deliveryOptions = this;

    	var n = 0;
    	$H(timeframes).each(function(timeframe) {
    		if (n++ > 6) {
    			return;
    		}

    		if (n > 1 && !deliveryOptions.options.allowTimeframes) {
    		    return;
    		}
    		var postnlTimeframe = new PostnlDeliveryOptions.Timeframe(timeframe.value, timeframe.key, deliveryOptions);

    		parsedTimeframes.push(postnlTimeframe);
    	});

    	this.timeframes = parsedTimeframes;

    	return this;;
    },

    renderTimeframes : function() {
    	$$('#timeframes li.option').each(function(element) {
    		element.remove();
    	});

    	this.weekdaysProcessed = new Array();

    	this.timeframes.each(function(timeframe) {
    		timeframe.render('timeframes');
    	});

        this.timeframes[0].select();

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
    }
});

PostnlDeliveryOptions.Option = new Class.create({
	element : false,

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

PostnlDeliveryOptions.Location = new Class.create(PostnlDeliveryOptions.Option, {
	initialize : function(location, locationIndex, deliveryOptions, type) {
		this.address = location.Address;
		this.distance = location.Distance;
		this.latitude = location.Latitude;
		this.longitude = location.Longitude;
		this.name = location.Name;
		this.openingHours = location.OpeningHours;

		this.locationIndex = locationIndex;

		this.deliveryOptions = deliveryOptions;

		this.type = type;
	},

	render : function(parent) {
		var date = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);

		var html = '<li class="location">';
		html += '<span class="bkg">';
		html += '<span class="bkg">';
		html += '<div class="content">';
		html += '<span class="name">' + this.name + '</span>';

		if (this.type == 'PG' || this.type == 'PGE') {
			html += '<small class="kind">' + Translator.translate('Post Office') + '</small>';
		} else {
			html += '<small class="kind">' + Translator.translate('Package Dispenser') + '</small>';
		}

		html += '<a href="#" class="more-info" title="' + Translator.translate('More Info') + '">' + Translator.translate('More Info') + '</a>';
		html += '</div>';
		html += '</span>';
		html += '</span>';
		html += '</li>';
		html += '<li class="option" id="location_' + this.locationIndex + '">';
		html += '<a href="#" class="data">';
		html += '<span class="bkg">';
		html += '<span class="bkg">';
		html += '<div class="content">';
		html += '<span class="day-date">';
		html += '<span class="day">' + this.deliveryOptions.weekdays[date.getDay()] + '</span>';
		html += '<span class="date">' + date.getDate() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '</span>';
		html += '</span>';
		html += '<span class="faux-radio"></span>';

		if (this.type == 'PGE') {
			html += '<span class="time">' + Translator.translate('from') + ' 8:30</span>';
		} else {
			html += '<span class="time">' + Translator.translate('from') + ' 16:00</span>';
		}

		html += '<span class="comment">' + this.getCommentHtml() + '</span>';
		html += '</div>';
		html += '</span>';
		html += '</span>';
		html += '</a>';
		html += '</li>';

		$(parent).insert({
			bottom: html
		});

		var element = $('location_' + this.locationIndex);
		element.observe('click', function(event) {
			event.stop();

			deliveryOptions.selectLocation(this);
		});

		this.element = element;

		return this;
	},

	getCommentHtml : function() {
		var commentHtml = '';
		var type = this.type;

		if (type == 'PGE') {
            var extraCosts = this.deliveryOptions.options.expressExtraCosts;
            var extraCostHtml = '';

            if (extraCosts) {
                extraCostHtml += ' + ' + extraCosts;
            }

			commentHtml = Translator.translate('early delivery') + extraCostHtml;
		} else if (type == 'PA') {
			commentHtml = '24/7 ' + Translator.translate('beschikbaar');
		}

		return commentHtml;
	}
});

PostnlDeliveryOptions.Timeframe = new Class.create(PostnlDeliveryOptions.Option, {
	initialize : function(timeframe, timeframeIndex, deliveryOptions) {
		this.date = timeframe.Date;
		this.from = timeframe.Timeframes.TimeframeTimeFrame[0].From;
		this.to   = timeframe.Timeframes.TimeframeTimeFrame[0].To;
		this.type = timeframe.Timeframes.TimeframeTimeFrame[0].TimeframeType;

		this.timeframeIndex = timeframeIndex;

		this.deliveryOptions = deliveryOptions;
	},

	render : function(parent) {
		var date = new Date(this.date.substring(6, 10), this.date.substring(3, 5), this.date.substring(0, 2));

		var html = '<li class="option" id="timeframe_' + this.timeframeIndex + '">';
		html += '<a href="#" class="data">';
		html += '<span class="bkg">';
		html += '<span class="bkg">';
		html += '<div class="content">';
		html += '<span class="day-date">';
		html += this.getWeekdayHtml();
		html += '</span>';
		html += '<span class="faux-radio"></span>';
		html += '<span class="time">' + this.from.substring(0, 5) + ' - ' + this.to.substring(0, 5) + '</span>';
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
		element.observe('click', function(event) {
			event.stop();

			deliveryOptions.selectTimeframe(this);
		});

		this.element = element;

		return this;
	},

	getCommentHtml : function() {
		var comment = '';
		if (this.type == 'Avond') {
			var extraCosts = this.deliveryOptions.options.eveningExtraCosts;
			var extraCostHtml = '';

			if (extraCosts) {
				extraCostHtml += ' + ' + extraCosts;
			}

			comment = '<span class="comment">' + Translator.translate('evening') + extraCostHtml + '</span>';
		}

		return comment;
	},

	getWeekdayHtml : function() {
		var date = new Date(this.date.substring(6, 10), this.date.substring(3, 5), this.date.substring(0, 2));

		var daysProcessed = this.deliveryOptions.weekdaysProcessed;
		var weekdayHtml = '';
		if (daysProcessed.indexOf(date.getDay()) == -1) {
			var weekdays = this.deliveryOptions.weekdays;

			daysProcessed.push(date.getDay());
			weekdayHtml = '<span class="day">' + weekdays[date.getDay()] + '</span>';
			weekdayHtml += '<span class="date">' + this.date.substring(0, 5) + '</span>';
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