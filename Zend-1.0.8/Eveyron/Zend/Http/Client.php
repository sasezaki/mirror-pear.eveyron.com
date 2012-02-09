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
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * @uses Zend_Http_Client
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Http
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Http_Client extends Zend_Http_Client
{    
    const REQUEST_MODE_NORMAL = 0;
    const REQUEST_MODE_MULTI = 1;
    
    /**
     * Proxy config
     * 
     * @var array
     */
    protected $proxy;
        
    /**
     * @var int
     */
    protected $retryCounter = 0;
    
    /**
     * Contructor method. Will create a new HTTP client. Accepts the target
     * URL and optionally configuration array.
     *
     * @param Zend_Uri_Http|string $uri [optional]
     * @param array $config [optional] Configuration key-value pairs.
     */
    public function __construct($uri = null, $config = null)
    {
        $this->config = array_merge($this->config, array(
            'mode' => self::REQUEST_MODE_NORMAL,
            'retries' => 1,
        ));
        
        parent::__construct($uri, $config);
    }
    
    /**
     * Retrieve the array of all configuration options
     * 
     * @return array 
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * Set the last HTTP response received by this client
     * 
     * @param Zend_Http_Response $response
     * @return Eveyron_Zend_Http_Client
     */
    public function setLastResponse(Zend_Http_Response $response)
    {
        $this->last_response = $response;
        return $this;
    }
    
    /**
     * Set the last HTTP request as string
     * 
     * @param string $request
     * @return Eveyron_Zend_Http_Client
     */
    public function setLastRequest($request)
    {
        $this->last_request = $request;
        return $this;
    }
        
    /**
     * Send the HTTP request and return an HTTP response object
     *
     * @param string $method [optional]
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    public function request($method = null)
    {            
        switch($this->config['mode']) 
        {
            default:
            case Eveyron_Zend_Http_Client::REQUEST_MODE_NORMAL:
                $this->retryCounter = 0;
                do {
                    try {
                        $response = parent::request($method);
                        ++$this->retryCounter;
                    }
                    catch(Zend_Http_Client_Exception $ignored) {
                    }            
                    
                    if($response->isSuccessful()) {
                        break;
                    }
                }
                while ($this->retryCounter < $this->config['retries']);            
                
                return $response;                
            break;
                        
            case Eveyron_Zend_Http_Client::REQUEST_MODE_MULTI:
                if (! $this->uri instanceof Zend_Uri_Http) {
                    /** @see Zend_Http_Client_Exception */
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception('No valid URI has been passed to the client');
                }
        
                if ($method) {
                    $this->setMethod($method);
                }
                $this->redirectCounter = 0;
                $response = null;
        
                // Make sure the adapter is loaded
                if ($this->adapter == null) {
                    $this->setAdapter($this->config['adapter']);
                }
                
              // Clone the URI and add the additional GET parameters to it
                $uri = clone $this->uri;
                if (! empty($this->paramsGet)) {
                    $query = $uri->getQuery();
                       if (! empty($query)) {
                           $query .= '&';
                       }
                    $query .= http_build_query($this->paramsGet, null, '&');
    
                    $uri->setQuery($query);
                }
    
                $body = $this->_prepareBody();
                $headers = $this->_prepareHeaders();
    
                // check that adapter supports streaming before using it
                if(is_resource($body) && !($this->adapter instanceof Zend_Http_Client_Adapter_Stream)) {
                    /** @see Zend_Http_Client_Exception */
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception('Adapter does not support streaming');
                }
    
                // Open the connection, send the request and read the response
                $this->adapter->connect($uri->getHost(), $uri->getPort(),
                    ($uri->getScheme() == 'https' ? true : false));
    
                if($this->config['output_stream']) {
                    if($this->adapter instanceof Zend_Http_Client_Adapter_Stream) {
                        $stream = $this->_openTempStream();
                        $this->adapter->setOutputStream($stream);
                    } else {
                        /** @see Zend_Http_Client_Exception */
                        require_once 'Zend/Http/Client/Exception.php';
                        throw new Zend_Http_Client_Exception('Adapter does not support streaming');
                    }
                }
    
                $this->last_request = $this->adapter->write($this->method,
                    $uri, $this->config['httpversion'], $headers, $body);        
            break;
        }
    }
    
    /**
     * Emulate XHR request
     * 
     * @param boolean $enable [optional] default is on
     * @param string $prototype [optional] prototype version
     * @return Eveyron_Zend_Http_Client
     */
    public function setXhrMode($enable, $prototype = null)
    {
        $this->resetHeaders();
        
        $this->setHeaders('X-Requested-With', ($enable ? 'XMLHttpRequest' : null));
        if($prototype !== null) {
            $this->setHeaders('X-Prototype-Version', ($enable ? $prototype : null));
        }
        return $this;    
    }
    
    /**
     * Emulate Flash client
     * 
     * @param boolean $enable [optional] default is on
     * @param string $ua [optional] user agent string; defaults to Shockwave Flash
     * @return Eveyron_Zend_Http_Client
     */
    public function setFlashMode($enable = true, $ua = 'Shockwave Flash')
    {
        $this->resetHeaders();        
        $this
            ->setHeaders('Accept', $enable ? 'text/*' : null)
            ->setHeaders('Connection', $enable ? 'Keep-Alive' : null)
            ->setHeaders('Pragma', $enable ? 'no-cache' : null)
            ->setHeaders('User-Agent', $enable ? $ua : null)
        ;
        
        return $this;
    }
    
    /**
     * Sets a proxy
     * 
     * @param string $proxy = 'host' OR 'host:port' OR 'user:pass@host' OR 'user:pass@host:port'
     * OR
     * @param array $proxy = array('proxy_host' => $host, 'proxy_port' => $port, 'proxy_user' => $user, 'proxy_pass' => $pass)
     * OR
     * @param array $proxy = array($host, $port[optional], $user[optional], $pass[optional])
     * OR
     * @param string $host
     * @param int $port [optional]
     * @param string $user [optional]
     * @param string $pass [optional]
     * 
     * @return array 
     */
    public function setProxy($proxy) 
    {
        $args = func_get_args();
        if(count($args) == 1) {
            if(is_string($proxy)) {
                if(strpos($proxy, ':') === false) {
                    $config = array(
                        'proxy_host' =>  $proxy,        
                    );                        
                }
                else {
                    $parts = explode(':', $proxy);
                    if(count($parts) === 2)
                    {
                        if(strpos($proxy, '@') === false) {
                            $config = array(
                                'proxy_host' => $parts[0],
                                'proxy_port' => $parts[1],            
                            );                    
                        }
                        else {
                            list($proxy_pass, $proxy_host) = explode('@', $parts[1]);
                            $config = array(
                                'proxy_host' => $proxy_host,
                                'proxy_user' => $parts[0],
                                'proxy_pass' => $proxy_pass,                                            
                            );                                                
                        }
                    }
                    else if(count($parts) === 3)
                    {
                        if(strpos($proxy, '@') !== false) {
                            list($proxy_pass, $proxy_host) = explode('@', $parts[1]);
                            $config = array(
                                'proxy_host' => $proxy_host,
                                'proxy_port' => $parts[2],
                                'proxy_user' => $parts[0],
                                'proxy_pass' => $proxy_pass,                                            
                            );    
                        }
                    }
                }
            }
            else if(is_array($proxy)) {
                // named array
                if(isset($proxy['proxy_host'])) {
                    $config = $proxy;
                }
                // index array
                else {
                    $config = array(                
                        'proxy_host' => $proxy[0],
                        'proxy_port' => isset($proxy[1]) ? $proxy[1] : null,
                        'proxy_user' => isset($proxy[2]) ? $proxy[2] : null,
                        'proxy_pass' => isset($proxy[3]) ? $proxy[3] : null,                                    
                    );                
                }
            }
        }
        else if(count($args) > 1) {
            $config = array(                
                'proxy_host' => $args[0],
                'proxy_port' => isset($args[1]) ? $args[1] : null,
                'proxy_user' => isset($args[2]) ? $args[2] : null,
                'proxy_pass' => isset($args[3]) ? $args[3] : null,                                    
            );                
        }        
        
        // normalize config
        foreach($config as $k => $v) {
            if($v === null) {
                unset($config[$k]);
                continue;
            }
            switch($k) {
                case 'proxy_host':
                case 'proxy_user':
                case 'proxy_pass':        
                    $config[$k] = (string) $v;
                break;    
                case 'proxy_port':        
                    $config[$k] = (int) $v;
                break;                        
            }
        }
        
        $this->proxy = $config;
        $this->setConfig($config);
        return $this;
    }    
    
    /**
     * Gets a proxy config
     * 
     * @return array 
     */
    public function getProxy() 
    {
        return $this->proxy;    
    }                        
}