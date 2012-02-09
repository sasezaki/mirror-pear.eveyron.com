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
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * @uses Zend_Application_Resource_ResourceAbstract
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Application
 * @subpackage Resource 
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Application_Resource_Env extends Zend_Application_Resource_ResourceAbstract
{                
    const DEFAULT_REGISTRY_KEY = 'Env';
    
    /**
     * 
     * @return void
     */
    public function init()
    {
         return $this->getEnv();    
    }
    
    /**
     * 
     * @return void
     */
    public function getEnv() {
        $options = $this->getOptions();
        foreach($options as $k => $v)
        {
            putenv($k.'='.$v);            
        }
    }
}