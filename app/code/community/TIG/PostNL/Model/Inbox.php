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
 *
 * The only reason this class exists is because the add() method and it's derivatives are only present since EE 1.12 and
 * CE 1.7. This class adds those methods in case they're missing. Each method will return it's parent if it exists to
 * ensure forwards compatibility.
 */
class TIG_PostNL_Model_Inbox extends Mage_AdminNotification_Model_Inbox
{
    /**
     * Add new message.
     *
     * @param int          $severity
     * @param string       $title
     * @param string|array $description
     * @param string       $url
     * @param bool         $isInternal
     *
     * @throws TIG_PostNL_Exception
     *
     * @return Mage_AdminNotification_Model_Inbox
     */
    public function add($severity, $title, $description, $url = '', $isInternal = true)
    {
        if ($this->_parentMethodExists($this, 'add')) {
            return parent::add($severity, $title, $description, $url, $isInternal);
        }

        if (!$this->getSeverities($severity)) {
            throw new TIG_PostNL_Exception(Mage::helper('postnl')->__('Wrong message type'), 'POSTNL-0087');
        }

        if (is_array($description)) {
            $description = '<ul><li>' . implode('</li><li>', $description) . '</li></ul>';
        }

        $date = date('Y-m-d H:i:s');
        $this->parse(
            array(
                array(
                    'severity'    => $severity,
                    'date_added'  => $date,
                    'title'       => $title,
                    'description' => $description,
                    'url'         => $url,
                    'internal'    => $isInternal
                )
            )
        );
        return $this;
    }

    /**
     * Add critical severity message.
     *
     * @param string       $title
     * @param string|array $description
     * @param string       $url
     * @param bool         $isInternal
     *
     * @return Mage_AdminNotification_Model_Inbox
     */
    public function addCritical($title, $description, $url = '', $isInternal = true)
    {
        if ($this->_parentMethodExists($this, 'addCritical')) {
            return parent::addCritical($title, $description, $url, $isInternal);
        }

        $this->add(self::SEVERITY_CRITICAL, $title, $description, $url, $isInternal);
        return $this;
    }

    /**
     * Add major severity message.
     *
     * @param string       $title
     * @param string|array $description
     * @param string       $url
     * @param bool         $isInternal
     *
     * @return Mage_AdminNotification_Model_Inbox
     */
    public function addMajor($title, $description, $url = '', $isInternal = true)
    {
        if ($this->_parentMethodExists($this, 'addMajor')) {
            return parent::addMajor($title, $description, $url, $isInternal);
        }

        $this->add(self::SEVERITY_MAJOR, $title, $description, $url, $isInternal);
        return $this;
    }

    /**
     * Add minor severity message.
     *
     * @param string       $title
     * @param string|array $description
     * @param string       $url
     * @param bool         $isInternal
     *
     * @return Mage_AdminNotification_Model_Inbox
     */
    public function addMinor($title, $description, $url = '', $isInternal = true)
    {
        if ($this->_parentMethodExists($this, 'addMinor')) {
            return parent::addMinor($title, $description, $url, $isInternal);
        }

        $this->add(self::SEVERITY_MINOR, $title, $description, $url, $isInternal);
        return $this;
    }

    /**
     * Add notice.
     *
     * @param string       $title
     * @param string|array $description
     * @param string       $url
     * @param bool         $isInternal
     *
     * @return Mage_AdminNotification_Model_Inbox
     */
    public function addNotice($title, $description, $url = '', $isInternal = true)
    {
        if ($this->_parentMethodExists($this, 'addNotice')) {
            return parent::addNotice($title, $description, $url, $isInternal);
        }

        $this->add(self::SEVERITY_NOTICE, $title, $description, $url, $isInternal);
        return $this;
    }

    /**
     * Checks parent class to see if the specified method exists.
     *
     * @param object $object
     * @param string $method
     *
     * @return boolean
     */
    protected function _parentMethodExists($object, $method)
    {
        $parentClass = get_parent_class($object);
        if ($parentClass === false) {
            return false;
        }

        if (method_exists($parentClass, $method)) {
            return true;
        }

        return false;
    }
}
