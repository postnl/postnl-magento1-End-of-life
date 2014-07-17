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
PostnlMassActionFilter = Class.create();
PostnlMassActionFilter.prototype = {
    filteredTypes      : [],

    massactionObject   : false,

    isDefaultCheckbox  : false,
    isBuspakjeCheckbox : false,

    isDefaultChecked   : false,

    initialize : function(massActionObject) {
        this.massactionObject = massActionObject;

        massActionObject.setCheckbox = function(checkbox) {
            if(checkbox.checked) {
                this.checkedString = varienStringArray.add(checkbox.value, this.checkedString);
            } else {
                this.checkedString = varienStringArray.remove(checkbox.value, this.checkedString);
            }
            this.updateCount();
            document.fire('postnl:massaction_checkbox_change');
        }.bind(massActionObject);

        massActionObject.checkCheckboxes = function() {
            this.getCheckboxes().each(function(checkbox) {
                checkbox.checked = varienStringArray.has(checkbox.value, this.checkedString);
            }.bind(this));

            document.fire('postnl:massaction_checkbox_change');
        }.bind(massActionObject);

        massActionObject.grid.initGridAjax = function() {
            this.initGrid();
            this.initGridRows();

            document.fire('postnl:massaction_grid_reload');
        }.bind(massActionObject.grid);

        this.registerObservers();
        this.init();

        return this;
    },

    registerObservers : function() {
        $('sales_order_grid_massaction-select').observe('change', this.init.bind(this));

        document.observe('postnl:massaction_checkbox_change', this.filterOptions.bind(this));
        document.observe('postnl:massaction_grid_reload', this.init.bind(this));

        return this;
    },

    init : function() {
        this.reset();

        this.massactionObject.onSelectChange();

        this.getIsDefaultCheckbox().disabled = true;
        this.getIsDefaultCheckbox().checked = true;
        this.getIsBuspakjeCheckbox().up().hide();
        this.hideOptions();

        this.getIsDefaultCheckbox().observe('click', this.defaultCheckboxChange.bind(this));
        this.getIsBuspakjeCheckbox().observe('click', this.buspakjeCheckboxChange.bind(this));

        this.filterOptions();

        return this;
    },

    reset : function() {
        this.filteredTypes = [];
        this.isDefaultCheckbox = false;
        this.isBuspakjeCheckbox = false;

        return this;
    },

    getIsDefaultCheckbox : function() {
        if (this.isDefaultCheckbox) {
            return this.isDefaultCheckbox;
        }

        var defaultCheckbox = $('postnl_use_default_checkbox');

        this.isDefaultCheckbox = defaultCheckbox;
        return defaultCheckbox;
    },

    getIsBuspakjeCheckbox : function() {
        if (this.isBuspakjeCheckbox) {
            return this.isBuspakjeCheckbox;
        }

        var buspakjeCheckbox = $('postnl_is_buspakje_checkbox');

        this.isBuspakjeCheckbox = buspakjeCheckbox;
        return buspakjeCheckbox;
    },

    isDefaultCheckboxChecked : function() {
        var defaultCheckbox = this.getIsDefaultCheckbox();

        if (defaultCheckbox.checked) {
            return true;
        }

        return false;
    },

    isBuspakjeCheckboxChecked : function() {
        var buspakjeCheckbox = this.getIsBuspakjeCheckbox();

        if (buspakjeCheckbox.checked) {
            return true;
        }

        return false;
    },

    defaultCheckboxChange : function() {
        this.isDefaultChecked = this.isDefaultCheckboxChecked();

        if (this.isDefaultCheckboxChecked()) {
            this.hideOptions();
        } else {
            this.showOptions();
        }

        return this;
    },

    buspakjeCheckboxChange : function() {
        this.filterOptions();

        return this;
    },

    hideOptions : function() {
        $$('#sales_order_grid_massaction-form-additional select').each(function(element) {
            element.up().hide();
        });

        return this;
    },

    showOptions : function() {
        this.hideOptions();

        var filteredTypes = this.filteredTypes;
        if (filteredTypes.length != 1) {
            this.isDefaultChecked = this.getIsDefaultCheckbox().checked;

            this.getIsDefaultCheckbox().disabled = true;
            this.getIsDefaultCheckbox().checked = true;
            this.getIsBuspakjeCheckbox().up().hide();

            return this;
        }

        this.getIsDefaultCheckbox().disabled = false;
        this.getIsDefaultCheckbox().checked = this.isDefaultChecked;

        var filteredType = filteredTypes[0];
        if (filteredType == 'domestic' || filteredType == 'buspakje') {
            this.getIsBuspakjeCheckbox().up().show();
        } else {
            this.getIsBuspakjeCheckbox().up().hide();
        }

        if (this.isDefaultCheckboxChecked()) {
            return this;
        }

        $('postnl_' + filteredType + '_options').up().show();

        return this;
    },

    filterOptions : function() {
        this.filteredTypes = [];

        $$('input.massaction-checkbox:checked').each(function(element) {
            var shipmentType = $('postnl-shipmenttype-' + element.getValue());
            shipmentType = shipmentType.getAttribute('data-product-type');

            this.addTypeToFilter(shipmentType);
        }.bind(this));

        this.showOptions();
        return this;
    },

    addTypeToFilter : function(shipmentType) {
        var filteredTypes = this.filteredTypes;

        if (!shipmentType) {
            shipmentType = 'non-postnl';
        }

        if (shipmentType == 'domestic' && this.isBuspakjeCheckboxChecked()) {
            shipmentType = 'buspakje';
        }

        if (indexOf.call(filteredTypes, shipmentType) == -1) {
            filteredTypes.push(shipmentType);
        }

        return this;
    }
};

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