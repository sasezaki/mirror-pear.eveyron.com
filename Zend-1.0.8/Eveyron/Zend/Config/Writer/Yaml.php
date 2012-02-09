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
 * @see Zend_Config_Writer
 */
require_once 'Zend/Config/Writer/FileAbstract.php';

/**
 * YAML Writer for Zend_Config
 * Supports different YAML parsers.
 * 
 * @uses Zend_Config
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Config
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Config_Writer_Yaml extends Zend_Config_Writer_FileAbstract
{
    /**
     * Reference to Eveyron_Yaml instance
     * 
     * @var Eveyron_Yaml
     */
    protected $_yaml;    
        
    /**
     * Adapter class to use to parse yaml
     * 
     * @var array
     */
    protected $_yamlOptions = array(
        'adapter' => 'Eveyron_Yaml_Adapter_PeclYaml'
    );
        
    /**
     * String that separates the parent section name
     *
     * @var string
     */
    protected $_sectionSeparator = '<';
            
    /**
     * Create a new adapter
     *
     * $options can only be passed as array or be omitted
     *
     * @param null|array $options
     */
    public function __construct(array $options = null)
    {
        if(isset($options['adapter'])) {
            $options['yamlOptions']['adapter'] = $options['adapter'];
            unset($options['adapter']);
        }
        
        parent::__construct($options);
        
        /**
         * @see Eveyron_Yaml
         */
        include_once 'Eveyron/Yaml.php';        
        if (!class_exists('Eveyron_Yaml')) {
            self::_throwException('Eveyron_Yaml class is missing. Please run: pear channel-discover pear.eveyron.com && pear install eveyron/Yaml');
        }
        
        $this->_yaml = new Eveyron_Yaml($this->_yamlOptions);
    }
    
    /**
     * 
     * @param string $string
     * @return Eveyron_Config_Writer_Yaml 
     */
    public function setSectionSeparator($string) {
        $this->_sectionSeparator = trim((string) $string);
        return $this;
    }
            
    /**
     * Sets YAML adapter options
     * 
     * @param array $options
     * @return Eveyron_Config_Writer_Yaml
     */
    public function setYamlOptions($options) {
        $this->_yamlOptions = (array) $options;
        return $this;
    }
        
    /**
     * Render a Zend_Config into a YAML config string.
     *
     * @return string
     */
    public function render()
    {
        $yamlString  = '';
        $extends     = $this->_config->getExtends();
        $sectionName = $this->_config->getSectionName();

        $config = $this->_sortRootElements($this->_config);
        $new_config = array();
        foreach ($config as $sectionName => $data) {
            if (!($data instanceof Zend_Config)) {
                $new_config[$sectionName] = $data;
            } else {
                if (isset($extends[$sectionName])) {
                    $sectionName .= ' '.$this->_sectionSeparator.' '.$extends[$sectionName];
                }
                $new_config[$sectionName] = $data->toArray();
            }                
        }
        
        $yamlString = $this->_yaml->emit($new_config);
        return $yamlString;
    }

    /**
     * Taken directly from Zend_Config_Writer_Ini::_sortRootElements
     * 
     * Root elements that are not assigned to any section needs to be
     * on the top of config.
     * 
     * @see    http://framework.zend.com/issues/browse/ZF-6289
     * @param  Zend_Config
     * @return Zend_Config
     */
    protected function _sortRootElements(Zend_Config $config)
    {
        $configArray = $config->toArray();
        $sections = array();
        
        // remove sections from config array
        foreach ($configArray as $key => $value) {
            if (is_array($value)) {
                $sections[$key] = $value;
                unset($configArray[$key]);
            }
        }
        
        // read sections to the end
        foreach ($sections as $key => $value) {
            $configArray[$key] = $value;
        }
        
        return new Zend_Config($configArray);
    }
        
    /**
     * Lazy-loading for Zend_Config_Exception
     * @param string $msg
     * @return void
     * @throws Zend_Config_Exception
     */    
    protected static function _throwException($msg) {
       /**
         * @see Zend_Config_Exception
         */
        require_once 'Zend/Config/Exception.php';
        throw new Zend_Config_Exception($msg);        
    }    
}