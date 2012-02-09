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
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * @uses Zend_Application_Resource_ResourceAbstract
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Application
 * @subpackage Resource 
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Application_Resource_Acl extends Zend_Application_Resource_ResourceAbstract
{                
    const DEFAULT_REGISTRY_KEY = 'Acl';
    
    /**
     * @var Zend_Acl
     */
    protected static $_acl = null;
    
    /**
     * 
     * @return void
     */
    public function init()
    {
         return $this->getAcl();    
    }
    
    /**
     * 
     * @return void
     */
    public function getAcl() {
        if(null === self::$_acl) {
            $options = $this->getOptions();
            self::$_acl = new Zend_Acl();
            
            if(isset($options['roles'])) {
                 foreach($options['roles'] as $role) {
                     self::$_acl->addRole($role['role'], self::getRoles($role['parents']));
                 }
            }
            if(isset($options['resources'])) {
                 foreach($options['resources'] as $resource) {
                     self::$_acl->addResource($resource['resource'], isset($resource['parent']) ? $resource['parent'] : null);
                 }
            }
            if(isset($options['allow'])) {
                self::setRules('allow', $options['allow']);
            }
            if(isset($options['deny'])) {
                self::setRules('deny', $options['deny']);
            }                    
        }
        return self::$_acl;
    }
    
    /**
     * 
     * @param string $type allow|deny
     * @param array $rules
     * @return void
     */
    public static function setRules($type, $rules) {
        if(empty($rules)) {
            return;
        }
        foreach($rules as $rule) {
            $roles = null;
            $resources = null;
            $priveleges = null;
            $assert = null;
            
            if(isset($rule['roles'])) {
                $roles = self::getRoles($rule['roles']);
            }
            if(isset($rule['resources'])) {
                $resources = $rule['resources'];
            }    
            if(isset($rule['priveleges'])) {
                $priveleges = $rule['priveleges'];
            }                    
            if(isset($rule['assert']) && class_exists($rule['assert']) && in_array('Zend_Acl_Assert_Interface', class_implements($rule['assert']))) {
                $assert = new $rule['assert'];
            }

            switch($type) {
                case 'allow':
                    self::$_acl->allow($roles, $resources, $priveleges, $assert);
                break;
                
                case 'deny':
                    self::$_acl->deny($roles, $resources, $priveleges, $assert);
                break;                
            }            
        }        
    }
    
    /**
     * Implements role referencing for previously defined roles
     * 
     * application.ini
     * 
     * resources.acl.roles[0].role = "admin"
     * resources.acl.roles[1].role = "editor"
     * resources.acl.roles[1].parents = "admin"
     * resources.acl.roles[2].role = "guest"
     * resources.acl.roles[2].parents[] = "editor"
     * resources.acl.roles[2].parents[] = "admin"
     * 
     * @param array|string $parents
     * @return array
     */
    public static function getRoles($roles) {
        if(empty($roles)) {
            return null;
        }
        $normalized_roles = array();
        if(!is_array($roles)) {
            $roles = array($roles);
        }
        foreach($roles as $role) {            
            if(self::$_acl->hasRole($role)) {
                $normalized_roles[] = self::$_acl->getRole($role);
            }
            else {
                $normalized_roles[] = $role;                
            }
        }        
        return $normalized_roles;
    }    
}