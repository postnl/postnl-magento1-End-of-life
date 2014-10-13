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
document.observe('dom:loaded', function(){

    if(null !== document.getElementById('postnl_support'))
    {
        // show the support tab
        var supportTab = document.getElementById('postnl_support');
        supportTab.show();

        $('postnl_support-state').setValue(1);
        $('postnl_support-head').up().hide();

        // create the wizard
        var postnlWizard = document.createElement('div');
        postnlWizard.id = 'postnl-wizard';
        postnlWizard.className = 'section-config';

        var postnlWizardFieldset = document.createElement('fieldset');
        postnlWizard.appendChild(postnlWizardFieldset);

        var sectionParent = supportTab.parentNode.nextSibling;
        sectionParent.parentNode.insertBefore(postnlWizard, sectionParent);

        // move 5 existing config sections into the wizard
        var wizardSectionConfigs = $$('.section-config.postnl-wizard');

        wizardSectionConfigs.each(function(element) {
            postnlWizardFieldset.appendChild(element);
        });

        // add navigation
        var postnlWizardNavigation = document.createElement('ul'),
            postnlWizardList = document.createElement('li'),
            postnlWizardLink = document.createElement('a');

        var step = 0;
        $$('#postnl-wizard .section-config .entry-edit-head a').each(function(elem){
            var listClone = postnlWizardList.cloneNode(),
                linkClone = postnlWizardLink.cloneNode();

            linkClone.href = '#';
            linkClone.rel = elem.id.replace('-head', '');
            linkClone.onclick = function(){
                // switch tabs
                $$('#postnl-wizard .section-config').each(function(elem){
                    elem.hide();
                });
                document.getElementById(this.rel).parentNode.style.display = 'block';
                document.getElementById(this.rel).style.display = 'block';

                // switch wizard nav active state
                $$('#postnl-wizard ul a').each(function(elem){
                    elem.className = '';
                });
                this.className = 'active';
                return false;
            };
            linkClone.innerHTML = (++step) + '. ' + elem.innerHTML;

            listClone.appendChild(linkClone);
            postnlWizardNavigation.appendChild(listClone);
        });

        postnlWizardFieldset.firstChild.insert({
            before: postnlWizardNavigation
        });

        // init active tab
        postnlWizard.select('.section-config fieldset').invoke('hide');
        postnlWizard.select('.section-config fieldset')[0].show();
        postnlWizard.select('ul a')[0].addClassName('active');

        // create the advanced settings group
        var postnlAdvanced = document.createElement('div');
        postnlAdvanced.id = 'postnl-advanced';
        postnlAdvanced.className = 'section-config';

        var postnlAdvancedFieldset = document.createElement('fieldset');
        postnlAdvancedFieldset.id = 'postnl_advanced';
        postnlAdvancedFieldset.style.display = 'none';
        postnlAdvanced.appendChild(postnlAdvancedFieldset);

        postnlWizard.parentNode.insertBefore(postnlAdvanced, postnlWizard.nextSibling);

        // move all other sections to the advanced settings group
        $$('.section-config:not(.postnl-wizard,.postnl-support,#postnl-advanced,#postnl-wizard)').each(function(element) {
            postnlAdvancedFieldset.appendChild(element);
        });

        // advanced group header
        var postnlAdvancedHeader = document.createElement('div'),
            postnlAdvancedLink = postnlWizardLink.cloneNode();

        postnlAdvancedHeader.className = 'entry-edit-head collapseable';
        postnlAdvancedLink.innerHTML = 'Advanced settings'; // TODO: translate
        postnlAdvancedLink.id = 'postnl_advanced-head';
        postnlAdvancedLink.href = '#';
        postnlAdvancedLink.onclick = function() {
            Fieldset.toggleCollapse('postnl_advanced', '');
            return false;
        };
        postnlAdvancedHeader.appendChild(postnlAdvancedLink);

        postnlAdvancedFieldset.parentNode.insertBefore(postnlAdvancedHeader, postnlAdvancedFieldset);
    }
});