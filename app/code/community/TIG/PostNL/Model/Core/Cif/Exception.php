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
 */
 
/**
 * Custom Exception class for CIF exceptions
 * 
 * @see TIG_PostNL_Exception
 * @see Mage_Core_Exception
 * 
 * @link http://php.net/manual/en/language.exceptions.extending.php
 */
class TIG_PostNL_Model_Core_Cif_Exception extends TIG_PostNL_Exception
{
    /**
     * XML sent to CIF by the extension
     * 
     * @var string The XML string sent to CIF
     */
    protected $_requestXml;
    
    /**
     * XML received in response
     * 
     * @var string The XML string CIF returned
     */
    protected $_responseXml;
    
    /**
     * Array of error numbers
     * 
     * @var array
     */
    protected $_errorNumbers = array();
    
    /**
     * Set $_requestXml to specified value
     * 
     * @return TIG_PostNL_Model_Core_Cif_Exception
     */
    public function setRequestXml($xml)
    {
        $this->_requestXml = $xml;
        
        return $this;
    }
    
    /**
     * Set $_responseXml to specified value
     * 
     * @return TIG_PostNL_Model_Core_Cif_Exception
     */
    public function setResponseXml($xml)
    {
        $this->_responseXml = $xml;
        
        return $this;
    }
    
    /**
     * Set the error numbers array
     * 
     * @param array $errorNumbers
     * 
     * @return TIG_PostNL_Model_Core_Cif_Exception
     */
    public function setErrorNumbers($errorNumbers)
    {
        $this->_errorNumbers = $errorNumbers;
        
        return $this;
    }
    
    /**
     * Get $_requestXml
     * 
     * @return string
     */
    public function getRequestXml()
    {
        return $this->_requestXml;
    }
    
    /**
     * Get $_responseXml
     * 
     * @return string
     */
    public function getResponseXml()
    {
        return $this->_responseXml;
    }
    
    /**
     * get the error numbers array
     * 
     * @return array
     */
    public function getErrorNumbers()
    {
        return $this->_errorNumbers;
    }
    
    /**
     * Add an error number to the error numbers array
     * 
     * @param int $errorNumber
     * 
     * @return TIG_PostNL_Model_Core_Cif_Exception
     */
    public function addErrorNumber($errorNumber)
    {
        $errorNumbers = $this->getErrorNumbers();
        $errorNumbers[] = $errorNumber;
        
        $this->setErrorNumbers($errorNumbers);
        
        return $this;
    }
}
