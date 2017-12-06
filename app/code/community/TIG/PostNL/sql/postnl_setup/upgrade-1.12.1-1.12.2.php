<?php
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
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
const XPATH_CUTOFF_TIME             = 'postnl/cif_labels_and_confirming/cutoff_time';
const XPATH_SATURDAY_CUTOFF_TIME      = 'postnl/cif_labels_and_confirming/saturday_cutoff_time';

$installer = $this;
$installer->startSetup();
/***********************************************************************************************************************
 * Reset old webservice settings and insert the test api key.
 **********************************************************************************************************************/
$settingsToReset = array(
    'cif_version_barcode',
    'cif_version_confirming',
    'cif_version_deliverydate',
    'cif_version_labelling',
    'cif_version_location',
    'cif_version_shippingstatus',
    'cif_version_timeframe',
);
$installer->resetWebserviceVersions($settingsToReset)->installTestApikey();

/***********************************************************************************************************************
 * Copy regular cut-off to saturday cut-off.
 **********************************************************************************************************************/
$installer->moveConfigSettingInDb(XPATH_CUTOFF_TIME,XPATH_SATURDAY_CUTOFF_TIME);

$installer->endSetup();