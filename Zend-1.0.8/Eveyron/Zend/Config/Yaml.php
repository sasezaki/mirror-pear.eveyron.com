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
 * @see Zend_Config
 */
require_once 'Zend/Config.php';

/**
 * YAML Adapter for Zend_Config
 * Supports different YAML parsers.
 * 
 * @uses Zend_Config
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Config
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Config_Yaml extends Zend_Config
{
    /**
     * Adapter class to use to parse yaml
     * 
     * @var string
     */
    protected $_adapter = 'Eveyron_Yaml_Adapter_PeclYaml';
    
    /**
     * Reference to Eveyron_Yaml instance
     * 
     * @var Eveyron_Yaml
     */
    protected $_yaml;    
        
    /**
     * String that separates the parent section name
     *
     * @var string
     */
    protected $_sectionSeparator = '<';
            
    /**
     * Whether to skip extends or not
     *
     * @var boolean
     */
    protected $_skipExtends = false;
    
    /**
     * This is a slightly modified version of Zend_Config_Ini::__construct
     * 
     * Loads the section $section from the config file $filename for
     * access facilitated by nested object properties.
     *
     * If the section name contains a "$_sectionSeparator" then the section name to the right
     * is loaded and included into the properties. Note that the keys in
     * this $section will override any keys of the same
     * name in the sections that have been included via "$_sectionSeparator".
     *
     * If the $section is null, then all sections in the YAML file are loaded.
     *
     *
     * example yaml file:
     *      production:
     *        db.connection: database
     *        hostname: live
     *
     *      staging : all
     *        hostname: staging
     *
     * after calling $data = new Eveyron_Config_Yaml($file, 'staging'); then
     *      $data->hostname === "staging"
     *      $data->db->connection === "database"
     *
     * The $options parameter may be provided as either a boolean or an array.
     * If provided as a boolean, this sets the $allowModifications option of
     * Zend_Config. If provided as an array, there are two configuration
     * directives that may be set. For example:
     *
     * $options = array(
     *     'driver' => Eveyron_Config_Yaml::YAML_DRIVER_SF_YAML
     *     'allowModifications' => false,
     *     'sectionSeparator'      => '<'
     *      );
     *
     * @param  string        $filename
     * @param  string|null   $section
     * @param  boolean|array $options
     * @throws Zend_Config_Exception
     * @return void
     */    
    public function __construct($filename, $section = null, $options = false)
    {
        /**
         * @see Eveyron_Yaml
         */
        include_once 'Eveyron/Yaml.php';        
        if (!class_exists('Eveyron_Yaml')) {
            self::_throwException('Eveyron_Yaml class is missing. Please run: pear channel-discover pear.eveyron.com && pear install eveyron/Yaml');
        }

        if (empty($filename)) {
            self::_throwException('Filename is not set');
        }
        
        $allowModifications = false;
        if (is_bool($options)) {
            $allowModifications = $options;
        } elseif (is_array($options)) {
            if (isset($options['allowModifications'])) {
                $allowModifications = (bool) $options['allowModifications'];
            }
            if (isset($options['skipExtends'])) {
                $this->_skipExtends = (bool) $options['skipExtends'];
            }        
        }
        
        $this->_yaml = new Eveyron_Yaml(array_merge(array('adapter' => $this->_adapter), (array) $options));
        
        $yamlArray = $this->_loadYamlFile($filename);
        $dataArray = array();
        
        if (null === $section) {
            // Load entire file
            foreach ($yamlArray as $sectionName => $sectionData) {
                if(!is_array($sectionData)) {
                    $dataArray = $this->_arrayMergeRecursive($dataArray, $sectionData);
                } else {
                    $dataArray[$sectionName] = $this->_processSection($yamlArray, $sectionName);
                }
            }
        } 
        else {
            // Load one or more sections
            if (!is_array($section)) {
                $section = array($section);
            }
            foreach ($section as $sectionName) {
                if (!isset($yamlArray[$sectionName])) {
                    self::_throwException("Section '$sectionName' cannot be found in $filename");
                }
                $dataArray = $this->_arrayMergeRecursive($this->_processSection($yamlArray, $sectionName), $dataArray);
            }
        }

        parent::__construct($dataArray, $allowModifications);
        
        $this->_loadedSection = $section;
    }
    
    /**
     * Load the YAML file from disk. Use a private error
     * handler to convert any loading errors into a Zend_Config_Exception
     * 
     * @param string $filename
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _parseYamlFile($filename)
    {
        set_error_handler(array($this, '_loadFileErrorHandler'));
        $yamlArray = $this->_yaml->parse(file_get_contents($filename));   
        restore_error_handler();
        
        // Check if there was a error while loading file
        if ($this->_loadFileErrorStr !== null) {
            self::_throwException($this->_loadFileErrorStr);
        }
        
        return $yamlArray;
    }
        
    /**
     * This is taken directly from Zend_Config_Ini::_loadIniFile
     * 
     * Load the yaml file and preprocess the section separator ('$_sectionSeparator' in the
     * section name (that is used for section extension) so that the resultant
     * array has the correct section names and the extension information is
     * stored in a sub-key called ';extends'. We use ';extends' as this can
     * never be a valid key name in an YAML file that has been loaded using
     * different drivers.
     *
     * @param string $filename
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _loadYamlFile($filename)
    {
        $loaded = $this->_parseYamlFile($filename);
        $yamlArray = array();
        foreach ($loaded as $key => $data)
        {
            $pieces = explode($this->_sectionSeparator, $key);
            $thisSection = trim($pieces[0]);
            switch (count($pieces)) {
                case 1:
                    $yamlArray[$thisSection] = $data;
                    break;

                case 2:
                    $extendedSection = trim($pieces[1]);
                    $yamlArray[$thisSection] = array_merge(array(';extends'=>$extendedSection), $data);
                    break;

                default:
                    self::_throwException("Section '$thisSection' may not extend multiple sections in $filename");
            }
        }

        return $yamlArray;
    }    
    
    /**
     * Process each element in the section and handle the ";extends" inheritance
     * key.
     *
     * @param  array  $yamlArray
     * @param  string $section
     * @param  array  $config
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _processSection($yamlArray, $section, $config = array())
    {
        $key = ';extends';
        $thisSection = $yamlArray[$section];
        if(isset($thisSection[$key])) {
            $value = $thisSection[$key];
            if (isset($yamlArray[$value])) {
                $this->_assertValidExtend($section, $value);
                unset($thisSection[$key]);
                $config = $this->_arrayMergeRecursive($yamlArray[$value], $thisSection);
            } else {
                self::_throwException("Parent section '$section' cannot be found");
            }            
        } else {
            $config = $thisSection;
        }

        return $config;
    }
    
    /**
     * Lazy-loading for Zend_Config_Exception
     * 
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