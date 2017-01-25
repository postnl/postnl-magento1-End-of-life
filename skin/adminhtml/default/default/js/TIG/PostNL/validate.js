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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
document.observe('dom:loaded', function() {
    $$('.postnl-validate-empty').each(function(element) {
        element.observe('change', function(event) {
            var eventElement = Event.element(event);
            checkEmpty(eventElement);
        });

        element.observe('keyup', function(event) {
            var eventElement = Event.element(event);
            checkEmpty(eventElement);
        });

        checkEmpty(element);
    });

    $$('.postnl-validate-empty-group').each(function(element) {
        element.observe('change', function(event) {
            var eventElement = Event.element(event);
            checkEmptyGroup(eventElement);
        });

        element.observe('keyup', function(event) {
            var eventElement = Event.element(event);
            checkEmptyGroup(eventElement);
        });

        checkEmptyGroup(element);
    });

    var streetFields = $$('.postnl-street-field');
    validateStreetFields = function() {
        var postnlStreetFieldsUsed = [];

        streetFields.each(function(streetField) {
            var streetFieldOptions = streetField.select('option');
            var fieldValue = streetField.getValue();
            var valueMustChange = false;

            streetFieldOptions.each(function(option) {
                var optionValue = option.value;
                if (postnlStreetFieldsUsed.indexOf(optionValue) > -1) {
                    option.hide();

                    if (optionValue == fieldValue) {
                        valueMustChange = true;
                    }
                } else {
                    option.show();
                }
            });

            if (valueMustChange) {
                streetFieldOptions.each(function(option) {
                    if (!valueMustChange) {
                        return;
                    }

                    if (option.getStyle('display') != 'none') {
                        option.selected = true;
                        valueMustChange = false;
                        fieldValue = option.value;
                    }
                });
            }

            postnlStreetFieldsUsed.push(fieldValue);
        });
    };

    streetFields.invoke('observe', 'change', validateStreetFields);
    validateStreetFields();
});

function checkEmpty(eventElement) {
    if (eventElement.value.empty()) {
        eventElement.addClassName('postnl-validate-empty-failed');
        return;
    }

    eventElement.removeClassName('postnl-validate-empty-failed');
}

function checkEmptyGroup(eventElement) {
    var groupRegex = /^postnl-validate-group-(-?\w+)$/;

    var result = false;
    $w(eventElement.className).each(
        function(name) {
            if (result) {
                return;
            }

            var group = groupRegex.exec(name);
            if (group && group[1]) {
                result = group[1];
            }
        }
    );

    var empty = true;
    var elements = $$('.postnl-validate-empty-group.postnl-validate-group-' + result);
    elements.each(
        function(element) {
            if (!empty) {
                return;
            }

            empty = element.value.empty();
        }
    );

    if (empty) {
        elements.each(
            function(element) {
                element.addClassName('postnl-validate-empty-failed');
            }
        );
    } else {
        elements.each(
            function(element) {
                element.removeClassName('postnl-validate-empty-failed');
            }
        );
    }
}

if (typeof indexOf == 'undefined') {
    var indexOf = function(needle) {
        if(typeof Array.prototype.indexOf === 'function') {
            indexOf = Array.prototype.indexOf;
        } else {
            indexOf = function(needle) {
                var i = -1, index = -1;

                for(i = 0; i < this.length; i++) {
                    if(this[i] === needle) {
                        index = i;
                        break;
                    }
                }

                return index;
            };
        }

        return indexOf.call(this, needle);
    };
}
