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
 * @see http://code.google.com/p/spyc/
 * 
 * YAML 1.0 specification.
 *
 */

/**
 * @category Eveyron
 * @package Eveyron_Yaml
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Yaml_Adapter_Spyc
    extends Eveyron_Yaml_Adapter_Abstract 
    implements Eveyron_Yaml_Adapter_Interface
{
    /**
     * 
     * @return void
     */
    public function __construct() {
        include_once 'spyc/spyc.php';
        if(!class_exists('Spyc')) {
            self::_throwException('Spyc class cannot be loaded');
        }    
    }    
    
    /**
     * Parse yaml string into array
     * 
     * @param string $input
     * @return array
     */
    public function parse($input) {
        $output = Spyc::YAMLLoad($input);        
        return $output;        
    }
    
    /**
     * Generate yaml string from array
     * 
     * @param array $data
     * @return string
     */
    public function emit($data) {
        $output = Spyc::YAMLDump($data);
        return $output;                
    }
}