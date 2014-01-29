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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
document.observe('dom:loaded', function() {
	if ($('billing:country_id')) {
		var countryId = $('billing:country_id').getValue();
		var postcode = $('billing:postcode').getValue();
		var housenumber = $('billing:street2').getValue();
		
		changePostcodeCheckDisabledFields(countryId, 'billing');
		checkPostcode(postcode, housenumber, 'billing');
		
		$('billing:street1').setValue(
			$('virtual:billing:street1').getValue()
		);
		
		$('billing:street2').setValue(
			$('virtual:billing:street2').getValue()
		);
		
		$('billing:street3').setValue(
			$('virtual:billing:street3').getValue()
		);
	}
	
	$('billing:country_id').observe('change', function(event) {
		var countryId = this.getValue();
		
		changePostcodeCheckDisabledFields(countryId, 'billing');
	});
	
	$('billing:postcode').observe('change', function(event) {
		var postcode = this.getValue();
	    var housenumber = $('virtual:billing:street2').getValue();
	    
	    checkPostcode(postcode, housenumber, 'billing');
	});
	
	$('virtual:billing:street1').observe('change', function(event) {
		var value = this.getValue();
		
		$('billing:street1').setValue(value);
	});
	
	$('virtual:billing:street2').observe('change', function(event) {
		var value = this.getValue();
		
		$('billing:street2').setValue(value);
		
	    var postcode = $('billing:postcode').getValue();
		var housenumber = this.getValue();
	    
	    checkPostcode(postcode, housenumber, 'billing');
	});
	
	$('virtual:billing:street3').observe('change', function(event) {
		var value = this.getValue();
		
		$('billing:street3').setValue(value);
	});
	
	if ($('shipping:country_id')) {
		var countryId = $('shipping:country_id').getValue();
		var postcode = $('shipping:postcode').getValue();
		var housenumber = $('shipping:street2').getValue();
		
		changePostcodeCheckDisabledFields(countryId, 'shipping');
		checkPostcode(postcode, housenumber, 'shipping');
		
		$('shipping:street1').setValue(
			$('virtual:shipping:street1').getValue()
		);
		
		$('shipping:street2').setValue(
			$('virtual:shipping:street2').getValue()
		);
		
		$('shipping:street3').setValue(
			$('virtual:shipping:street3').getValue()
		);
	}
	
	$('shipping:country_id').observe('change', function(event) {
		var countryId = this.getValue();
		
		changePostcodeCheckDisabledFields(countryId, 'shipping');
	});
	
	$('shipping:postcode').observe('change', function(event) {
		var postcode = this.getValue();
	    var housenumber = $('virtual:shipping:street2').getValue();
	    
	    checkPostcode(postcode, housenumber, 'shipping');
	});
	
	$('virtual:shipping:street1').observe('change', function(event) {
		var value = this.getValue();
		
		$('shipping:street1').setValue(value);
	});
	
	$('virtual:shipping:street2').observe('change', function(event) {
		var value = this.getValue();
		
		$('shipping:street2').setValue(value);
		
	    var postcode = $('shipping:postcode').getValue();
		var housenumber = this.getValue();
	    
	    checkPostcode(postcode, housenumber, 'shipping');
	});
	
	$('virtual:shipping:street3').observe('change', function(event) {
		var value = this.getValue();
		
		$('shipping:street3').setValue(value);
	});
	
	/**
	 * Updates the street name and city fields. They will be either enabled or disabled based on the countryId parameter.
	 * 
     * @param string countryId|boolean
     * @param string addressType
     * 
     * @return void|boolean
	 */
	function changePostcodeCheckDisabledFields(countryId, addressType) {
		/**
		 * Only the billing and shipping address types are currently supported
		 */
		if (addressType != 'billing' && addressType != 'shipping') {
			return false;
		}
		
		var streetLine = $('virtual:' + addressType + ':street1');
		var city = $(addressType + ':city');
		
		/**
		 * For the Netherlands we need to disable the streetline and city fields.
		 */
		if (countryId == 'NL') {
			streetLine.readOnly = true;
			streetLine.addClassName('postnl-readonly');
			
			city.readOnly = true;
			city.addClassName('postnl-readonly');
			
			return;
		}
		
		/**
		 * For all other countries we need to make sure they're enabled instead.
		 */
		streetLine.readOnly = false;
		streetLine.removeClassName('postnl-readonly');
		
		city.readOnly = false;
		city.removeClassName('postnl-readonly');
		
		return;
	}
	
	/**
	 * Validates the postcode and housenumber combination. If valid, a streetname and city will be returned. Otherwise the
	 * customer will have to manually enter his/her streetname and housenumber.
	 * 
	 * @param string postcode
	 * @param int|string housenumber
	 * @param string addressType
	 * 
	 * @return boolean|void
	 */
	function checkPostcode(postcode, housenumber, addressType) {
		if (typeof postcodecheckUrl === 'undefined') {
			return false;
		}
		
		/**
		 * Only the billing and shipping address types are currently supported
		 */
		if (addressType != 'billing' && addressType != 'shipping') {
			return false;
		}
		
		if (!postcode.length || !housenumber.length) {
			return false;
		}
		
		if ($(addressType + ':country_id').getValue() != 'NL') {
			return false;
		}
		
    	$('postnl_address_error_' + addressType).hide();
		new Ajax.Request(postcodecheckUrl,{
            method: 'post',
            parameters: {
            	postcode: postcode,
            	housenumber: housenumber
            },
            onSuccess: function(response) {
                if (response.responseText == 'error') {
            		$('postnl_address_error_' + addressType).show();
                	changePostcodeCheckDisabledFields(false, addressType);
                }
            },
            onFailure: function(response) {
            	$('postnl_address_error_' + addressType).show();
            	changePostcodeCheckDisabledFields(false, addressType);
            }
        });
        
		return;
	}
});
