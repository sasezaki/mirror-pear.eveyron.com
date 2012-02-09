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
 * @see Zend_Translate_Adapter
 */
require_once 'Eveyron/Zend/Translate/Adapter/Abstract.php';

/**
 * @link http://code.google.com/apis/ajaxlanguage/documentation/reference.html
 * @uses Zend_Translate_Adapter
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Translate
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Translate_Adapter_Google extends Eveyron_Zend_Translate_Adapter_Abstract
{    
    /**
     * Languages supported by Google Translate
     * 
     * @var array
     */
    protected $_languages = array(
        self::LANGUAGE_AFRIKAANS => 'af',
        self::LANGUAGE_ALBANIAN => 'sq',
        self::LANGUAGE_AMHARIC => 'am',
        self::LANGUAGE_ARABIC => 'ar',
        self::LANGUAGE_ARMENIAN => 'hy',
        self::LANGUAGE_AZERBAIJANI => 'az',
        self::LANGUAGE_BASQUE => 'eu',
        self::LANGUAGE_BELARUSIAN => 'be',
        self::LANGUAGE_BENGALI => 'bn',
        self::LANGUAGE_BIHARI => 'bh',
        self::LANGUAGE_BRETON => 'br',
        self::LANGUAGE_BULGARIAN => 'bg',
        self::LANGUAGE_BURMESE => 'my',
        self::LANGUAGE_CATALAN => 'ca',
        self::LANGUAGE_CHEROKEE => 'chr',
        self::LANGUAGE_CHINESE => 'zh',
        self::LANGUAGE_CHINESE_SIMPLIFIED => 'zh-CN',
        self::LANGUAGE_CHINESE_TRADITIONAL => 'zh-TW',
        self::LANGUAGE_CORSICAN => 'co',
        self::LANGUAGE_CROATIAN => 'hr',
        self::LANGUAGE_CZECH => 'cs',
        self::LANGUAGE_DANISH => 'da',
        self::LANGUAGE_DHIVEHI => 'dv',
        self::LANGUAGE_DUTCH => 'nl',
        self::LANGUAGE_ENGLISH => 'en',
        self::LANGUAGE_ESPERANTO => 'eo',
        self::LANGUAGE_ESTONIAN => 'et',
        self::LANGUAGE_FAROESE => 'fo',
        self::LANGUAGE_FILIPINO => 'tl',
        self::LANGUAGE_FINNISH => 'fi',
        self::LANGUAGE_FRENCH => 'fr',
        self::LANGUAGE_FRISIAN => 'fy',
        self::LANGUAGE_GALICIAN => 'gl',
        self::LANGUAGE_GEORGIAN => 'ka',
        self::LANGUAGE_GERMAN => 'de',
        self::LANGUAGE_GREEK => 'el',
        self::LANGUAGE_GUJARATI => 'gu',
        self::LANGUAGE_HAITIAN_CREOLE => 'ht',
        self::LANGUAGE_HEBREW => 'iw',
        self::LANGUAGE_HINDI => 'hi',
        self::LANGUAGE_HUNGARIAN => 'hu',
        self::LANGUAGE_ICELANDIC => 'is',
        self::LANGUAGE_INDONESIAN => 'id',
        self::LANGUAGE_INUKTITUT => 'iu',
        self::LANGUAGE_IRISH => 'ga',
        self::LANGUAGE_ITALIAN => 'it',
        self::LANGUAGE_JAPANESE => 'ja',
        self::LANGUAGE_JAVANESE => 'jw',
        self::LANGUAGE_KANNADA => 'kn',
        self::LANGUAGE_KAZAKH => 'kk',
        self::LANGUAGE_KHMER => 'km',
        self::LANGUAGE_KOREAN => 'ko',
        self::LANGUAGE_KURDISH => 'ku',
        self::LANGUAGE_KYRGYZ => 'ky',
        self::LANGUAGE_LAO => 'lo',
        self::LANGUAGE_LATIN => 'la',
        self::LANGUAGE_LATVIAN => 'lv',
        self::LANGUAGE_LITHUANIAN => 'lt',
        self::LANGUAGE_LUXEMBOURGISH => 'lb',
        self::LANGUAGE_MACEDONIAN => 'mk',
        self::LANGUAGE_MALAY => 'ms',
        self::LANGUAGE_MALAYALAM => 'ml',
        self::LANGUAGE_MALTESE => 'mt',
        self::LANGUAGE_MAORI => 'mi',
        self::LANGUAGE_MARATHI => 'mr',
        self::LANGUAGE_MONGOLIAN => 'mn',
        self::LANGUAGE_NEPALI => 'ne',
        self::LANGUAGE_NORWEGIAN => 'no',
        self::LANGUAGE_OCCITAN => 'oc',
        self::LANGUAGE_ORIYA => 'or',
        self::LANGUAGE_PASHTO => 'ps',
        self::LANGUAGE_PERSIAN => 'fa',
        self::LANGUAGE_POLISH => 'pl',
        self::LANGUAGE_PORTUGUESE => 'pt',
        self::LANGUAGE_PORTUGUESE_PORTUGAL => 'pt-PT',
        self::LANGUAGE_PUNJABI => 'pa',
        self::LANGUAGE_QUECHUA => 'qu',
        self::LANGUAGE_ROMANIAN => 'ro',
        self::LANGUAGE_RUSSIAN => 'ru',
        self::LANGUAGE_SANSKRIT => 'sa',
        self::LANGUAGE_SCOTS_GAELIC => 'gd',
        self::LANGUAGE_SERBIAN => 'sr',
        self::LANGUAGE_SINDHI => 'sd',
        self::LANGUAGE_SINHALESE => 'si',
        self::LANGUAGE_SLOVAK => 'sk',
        self::LANGUAGE_SLOVENIAN => 'sl',
        self::LANGUAGE_SPANISH => 'es',
        self::LANGUAGE_SUNDANESE => 'su',
        self::LANGUAGE_SWAHILI => 'sw',
        self::LANGUAGE_SWEDISH => 'sv',
        self::LANGUAGE_SYRIAC => 'syr',
        self::LANGUAGE_TAJIK => 'tg',
        self::LANGUAGE_TAMIL => 'ta',
        self::LANGUAGE_TATAR => 'tt',
        self::LANGUAGE_TELUGU => 'te',
        self::LANGUAGE_THAI => 'th',
        self::LANGUAGE_TIBETAN => 'bo',
        self::LANGUAGE_TONGA => 'to',
        self::LANGUAGE_TURKISH => 'tr',
        self::LANGUAGE_UKRAINIAN => 'uk',
        self::LANGUAGE_URDU => 'ur',
        self::LANGUAGE_UZBEK => 'uz',
        self::LANGUAGE_UIGHUR => 'ug',
        self::LANGUAGE_VIETNAMESE => 'vi',
        self::LANGUAGE_WELSH => 'cy',
        self::LANGUAGE_YIDDISH => 'yi',
        self::LANGUAGE_YORUBA => 'yo',
    );    
    
    /**
     * Constructor
     *
     * @param array|Zend_Config $options Translation options for this adapter
     * @throws Zend_Translate_Exception
     * @return void
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->_client->setUri('http://ajax.googleapis.com/ajax/services/language/translate');
    }
        
    /**
     * Translate message
     *
     * @param string $messageId
     * @param Zend_Locale|string $locale
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        if ($locale === null) {            
            $locale = $this->getLocale();
        }

        if ( ! isset($this->_languages[$locale])) {
            // language is not supported by this adapter, return original string            
            return $messageId;
        }        
        
        // map generic language value to adapter specific one
        $locale = $this->_languages[$locale];
        
        if (!Zend_Locale::isLocale($locale, true)) {
            if (!Zend_Locale::isLocale($locale, false)) {
                // language does not exist, return original string
                return $messageId;
            }
        }

        $source = $this->_options['source'];
        if ($source == $locale) {
            return $messageId;
        }

        $langpair = $source.'|'.$locale;
        $cacheId = 'translation_'.preg_replace('/[|\-]+/si','_',$langpair).'_'.md5($messageId);
        
        if(!$result = $this->getCache()->load($cacheId)) {
            $this->_client->setParameterGet(array(
                'v' => '1.0',
                'q' => $messageId,
                'langpair' => $langpair,
                //'format' => 'html' // html|text
            ));

            try {
                $response = $this->_client->request();
            }
            catch(Zend_Http_Client_Adapter_Exception $e)
            {
                return $messageId;
            }

            $response = $response->getBody();            
            $this->_client->resetParameters();
            $data = json_decode($response);

            if($data->responseStatus != 200 && !$data->responseDetails)
            {
                return $messageId;
            }
            $result = $data->responseData->translatedText;
            $this->getCache()->save($result, $cacheId, array('translation'));
        }

        return $result;
    }

    /**
     * Load translation data
     *
     * @param string|array $data
     * @param string $locale  Locale/Language to add data for, identical with locale identifier, see Zend_Locale for more information
     * @param array $options [optional] Options to use
     */
    protected function _loadTranslationData($data, $locale, array $options = array())
    {
        $options = $options + $this->_options;
        if (($options['clear'] == true) ||  !isset($this->_translate[$locale])) {
            $this->_translate[$locale] = array();
        }

        $this->_translate[$locale] = $data + $this->_translate[$locale] + array($locale);
    }

    /**
     * Returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Google";
    }
}