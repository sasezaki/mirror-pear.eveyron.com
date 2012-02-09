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
class Eveyron_Zend_Http_MultiClient_Adapter_MultiCurl 
    extends Eveyron_Zend_Http_MultiClient_Adapter 
    implements Eveyron_Zend_Http_MultiClient_Adapter_Interface
{
    /**
     * The curl multi client session handle
     *
     * @var resource|null
     */
    protected $_curl_multi = null;
    
    /**
     * @var array
     */
    protected $_responses = array();
    
    /**
     * Number of active handles
     *
     * @var int
     */
    protected $_running = 0;
    
    /**
     * @var array
     */
    protected $_key_handle_map = array();
    
    /**
     *
     * @return void
     */
    public function __construct() 
    {
        if (!extension_loaded('curl')) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('cURL extension has to be loaded to use this Eveyron_Zend_Http_MultiClient_Adapter_MultiCurl adapter.');
        }
    }
    
    /**
     * Initialize curl multi client
     *
     * @return void
     * @throws Zend_Http_Client_Adapter_Exception if unable to connect
     */
    public function connect() 
    {
        // If we're already connected, disconnect first
        if ($this->_curl_multi) {
            $this->close();
        }
        
        $this->_curl_multi = curl_multi_init();
        
        if (!$this->_curl_multi) {
            $this->close();
            
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Unable to initialize multi handle.');
        }
        
        // reset
        $this->_key_handle_map = array();
        
        // add the handles
        foreach ($this->getClients() as $key=>$client) 
        {        
            //echo $key."\n";
            if(! $client instanceof Zend_Http_Client)
                continue;

            $client->request();            
            
            // get curl handle
            $ch = $client->getAdapter()->getHandle();
            
            if (!is_resource($ch)) {
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception('Unable to get a cURL handle from the adapter.');
            }
            
            // store key-handle association
            $this->_key_handle_map[$key] = (string) $ch;
            
            // add a handle
            $code = curl_multi_add_handle($this->_curl_multi, $ch);
            
            if ($code !== CURLM_OK && $code !== CURLM_CALL_MULTI_PERFORM) {
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception('Unable to add one of the cURL handles.');
            }
        }
    }
    
    /**
     * Send request to the remote server
     * 
     * @return string $request
     * @throws Zend_Http_Client_Adapter_Exception If connection fails, connected to wrong host, no PUT file defined, unsupported method, or unsupported cURL option
     */
    public function write() 
    {
        $requests = array();
        
        // start executing registered handles
        do {
            $code = curl_multi_exec($this->_curl_multi, $running);
        } while ($code === CURLM_CALL_MULTI_PERFORM);
        
        // block further script execution until all handles are processed and ready for harvesting
        while ($running && $code === CURLM_OK) {
            // see if a handle ready for an action
            if (curl_multi_select($this->_curl_multi) !== - 1) {
                // wait until something changes
                do {
                    $code = curl_multi_exec($this->_curl_multi, $running);
                } while ($code === CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        while ($msg = curl_multi_info_read($this->_curl_multi, $msgs_in_queue)) 
        {
            $handle_key_map = array_flip($this->_key_handle_map);
            $key = $handle_key_map[(string) $msg['handle']];
            $adapter = $this->getClient($key)->getAdapter();
            
             $adapter->setHandle($msg['handle']);
            $requests[$key] = $adapter->postWrite();
            $response = $adapter->read();
            
            $this->_responses[$key] = $response;
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
        if (is_resource($this->_curl_multi)) {
            foreach ($this->getClients() as $client) {
                if (!is_resource($client->getAdapter()->getHandle()))
                    continue;
                curl_multi_remove_handle($this->_curl_multi, $client->getAdapter()->getHandle());
                $client->getAdapter()->close();
            }
            curl_multi_close($this->_curl_multi);
        }
        $this->_curl_multi = null;
    }
    
    /**
     * Get cUrl Multi Handle
     *
     * @return resource
     */
    public function getHandle() 
    {
        return $this->_curl_multi;
    }
    
    /**
     * Sets $clients.
     *
     * @param object $clients
     * @see Eveyron_Zend_Http_MultiClient_Adapter::$clients
     */
    public function setClients($clients) 
    {
        parent::setClients($clients, 'Eveyron_Zend_Http_Client_Adapter_Curl');
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