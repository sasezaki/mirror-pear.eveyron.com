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
 * @see Eveyron_Zend_Controller_Action_Helper_AbstractContext
 */
require_once 'Eveyron/Zend/Controller/Action/Helper/AbstractContext.php';

/**
 * Registering:
 * application/configs/application.ini
 * 
 * resources.frontController.actionhelperpaths.Eveyron_Zend_Controller_Action_Helper_ = "Eveyron/Zend/Controller/Action/Helper/"
 * 
 * Usage:
 * application/modules/default/controllers/IndexController.php
 * 
 * class IndexController extends Zend_Controller_Action
 * {    
 *         public function init() {        
 *             $this->_helper
 *                 ->phpContext()
 *                 ->addActionContexts(array(
 *                     'index' => array('php')
 *                 ))
 *                 ->initContext()
 *             ;
 *         }
 *         
 *         public function indexAction() {
 *             $result = new stdClass;
 *             $result->success = true;
 *             $result->payload = array('id' => '1234');
 *             $this->view->assign((array) $result);
 *         }
 * }
 * 
 * Invoking:
 * http://yourwebsite/default/index/index/format/php
 */

/**
 * @uses Eveyron_Zend_Controller_Action_Helper_AbstractContext
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Controller
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Controller_Action_Helper_PhpContext extends Eveyron_Zend_Controller_Action_Helper_AbstractContext
{        
    /**
     * Controller property to utilize for context switching
     * @var string
     */
    protected $_contextKey = 'php';

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->clearContexts();
        $this->setContext($this->_contextKey, array(
            'suffix'    => 'php',
            'headers'   => array('Content-Type' => 'text/php'),
             'callbacks' => array(
                'init' => 'initPhpContext',
                'post' => 'postPhpContext'
            )                                
        ));        
    }

    /**
     * PHP context extra initialization
     *
     * Turns off viewRenderer auto-rendering
     *
     * @return void
     */
    public function initPhpContext()
    {
        $this->initAbstractContext();
    }

    /**
     * PHP post processing
     *
     * PHP serialize view variables to response body
     *
     * @return void
     */
    public function postPhpContext()
    {
        if (!$this->getAutoSerialization()) {
            return;
        }
                
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        if ($view instanceof Zend_View_Interface) {
            if(method_exists($view, 'getVars')) {
                $vars = serialize($view->getVars());
                $this->getResponse()->setBody($vars);
            } else {
                require_once 'Zend/Controller/Action/Exception.php';
                throw new Zend_Controller_Action_Exception('View does not implement the getVars() method needed to encode the view into JSON');
            }
        }
    }
}