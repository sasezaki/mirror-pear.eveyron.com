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
 * @see Eveyron_Yaml_Adapter_Abstract
 */
require_once 'Eveyron/Yaml/Adapter/Abstract.php';

/**
 * @see Eveyron_Yaml_Adapter_Interface
 */
require_once 'Eveyron/Yaml/Adapter/Interface.php';

/**
 * @see http://components.symfony-project.org/yaml/
 * 
 * YAML 1.1|1.2 specification.
 * 
 * sfYaml installation:
 * 
 * pear channel-discover pear.symfony-project.com
 * pear install symfony/YAML
 */

/**
 * @category Eveyron
 * @package Eveyron_Yaml
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Yaml_Adapter_SfYaml 
    extends Eveyron_Yaml_Adapter_Abstract 
    implements Eveyron_Yaml_Adapter_Interface
{
    /**
     * 
     * @return void
     */
    public function __construct() {
        // PEAR installation is assumed
        include_once 'SymfonyComponents/YAML/sfYaml.php';
        if(!class_exists('sfYaml')) {
            self::_throwException('sfYaml class cannot be loaded');
        }    
    }    
    
    /**
     * Set the configuration array for the adapter
     *
     * @throws Eveyron_Yaml_Adapter_Exception
     * @param  array $config
     * @return Eveyron_Yaml_Adapter_SfYaml
     */
    public function setConfig($config = array())
    {
        parent::setConfig($config);
        
        foreach ($this->config as $k => $v) {
            switch($k) {
                case 'version':
                    sfYaml::setSpecVersion($v);
                    break;
            }
        }

        return $this;
    }
        
    /**
     * Parse yaml string into array
     * 
     * @param string $input
     * @return array
     */
    public function parse($input) {
        try {
            $output = sfYaml::load($input);
        }
        catch(InvalidArgumentException $ex) {
            self::_throwException($ex->getMessage());
        }            
        return $output;        
    }
    
    /**
     * Generate yaml string from array
     * 
     * @param array $data
     * @return string
     */
    public function emit($data) {
        $output = sfYaml::dump($data);
        return $output;                
    }
}