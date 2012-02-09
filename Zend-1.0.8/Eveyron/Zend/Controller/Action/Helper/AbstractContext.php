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
 * @see Zend_Controller_Action_Helper_ContextSwitch
 */
require_once 'Zend/Controller/Action/Helper/ContextSwitch.php';

/**
 * @uses Zend_Controller_Action_Helper_ContextSwitch
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Controller
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Controller_Action_Helper_AbstractContext extends Zend_Controller_Action_Helper_ContextSwitch
{
    /**
     * Context auto-serialization flag
     * 
     * @var boolean
     */
    protected $_autoSerialization = true;
        
    /**
     * Should contexts auto-serialize?
     *
     * @param  boolean $flag
     * @return Zend_Controller_Action_Helper_ContextSwitch Provides a fluent interface
     */
    public function setAutoSerialization($flag)
    {
        $this->_autoSerialization = (bool) $flag;
        return $this;
    }

    /**
     * Get context auto-serialization flag
     *
     * @return boolean
     */
    public function getAutoSerialization()
    {
        return $this->_autoSerialization;
    }
    
    /**
     * context extra initialization
     *
     * Turns off viewRenderer auto-rendering
     *
     * @return void
     */
    public function initAbstractContext()
    {
        if (!$this->getAutoSerialization()) {
            return;
        }
                
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        if ($view instanceof Zend_View_Interface) {
            $viewRenderer->setNoRender(true);
        }
    }    
}