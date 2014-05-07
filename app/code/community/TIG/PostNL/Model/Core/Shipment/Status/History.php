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
 *
 * Class TIG_PostNL_Model_Core_Shipment_Status_History
 *
 * @method string getTimestamp()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setTimestamp(string $value)
 * @method string getRouteName()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setRouteName(string $value)
 * @method string getDescription()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setDescription(string $value)
 * @method string getLocationCode()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setLocationCode(string $value)
 * @method int getStatusId()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setStatusId(int $value)
 * @method string getCode()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setCode(string $value)
 * @method string getDestinationLocationCode()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setDestinationLocationCode(string $value)
 * @method string getRouteCode()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setRouteCode(string $value)
 * @method int getParentId()
 * @method TIG_PostNL_Model_Core_Shipment_Status_History setParentId(int $value)
 */
class TIG_PostNL_Model_Core_Shipment_Status_History extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'postnl_shipment_status_history';

    public function _construct()
    {
        $this->_init('postnl_core/shipment_status_history');
    }

    /**
     * Set the 'phase' attribute. The phase must be formatted as a 2 digit number (i.e. 01, 04, 12, 99 etc.)
     *
     * @param string | int $phase
     *
     * @return TIG_PostNL_Model_Core_Shipment_Status_History
     */
    public function setPhase($phase)
    {
        if (strlen($phase) < 2) {
            $phase = '0' . $phase;
        }

        $this->setData('phase', $phase);
        return $this;
    }

    /**
     * Load a history item based on a postnl shipment id and a status code.
     *
     * @param int $shipmentId
     * @param string $code
     *
     * @return TIG_PostNL_Model_Core_Shipment_Status_History
     */
    public function loadByShipmentIdAndCode($shipmentId, $code)
    {
        /**
         * @var TIG_PostNL_Model_Core_Resource_Shipment_Status_History_Collection $collection
         */
        $collection = $this->getCollection();
        $collection->addFieldToSelect('status_id')
                   ->addFieldToFilter('parent_id', array('eq' => $shipmentId))
                   ->addFieldToFilter('code', array('eq' => $code));

        $collection->getSelect()->limit(1); //we only want 1 item

        $id = $collection->getFirstItem()->getId();

        if ($id) {
            $this->load($id);
        }

        return $this;
    }

    /**
     * Check if a status history item exists for the given postnl shipment and status
     *
     * @param int $shipmentId
     * @param     $status
     *
     * @return boolean
     */
    public function statusHistoryIsNew($shipmentId, $status)
    {
        /**
         * @var TIG_PostNL_Model_Core_Resource_Shipment_Status_History_Collection $collection
         */
        $collection = $this->getCollection();
        $collection->addFieldToSelect('status_id')
                   ->addFieldToFilter('parent_id', array('eq' => $shipmentId))
                   ->addFieldToFilter('code', array('eq' => $status->Code));

        if ($status->LocationCode !== '') {
            $collection->addFieldToFilter('location_code', array('eq' => $status->LocationCode));
        }

        if ($status->DestinationLocationCode !== '') {
            $collection->addFieldToFilter('destination_location_code', array('eq' => $status->DestinationLocationCode));
        }

        if ($status->RouteCode !== '') {
            $collection->addFieldToFilter('route_code', array('eq' => $status->RouteCode));
        }

        if ($status->RouteName !== '') {
            $collection->addFieldToFilter('route_name', array('eq' => $status->RouteName));
        }

        if ($collection->getSize() < 1) {
            return true;
        }

        return false;
    }
}
