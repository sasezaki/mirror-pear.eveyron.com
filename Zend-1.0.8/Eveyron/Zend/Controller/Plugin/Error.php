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
 * @see Zend_Controller_Plugin_Abstract
 */
require_once 'Zend/Controller/Plugin/Abstract.php';

class Eveyron_Zend_Controller_Plugin_Error extends Zend_Controller_Plugin_Abstract {
    
    /**
     * Dynamically set error handler to an active module
     * 
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $moduleName = $request->getModuleName();
        $frontController = Zend_Controller_Front::getInstance();
        if(false !== ($plugin = $frontController->getPlugin('Zend_Controller_Plugin_ErrorHandler'))) {
            $plugin->setErrorHandlerModule($moduleName);
        }
    }
}