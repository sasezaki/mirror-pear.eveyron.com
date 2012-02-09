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
 * @see Zend_Log_Writer_Abstract
 */
require_once 'Zend/Log/Writer/Abstract.php';

/**
 * @uses Zend_Log_Writer_Abstract
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage Log
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_Log_Writer_Doctrine extends Zend_Log_Writer_Abstract
{
    /**
     * Model object
     * 
     * @var Doctrine_Record
     */
    private $_model;

    /**
     * Relates database columns names to log data field keys.
     *
     * @var array
     */
    private $_columnMap;

    /**
     * Class constructor
     *
     * @param Doctrine_Record $model         Log table in database
     * @param array $columnMap
     */
    public function __construct(Doctrine_Record $model, $columnMap = null)
    {
        $this->_model = $model;
        if($columnMap !== null) {
            $this->_columnMap = $columnMap;
        }
    }

    /**
     * Create a new instance of App_Log_Writer_Doctrine
     * 
     * @param  array|Zend_Config $config
     * @return App_Log_Writer_Doctrine
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'model'        => null, 
            'columnMap' => null,
        ), $config);
        
        if (isset($config['columnmap'])) {
            $config['columnMap'] = $config['columnmap'];
        }
        
        return new self(
            $config['model'],
            $config['columnMap']
        );
    }
    
    /**
     * Formatting is not possible on this writer
     */
    public function setFormatter($formatter)
    {
        require_once 'Zend/Log/Exception.php';
        throw new Zend_Log_Exception(get_class() . ' does not support formatting');
    }

    /**
     * Remove reference to database adapter
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_model = null;
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     * @throws Zend_Log_Exception
     */
    protected function _write($event)
    {
        if ($this->_model === null) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Doctrine Record is null');
        }

        if ($this->_columnMap === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach ($this->_columnMap as $columnName => $fieldKey) {
                $dataToInsert[$columnName] = $event[$fieldKey];
            }
        }
        try {
            $this->_model->cleanData($dataToInsert);
            $this->_model->fromArray($dataToInsert);
            $this->_model->save();
        }
        catch(Exception $ex) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception($ex->getMessage());
        }
    }
}