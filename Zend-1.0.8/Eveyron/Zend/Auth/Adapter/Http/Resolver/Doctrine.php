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
 * @see Zend_Auth_Adapter_Http_Resolver_Interface
 */
require_once 'Zend/Auth/Adapter/Http/Resolver/Interface.php';

/**
 * @uses Zend_Auth_Adapter_Http_Resolver_Interface
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Auth
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Auth_Adapter_Http_Resolver_Doctrine implements Zend_Auth_Adapter_Http_Resolver_Interface
{
      /**
       * @var array
       */
      protected $_config = array(
        'table' => 'User',
        'usernamecol' => 'email',
        'passwordcol' => 'password'
    );

    /**
     * Constructor
     * 
     * @param object $config [optional]
     * @return void
     */
    public function __construct($config = '')
    {
        if (!empty($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Resolve credentials
     *
     * @param  string $username Username
     * @param  string $realm    Authentication Realm
     * @throws Zend_Auth_Adapter_Http_Resolver_Exception
     * @return string|false User's shared secret, if the user is found in the
     *         realm, false otherwise.
     */
    public function resolve($username, $realm)
    {
        if (empty($username)) {
            self::_throwException('Username is required');
        } else if (!ctype_print($username) || strpos($username, ':') !== false) {
            self::_throwException('Username must consist only of printable characters, excluding the colon');                                                              
        }
        if (empty($realm)) {
            self::_throwException('Realm is required');
        } else if (!ctype_print($realm) || strpos($realm, ':') !== false) {
            self::_throwException('Realm must consist only of printable characters, excluding the colon');
        }

        if(isset($this->_config['query']) && $this->_config['query'] instanceof Doctrine_Query) {
            $query = $this->_config['query'];
        }
        else {
            $query = Doctrine_Query::create()
                        ->from($this->_config['table'].' u')
                        ->select('u.'.$this->_config['passwordcol'])
                        ;
        }
        $alias = $query->getRootAlias();
        
        if(isset($this->_config['strict']) && $this->_config['strict']) {
            $query->where($alias.'.'.$this->_config['usernamecol'].' = ?', $username);
        }
        else {
            $query->where('LOWER('.$alias.'.'.$this->_config['usernamecol'].') = ?', strtolower($username));
        }
        
        $result = $query->fetchOne(array(), Doctrine::HYDRATE_SINGLE_SCALAR);

        return $result;
    }
    
    /**
     * Lazy-load and throw an exception
     * 
     * @var string
     * @return void
     * @throws Zend_Auth_Adapter_Http_Resolver_Exception
     */
    protected static function _throwException($msg) {
        /**
         * @see Zend_Auth_Adapter_Http_Resolver_Exception
         */
        require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
        throw new Zend_Auth_Adapter_Http_Resolver_Exception($msg);        
    }
    
    /**
     * Returns $config.
     * 
     * @return array
     */
    public function getConfig() {
        return $this->_config;
    }
    
    /**
     * Sets $config.
     *
     * @param array $config
     * @return Eveyron_Zend_Auth_Adapter_Http_Resolver_Doctrine
     */
    public function setConfig(array $config) {
        $this->_config = $config;
        return $this;
    }    
}