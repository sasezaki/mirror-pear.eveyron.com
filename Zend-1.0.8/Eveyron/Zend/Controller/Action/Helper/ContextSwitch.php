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
 * @see Eveyron_Zend_Controller_Action_Helper_YamlContext
 */
require_once 'Eveyron/Zend/Controller/Action/Helper/YamlContext.php';

/**
 * @see Eveyron_Zend_Controller_Action_Helper_PhpContext
 */
require_once 'Eveyron/Zend/Controller/Action/Helper/PhpContext.php';

/**
 * @see Eveyron_Zend_Controller_Action_Helper_XmlContext
 */
require_once 'Eveyron/Zend/Controller/Action/Helper/XmlContext.php';

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
 *                 ->contextSwitch()
 *                 ->addActionContexts(array(
 *                     'index' => array('yaml', 'php', 'xml', 'json')
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
 * http://yourwebsite/default/index/index/format/yaml
 * http://yourwebsite/default/index/index/format/php
 * http://yourwebsite/default/index/index/format/xml
 * http://yourwebsite/default/index/index/format/json
 */

/**
 * @uses Eveyron_Zend_Controller_Action_Helper_AbstractContext
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Controller
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Controller_Action_Helper_ContextSwitch extends Eveyron_Zend_Controller_Action_Helper_AbstractContext
{            
    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null)
    {        
        parent::__construct($options);

        $yamlContext = new Eveyron_Zend_Controller_Action_Helper_YamlContext($options);
        $yamlContext->setCallbacks('yaml', array(
            'init' => array($yamlContext, 'initYamlContext'),
            'post' => array($yamlContext, 'postYamlContext')
        ));
                
        $phpContext = new Eveyron_Zend_Controller_Action_Helper_PhpContext($options);        
        $phpContext->setCallbacks('php', array(
            'init' => array($phpContext, 'initPhpContext'),
            'post' => array($phpContext, 'postPhpContext')
        ));
                
        $xmlContext = new Eveyron_Zend_Controller_Action_Helper_XmlContext($options);        
        $xmlContext->setCallbacks('xml', array(
            'init' => array($xmlContext, 'initXmlContext'),
            'post' => array($xmlContext, 'postXmlContext')
        ));
        
        // overload existing xml context
        $this->setContext('xml', $xmlContext->getContext('xml'));

        // add new contexts
        $this->addContexts(array(    
            'yaml' => $yamlContext->getContext('yaml'),
            'php' => $phpContext->getContext('php')            
        ));
    }    
}