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
    this.mapper         = {};
    this.skipEmpty      = false;

    /**
     * Get data
     *
     * @returns {null|*}
     */
    this.getData = function() {
        return this.data;
    }

    /**
     * Set data (javascript object expected)
     *
     * @param data
     * @returns {FormPopulate}
     */
    this.setData = function(data) {
        this.data = data;
        return this;
    }

    /**
     * Get mapper
     *
     * @returns {{}|*}
     */
    this.getMapper = function() {
        return this.mapper;
    }

    /**
     * Set mapper (javascript object expected, with values being either strings or arrays)
     *
     * @param mapper
     * @returns {FormPopulate}
     */
    this.setMapper = function(mapper) {
        this.mapper = mapper;
        return this;
    }

    /**
     * Get skipEmpty
     *
     * @returns {{}|*}
     */
    this.getSkipEmpty = function() {
        return this.skipEmpty;
    }

    /**
     * Set skipEmpty value (bool)
     *
     * @param value (bool)
     * @returns {FormPopulate}
     */
    this.setSkipEmpty = function(value) {
        this.skipEmpty = value;
        return this;
    }

    /**
     * Get defaultValues
     *
     * @returns {{}|*}
     */
    this.getDefaultValues = function() {
        return this.defaultValues;
    }

    /**
     * Set defaultValues (javascript object expected, with values being either strings or arrays)
     *
     * @param defaultValues
     * @returns {FormPopulate}
     */
    this.setDefaultValues = function(defaultValues) {
        this.defaultValues = defaultValues;
        return this;
    }

    /**
     * Populate elements based on mapper
     *
     * @param form
     * @returns {FormPopulate}
     */
    this.populate = function(form) {
        var data = this.getData();
        var defaultValues = this.getDefaultValues();

        // before we start, use defaultValues to pre-populate where needed, if set
        if (defaultValues !== undefined) {
            this.populateFormWithData(form, defaultValues[form]);
        }

        // now populate with the actual data
        this.populateFormWithData(form, data);

        return this;
    }

    /**
     * Generic function to populate <form> with <data>
     *
     * @param form
     * @param data
     * @returns {FormPopulate}
     */
    this.populateFormWithData = function(form, data) {
        var mapper = this.getMapper();

        if (mapper[form] === undefined || !data) {
            return this;
        }


        for (var key in data) {
            // check if there's a mapper field configured for this data value
            if (mapper[form][key] === undefined) {
                continue;
            }

            // check if this is an array, if so, check if the data is an array too
            if (typeof mapper[form][key] === 'object') {
                if (typeof data[key] === 'object') {
                    // both are arrays, so we need to loop & set all data in their mapped fields
                    var arrayData = data[key];
                    var arrayMapper = mapper[form][key];

                    for (var arrayKey in arrayData) {
                        if (typeof arrayMapper[arrayKey] === 'function') { continue; }

                        // check if the mapped fields are array-in-array (meaning linked to array data but multiple
                        // target fields)
                        if (typeof arrayMapper[arrayKey] === 'object') {
                            var subArrayMapper = arrayMapper[arrayKey];
                            for (var subArrayKey in subArrayMapper) {
                                if (typeof arrayMapper[subArrayKey] === 'function') { continue; }

                                this.populateElement($(subArrayMapper[subArrayKey]), arrayData[arrayKey]);
                            }
                        } else {
                            this.populateElement($(arrayMapper[arrayKey]), arrayData[arrayKey]);
                        }

                    }
                } else {
                    // only the mapper is an array, so we need to set 1 value in multiple fields
                    var targetData = data[key];
                    var arrayMapper = mapper[form][key];

                    for (var arrayKey in arrayMapper) {
                        if (typeof arrayMapper[arrayKey] === 'function') { continue; }

                        this.populateElement($(arrayMapper[arrayKey]), targetData);
                    }
                }
            } else {
                // single value with single mapped field
                this.populateElement($(mapper[form][key]), data[key]);
            }
        }

    }

    this.populateElement = function(element, value, defaultValue) {
        if (element && (value || (!value && !this.getSkipEmpty()))) {
            element.setValue(value);
        } else if (element && defaultValue) {
            element.setValue(defaultValue);
        }
    }

    /**
     * Populates all mapped forms
     *
     * @returns {FormPopulate}
     */
    this.populateAll = function() {
        for (var form in this.getMapper()) {
            this.populate(form);
        }
        return this;
    }

    /**
     * Copy all data from source to target, based on mapper
     *
     * @param source
     * @param target
     * @returns {FormPopulate}
     */
    this.copyFormData = function(source, target) {
        var mapper = this.getMapper();
        if (mapper[source] === undefined || mapper[target] === undefined) {
            return this;
        }

        for (var key in mapper[source]) {
            this.populateElement($(mapper[target][key]), $(mapper[source][key]).value);
        }
        return this;
    }

    /**
     * Without the use of a mapper, simply put .value from source into target
     *
     * @param source
     * @param target
     * @returns {FormPopulate}
     */
    this.copyElementData = function(source, target) {
        this.populateElement($(target), $(source).value);
        return this;
    }

    return this;
}