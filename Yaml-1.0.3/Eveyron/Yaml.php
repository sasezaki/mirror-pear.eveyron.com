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
 * @version 2010-09-13 13:59:24Z
 */
 
/**
 * @see Eveyron_Yaml_Adapter_Interface
 */
require_once 'Eveyron/Yaml/Adapter/Interface.php';

/**
 * @category Eveyron
 * @package Eveyron_Yaml
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Yaml implements Eveyron_Yaml_Adapter_Interface
{
    /**
     * @var Eveyron_Yaml_Adapter_Abstract
     */
    protected $adapter = null;
    
    /**
     * @var array
     */
    protected $config = array(
        'adapter' => 'Eveyron_Yaml_Adapter_SfYaml',
        'version' => '1.1',
    );    
    
    /**
     * Constructor
     * 
     * @param array $config [optional]
     * @return void
     */
    public function __construct($config = null) {
        if ($config !== null) {
            $this->setConfig($config);
        }
    }
    
    /**
     * Parse yaml string into array
     * 
     * @param string $input
     * @return array
     */    
    public function parse($input) {
        return $this->getAdapter()->parse($input);
    }
    
    /**
     * Generate yaml string from array
     * 
     * @param array $data
     * @return string
     */
    public function emit($data) {
        return $this->getAdapter()->emit($data);
    }    

    /**
     * Returns $adapter.
     *
     * @see Eveyron_Yaml::$adapter
     * @return Eveyron_Yaml_Adapter_Abstract
     */
    public function getAdapter() {
        // Make sure the adapter is loaded
        if ($this->adapter == null) {
            $this->setAdapter($this->config['adapter']);
        }        
        return $this->adapter;
    }
    
    /**
     * Sets $adapter.
     *
     * @param string $adapter
     * @see Eveyron_Yaml::$adapter
     * @return Eveyron_Yaml
     * @throws Eveyron_Yaml_Exception
     */
    public function setAdapter($adapter) {
        if(is_string($adapter)) {
            if(!class_exists($adapter)) {
                self::_throwException(sprintf('Unable to load adapter %s', $adapter));
            }
            $adapter = new $adapter;
        }
        
        if(!$adapter instanceof Eveyron_Yaml_Adapter_Interface) {
            self::_throwException('Passed adapter is not a YAML parser/emiter adapter.');
        }
        
        $this->adapter = $adapter;
        $config = $this->config;
        unset($config['adapter']);
        $this->adapter->setConfig($config);
                
        return $this;
    }
    
    /**
     * Lazy-loading for Eveyron_Yaml_Exception
     * @param string $msg
     * @return void
     * @throws Zend_Config_Exception
     */    
    protected static function _throwException($msg) {
       /**
         * @see Eveyron_Yaml_Exception
         */
        require_once 'Eveyron/Yaml/Exception.php';
        throw new Eveyron_Yaml_Exception($msg);        
    }    

    /**
     * Returns $config.
     *
     * @see Eveyron_Yaml::$config
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }
    
    /**
     * Sets $config.
     *
     * @param array $config
     * @see Eveyron_Yaml::$config
     * @return Eveyron_Yaml
     * @throws Eveyron_Yaml_Exception
     */
    public function setConfig($config = array()) {
        if (! is_array($config)) {
            self::_throwException('Array expected, got ' . gettype($config));
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }
        
        if(isset($this->config['adapter'])) {
            $this->setAdapter($this->config['adapter']);
        }

        return $this;
    }
}