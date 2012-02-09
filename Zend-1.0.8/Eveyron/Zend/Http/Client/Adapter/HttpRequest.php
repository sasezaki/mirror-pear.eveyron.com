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
 * @see Zend_Http_Client_Adapter_Interface
 */
require_once 'Zend/Http/Client/Adapter/Interface.php';

/**
 * @see Zend_Http_Client_Adapter_Stream
 */
require_once 'Zend/Http/Client/Adapter/Stream.php';

/**
 * @uses Zend_Http_Client_Adapter_Interface
 * @uses Zend_Http_Client_Adapter_Stream
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Http
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Http_Adapter_HttpRequest implements Zend_Http_Client_Adapter_Interface, Zend_Http_Client_Adapter_Stream
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $_config = array();

    /**
     * What host/port are we connected to?
     *
     * @var array
     */
    protected $_connected_to = array(null, null);

    /**
     * The HttpRequest handle
     *
     * @var HttpRequest|null
     */
    protected $_httpRequest = null;

    /**
     * List of cURL options that should never be overwritten
     *
     * @var array
     */
    protected $_invalidOverwritableHttpRequestOptions = array(
        'headers',
        'port',
        'redirect',
        'connecttimeout',
    );

    /**
     * Response gotten from server
     *
     * @var string
     */
    protected $_response = null;

    /**
     * Stream for storing output
     *
     * @var resource
     */
    protected $out_stream;

    /**
     * Adapter constructor
     *
     * Config is set using setConfig()
     *
     * @return void
     * @throws Zend_Http_Client_Adapter_Exception
     */
    public function __construct()
    {
        if (!extension_loaded('http')) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Http extension has to be loaded to use this Zend_Http_Client adapter.');
        }
    }

    /**
     * Set the configuration array for the adapter
     *
     * @throws Zend_Http_Client_Adapter_Exception
     * @param  Zend_Config | array $config
     * @return Zend_Http_Client_Adapter_Curl
     */
    public function setConfig($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();

        } elseif (! is_array($config)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Array or Zend_Config object expected, got ' . gettype($config)
            );
        }

        if(isset($config['proxy_user']) && isset($config['proxy_pass'])) {
            $this->setHttpRequestOption('proxyauth', $config['proxy_user'].":".$config['proxy_pass']);
            unset($config['proxy_user'], $config['proxy_pass']);
        }

        foreach ($config as $k => $v) {
            $option = strtolower($k);
            switch($option) {
                case 'proxy_host':
                    $this->setHttpRequestOption('proxyhost', $v);
                    break;
                case 'proxy_port':
                    $this->setHttpRequestOption('proxyport', $v);
                    break;
                default:
                    $this->_config[$option] = $v;
                    break;
            }
        }

        return $this;
    }

    /**
      * Retrieve the array of all configuration options
      *
      * @return array
      */
     public function getConfig()
     {
         return $this->_config;
     }

    /**
     * Direct setter for cURL adapter related options.
     *
     * @param  string|int $option
     * @param  mixed $value
     * @return Zend_Http_Adapter_Curl
     */
    public function setHttpRequestOption($option, $value)
    {
        if (!isset($this->_config['httpRequestOptions'])) {
            $this->_config['httpRequestOptions'] = array();
        }
        $this->_config['httpRequestOptions'][$option] = $value;
        return $this;
    }

    /**
     * Initialize curl
     *
     * @param  string  $host
     * @param  int     $port
     * @param  boolean $secure
     * @return void
     * @throws Zend_Http_Client_Adapter_Exception if unable to connect
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If we're already connected, disconnect first
        if ($this->_httpRequest) {
            $this->close();
        }

        // If we are connected to a different server or port, disconnect first
        if ($this->_httpRequest
            && is_array($this->_connected_to)
            && ($this->_connected_to[0] != $host
            || $this->_connected_to[1] != $port)
        ) {
            $this->close();
        }

        // Do the actual connection
        $this->_httpRequest = new HttpRequest;
        if ($port != 80) {
            $this->_httpRequest->setOptions(array('port' => intval($port)));            
        }

        // Set timeout
        $this->_httpRequest->setOptions(array('connecttimeout' => $this->_config['timeout']));        

        // Set Max redirects
        $this->_httpRequest->setOptions(array('redirect' => $this->_config['maxredirects']));

        if (!$this->_httpRequest) {
            $this->close();

            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Unable to Connect to ' .  $host . ':' . $port);
        }

        if ($secure !== false) {
            // Behave the same like Zend_Http_Adapter_Socket on SSL options.
            if (isset($this->_config['sslcert'])) {
                $this->_httpRequest->setOptions(array('ssl' => array('cert' => $this->_config['sslcert'])));                
            }
            if (isset($this->_config['sslpassphrase'])) {
                $this->_httpRequest->setOptions(array('ssl' => array('certpasswd' => $this->_config['sslpassphrase'])));                
            }
        }

        // Update connected_to
        $this->_connected_to = array($host, $port);
    }

    /**
     * Send request to the remote server
     *
     * @param  string        $method
     * @param  Zend_Uri_Http $uri
     * @param  float         $http_ver
     * @param  array         $headers
     * @param  string        $body
     * @return string        $request
     * @throws Zend_Http_Client_Adapter_Exception If connection fails, connected to wrong host, no PUT file defined, unsupported method, or unsupported cURL option
     */
    public function write($method, $uri, $httpVersion = 1.1, $headers = array(), $body = '')
    {
        // Make sure we're properly connected
        if (!$this->_httpRequest) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are not connected");
        }

        if ($this->_connected_to[0] != $uri->getHost() || $this->_connected_to[1] != $uri->getPort()) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are connected to the wrong host");
        }

        // set URL
        $this->_httpRequest->setUrl($uri->__toString());

        // ensure correct HttpRequest call
        switch ($method) {
            case Zend_Http_Client::GET:
                $httpRequestMethod = HttpRequest::METH_GET;
                break;

            case Zend_Http_Client::POST:
                $httpRequestMethod = HttpRequest::METH_POST;
                break;

            case Zend_Http_Client::PUT:
                $httpRequestMethod = HttpRequest::METH_PUT;                
                break;

            case Zend_Http_Client::DELETE:
                $httpRequestMethod = HttpRequest::METH_DELETE;
                break;

            case Zend_Http_Client::OPTIONS:
                $httpRequestMethod = HttpRequest::METH_OPTIONS;
                break;

            case Zend_Http_Client::TRACE:
                $httpRequestMethod = HttpRequest::METH_TRACE;
                break;

            default:
                // For now, through an exception for unsupported request methods
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception("Method currently not supported");
        }

        if(is_resource($body) && $httpRequestMethod != HttpRequest::METH_PUT) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Streaming requests are allowed only with PUT");
        }
        
        // get http version to use
        $curlHttp = ($httpVersion == 1.1) ? HTTP_VERSION_1_1 : HTTP_VERSION_1_0;

        // mark as HTTP request and set HTTP method
        //curl_setopt($this->_httpRequest, $curlHttp, true);
        //@todo: find a way to set http version for httprequest
        $this->_httpRequest->setMethod($httpRequestMethod);

