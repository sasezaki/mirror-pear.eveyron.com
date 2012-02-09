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
 * @see Zend_View_Helper_Navigation_Sitemap
 */
require_once 'Zend/View/Helper/Navigation/Sitemap.php';

/**
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage View
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_View_Helper_Navigation_Sitemap extends Zend_View_Helper_Navigation_Sitemap {
    /**
     * Whether url must be validated
     *
     * @var bool
     */
    protected $_validateUrl = true;
    
    /**
     * Returns an escaped absolute URL for the given page or '' if url host doesn't match serverUrl host
     *
     * @param  Zend_Navigation_Page $page  page to get URL from
     * @return string
     */
    public function url(Zend_Navigation_Page $page) {
        $url = parent::url($page);
        
        if ($this->getValidateUrl() && stripos($url, $this->getServerUrl()) === false) {
            return '';
        }
        
        return $url;
    }
    
    /**
     * Sets whether XML output should be validated
     *
     * @param  bool $flag [optional] Default is true.
     * @return Eveyron_Zend_View_Helper_Navigation_Sitemap
     */
    public function setValidateUrl($flag = true) {
        $this->_validateUrl = (bool)$flag;
        return $this;
    }
    
    /**
     * Returns whether url should be validated
     *
     * @return bool
     */
    public function getValidateUrl() {
        return $this->_validateUrl;
    }
}