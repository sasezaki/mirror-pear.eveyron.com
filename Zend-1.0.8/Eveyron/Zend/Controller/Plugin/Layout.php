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

class Eveyron_Zend_Controller_Plugin_Layout extends Zend_Controller_Plugin_Abstract
{
    /**
     * Dynamically set layout based on the active module. 
     * Allows serving module-specific layouts.
     *
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        //$module = $this->getRequest()->getModuleName();
        $module = $request->getModuleName();
        $path = APPLICATION_PATH .'/modules/'.$module.'/layouts/scripts';
        // only change the layout if it exists otherwise bail out and use the default or current one.
        if(file_exists($path.DIRECTORY_SEPARATOR.$module.'.phtml')) {
            $layout = Zend_Layout::getMvcInstance();                
            $layout->setLayout($module);
            $layout->setLayoutPath($path);            
        }
    }
}