//        if($this->out_stream) {
//            // headers will be read into the response
//            curl_setopt($this->_httpRequest, CURLOPT_HEADER, false);
//            curl_setopt($this->_httpRequest, CURLOPT_HEADERFUNCTION, array($this, "readHeader"));
//            // and data will be written into the file
//            curl_setopt($this->_httpRequest, CURLOPT_FILE, $this->out_stream);
//        } else {
//            // ensure headers are also returned
//            //curl_setopt($this->_httpRequest, CURLOPT_HEADER, true);
//
//            // ensure actual response is returned
//            //curl_setopt($this->_httpRequest, CURLOPT_RETURNTRANSFER, true);
//        }
        
        $array_headers = array();
        // @todo: find a way to unset Accept: */*!
        $this->_httpRequest->setHeaders();
        $this->_httpRequest->setHeaders(array('Accept' => ''));
        
        foreach($headers as $header) {
            list($name, $value) = explode(':', $header, 2);
            $array_headers[$name] = $value;
        }
                
        //print_r($array_headers);
        // option A
        $this->_httpRequest->setHeaders($array_headers);
        
        // option B
        //$this->_httpRequest->setOptions(array('headers' => $array_headers));
        
        // option C    
        //$this->setHttpRequestOption('headers', $array_headers);
        
        //$headers = $this->_httpRequest->getHeaders();  
        //print_r($headers);
        //exit;
        if ($method == Zend_Http_Client::POST) {
            if(is_array($body)) {
                $this->_httpRequest->setContentType('application/x-www-form-urlencoded');
                $this->_httpRequest->setPostFields($body);
            }
            else if(is_string($body)) {
                $this->_httpRequest->setContentType('multipart/form-data');
                $this->_httpRequest->setRawPostData($body);
            }
        }  elseif ($method == Zend_Http_Client::PUT) {
           if(is_resource($body)) {
                   // option A
                //$this->_httpRequest->setPutData(stream_get_contents($body));
                // option B
                $meta_data = stream_get_meta_data($body);
                if(isset($meta_data['uri'])) {
                    $this->_httpRequest->setPutFile($meta_data['uri']);
                }
                fclose($body);
            }
            else {        
                $this->_httpRequest->setPutData($body);
            }
        }

        // set additional curl options
        if (isset($this->_config['httpRequestOptions'])) {
            foreach ((array)$this->_config['httpRequestOptions'] as $k => $v) {
                if (!in_array($k, $this->_invalidOverwritableHttpRequestOptions)) {
                    if($this->_httpRequest->setOptions(array($k => $v)) === false) {
                        require_once 'Zend/Http/Client/Exception.php';
                        throw new Zend_Http_Client_Exception(sprintf("Unknown or erroreous HttpRequest option '%s' set", $k));
                    }
                }
            }
        }

        // send the request
        try {
            $message = $this->_httpRequest->send();
        }
        catch (HttpException $ex) {
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception((string) $ex);            
        }

        // HttpMessage object casted as string contains headers + raw body 
        $response = (string) $message;

        // if we used streaming, headers are already there
        if(!is_resource($this->out_stream)) {
            $this->_response = $response;
        }

        // get the original request headers as string
        $request  = (string) $this->_httpRequest->getRequestMessage()."\r\n"; 
        $request .= $body;

        // cURL automatically decodes chunked-messages, this means we have to disallow the Zend_Http_Response to do it again
        if (stripos($this->_response, "Transfer-Encoding: chunked\r\n")) {
            $this->_response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $this->_response);
        }

        // Eliminate multiple HTTP responses.
        do {
            $parts  = preg_split('|(?:\r?\n){2}|m', $this->_response, 2);
            $again  = false;

            if (isset($parts[1]) && preg_match("|^HTTP/1\.[01](.*?)\r\n|mi", $parts[1])) {
                $this->_response    = $parts[1];
                $again              = true;
            }
        } while ($again);

        // cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
        if (stripos($this->_response, "HTTP/1.0 200 Connection established\r\n\r\n") !== false) {
            $this->_response = str_ireplace("HTTP/1.0 200 Connection established\r\n\r\n", '', $this->_response);
        }

        return $request;
    }

    /**
     * Return read response from server
     *
     * @return string
     */
    public function read()
    {
        return $this->_response;
    }

    /**
     * Close the connection to the server
     *
     */
    public function close()
    {
        $this->_httpRequest = null;
        $this->_connected_to = array(null, null);
    }

    /**
     * Get cUrl Handle
     *
     * @return resource
     */
    public function getHandle()
    {
        return $this->_httpRequest;
    }

    /**
     * Set output stream for the response
     *
     * @param resource $stream
     * @return Zend_Http_Client_Adapter_Socket
     */
    public function setOutputStream($stream)
    {
        $this->out_stream = $stream;
        return $this;
    }
}