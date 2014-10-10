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
        supportTab.style.display = 'block';
        document.getElementById('postnl_support-state').value = 1;
        document.getElementById('postnl_support-head').parentNode.style.display = 'none';

        // create the wizard
        var postnlWizard = document.createElement('div');
        postnlWizard.id = 'postnl-wizard';
        postnlWizard.className = 'section-config';

        var postnlWizardFieldset = document.createElement('fieldset');
        postnlWizard.appendChild(postnlWizardFieldset);

        var sectionParent = supportTab.parentNode.nextSibling;
        sectionParent.parentNode.insertBefore(postnlWizard, sectionParent);

        // merge old retour setting into address setting
        returnAddress = document.getElementById('postnl_cif_return_address');
        senderAddress = document.getElementById('postnl_cif_sender_address');
        senderAddress.parentNode.appendChild(returnAddress);

        // move 5 existing config sections into the wizard
        var sectionConfigs = $$('.section-config');
        //                               sectionConfigs[0] = support tab
        //                               sectionConfigs[1] = this wizard
        postnlWizardFieldset.appendChild(sectionConfigs[2]);
        postnlWizardFieldset.appendChild(sectionConfigs[3]);
        //                               sectionConfigs[4] = return address
        postnlWizardFieldset.appendChild(sectionConfigs[5]);
        postnlWizardFieldset.appendChild(sectionConfigs[6]);
        postnlWizardFieldset.appendChild(sectionConfigs[7]);

        // add navigation
        var postnlWizardNavigation = document.createElement('ul'),
            postnlWizardList = document.createElement('li'),
            postnlWizardLink = document.createElement('a'),
            fieldsetHeaders = $$('#postnl-wizard .section-config .entry-edit-head a');

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
                // show old retour setting with address setting
                if('postnl_cif_sender_address' == this.rel)
                {
                    document.getElementById('postnl_cif_return_address').style.display = 'block';
                }
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

        postnlWizardFieldset.insertBefore(postnlWizardNavigation, sectionConfigs[2]);

        // init active tab
        $$('#postnl-wizard .section-config fieldset')[0].style.display = 'block';
        $$('#postnl-wizard ul a')[0].className = 'active';

        // create the advanced settings group
        var postnlAdvanced = document.createElement('div');
        postnlAdvanced.id = 'postnl-advanced';
        postnlAdvanced.className = 'section-config';

        var postnlAdvancedFieldset = document.createElement('fieldset');
        postnlAdvanced.appendChild(postnlAdvancedFieldset);

        postnlWizard.parentNode.insertBefore(postnlAdvanced, postnlWizard.nextSibling);

        // move all other sections to the advanced settings group
        for(var i = 7, l = sectionConfigs.size(); i < l; i++)
        {
            postnlAdvancedFieldset.appendChild(sectionConfigs[i]);
        }

        // advanced switch
        /*
        var postnlAdvancedNavigation = document.createElement('ul'),
            listClone = postnlWizardList.cloneNode(),
            linkClone = postnlWizardLink.cloneNode();

        linkClone.href = '#';
        linkClone.onclick = function(){
            $$('#postnl-wizard .section-config').each(function(elem){
                elem.hide();
            });
            document.getElementById(this.rel).parentNode.style.display = 'block';
            document.getElementById(this.rel).style.display = 'block';
            return false;
        };
        linkClone.innerHTML = 'Advanced settings'; // TODO: translate

        listClone.appendChild(linkClone);
        postnlAdvancedNavigation.appendChild(listClone);
        postnlAdvancedFieldset.insertBefore(postnlAdvancedNavigation, sectionConfigs[7]);
        */

        // advanced group header
        var postnlAdvancedHeader = document.createElement('div'),
            postnlAdvancedLink = postnlWizardLink.cloneNode();

        postnlAdvancedHeader.className = 'entry-edit-head collapseable';
        postnlAdvancedLink.innerHTML = 'Advanced settings'; // TODO: translate
        postnlAdvancedHeader.appendChild(postnlAdvancedLink);

        postnlAdvancedFieldset.parentNode.insertBefore(postnlAdvancedHeader, postnlAdvancedFieldset);

        // frontend_class checkbox
        var postnlWizardCheckbox = document.createElement('input'),
            postnlWizardCheckdiv = document.createElement('div');
        postnlWizardCheckbox.type = 'checkbox';
        postnlWizardCheckbox.className = 'postnl-checkbox';

        $$('select.checkbox').each(function(elem){
            // add checkbox placeholder
            var checkboxClone = postnlWizardCheckbox.cloneNode();
            checkboxClone.rel = elem.id;
            checkboxClone.checked = (elem.value == 1); // expect 0 or 1
            elem.parentNode.appendChild(checkboxClone);
            // convert to multiselect and hover over checkbox
            elem.multiple = 'multiple';
            elem.parentNode.style.position = 'relative';
            elem.style.position = 'absolute';
            elem.style.zIndex = '137';
            elem.style.height = '30px';
            elem.style.opacity = '0';
            // native change event not supported, so we move the multiselect
            elem.style.top = (elem.value == 1) ? '-7px' : '7px';
            elem.onclick = function(){
                elem.style.top = (elem.style.top == '7px') ? '-7px' : '7px';
                this.next().checked = (elem.style.top == '7px') ? false : true;
            };
            // add non-clickable area over multiselect
            var checkdivClone = postnlWizardCheckdiv.cloneNode();
            checkdivClone.style.position = 'absolute';
            checkdivClone.style.zIndex = '1337';
            checkdivClone.style.top = '-7px';
            checkdivClone.style.width = '300px';
            checkdivClone.style.height = '15px';
            checkboxClone.parentNode.appendChild(checkdivClone);
            checkdivCloneClone = checkdivClone.cloneNode();
            checkdivCloneClone.style.top = '22px';
            checkboxClone.parentNode.appendChild(checkdivCloneClone);
            // TODO: remove for from legend to avoid substitute click event
        });
    }
});