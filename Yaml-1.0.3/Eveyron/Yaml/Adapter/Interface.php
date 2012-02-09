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
interface Eveyron_Yaml_Adapter_Interface
{
    /**
     * Set the configuration array for the adapter
     *
     * @param array $config
     */
    public function setConfig($config = array());
        
    /**
     * Parse yaml string into array
     * 
     * @param string $input
     * @return array
     */
    public function parse($input);
    
    /**
     * Generate yaml string from array
     * 
     * @param array $data
     * @return string
     */
    public function emit($data);
}