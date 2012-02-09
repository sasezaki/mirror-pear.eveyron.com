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

class Eveyron_Zend_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract
{       
    public function routeShutdown(Zend_Controller_Request_Abstract $request) 
    { 
        $locale = Zend_Registry::get('Zend_Locale');
        $translate = Zend_Registry::get('Zend_Translate');
        $language = $request->getParam('language');

        if(!$language || !$translate->isAvailable($language)) {
            return;
        }
    
        // persists language var even when a page has 'reset_params' => true
        $front = Zend_Controller_Front::getInstance();
        $front->getRouter()->setGlobalParam('language', $language);
        
           $locale->setLocale($language); // required! 
        $translate->setLocale($language); // pass $language NOT $locale!        
        
        Zend_Form::setDefaultTranslator($translate);
    }
}