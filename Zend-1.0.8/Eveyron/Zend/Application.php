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
 * @see Zend_Application
 */
require_once 'Zend/Application.php';

/**
 * @category Eveyron
 * @package Eveyron_Zend
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Application extends Zend_Application {
    
    /**
     * Load configuration file of options
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is provided
     * @return array
     */
    protected function _loadConfig($file)
    {
        try {
            return parent::__loadConfig($file);
        }
        catch (Zend_Application_Exception $ignored) {
            $environment = $this->getEnvironment();
            $suffix      = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            switch ($suffix) {
                case 'yml':
                case 'yaml':                    
                    $config = new Eveyron_Zend_Config_Yaml($file, $environment);
                    break;
    
                default:
                    throw new Zend_Application_Exception('Invalid configuration file provided; unknown config type');
            }            
        }
        
        return $config->toArray();
    }    
}