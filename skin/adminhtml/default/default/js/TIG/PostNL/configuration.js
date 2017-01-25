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
 *
 * @todo move hardcoded classnames and id to config object
 * @todo remove PostNl references for portability
 */
PostnlConfigWizard = new Class.create();
PostnlConfigWizard.prototype = {
    config       : {},
    postnlWizard : null,

    initialize : function(config) {
        this.config = Object.extend({
            wizardId     : 'postnl_wizard',
            wizardClass : 'postnl-wizard',
            sectionClass : 'section-config',
            supportTab   : $('postnl_support'),
            advancedGroupContainerId : 'postnl_advanced_container',
            advancedGroupId          : 'postnl_advanced_group'
        }, config || {});
    },

    render : function() {
        this.showSupportTab()
            .createWizard()
            .createAdvancedGroup()
            .registerObservers()
            .modusColor();

        $('postnl_config_loader').hide();
        $('postnl_config_form').show();

        return this;
    },

    registerObservers : function() {
        $$('#postnl-wizard ul a[href^="#"]').each(function(elem){
            Event.observe(elem, 'click', function(){
                var hash = elem.href;
                if(window.history.pushState) {
                    window.history.pushState(null, null, hash);
                    this.toHash();
                } else {
                    window.location.hash = hash;
                }

            }.bind(this));
        }.bind(this));

        window.onhashchange = function() {this.toHash('')}.bind(this);
        window.onload = function() {this.toHash('')}.bind(this);

        $$('#row_postnl_cif_mode input').invoke(
            'observe',
            'change',
            function() {
                this.modusColor();
            }.bind(this)
        );

        return this;
    },

    showSupportTab : function() {
        var config = this.config;

        var supportTab = config.supportTab;
        if (!supportTab) {
            return this;
        }

        supportTab.show();

        var supportTabState = supportTab.previous('input');
        if (supportTabState) {
            supportTabState.setValue(1);
        }

        var supportTabHead = supportTab.previous('div.entry-edit-head');
        if (supportTabHead) {
            supportTabHead.hide();
        }

        return this;
    },

    createWizard: function() {
        var config = this.config;
        var postnlWizard = new Element('div', {
            id      : config.wizardId,
            'class' : config.sectionClass
        });

        var postnlWizardFieldset = new Element('div', {
            'class' : 'fieldset'
        });

        postnlWizard.insert(postnlWizardFieldset);

        var supportTab = config.supportTab;
        if (supportTab) {
            supportTab.parentNode.insertBefore(postnlWizard, supportTab.nextSibling);
        } else {
            $('config_edit_form').insert({
                top : postnlWizard
            });
        }

        var wizardSectionConfigs = $$('.' + config.sectionClass + '.' + config.wizardClass);
        wizardSectionConfigs.each(function(element) {
            postnlWizardFieldset.insert(element);
        });

        var postnlWizardNavigation = new Element('ul');

        var step = 0;
        $$('#' + config.wizardId + ' .' + config.sectionClass + ' .entry-edit-head a').each(function(element) {
            step++;

            var listClone = new Element('li');
            var linkClone = new Element('a', {
                href      : '#wizard' + step,
                rel       : element.id.replace('-head', '')
            });

            linkClone.update(step + '. ' + element.innerHTML);
            linkClone.onclick   = function(){
                this.toStep(element.id.replace('-head', ''));
            }.bind(this);

            listClone.appendChild(linkClone);
            postnlWizardNavigation.appendChild(listClone);
        }.bind(this));

        postnlWizardFieldset.insert({
            top : postnlWizardNavigation
        });

        postnlWizard.select('.section-config fieldset').invoke('hide');
        postnlWizard.select('.section-config fieldset')[0].show();
        postnlWizard.select('ul a')[0].addClassName('active');

        this.postnlWizard = postnlWizard;

        return this;
    },

    createAdvancedGroup : function() {
        var postnlWizard = this.postnlWizard;

        if (!postnlWizard) {
            return this;
        }

        var config = this.config;
        var postnlAdvanced = new Element('div', {
            id      : config.advancedGroupContainerId,
            'class' : config.sectionClass
        });

        var postnlAdvancedFieldset = new Element('fieldset', {
            id : config.advancedGroupId,
            style : 'display:none;'
        });

        postnlAdvanced.insert(postnlAdvancedFieldset);
        postnlWizard.insert({
            after : postnlAdvanced
        });

        /**
         * move all other sections to the advanced settings group
         *
         * @todo get class list from config
         */
        $$('.section-config').each(
            function(element) {
                if (element.hasClassName(config.wizardClass)
                    || element.hasClassName('postnl-support')
                    || element.id == config.advancedGroupId
                    || element.id == config.advancedGroupContainerId
                    || element.id == config.wizardId
                ) {
                    return;
                }

                postnlAdvancedFieldset.insert(element);
            }
        );

        var postnlAdvancedHeader = new Element('div', {
            'class' : 'entry-edit-head collapseable'
        });

        var postnlAdvancedLink   = new Element('a', {
            id        : 'postnl_advanced_group-head',
            href      : 'javascript:return false;'
        });
        postnlAdvancedLink.update(Translator.translate('Advanced Settings'));
        postnlAdvancedLink.onclick = function() {
            Fieldset.toggleCollapse('postnl_advanced_group');
            return false;
        };

        postnlAdvancedHeader.insert(postnlAdvancedLink);
        postnlAdvancedFieldset.up().insert({
            top : postnlAdvancedHeader
        });

        return this;
    },

    toStep : function(rel) {
        // switch tabs
        $$('#postnl_wizard .section-config').invoke('hide');

        $(rel).show();
        $(rel).up().show();

        // switch wizard nav active state
        $$('#postnl_wizard ul a').invoke('removeClassName', 'active');
        $$('a[rel="'+rel+'"]')[0].addClassName('active');

        return false;
    },

    toHash : function(hash) {
        if (!hash) {
            hash = window.location.hash;
        }

        var target = $$('a[href="' + hash + '"]')[0];
        return this.toStep(target.rel);
    },

    modusColor : function() {
        var modeField = $('row_postnl_cif_mode');
        if (!modeField) {
            return;
        }

        var selectedRadioButton = $$('input[name="groups[cif][fields][mode][value]"]:checked')[0];
        var value = '';
        if (selectedRadioButton) {
            value = selectedRadioButton.getValue();
        }

        switch(value)
        {
            case '1':
                modeField.style.background = '#FF7';
                break;
            case '2':
                modeField.style.background = '#7F7';
                break;
            case '0': //no break
            default:
                modeField.style.background = '#F77';
                break;
        }
    }
};
