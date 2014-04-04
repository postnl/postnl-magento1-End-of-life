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

var MijnpakketLogin = new Class.create();
MijnpakketLogin.prototype = {
    postnlLogin      : null,
    publicId         : null,
    profileAccessUrl : null,

    elementId        : null,
    debug            : null,

    /**
     * @constructor
     *
     * @param publicId
     */
    initialize : function(publicId, profileAccessUrl) {
        this.postnlLogin      = PostNL.Login;
        this.publicId         = publicId;
        this.profileAccessUrl = profileAccessUrl;
    },

    /**
     * @param elementId
     * @param debug
     *
     * @returns {MijnpakketLogin}
     */
    init : function(elementId, debug) {
        this.elementId = elementId;
        this.debug = debug;

        var params = {
            elementId  : this.elementId,
            pId        : this.publicId,
            onResponse : this.loginResponse.bind(this),
            debug      : this.debug
        };

        this.postnlLogin.init(params);

        return this;
    },

    /**
     * @param data
     *
     * @returns {MijnpakketLogin}
     */
    loginResponse : function(data) {
        if (this.debug) {
            console.log(data);
        }

        if (!data.Type || data.Type != 'Transfer' || !data.Token) {
            return this;
        }
        this.getProfileData(data.Token);

        return this;
    },

    /**
     * @param token
     *
     * @returns {MijnpakketLogin}
     */
    getProfileData : function(token) {
        var params = {
            isAjax : true,
            token  : token
        };

        this.getProfileDataRequest = new Ajax.PostnlRequest(this.profileAccessUrl, {
            method     : 'post',
            parameters : params,
            onCreate   : function() {
                document.fire('postnl:getProfileDataRequestStart');
            },
            onSuccess  : function(response) {
                var responseText = response.responseText;
                if (responseText != 'OK') {
                    return;
                }

                document.fire('postnl:costsSaved');
            },
            onComplete : function(response) {
                if (this.debug) {
                    console.log(response.responseText);
                }
            }
        });

        return this;
    }
};