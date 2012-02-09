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
class Eveyron_Zend_Application_Resource_Zfdebug extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'Zfdebug';

    /**
     * @var ZFDebug_Controller_Plugin_Debug
     */
    protected static $_zfdebug = null;

    /**
     *
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function init()
    {
        return $this->getZfdebug();
    }

    /**
     *
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function getZfdebug() {
        if (null === $this->_zfdebug) {
            include_once 'Danceric/Controller/Plugin/Debug/Plugin/Doctrine.php';
            if(!class_exists('Danceric_Controller_Plugin_Debug_Plugin_Doctrine')) {
                throw new Zend_Application_Resource_Exception('Unable to load class Danceric_Controller_Plugin_Debug_Plugin_Doctrine. See http://github.com/danceric/zfdebugdoctrine');
            }
            $options = $this->getOptions();
            // Only enable zfdebug if options have been specified for it
            if ((bool) $options['debug'] === true)
            {
                $doctrinePlugin = null;
                // Setup autoloader with namespace
                if(isset($options['autoload']) && $options['autoload']) {
                    $this->getBootstrap()->getApplication()->getAutoloader()->registerNamespace('ZFDebug_');
                }

                // Ensure the front controller is initialized
                $this->getBootstrap()->bootstrap('FrontController');
                $front = Zend_Controller_Front::getInstance();

                if((bool) $options['plugins']['Doctrine'] === true) {
                    $this->getBootstrap()->bootstrap('doctrine');
                    $doctrinePlugin = new Danceric_Controller_Plugin_Debug_Plugin_Doctrine();
                }
                // remove it otherwise zfdebug will try to include it
                unset($options['plugins']['Doctrine']);

                // Create ZFDebug instance
                $zfdebug = new ZFDebug_Controller_Plugin_Debug($options);

                if($doctrinePlugin instanceof Danceric_Controller_Plugin_Debug_Plugin_Doctrine) {
                    $zfdebug->registerPlugin($doctrinePlugin);
                }

                // Register ZFDebug with the front controller
                $front->registerPlugin($zfdebug);
            }

            self::$_zfdebug = $zfdebug;
        }

        return self::$_zfdebug;
    }
}