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
 *  Usage.
 *  
 *  application/modules/default/layouts/default.phtml
 *  
 *  $activePage = $this->navigation()->findActive($this->navigation()->getContainer());
 *  $activePage = $activePage['page'];
 *  $this->headMetaPage($activePage);
 *  echo $this->headMeta()."\n";
 */

/**
 * @see Zend_View_Helper_HeadMeta
 */
require_once 'Zend/View/Helper/HeadMeta.php';

/**
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage View
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_View_Helper_HeadMetaPage extends Zend_View_Helper_HeadMeta 
{
    /**
     * 
     * @param Zend_Navigation_Page $page
     * @param boolean $translate [optional]
     * @return void
     */
    public function headMetaPage($page, $translate = true) {
        if($page instanceof Zend_Navigation_Page) {
            if($description = (string)$page->description) {
                $this->setName('description', $translate ? $this->translate($description) : $description);
            }
            if($keywords = (string)$page->keywords) {
                $this->setName('keywords', $translate ? $this->translate($keywords) : $keywords);
            }    
        }        
    }
}