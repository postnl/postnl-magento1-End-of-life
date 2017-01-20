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

/**
 * FormPopulate will populate form fields defined in the mapper with the data provided.
 *
 * Sample mapper format:
 *
 * var mapper = {
 *      'form-name': {
 *          'data-key-1': 'input-field-1',
 *      }
 * }
 *
 * Possible key/value combinations:
 *  - 'string': 'string'
 *    direct populate (1-on-1)
 *  - 'string': array
 *    if data is string value, all fields in array populated with 1 string value, (1-on-multiple)
 *    if data is also array, values in array are populated to fields in array (multiple 1-on-1)
 *  - 'string': array(array)
 *    if data is array, values in array are populated to all fields in subArray (multiple 1-on-multiple)
 *
 * @returns {FormPopulate}
 * @constructor
 */
function FormPopulate () {
    this.data           = null;
    this.mapper         = null;
    this.defaultValues  = null;
    this.skipEmpty      = false;

    /**
     * Get data
     *
     * @returns {null|Object}
     */
    this.getData = function() {
        return this.data;
    }

    /**
     * Set data
     *
     * @param data Object
     * @returns {FormPopulate}
     */
    this.setData = function(data) {
        if (typeof data === 'object') {
            this.data = data;
        }
        return this;
    }

    /**
     * Get mapper
     *
     * @returns {null|Object}
     */
    this.getMapper = function() {
        return this.mapper;
    }

    /**
     * Set mapper
     *
     * @param mapper Object
     * @returns {FormPopulate}
     */
    this.setMapper = function(mapper) {
        if (typeof mapper === 'object') {
            this.mapper = mapper;
        }
        return this;
    }

    /**
     * Set skipEmpty to true
     *
     * @returns {FormPopulate}
     */
    this.enableSkipEmpty = function() {
        this.skipEmpty = true;
        return this;
    }

    /**
     * Set skipEmpty to false
     *
     * @returns {FormPopulate}
     */
    this.disableSkipEmpty = function() {
        this.skipEmpty = false;
        return this;
    }

    /**
     * Get skipEmpty value
     *
     * @returns {boolean}
     */
    this.canSkipEmpty = function() {
        return this.skipEmpty;
    }

    /**
     * Get defaultValues
     *
     * @returns {null|Object}
     */
    this.getDefaultValues = function() {
        return this.defaultValues;
    }

    /**
     * Set defaultValues
     *
     * @param defaultValues Object
     * @returns {FormPopulate}
     */
    this.setDefaultValues = function(defaultValues) {
        if (typeof defaultValues === 'object') {
            this.defaultValues = defaultValues;
        }
        return this;
    }

    /**
     * Start populating form
     *
     * @param form string
     * @returns {false|FormPopulate}
     */
    this.populate = function(form) {
        // Return false if either data or mapper isn't set.
        if (!this.getData() || !this.getMapper()) {
            return false;
        }

        // Before we start, use defaultValues to pre-populate where needed
        if (this.getDefaultValues()) {
            var defaultValues = this.getDefaultValues();
            this.populateFormWithData(form, defaultValues[form]);
        }

        // Populate the form with the actual data
        this.populateFormWithData(form, this.getData());

        return this;
    }


    /**
     * Populate form with data & mapper
     *
     * @param form string
     * @param data Object
     * @param mapper Object|Array|undefined
     * @returns {false|FormPopulate}
     */
    this.populateFormWithData = function(form, data, mapper) {
        // Either a given mapper or the configured mapper
        if (!mapper) {
            var mapper = this.getMapper();
        }

        // In case we're using this.mapper, we need to go into the relevant form sub-array. Since we're working with
        // these sub-arrays, we can do so consistently.
        if (mapper[form] !== undefined) {
            mapper = mapper[form];
        }

        for (var key in data) {
            // Continue if data has not haveOwnProperty key, which means it's a proto thing
            if (!data.hasOwnProperty(key)) {
                continue;
            }
            // Continue if there's no mapper field configured for this data value
            if (mapper[key] === undefined) {
                continue;
            }

            // Check if this is an object, if so, check if the data is an object too
            if (typeof mapper[key] === 'object') {
                if (typeof data[key] === 'object') {
                    // both are objects, so we can call populateFormWithData with this nested level
                    this.populateFormWithData(form, data[key], mapper[key]);
                } else {
                    // only the mapper is an object, so we need to set 1 value in multiple fields
                    var targetData = data[key];
                    var arrayMapper = mapper[key];

                    for (var arrayKey in arrayMapper) {
                        this.populateElement($(arrayMapper[arrayKey]), targetData);
                    }
                }
            } else {
                // Single value with single mapped field
                this.populateElement($(mapper[key]), data[key]);
            }
        }
        return this;
    }

    /**
     * Handles actually populating an element with a value
     *
     * @param element object
     * @param value string
     * @returns {FormPopulate}
     */
    this.populateElement = function(element, value) {
        // If element is not a valid value, we're going to ignore it
        if (!element || element === undefined || typeof element === 'function') {
            return this;
        }

        // If value is set or we're not allowed to skip, we put the value even if it's empty
        if (value || !this.canSkipEmpty()) {
            element.setValue(value);
        }
        return this;
    }

    /**
     * Populate all configured forms in this.mapper
     *
     * @returns {false|FormPopulate}
     */
    this.populateAll = function() {
        // If mapper is not set, return false
        if (!this.getMapper()) {
            return false;
        }

        // Loop through mapper to call populate with all configured forms
        for (var form in this.getMapper()) {
            this.populate(form);
        }
        return this;
    }

}
