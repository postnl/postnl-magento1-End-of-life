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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
/**
 * Location of the Magento WSDL definition.
 */
$api                = 'http://your.magento.shop/api/v2_soap/?wsdl=1';

/**
 * The username and API key of your API user.
 */
$username           = 'test';
$apiKey             = 'testing';

/**
 * The method you intend to use.
 */
$method             = '';

/**
 * Some parameters for the API method.
 */
$orderIds           = array();
$shipmentIds        = array();
$labelSize          = 'A4';
$labelStartPosition = 1;

/**
 * Whether the API is in WS-I mode or not.
 */
$useWsiMode         = false;

/**
 * Modify the above parameters based on parameters in the $_GET super global.
 */
if (!empty($_GET['api'])) {
    $api = $_GET['api'];
}

if (!empty($_GET['username'])) {
    $username = $_GET['username'];
}

if (!empty($_GET['api_key'])) {
    $apiKey = $_GET['api_key'];
}

if (!empty($_GET['method'])) {
    $method = $_GET['method'];
}

if (!empty($_GET['order_ids'])) {
    $orderIds = $_GET['order_ids'];
}

if (!empty($_GET['shipment_ids'])) {
    $shipmentIds = $_GET['shipment_ids'];
}

if (!empty($_GET['label_size'])) {
    $labelSize = $_GET['label_size'];
}

if (!empty($_GET['label_start_position'])) {
    $labelStartPosition = $_GET['label_start_position'];
}

if (isset($_GET['use_wsi_mode'])) {
    $useWsiMode = (bool) (int) $_GET['use_wsi_mode'];
}

/**
 * Disable the PHP time limit to prevent timeout errors when processing a large number of orders or shipments.
 */
set_time_limit(0);

/**
 * Save the current timestamp for time-tracking purposes.
 */
$time = microtime(true);
try {
    /**
     * Instantiate the API client.
     */
    $cli = new SoapClient($api, array('trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE));

    /**
     * Save the current timestamp again.
     */
    $clientTime = microtime(true);

    if (false === $useWsiMode) {
        /**
         * Non-WSI
         */

        /**
         * Login and get a session ID for future API calls.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $sessionId = $cli->login($username, $apiKey);

        /**
         * Save the current timestamp again.
         */
        $sessionTime = microtime(true);

        /**
         * Call the requested API method with the supplied parameters.
         */
        switch ($method) {
            case 'postnlCreateShipments': //no break
            case 'postnlCreateShipmentsConfirmAndPrintLabels':
                $result = $cli->$method($sessionId, $orderIds);
                break;
            case 'postnlPrintShippingLabels': //no break
            case 'postnlConfirmAndPrintShippingLabels':
                $result = $cli->$method($sessionId, $shipmentIds, $labelSize, $labelStartPosition);
                break;
            case 'postnlConfirmShipments': //no break
            case 'postnlGetTrackAndTraceUrls': //no break
            case 'postnlGetStatusInfo':
                $result = $cli->$method($sessionId, $shipmentIds);
                break;
            default:
                die('Invalid method selected: ' . $method);
        }

        /**
         * Save the timestamp again.
         */
        $endTime = microtime(true);

        /**
         * If the method returned a label pdf, save it to a pdf file.
         */
        if (isset($result[0]->label)) {
            foreach ($result as $resultItem) {
                file_put_contents('result_' . $resultItem->shipment_id . '.pdf', base64_decode($resultItem->label));
            }
        }
    } else {
        /**
         * WSI
         */

        /**
         * Login and get a session ID for future API calls.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $sessionId = $cli->login(array('username' => $username, 'apiKey' => $apiKey));

        /**
         * Save the current timestamp again.
         */
        $sessionTime = microtime(true);

        /**
         * Call the requested API method with the supplied parameters.
         */
        switch ($method) {
            case 'postnlCreateShipments':
                $params = array(
                    'sessionId' => $sessionId->result,
                    'orderIds'  => $orderIds,
                );
                $result = $cli->$method($params);
                break;
            case 'postnlCreateShipmentsConfirmAndPrintLabels':
                $params = array(
                    'sessionId'          => $sessionId->result,
                    'orderIds'           => $orderIds,
                    'labelSize'          => $labelSize,
                    'labelStartPosition' => $labelStartPosition,
                );
                $result = $cli->$method($params);
                break;
            case 'postnlPrintShippingLabels': //no break
            case 'postnlConfirmAndPrintShippingLabels':
                $params = array(
                    'sessionId'          => $sessionId->result,
                    'shipmentIds'        => $shipmentIds,
                    'labelSize'          => $labelSize,
                    'labelStartPosition' => $labelStartPosition,
                );
                $result = $cli->$method($params);
                break;
            case 'postnlConfirmShipments': //no break
            case 'postnlGetTrackAndTraceUrls': //no break
            case 'postnlGetStatusInfo':
                $params = array(
                    'sessionId'   => $sessionId->result,
                    'shipmentIds' => $shipmentIds,
                );
                $result = $cli->$method($params);
                break;
            default:
                die('Invalid method selected: ' . $method);
        }

        /**
         * Save the timestamp again.
         */
        $endTime = microtime(true);

        /**
         * If the method returned a label pdf, save it to a pdf file.
         */
        $resultArray = $result->result->complexObjectArray;
        if (is_array($resultArray) && isset($resultArray[0]->label)) {
            foreach ($result->result->complexObjectArray as $resultItem) {
                file_put_contents('result_' . $resultItem->shipment_id . '.pdf', base64_decode($resultItem->label));
            }
        } elseif (isset($resultArray->label)) {
            /** @noinspection PhpUndefinedFieldInspection */
            file_put_contents('result_' . $resultArray->shipment_id . '.pdf', base64_decode($resultArray->label));
        }
    }

    /**
     * Dump the result as well as the XML request and response for debugging purposes.
     */
    echo '<pre>';
    var_dump($result);
    echo PHP_EOL
        . 'REQUEST:'
        . PHP_EOL
        . htmlentities(formatXML($cli->__getLastRequest()))
        . PHP_EOL . 'RESPONSE:'
        . PHP_EOL
        . htmlentities(formatXML($cli->__getLastResponse()))
        . PHP_EOL
        . 'TIMING:'
        . PHP_EOL;

    /**
     * Dump the saved timestamps.
     */
    var_dump(
        array(
            'start'   => $time,
            'client'  => $clientTime,
            'session' => $sessionTime,
            'end'     => $endTime
        )
    );

    echo PHP_EOL
        . 'DURATION:'
        . PHP_EOL;

    /**
     * Dump the duration of the various parts of the above code.
     */
    var_dump(
        array(
            'start'   => $time,
            'client'  => $clientTime - $time,
            'session' => $sessionTime - $clientTime,
            'end'     => $endTime - $sessionTime
        )
    );

} catch(Exception $e) {
    /**
     * Dump the result as well as the XML request and response for debugging purposes.
     */
    echo '<pre>'
        . $e
        . PHP_EOL
        . 'REQUEST:'
        . PHP_EOL
        . htmlentities(formatXML($cli->__getLastRequest()))
        . PHP_EOL . 'RESPONSE:'
        . PHP_EOL
        . htmlentities(formatXML($cli->__getLastResponse()));
}

/**
 * Format XML for easier reading.
 *
 * @param $xml
 *
 * @return string
 */
function formatXML($xml) {
    if (empty($xml)) {
        return '';
    }

    $dom = new DOMDocument('1.0');
    $dom->loadXML($xml);
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    return $dom->saveXML();
}
