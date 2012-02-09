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
 * @see http://pear.horde.org/index.php?package=yaml
 * 
 * Horde_Yaml installation:
 * 
 * pear channel-discover pear.horde.org
 * pear install horde/Yaml
 */

/**
 * @category Eveyron
 * @package Eveyron_Yaml
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Yaml_Adapter_HordeYaml 
    extends Eveyron_Yaml_Adapter_Abstract 
    implements Eveyron_Yaml_Adapter_Interface
{
    /**
     * 
     * @return void
     */
    public function __construct() {
        // PEAR installation is assumed
        include_once 'Horde/Yaml.php';
        if(!class_exists('Horde_Yaml')) {
            self::_throwException('Horde_Yaml class cannot be loaded');
        }    
    }
        
    /**
     * Parse yaml string into array
     * 
     * @param string $input
     * @return array
     * @throws Eveyron_Yaml_Adapter_Exception
     */
    public function parse($input) {
        require_once 'Horde/Yaml/Node.php';
        require_once 'Horde/Yaml/Loader.php';
        
        try {
            $output = Horde_Yaml::load($input);
        } catch(InvalidArgumentException $ex) {
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
        require_once 'Horde/Yaml/Dumper.php';
        $output = Horde_Yaml::dump($data, $this->config);
        return $output;                
    }
}