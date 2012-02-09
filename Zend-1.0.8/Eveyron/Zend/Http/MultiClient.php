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
 * @see Eveyron_Zend_Http_Client
 */
require_once 'Eveyron/Zend/Http/Client.php';

/**
 * 
 * A drop-in solution for ZendFramework to send multiple non-blocking requests. 
 * Comes with 2 adapters for cUrl and HttpRequest. 
 * Usage:
  
         $client1 = new Zend_Http_Client();
         // use HttpRequest Adapter
        $client1->setAdapter('Eveyron_Zend_Http_Client_Adapter_HttpRequest');
        // or CURL Adapter    
        //$client1->setAdapter('Zend_Http_Client_Adapter_Curl');
        
        $client1
            ->setUri('http://www.yahoo.com')
            ;            
        
        $client2 = clone $client1;
        $client2        
            ->setUri('http://www.google.com')
            ;    
            
        $client3 = clone $client1;
        $client3        
            ->setUri('http://www.bing.com')
            ;            
                                    
        $clients = array(
            'yahoo' => $client1, 
            'google' => $client2, 
            'bing' => $client3
        );
        
        $config = array(
            // use HttpRequest Adapter
            'adapter'   => 'Eveyron_Zend_Http_MultiClient_Adapter_HttpRequestPool',
            // or CURL Adapter
            //'adapter'   => 'Eveyron_Zend_Http_MultiClient_Adapter_MultiCurl',    
        );        
        
        $ahmc = new Eveyron_Zend_Http_MultiClient($clients, $config);
        $responses = $ahmc->request();
        
        foreach($responses as $k => $response) {
            echo $response->getBody();                    
        }                        
 */

/**
 * @uses Eveyron_Zend_Http_Client
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Http
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Http_MultiClient extends Eveyron_Zend_Http_Client {
    
    /**
     * @var array of Zend_Http_Client
     */
    protected $clients = array();
        
    /**
     * Configuration array, set using the constructor or using ::setConfig()
     *
     * @var array
     */
    protected $config = array(
        'storeresponse' => true, 
        'adapter' => 'Eveyron_Zend_Http_MultiClient_Adapter_MultiCurl',
    );
    
    /**
     * The adapter used to perform the actual connection to the server
     *
     * @var Eveyron_Zend_Http_MultiClient_Adapter
     */
    protected $adapter = null;
    
    /**
     *
     * @param array $clients [optional]
     * @param array $config [optional] Configuration key-value pairs.
     * @return
     */
    public function __construct($clients = null, $config = null) 
    {
        if ($clients !== null) {
            $this->setClients($clients);
        }
        
        parent::__construct(null, $config);
    }
        
    /**
     * Send multiple requests;
     * 
     * @return array of Zend_Http_Response
     */
    public function request() 
    {
        $raw_responses = array();
        $responses = array();
        
        // Make sure the adapter is loaded
        if ($this->adapter == null) {
            $this->setAdapter($this->config['adapter']);
        }
        
        $this->adapter->setClients($this->getClients());

        $this->adapter->connect();
        $this->last_request = $this->adapter->write();
        $raw_responses = $this->adapter->read();

        if (empty($raw_responses)) {
            /** @see Zend_Http_Client_Exception */ 
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Unable to read response, or response is empty');
        }
       
        foreach ($raw_responses as $key=>$response)
        {
            if(empty($response))
                continue;            

            $response = Zend_Http_Response::fromString($response);                
            $responses[$key] = $response;
            
            $client = $this->getClient($key);    
            if($client instanceof Zend_Http_Client)
            {
                $clientConfig = $client->getConfig();
                if(isset($clientConfig['storeresponse']) && $clientConfig['storeresponse'] === true) {
                    $client->setLastResponse($response);
                }                
            }            
            $response = null;
            unset($response, $client);
        }
        
        if ($this->config['storeresponse']) {
            $this->last_response = $responses;
        }
        unset($raw_responses);
        
        return $responses;
    }                    
    
    /**
     *
     * @return void
     */
    public function setAdapter($adapter) 
    {
        if (is_string($adapter)) {
            if (!class_exists($adapter)) {
                try {
                    require_once 'Zend/Loader.php';
                    Zend_Loader::loadClass($adapter);
                }
                catch(Zend_Exception $e) {
                    /** @see Zend_Http_Client_Exception */ 
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception("Unable to load adapter '$adapter': {$e->getMessage()}", 0, $e);
                }
            }
            
            $adapter = new $adapter;
        }

        if (!$adapter instanceof Eveyron_Zend_Http_MultiClient_Adapter_Interface) {
            /** @see Zend_Http_Client_Exception */ 
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Passed adapter is not a HTTP connection adapter');
        }
        
        $this->adapter = $adapter;
        $config = $this->config;
        unset($config['adapter']);
        $this->adapter->setConfig($config);
    }
    
    /**
     * Returns $clients.
     *
     * @see Eveyron_Zend_Http_MultiClient::$clients
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }
    
    /**
     * Sets $clients.
     *
     * @param array $clients
     * @see Eveyron_Zend_Http_MultiClient::$clients
     * @return Eveyron_Zend_Http_MultiClient
     */
    public function setClients(array $clients = array()) 
    {
        $this->clients = array();
        foreach($clients as $key => $client) {
            $this->setClient($key, $client);
        }
        return $this;
    }
    
    /**
     * Sets Zend_Http_Client 
     * 
     * @param mixed $key
     * @param Zend_Http_Client $client
     * @return Eveyron_Zend_Http_MultiClient
     */
    public function setClient($key, Zend_Http_Client $client) 
    {
        $config = $this->getConfig();
        if(isset($config['adapter'])) {
            unset($config['adapter']);
        }
        // force multi-client mode
        $config['mode'] = Eveyron_Zend_Http_Client::REQUEST_MODE_MULTI;
        $client->setConfig($config);
        $this->clients[$key] = $client;
        return $this;
    }
    
    /**
     * Gets a client
     * 
     * @param mixed $key
     * @return boolean|Zend_Http_Client
     */
    public function getClient($key)
    {
        if(isset($this->clients[$key])) {
            return $this->clients[$key];
        }
        
        return false;
    }    
}