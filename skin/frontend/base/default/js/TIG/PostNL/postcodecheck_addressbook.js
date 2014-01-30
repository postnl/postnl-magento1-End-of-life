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
document.observe('dom:loaded', function() {
	if ($('country')) {
		var countryId = $('country').getValue();
		var postcode = $('zip').getValue();
		var housenumber = $('street2').getValue();
		
		changePostcodeCheckDisabledFields(countryId);
		checkPostcode(postcode, housenumber);
		
		$('street1').setValue(
			$('virtual_street1').getValue()
		);
		
		$('street2').setValue(
			$('virtual_street2').getValue()
		);
		
		$('street3').setValue(
			$('virtual_street3').getValue()
		);
	}
	
	$('country').observe('change', function(event) {
		var countryId = this.getValue();
		alert(countryId);
		changePostcodeCheckDisabledFields(countryId);
	});
	
	$('zip').observe('change', function(event) {
		var postcode = this.getValue();
	    var housenumber = $('virtual_street2').getValue();
	    
	    checkPostcode(postcode, housenumber);
	});
	
	$('virtual_street1').observe('change', function(event) {
		var value = this.getValue();
		
		$('street1').setValue(value);
	});
	
	$('virtual_street2').observe('change', function(event) {
		var value = this.getValue();
		
		$('street2').setValue(value);
		
	    var postcode = $('zip').getValue();
		var housenumber = this.getValue();
	    
	    checkPostcode(postcode, housenumber);
	});
	
	$('virtual_street3').observe('change', function(event) {
		var value = this.getValue();
		
		$('street3').setValue(value);
	});
	
	/**
	 * Updates the street name and city fields. They will be either enabled or disabled based on the countryId parameter.
	 * 
     * @param string countryId|boolean
     * 
     * @return void|boolean
	 */
	function changePostcodeCheckDisabledFields(countryId) {		
		var streetLine = $('virtual_street1');
		var city = $('city');
		
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
	 * 
	 * @return boolean|void
	 */
	function checkPostcode(postcode, housenumber) {
		if (typeof postcodecheckUrl === 'undefined') {
			return false;
		}
		
		if (!postcode.length || !housenumber.length) {
			return false;
		}
		
		if ($('country').getValue() != 'NL') {
			return false;
		}
		
    	$('postnl_address_error').hide();
		new Ajax.Request(postcodecheckUrl,{
            method: 'post',
            parameters: {
            	postcode: postcode,
            	housenumber: housenumber
            },
            onSuccess: function(response) {
                if (response.responseText == 'error') {
            		$('postnl_address_error').show();
                	changePostcodeCheckDisabledFields(false);
                }
            },
            onFailure: function(response) {
            	$('postnl_address_error').show();
            	changePostcodeCheckDisabledFields(false);
            }
        });
        
		return;
	}
});
