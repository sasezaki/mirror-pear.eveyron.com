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
 * 
 * Configuration.
 * 
 * application/configs/cdn.ini
 * 
 * [production]
 * cdn.http.jquery = "http://ajax.googleapis.com/ajax/libs/jquery"
 * cdn.https.jquery = "https://ajax.googleapis.com/ajax/libs/jquery"
 * 
 * [development : production]
 * cdn.http.jquery = "/lib/vendor/jquery"
 * cdn.https.jquery = "/lib/vendor/jquery"
 * 
 * Initialization.
 * 
 * application/Bootstrap.php
 * 
 *     protected function _initCdn() {
 *      $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/cdn.ini', APPLICATION_ENV);
 *      $this->setOptions($config->toArray());    
 *      App_View_Helper_Cdn::setTypes($config->cdn->toArray());
 *  }
 *  
 *  Usage.
 *  
 *  application/modules/default/layouts/default.phtml
 *  
 *  $this->headScript()->appendFile($this->cdn('jquery').'/1.3.2/jquery.min.js');
 */

/**
 * @see Zend_View_Helper_Abstract
 */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage View
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_View_Helper_Cdn extends Zend_View_Helper_Abstract 
{
    /**
     * Pre-defined resource types 
     * 
     * @var array
     */
    protected static $_types = array(
        'http' => array(
            'default' => '',
            'images'  => '/images',
            'styles'  => '/styles',
            'scripts' => '/scripts',
        ),
        'https' => array(
            'default' => '',
            'images'  => '/images',
            'styles'  => '/styles',
            'scripts' => '/scripts',
        ),        
    );

    /**
     * Set resource types
     * 
     * @param array $types
     * @return void
     */    
    public static function setTypes(array $types)
    {
        self::$_types = $types;
    }
    
    /**
     * Auto-detects the request scheme and sets the cdn value accordingly 
     * 
     * @param string $type [optional]
     * @param string $scheme [optional] set it to force a particular scheme
     * @return string
     */
    public function cdn($type = 'default', $scheme = null)
    {
        if($scheme === null) {
            $scheme = 'http';
            $front = Zend_Controller_Front::getInstance();
            $request = $front->getRequest();            
            if($request instanceof Zend_Controller_Request_Http) {
                $scheme = $request->getScheme();
            }
        }
        
        if (!isset(self::$_types[$scheme][$type])) {
            throw new Zend_View_Exception('No CDN set for resource type ' . $type);
        }
        
        $cdn = self::$_types[$scheme][$type];
        
        return $cdn;
    }
}