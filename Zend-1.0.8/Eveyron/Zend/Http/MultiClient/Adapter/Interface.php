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
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Http
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
interface Eveyron_Zend_Http_MultiClient_Adapter_Interface {

    /**
     * Connect to the remote server
     * 
     * @return void
     */
    public function connect();
    
    /**
     * Send request to the remote server
     *
     * @return string Request as text
     */
    public function write();
    
    /**
     * Read response from server
     *
     * @return string
     */
    public function read();
    
    /**
     * Close the connection to the server
     * 
     * @return void
     */
    public function close();
}