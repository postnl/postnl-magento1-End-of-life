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
class TIG_PostNL_Model_DeliveryOptions_Observer_GoMage_LightCheckout_Ajax
    extends TIG_PostNL_Model_DeliveryOptions_Observer_ShippingMethodAvailable
{
    /**
     * @var null|TIG_PostNL_Model_DeliveryOptions_Observer_IdCheck
     */
    protected $_idCheckObserverModel = null;

    /**
     * Set a registry flag to prevent the PostNL Order from being reset unintentionally.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function setRegistryFlag(Varien_Event_Observer $observer)
    {
        Mage::unregister(self::IGNORE_POSTNL_ORDER_RESET_REGISTRY_KEY);

        $action = $this->getAction($observer);
        if ($action == 'get_totals' || $action == 'discount') {
            Mage::register(self::IGNORE_POSTNL_ORDER_RESET_REGISTRY_KEY, true);
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function validateIdCheck(Varien_Event_Observer $observer)
    {
        $action = $this->getAction($observer);
        if ($action != 'save_payment_methods') {
            return $this;
        }

        /** @var GoMage_Checkout_Model_Type_Onestep_Calculator $calculator */
        $calculator = Mage::getModel('gomage_checkout/type_onestep_calculator', Mage::app()->getRequest());

        /** @var TIG_PostNL_Model_DeliveryOptions_Observer_IdCheck $observer */
        $observer = $this->getIdCheckObserverModel();

        $result = $observer->validate();

        if ($result['error']) {
            $calculator->result->error   = true;
            $calculator->result->message = $result['message'];

            $calculator->prepareResult();
            Mage::app()->getResponse()->setBody(json_encode($calculator->result));
            return $this;
        }

        $observer->saveData();

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return mixed
     */
    protected function getAction(Varien_Event_Observer $observer)
    {
        /** @var GoMage_Checkout_OnepageController $controller */
        /** @noinspection PhpUndefinedMethodInspection */
        $controller = $observer->getControllerAction();

        $request = $controller->getRequest();
        $action = $request->getParam('action', false);

        return $action;
    }

    /**
     * @return TIG_PostNL_Model_DeliveryOptions_Observer_IdCheck
     */
    protected function getIdCheckObserverModel()
    {
        if ($this->_idCheckObserverModel === null) {
            $this->_idCheckObserverModel = Mage::getModel('postnl_deliveryoptions/observer_idCheck');
        }

        return $this->_idCheckObserverModel;
    }
}
