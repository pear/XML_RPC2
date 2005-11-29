<?php
/* LICENSE AGREEMENT. If folded, press za here to unfold and read license {{{ 
   vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4 foldmethod=marker:    
   +-----------------------------------------------------------------------------+
   | Copyright (c) 2004 Sérgio Gonçalves Carvalho                                |
   +-----------------------------------------------------------------------------+
   | This file is part of XML_RPC2.                                              |
   |                                                                             |
   | XML_RPC is free software; you can redistribute it and/or modify             |
   | it under the terms of the GNU Lesser General Public License as published by |
   | the Free Software Foundation; either version 2.1 of the License, or         |
   | (at your option) any later version.                                         |
   |                                                                             |
   | XML_RPC2 is distributed in the hope that it will be useful,         |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU Lesser General Public License for more details.                         |
   |                                                                             |
   | You should have received a copy of the GNU Lesser General Public License    |
   | along with XML_RPC2; if not, write to the Free Software             |
   | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA                    |
   | 02111-1307 USA                                                              |
   +-----------------------------------------------------------------------------+
   | Author: Sérgio Carvalho <sergio.carvalho@portugalmail.com>                  |
   +-----------------------------------------------------------------------------+
}}} */      
/**
 * @package XML_RPC2
 */
/**
 */
/* dependencies {{{ */
require_once 'XML/RPC2/Exception.php';
require_once 'XML/RPC2/Backend/Php/Value.php';
/* }}} */
/**
 * XML_RPC scalar value abstract class. All XML_RPC value classes representing scalar types inherit from XML_RPC2_Value_Scalar
 * 
 * @author Sérgio Carvalho
 * @package XML_RPC2
 */
abstract class XML_RPC2_Backend_Php_Value_Scalar extends XML_RPC2_Backend_Php_Value
{
    /* Fields {{{ */
    /** The native type of this scalar instance as per XML_RPC spec */
    /* setScalarType {{{ */
    /**
     * scalarType property setter
     *
     * @param mixed value The new scalarType
     */
    protected function setScalarType($value) 
    {
        switch ($value) {
            case 'int':
            case 'i4':
            case 'boolean':
            case 'string':
            case 'double':
            case 'dateTime.iso8601':
            case 'base64':
                $this->_scalarType = $value;
                break;
            default:
                throw new XML_RPC2_InvalidTypeException(sprintf('Type \'%s\' is not an XML-RPC scalar type', $value));
        }
    }
    /* }}} */
    /* getScalarType {{{ */
    /**
     * scalarType property getter
     *
     * @return mixed The current scalarType
     */
    public function getScalarType() 
    {
        return $this->_scalarType;
    }
    /* }}} */
    protected $_scalarType = null;
    /** The native value of this scalar instance */
    /* setNativeValue {{{ */
    /**
     * nativeValue property setter
     *
     * @param mixed value The new nativeValue
     */
    protected function setNativeValue($value) 
    {
        $this->_nativeValue = $value;
    }
    /* }}} */
    /* getNativeValue {{{ */
    /**
     * nativeValue property getter
     *
     * @return mixed The current nativeValue
     */
    public function getNativeValue() 
    {
        return $this->_nativeValue;
    }
    /* }}} */
    protected $_nativeValue = null;
    /* }}} */
    /* Constructor {{{ */
    /**
     * Constructor. Will build a new XML_RPC2_Value_Scalar with the given nativeValue
     *
     * @param mixed nativeValue
     */
    public function __construct($scalarType, $nativeValue) 
    {
        $this->setScalarType($scalarType);
        $this->setNativeValue($nativeValue);
    }
    /* }}} */
    /* createFromNative {{{ */
    /**
     * Choose a XML_RPC2_Value subclass appropriate for the 
     * given value and create it.
     * 
     * @param string The native value
     * @param string Optinally, the scalar type to use
     * @throws XML_RPC2_InvalidTypeEncodeException When native value's type is not a native type
     * @return XML_RPC2_Value The newly created value
     */
    public static function createFromNative($nativeValue, $explicitType = null)
    {
        if (is_null($explicitType)) {
            switch (gettype($nativeValue)) {
                case 'boolean':
                case 'integer':
                case 'double':
                case 'string':
                    $explicitType = gettype($nativeValue);
                    break;
                default:
                    throw new XML_RPC2_InvalidTypeEncodeException(sprintf('Impossible to encode scalar value \'%s\' from type \'%s\'. Native type is not a scalar XML_RPC type (boolean, integer, double, string)',
                        (string) $nativeValue,
                        gettype($nativeValue)));
            }
        }
        $explicitType = ucfirst(strtolower($explicitType));
        require_once(sprintf('XML/RPC2/Backend/Php/Value/%s.php', $explicitType));
        $explicitType = sprintf('XML_RPC2_Backend_Php_Value_%s', $explicitType);
        return new $explicitType($nativeValue);
    }
    /* }}} */
    /* encode {{{ */
    /**
     * Encode the instance into XML, for transport
     * 
     * @return string The encoded XML-RPC value,
     */
    public function encode() 
    {
        return '<' . $this->getScalarType() . '>' . $this->getNativeValue() . '</' . $this->getScalarType() . '>';
    }
    /* }}} */
}
?>
