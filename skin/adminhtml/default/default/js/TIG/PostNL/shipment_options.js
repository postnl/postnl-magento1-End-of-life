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
 *
 */
function increment(value, incrementStep) {
    if (value > 0) {
        return Math.ceil(value/incrementStep) * incrementStep;
    }

    return incrementStep;
}

function addProductOptions() {
    postnlProductOptionsContainer = $('postnl_product_option_container');
    $$('.order-totals-bottom div.a-right').each(function(element) {
        element.insert({
            before: postnlProductOptionsContainer
        });
    });
}

function addExtraCover() {
    postnlExtraCoverValue = $('postnl_extra_cover');
    postnlExtraCoverValue.value = increment(extraCoverValue, 500);

    postnlExtraCoverContainer = $('postnl_extra_cover_container');
    $('postnl_product_option_container').insert({
        after: postnlExtraCoverContainer
    });
}

function addParcelCount() {
    postnlParcelCountContainer = $('postnl_parcel_count_container');
    $('postnl_extra_cover_container').insert({
        after: postnlParcelCountContainer
    });
}

function addGlobalPackFields() {
    postnlShipmentTypeContainer = $('postnl_shipment_type_container');
    postnlTreatAsAbandonedContainer = $('postnl_treat_as_abandoned_container');

    $('postnl_extra_cover_container').insert({
        after: postnlShipmentTypeContainer
    });

    $('postnl_shipment_type_container').insert({
        after: postnlTreatAsAbandonedContainer
    });
}

function showOrHideExtraOptions(productOptions) {
    if (productOptions[productOptions.selectedIndex].hasClassName('extra_cover')) {
        $('postnl_extra_cover_container').show();
    } else {
        $('postnl_extra_cover_container').hide();
    }
}

Validation.add('validate-increment-500', 'The given value must be a multiple of 500.', function(value) {
    if (value % 500 == 0) {
        return true;
    }
    return false;
});

document.observe('dom:loaded', function() {
    addProductOptions();
    addExtraCover();
    addParcelCount();

    if (isGlobalPackShipment) {
        addGlobalPackFields();
    }

    var productOptions = $('postnl_product_option');
    productOptions.observe('change', function() {
        showOrHideExtraOptions(productOptions);
    });

    showOrHideExtraOptions(productOptions);
});