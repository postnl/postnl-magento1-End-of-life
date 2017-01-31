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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Core_Resource_Integrity extends TIG_PostNL_Model_Resource_Db_Abstract
{
    /**
     * Initialize PostNL integrity model.
     */
    public function _construct()
    {
        $this->_init('postnl_core/integrity', 'integrity_id');
    }

    /**
     * Save the results of an integrity check.
     *
     * @param array $data
     *
     * @return $this
     */
    public function saveIntegrityCheckResults(array $data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->truncateTable($this->getMainTable());

        if (empty($data)) {
            return $this;
        }

        $insertData = array();
        foreach ($data as $entityType => $entityData) {
            if (!is_array($entityData)) {
                continue;
            }

            foreach ($entityData as $error) {
                if (!isset($error['id']) || !isset($error['error_code'])) {
                    continue;
                }

                $insertData[] = array(
                    'entity_type' => $entityType,
                    'entity_id'   => $error['id'],
                    'error_code'  => $error['error_code'],
                );
            }
        }

        $adapter->insertMultiple($this->getMainTable(), $insertData);

        return $this;
    }
}
