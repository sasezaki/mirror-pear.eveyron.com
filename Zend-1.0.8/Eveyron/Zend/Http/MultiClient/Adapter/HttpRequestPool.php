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
 * @see Eveyron_Zend_Http_MultiClient_Adapter
 */
require_once 'Eveyron/Zend/Http/MultiClient/Adapter.php';

/**
 * @see Eveyron_Zend_Http_MultiClient_Adapter_Interface
 */
require_once 'Eveyron/Zend/Http/MultiClient/Adapter/Interface.php';

/**
 * @uses Eveyron_Zend_Http_MultiClient_Adapter
 * @uses Eveyron_Zend_Http_MultiClient_Adapter_Interface
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Http
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Http_MultiClient_Adapter_HttpRequestPool
    extends Eveyron_Zend_Http_MultiClient_Adapter 
    implements Eveyron_Zend_Http_MultiClient_Adapter_Interface
{    
    /**
     * The HttpRequestPool object
     *
     * @var HttpRequestPool|null
     */
    protected $_httpRequestPool = null;
    
    /**
     * @var array
     */
    protected $_responses = array();
    
    /**
     * Number of active handles
     * 
     * @var int
     */
    protected $_running;
      
    /**
     * @var array
     */
    protected $_key_handle_map = array();
        
    /**
     *
     * @return void
     */
    public function __construct() {
        if (!extension_loaded('http')) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Http extension has to be loaded to use Eveyron_Zend_Http_MultiClient_Adapter_HttpRequestPool adapter.');
        }
    }
    
    /**
     * Initialize HttpRequest
     *
     * @return void
     * @throws Zend_Http_Client_Adapter_Exception if unable to attach HttpRequest
     */
    public function connect() {
        // If we're already connected, disconnect first
        if ($this->_httpRequestPool) {
            $this->close();
        }
        
        $this->_httpRequestPool = new HttpRequestPool;
        
        if (!$this->_httpRequestPool) {
            $this->close();            
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Unable to initialize HttpRequestPool.');
        }
        
        $this->_key_handle_map = array();
        
        // add the handles
        foreach ($this->getClients() as $key => $client) {           
               // get original config
            $config = $client->getConfig();

            try {
                $client->request();
            }
            catch(Zend_Http_Exception $ignored) {
            
            }
            
            // reset the config back to original
            $client->setConfig($config);
            
            //print_r($client);
            // get HttpRequest handle            
            $httpRequest = $client->getAdapter()->getHandle();
            //print_r($httpRequest);
            
            if(! $httpRequest instanceof HttpRequest)
                continue;
                
            // store key-handle association
            $this->_key_handle_map[] = $key;
            
            $httpRequest->setOptions(array(
                'proxyhost' => $config['proxy_host'],
                'proxyport' => $config['proxy_port'],
                'connecttimeout' => $config['timeout']
            ));

            // add a handle
            try {
                $this->_httpRequestPool->attach($httpRequest);    
            }
            catch (HttpException $ex) {
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception($ex->getMessage());                
            }                       
            
            // remove original HttpRequest handle
            $client->getAdapter()->close();
        }        
    }
    
    /**
     * Send non-blocking requests to remote server(s)
     * 
     * @throws Zend_Http_Client_Adapter_Exception
     */
    public function write() {
    
        $requests = array();
        
        try {
            $this->_httpRequestPool->send();
        }
        catch(HttpException $ex) {
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception($ex->getMessage());            
        }
            
        // process the responses                          
        // the requests are returned in the order they were attached
        foreach ($this->_httpRequestPool as $index => $httpRequest) {              
            $key = $this->_key_handle_map[$index];            
            $requests[$key] = (string) $httpRequest->getRequestMessage();
            $this->_responses[$key] = (string) $httpRequest->getResponseMessage();
        }
        
        return $requests;
    }
      
    /**
     * Return read response from server
     *
     * @return string
     */
    public function read() 
    {
        return $this->_responses;
    }
    
    /**
     * Close the connection to the server
     *
     * @return void
     */
    
    public function close() 
    {
        if ($this->_httpRequestPool instanceof HttpRequestPool) {
            $this->_httpRequestPool->reset();
            $this->_httpRequestPool->__destruct();
        }    
        
        $this->_httpRequestPool = null;
    }
    
    /**
     * Get HttpRequestPool Handle
     *
     * @return resource
     */
    public function getHandle() {
        return $this->_httpRequestPool;
    }
    
    /**
     * Sets $clients.
     *
     * @param array $clients
     * @see Eveyron_Zend_Http_MultiClient_Adapter::$clients
     */
    public function setClients($clients) 
    {
        parent::setClients($clients, 'Eveyron_Zend_Http_Client_Adapter_HttpRequest');
    }
    
    /**
     * Destructor
     * 
     * @return void
     */
    public function __destruct() 
    {
        $this->_key_handle_map = null;
        $this->close();
    }
}