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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_MijnpakketController extends Mage_Core_Controller_Front_Action
{
    /**
     * Regular expression used to validate tokens.
     */
    const TOKEN_REGEX = '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/';

    /**
     * Get mijnpakket profile access using a token.
     *
     * @return $this
     */
    public function getProfileAccessAction()
    {
        /**
         * This action may only be called using AJAX requests.
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $token = $this->getRequest()->getParam('token');
        try {
            $token = $this->_validateToken($token);
        } catch (Exception $e) {
            Mage::helper('postnl/mijnpakket')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        $cif = Mage::getModel('postnl_mijnpakket/cif');
        $response = $cif->getProfileAccess($token);
        var_dump($response);

        return $this;
    }

    /**
     * Validate the input token specified.
     *
     * @param string $token
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _validateToken($token)
    {
        $tokenValidator = new Zend_Validate_Regex(self::TOKEN_REGEX);
        if (!$tokenValidator->isValid($token)) {
            throw new TIG_PostNL_Exception(
                $this->__('Invalid token specified: %s', $token),
                'POSTNL-0157'
            );
        }

        return $token;
    }
}