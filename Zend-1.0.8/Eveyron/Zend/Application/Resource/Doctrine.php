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
class Eveyron_Zend_Application_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'Doctrine';
    
    /**
     * @var Doctrine_Manager
     */
    protected static $_doctrine = null;
    
    /**
     * 
     * @return Doctrine_Manager
     */
    public function init()
    {
         return $this->getDoctrine();    
    }
    
    /**
     * 
     * @return Doctrine_Manager 
     */
    public function getDoctrine() {
        if (null === self::$_doctrine) {

            $this->injectDoctrine();
                
            $options = $this->getOptions();
            
            if(!isset($options['connection_string'])) {
                throw new Zend_Application_Resource_Exception('Connection string is not set.');
            }    
                        
            // see http://weierophinney.net/matthew/archives/220-Autoloading-Doctrine-and-Doctrine-entities-from-Zend-Framework.html
            foreach($options['autoload'] as $namespace => $enabled) {            
                if($enabled) {
                    $this->getBootstrap()->getApplication()->getAutoloader()->pushAutoloader(array('Doctrine', $namespace));
                }
            }        

            if(isset($options['cache']['query'])) {
                $queryCacheDriver = self::loadCacheDriver($options['cache']['query']);            
            }
            if(isset($options['cache']['result'])) {
                $resultCacheDriver = self::loadCacheDriver($options['cache']['result']);            
            }        
            
            // The query cache has no disadvantages, since you always get a fresh query result.
            if($queryCacheDriver instanceof Doctrine_Cache_Driver) {
                $options['attribute']['ATTR_QUERY_CACHE'] = $queryCacheDriver;
            }
            if($resultCacheDriver instanceof Doctrine_Cache_Driver) {
                $options['attribute']['ATTR_RESULT_CACHE'] = $resultCacheDriver;
            }
            
            $manager = Doctrine_Manager::getInstance();
            if(isset($options['attribute'])) {
                foreach($options['attribute'] as $name => $value) {
                    if(($constant = @constant($value)) !== null) {
                        $value = $constant;
                    }
                    $manager->setAttribute(constant(sprintf('%s::%s', 'Doctrine', $name)), $value);
                }
            }
            if(isset($options['models_path'])) {
                Doctrine::loadModels($options['models_path']);
            }
        
            $manager->openConnection($options['connection_string'], $options['connection_name']);
            
            // extensions
            if(isset($options['extensions_path'])) {
                Doctrine::setExtensionsPath($options['extensions_path']);
            
                if(!empty($options['extensions']))
                {
                    foreach($options['extensions'] as $extension)
                    {
                        $manager->registerExtension($extension);
                    }
                }
            }
            self::$_doctrine = $manager;    
        }
        
        return self::$_doctrine;
    }
    
    /**
     * 
     * @param array $options
     * @return Doctrine_Cache_Driver|null
     */
    public static function loadCacheDriver(array $options) {
        if(empty($options) || (isset($options['options']['disable_caching']) && (bool) $options['options']['disable_caching'] === true)) {
            return null;
        }
        unset($options['options']['disable_caching']);
        if(!class_exists($options['driver'])) {
            throw new Zend_Application_Resource_Exception(sprintf('% class not found.', $options['driver']));
        }        
        if(!in_array('Doctrine_Cache_Driver', class_parents($options['driver']))) {
            throw new Zend_Application_Resource_Exception('Cache driver must be an instance of Doctrine_Cache_Driver.');
        }        

        // Doctrine_Cache_Db expects pre-initialized connection
        if($options['driver'] === 'Doctrine_Cache_Db' || in_array('Doctrine_Cache_Db', class_parents($options['driver']))) {
            $options['options']['connection'] = Doctrine_Manager::connection($options['options']['connection']);        
        }
        $cacheDriver = new $options['driver']($options['options']);

        // create a cache table if missing
        if($cacheDriver instanceof Doctrine_Cache_Db) {
            if(!$cacheDriver->getConnection()->import->tableExists($options['options']['tableName'])) {
                $cacheDriver->createTable();
            }            
        }
        
        return $cacheDriver;                            
    }
    
    /**
     * Intelligently injects the doctrine
     * Note: since doctrine version 1.2.3 the distribution folder structure had changed
     * 
     * @return void
     */
    public function injectDoctrine() {
        $options = $this->getOptions();

        // prior to version 1.2.3
        $path = 'Doctrine/lib/Doctrine.php';
        $path_compiled = 'Doctrine/lib/Doctrine.compiled.php';
                    
        if(isset($options['library']['version']) && version_compare($options['library']['version'], '1.2.3', '>=')) {
            $path = 'Doctrine.php';
            $path_compiled = 'Doctrine.compiled.php';
        }

        // compiled version doesn't work for CLI        
        if(isset($options['library']['compiled']) && (bool) $options['library']['compiled'] === true && PHP_SAPI !== 'cli') {
            if(!(@include_once $path_compiled)) {
                require_once $path;
            }
        }
        else {
            require_once $path;
        }
    }
}