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
 * @category Eveyron
 * @package Eveyron_Yaml
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Yaml_Adapter_Abstract
{
    /**
     * @var array
     */
    protected $config = null;
    
    /**
     * Set the configuration array for the adapter
     *
     * @throws Eveyron_Yaml_Adapter_Exception
     * @param  array $config
     * @return Eveyron_Yaml_Adapter_Abstract
     */
    public function setConfig($config = array())
    {
        if (! is_array($config)) {
            self::_throwException('Array expected, got ' . gettype($config));
        }

        foreach ($config as $k => $v) {
            $option = strtolower($k);
            $this->config[$option] = $v;                    
        }
                    
        return $this;
    }
        
    /**
     * Lazy-loading for Eveyron_Yaml_Adapter_Exception
     * @param string $msg
     * @return void
     * @throws Eveyron_Yaml_Adapter_Exception
     */    
    protected static function _throwException($msg) {
       /**
         * @see Eveyron_Yaml_Adapter_Exception
         */
        require_once 'Eveyron/Yaml/Adapter/Exception.php';
        throw new Eveyron_Yaml_Adapter_Exception($msg);        
    }
}