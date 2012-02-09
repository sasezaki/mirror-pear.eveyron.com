<?php
/**
 * Eveyron PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * @category Eveyron
 * @author eveyron@eveyron.com
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @link http://www.eveyron.com
 * @version 2010-10-16 13:33:33Z
 */
 
/**
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Http
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Http_MultiClient_Adapter {
    /**
     * Parameters array
     *
     * @var array
     */
    protected $_config = array();
    
    /**
     * @var array of Zend_Http_Client
     */
    protected $_clients = array();
       
    /**
     * 
     * @param mixed           $key
     * @param Zend_Http_Client $client
     * @return 
     */
    public function setClient($key, Zend_Http_Client $client) {
        $this->_clients[$key] = $client;
    }
           
    /**
     * Sets $clients.
     *
     * @param object $clients
     * @see Eveyron_Zend_Http_MultiClient_Adapter::$clients
     */
    public function setClients($clients, $class) {
        if (is_array($clients)) {
            foreach ($clients as $key => $client) {                
                if ($client->getAdapter() == null) {
                    $config = $client->getConfig();
                    $client->setAdapter($config['adapter']);
                }
                if (!$client->getAdapter() instanceof $class)
                    continue;
                    
                $this->_clients[$key] = $client;
            }
        }
        
        return $this;
    }
    
    /**
     * Returns $clients.
     *
     * @see Eveyron_Zend_Http_MultiClient_Adapter::$clients
     */
    public function getClients() {
        return $this->_clients;
    }
    
    /**
     * 
     * @param mixed $key
     * @return Zend_Http_Client
     */    
    public function getClient($key) {
        return $this->_clients[$key];
    }
                
    /**
     * Set the configuration array for the adapter
     *
     * @param Zend_Config | array $config
     */
    public function setConfig($config = array()) {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
            
        } elseif (!is_array($config)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Array or Zend_Config object expected, got '.gettype($config));
        }
        
        foreach ($config as $k=>$v) {
            $this->_config[strtolower($k)] = $v;
        }
        
        foreach ($this->getClients() as $client) {
            $client->getAdapter()->setConfig($this->_config);
        }
    }
    
    /**
     * Retrieve the array of all configuration options
     *
     * @return array
     */
    public function getConfig() {
        return $this->_config;
    }    
}