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
 * @see Zend_Locale
 */
require_once 'Zend/Locale.php';

/** 
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * @see Zend_Translate_Adapter
 */
require_once 'Zend/Translate/Adapter.php';

/**
 * Abstract class for different translate adapters
 * 
 * @uses Zend_Translate_Adapter
 * @uses Zend_Http_Client
 * @uses Zend_Locale
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Translate
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
abstract class Eveyron_Zend_Translate_Adapter_Abstract extends Zend_Translate_Adapter
{    
    const LANGUAGE_AFRIKAANS = 'af';
    const LANGUAGE_ALBANIAN = 'sq';
    const LANGUAGE_AMHARIC = 'am';
    const LANGUAGE_ARABIC = 'ar';
    const LANGUAGE_ARMENIAN = 'hy';
    const LANGUAGE_AZERBAIJANI = 'az';
    const LANGUAGE_BASQUE = 'eu';
    const LANGUAGE_BELARUSIAN = 'be';
    const LANGUAGE_BENGALI = 'bn';
    const LANGUAGE_BIHARI = 'bh';
    const LANGUAGE_BRETON = 'br';
    const LANGUAGE_BULGARIAN = 'bg';
    const LANGUAGE_BURMESE = 'my';
    const LANGUAGE_CATALAN = 'ca';
    const LANGUAGE_CHEROKEE = 'chr';
    const LANGUAGE_CHINESE = 'zh';
    const LANGUAGE_CHINESE_SIMPLIFIED = 'zh_CN';
    const LANGUAGE_CHINESE_TRADITIONAL = 'zh_TW';
    const LANGUAGE_CORSICAN = 'co';
    const LANGUAGE_CROATIAN = 'hr';
    const LANGUAGE_CZECH = 'cs';
    const LANGUAGE_DANISH = 'da';
    const LANGUAGE_DHIVEHI = 'dv';
    const LANGUAGE_DUTCH = 'nl';
    const LANGUAGE_ENGLISH = 'en';
    const LANGUAGE_ESPERANTO = 'eo';
    const LANGUAGE_ESTONIAN = 'et';
    const LANGUAGE_FAROESE = 'fo';
    const LANGUAGE_FILIPINO = 'tl';
    const LANGUAGE_FINNISH = 'fi';
    const LANGUAGE_FRENCH = 'fr';
    const LANGUAGE_FRISIAN = 'fy';
    const LANGUAGE_GALICIAN = 'gl';
    const LANGUAGE_GEORGIAN = 'ka';
    const LANGUAGE_GERMAN = 'de';
    const LANGUAGE_GREEK = 'el';
    const LANGUAGE_GUJARATI = 'gu';
    const LANGUAGE_HAITIAN_CREOLE = 'ht';
    const LANGUAGE_HEBREW = 'iw';
    const LANGUAGE_HINDI = 'hi';
    const LANGUAGE_HUNGARIAN = 'hu';
    const LANGUAGE_ICELANDIC = 'is';
    const LANGUAGE_INDONESIAN = 'id';
    const LANGUAGE_INUKTITUT = 'iu';
    const LANGUAGE_IRISH = 'ga';
    const LANGUAGE_ITALIAN = 'it';
    const LANGUAGE_JAPANESE = 'ja';
    const LANGUAGE_JAVANESE = 'jw';
    const LANGUAGE_KANNADA = 'kn';
    const LANGUAGE_KAZAKH = 'kk';
    const LANGUAGE_KHMER = 'km';
    const LANGUAGE_KOREAN = 'ko';
    const LANGUAGE_KURDISH = 'ku';
    const LANGUAGE_KYRGYZ = 'ky';
    const LANGUAGE_LAO = 'lo';
    const LANGUAGE_LATIN = 'la';
    const LANGUAGE_LATVIAN = 'lv';
    const LANGUAGE_LITHUANIAN = 'lt';
    const LANGUAGE_LUXEMBOURGISH = 'lb';
    const LANGUAGE_MACEDONIAN = 'mk';
    const LANGUAGE_MALAY = 'ms';
    const LANGUAGE_MALAYALAM = 'ml';
    const LANGUAGE_MALTESE = 'mt';
    const LANGUAGE_MAORI = 'mi';
    const LANGUAGE_MARATHI = 'mr';
    const LANGUAGE_MONGOLIAN = 'mn';
    const LANGUAGE_NEPALI = 'ne';
    const LANGUAGE_NORWEGIAN = 'no';
    const LANGUAGE_OCCITAN = 'oc';
    const LANGUAGE_ORIYA = 'or';
    const LANGUAGE_PASHTO = 'ps';
    const LANGUAGE_PERSIAN = 'fa';
    const LANGUAGE_POLISH = 'pl';
    const LANGUAGE_PORTUGUESE = 'pt';
    const LANGUAGE_PORTUGUESE_PORTUGAL = 'pt_PT';
    const LANGUAGE_PUNJABI = 'pa';
    const LANGUAGE_QUECHUA = 'qu';
    const LANGUAGE_ROMANIAN = 'ro';
    const LANGUAGE_RUSSIAN = 'ru';
    const LANGUAGE_SANSKRIT = 'sa';
    const LANGUAGE_SCOTS_GAELIC = 'gd';
    const LANGUAGE_SERBIAN = 'sr';
    const LANGUAGE_SINDHI = 'sd';
    const LANGUAGE_SINHALESE = 'si';
    const LANGUAGE_SLOVAK = 'sk';
    const LANGUAGE_SLOVENIAN = 'sl';
    const LANGUAGE_SPANISH = 'es';
    const LANGUAGE_SUNDANESE = 'su';
    const LANGUAGE_SWAHILI = 'sw';
    const LANGUAGE_SWEDISH = 'sv';
    const LANGUAGE_SYRIAC = 'syr';
    const LANGUAGE_TAJIK = 'tg';
    const LANGUAGE_TAMIL = 'ta';
    const LANGUAGE_TATAR = 'tt';
    const LANGUAGE_TELUGU = 'te';
    const LANGUAGE_THAI = 'th';
    const LANGUAGE_TIBETAN = 'bo';
    const LANGUAGE_TONGA = 'to';
    const LANGUAGE_TURKISH = 'tr';
    const LANGUAGE_UKRAINIAN = 'uk';
    const LANGUAGE_URDU = 'ur';
    const LANGUAGE_UZBEK = 'uz';
    const LANGUAGE_UIGHUR = 'ug';
    const LANGUAGE_VIETNAMESE = 'vi';
    const LANGUAGE_WELSH = 'cy';
    const LANGUAGE_YIDDISH = 'yi';
    const LANGUAGE_YORUBA = 'yo';
    
    /**
     * User specified languages
     * 
     * @var array
     */
    protected $_languages = array();
            
    /**
     * @var Zend_Http_Client
     */
    protected $_client;
    
    /**
     * Generates the adapter
     *
     * @param  array|Zend_Config $options Translation options for this adapter
     * @throws Zend_Translate_Exception
     * @return void
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        
        // add all available languages to enable getList, isAvailable methods
        foreach($options['languages'] as $v)
        {
            $language = self::getLanguageByName($v);
            if(!in_array($language, $this->_languages)) {
                require_once 'Zend/Translate/Exception.php';
                throw new Zend_Translate_Exception(sprintf('Language %s (%s) is not supported by this adapter.', $v, $language));                
            }
            $this->_translate[$language] = $language;
        }
        
        $this->_client = new Zend_Http_Client(null, array(
            'maxredirects' => 0,
            'adapter' => 'Zend_Http_Client_Adapter_Curl',            
        ));        
    }
    
    /**
     * Gets language value by a common name
     * 
     * @param string $value language name, e.g. JAPANESE or LANGUAGE_JAPANESE 
     * @return string
     */
    public static function getLanguageByName($value)
    {
        if(stripos($value, 'language_') === false) {
            $value = 'language_'.$value;
        }            
        $value = strtoupper($value);        
        $language = constant('self::'.$value);
        if($language === null) {
            require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception(sprintf('Undefined constant: %s', $value));
        }
        
        return $language;
    }    
    
    /**
     * Lists all available languages as code-language pair
     * 
     * @return array
     */
    public static function getAvailableLanguages()
    {
        $result = array();
        $refClass = new ReflectionClass(__CLASS__);
        foreach($refClass->getConstants() as $k => $v)
        {
            if(substr($k, 0, strlen('LANGUAGE_')) !== 'LANGUAGE_') {
                continue;
            }
            $k = substr($k, strlen('LANGUAGE_'));
            $k = ucfirst(strtolower($k));
            $ks = explode('_', $k);
            
            if(count($ks) > 1) {
                $k = array_shift($ks) . ' (' . implode(' ', $ks).')';
            }            
            $result[$v] = $k;
        }
        return $result;
    }    
}