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
PostnlMassActionFilter = Class.create();
PostnlMassActionFilter.prototype = {
    filteredTypes : [],

    massactionObject : false,

    isDefaultCheckbox  : false,
    isBuspakjeCheckbox : false,
    isBuspakjeField    : false,

    isDefaultChecked : false,

    _hasDefaultCheckbox    : null,
    _hasIsBuspakjeCheckbox : null,

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
        this.massactionObject.select.observe('change', this.init.bind(this));

        document.observe('postnl:massaction_checkbox_change', this.filterOptions.bind(this));
        document.observe('postnl:massaction_grid_reload', this.init.bind(this));

        return this;
    },

    init : function() {
        this.reset();

        this.massactionObject.onSelectChange();

        this.getIsDefaultCheckbox().disabled = true;
        this.getIsDefaultCheckbox().checked = true;
        this.hideIsBuspakjeContainer();
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
        this.isBuspakjeField = false;
        this._hasDefaultCheckbox = null;
        this._hasIsBuspakjeCheckbox = null;

        return this;
    },

    hasIsDefaultCheckbox : function() {
        if (this._hasDefaultCheckbox != null) {
            return this._hasDefaultCheckbox;
        }

        if ($('postnl_use_default_checkbox')) {
            this._hasDefaultCheckbox = true;
            return true;
        }

        this._hasDefaultCheckbox = false;
        return false;
    },

    getIsDefaultCheckbox : function() {
        if (this.isDefaultCheckbox) {
            return this.isDefaultCheckbox;
        }

        var defaultCheckbox = $('postnl_use_default_checkbox');
        if (!defaultCheckbox) {
            defaultCheckbox = new Element('input');
        }

        this.isDefaultCheckbox = defaultCheckbox;
        return defaultCheckbox;
    },

    hasIsBuspakjeCheckbox : function() {
        if (this._hasIsBuspakjeCheckbox != null) {
            return this._hasIsBuspakjeCheckbox;
        }

        if ($('postnl_is_buspakje_checkbox')) {
            this._hasIsBuspakjeCheckbox = true;
            return true;
        }

        this._hasIsBuspakjeCheckbox = false;
        return false;
    },

    getIsBuspakjeCheckbox : function() {
        if (this.isBuspakjeCheckbox) {
            return this.isBuspakjeCheckbox;
        }

        var buspakjeCheckbox = $('postnl_is_buspakje_checkbox');
        if (!buspakjeCheckbox) {
            buspakjeCheckbox = new Element('input');
        }

        this.isBuspakjeCheckbox = buspakjeCheckbox;
        return buspakjeCheckbox;
    },

    getIsBuspakjeField : function() {
        if (this.isBuspakjeField) {
            return this.isBuspakjeField;
        }

        var buspakjeField = $('postnl_is_buspakje');
        if (!buspakjeField) {
            buspakjeField = new Element('input');
        }

        this.isBuspakjeField = buspakjeField;
        return buspakjeField;
    },

    showIsBuspakjeContainer : function() {
        var isBuspakjeCheckbox = this.getIsBuspakjeCheckbox();
        var container = isBuspakjeCheckbox.up();

        if (!container) {
            return this;
        }

        container.show();
        return this;
    },

    hideIsBuspakjeContainer : function() {
        var isBuspakjeCheckbox = this.getIsBuspakjeCheckbox();
        var container = isBuspakjeCheckbox.up();

        if (!container) {
            return this;
        }

        container.hide();
        return this;
    },

    isDefaultCheckboxChecked : function() {
        var defaultCheckbox = this.getIsDefaultCheckbox();

        if (!this.hasIsDefaultCheckbox()) {
            return false;
        }

        if (defaultCheckbox.checked) {
            return true;
        }

        return false;
    },

    isBuspakjeCheckboxChecked : function() {
        var buspakjeCheckbox = this.getIsBuspakjeCheckbox();

        if (!this.hasIsBuspakjeCheckbox()) {
            return false;
        }

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
        var selectedOption = this.massactionObject.form.select('select')[0];
        if (!selectedOption) {
            return;
        }

        var selectedOptionValue = selectedOption.getValue();
        if (!(/^postnl_(.?)*$/.test(selectedOptionValue))) {
            return;
        }

        this.massactionObject.formAdditional.select('select').each(function(element) {
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
            this.getIsBuspakjeField().setValue(-1);
            this.hideIsBuspakjeContainer();

            return this;
        }

        var filteredType = filteredTypes[0];
        if (filteredType == 'non-postnl') {
            this.isDefaultChecked = this.getIsDefaultCheckbox().checked;

            this.getIsDefaultCheckbox().disabled = true;
            this.getIsDefaultCheckbox().checked = true;
            this.getIsBuspakjeField().setValue(-1);
            this.hideIsBuspakjeContainer();

            return this;
        }

        this.getIsDefaultCheckbox().disabled = false;
        this.getIsDefaultCheckbox().checked = this.isDefaultChecked;

        if (filteredType == 'domestic' || filteredType == 'buspakje') {
            this.getIsBuspakjeField().setValue(this.isBuspakjeCheckboxChecked() ? 1 : '');
            this.showIsBuspakjeContainer();
        } else {
            this.getIsBuspakjeField().setValue(-1);
            this.hideIsBuspakjeContainer();
        }

        if (this.isDefaultCheckboxChecked()) {
            return this;
        }

        var optionsField = $('postnl_' + filteredType + '_options');
        if (optionsField) {
            $('postnl_' + filteredType + '_options').up().show();
        }

        return this;
    },

    filterOptions : function() {
        this.filteredTypes = [];

        $$('input.massaction-checkbox:checked').each(function(element) {
            var shipmentTypeColumn = $('postnl-shipmenttype-' + element.getValue());
            if (shipmentTypeColumn) {
                shipmentType = shipmentTypeColumn.getAttribute('data-product-type');
            } else {
                shipmentType = 'non-postnl';
            }

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