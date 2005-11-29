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
 * @author Sergio Carvalho <sergio.carvalho@portugalmail.com>
 */
/**
 */
/* dependencies {{{ */
require_once 'XML/RPC2/Exception.php';
require_once 'XML/RPC2/Backend/Php/Value.php';
/* }}} /*
/**
 * XML_RPC request backend class. This class represents an XML_RPC request, exposing the methods 
 * needed to encode/decode a request.
 *
 * @author Sergio Carvalho <sergio.carvalho@portugalmail.com>
 * @package XML_RPC2
 */
class XML_RPC2_Backend_Php_Request
{
    /* Fields {{{ */
    /** Name of requested method */
    protected $_methodName = '';
    /** Request parameters */
    protected $_parameters = null;
    /* }}} */
    /* setMethodName {{{ */
    /**
     * methodName property setter
     *
     * @param mixed value The new methodName
     */
    protected function setMethodName($value) 
    {
        $this->_methodName = $value;
    }
    /* }}} */
    /* getMethodName {{{ */
    /**
     * methodName property getter
     *
     * @return mixed The current methodName
     */
    public function getMethodName() 
    {
        return $this->_methodName;
    }
    /* }}} */
    /* setParameters {{{ */
    /**
     * parameters property setter
     *
     * @param mixed value The new parameters
     */
    public function setParameters($value) 
    {
        $this->_parameters = $value;
    }
    /* }}} */
    /* addParameter {{{ */
    /**
     * parameters property appender
     *
     * @param mixed value The new parameter
     */
    public function addParameter($value) 
    {
        $this->_parameters[] = $value;
    }
    /* }}} */
    /* getParameters {{{ */
    /**
     * parameters property getter
     *
     * @return mixed The current parameters
     */
    public function getParameters() 
    {
        return $this->_parameters;
    }
    /* }}} */
    /* constructor {{{ */
    /**
     * Create a new xml-rpc request with the provided methodname
     *
     * @param string Name of method targeted by this xml-rpc request
     */
    function __construct($methodName)
    {
        $this->setMethodName($methodName);
        $this->setParameters(array());
    }
    /* }}} */
    /* encode {{{ */
    /**
     * Encode the request for transmission.
     *
     * @return string XML-encoded request (a full XML document)
     */
    public function encode()
    {
        $methodName = $this->getMethodName();
        $parameters = $this->getParameters();

        $result = '<?xml version="1.0"?>';
        $result .= '<methodCall>';
        $result .= "<methodName>${methodName}</methodName>";
        $result .= '<params>';
        foreach($parameters as $parameter) {
            $result .= '<param><value>';
            $result .= ($parameter instanceof XML_RPC2_Backend_Php_Value) ? $parameter->encode() : XML_RPC2_Backend_Php_Value::createFromNative($parameter)->encode();
            $result .= '</value></param>';
        }
        $result .= '</params>';
        $result .= '</methodCall>';
        return $result;
    }
    /* }}} */
    /* createFromDecode {{{ */
    /**
     * Decode a request from XML and construct a request object with the createFromDecoded values
     *
     * @param SimpleXMLElement The encoded XML-RPC request.
     * @return XML_RPC2_Backend_Php_Request The xml-rpc request, represented as an object instance
     */
    public static function createFromDecode($simpleXML) 
    {
        $methodName = (string) $simpleXML->methodName;
        $params = array();
        foreach ($simpleXML->params->param as $param) {
            foreach ($param->value as $value) {
                $params[] = XML_RPC2_Backend_Php_Value::createFromDecode($value)->getNativeValue();
            }
        }
        $result = new XML_RPC2_Backend_Php_Request($methodName);
        $result->setParameters($params);
        return $result;
    }
    /* }}} */
}
?>
