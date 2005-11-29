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
require_once 'XML/RPC2/Backend/Php/Value/Struct.php';
/* }}} /*
/**
 * XML-RPC response backend class. 
 *
 * This class represents an XML_RPC request, exposing the methods 
 * needed to encode/decode an xml-rpc response.
 *
 * @author Sérgio Carvalho
 * @package XML_RPC2
 */
class XML_RPC2_Backend_Php_Response
{
    /* encode {{{ */
    /**
     * Encode a normal XML-RPC response, containing the provided value
     *
     * You may supply a php-native value, or an XML_RPC2_Backend_Php_Value instance, to be returned. Usually providing a native value
     * is more convenient. However, for some types, XML_RPC2_Backend_Php_Value::createFromNative can't properly choose the xml-rpc 
     * type. In these cases, constructing an XML_RPC2_Backend_Php_Value and using it as param here is the only way to return the desired 
     * type.
     *
     * @see http://www.xmlrpc.com/spec
     * @see XML_RPC2_Backend_Php_Value::createFromNative
     * @param mixed The result value which the response will envelop
     * @return string The XML payload
     */
    public static function encode($param) 
    {
        if (!$param instanceof XML_RPC2_Backend_Php_Value) {
            $param = XML_RPC2_Backend_Php_Value::createFromNative($param);
        }
        ob_start();
        print('<?xml version="1.0"?>');
?>
<methodResponse><params><param><value><?= $param->encode() ?></value></param></params></methodResponse><?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
    /* }}} */
    /* encodeFault {{{ */
    /**
     * Encode a fault XML-RPC response, containing the provided code and message
     *
     * @see http://www.xmlrpc.com/spec
     * @param int    Response code
     * @param string Response message
     * @return string The XML payload
     */
    public static function encodeFault($code, $message)
    {
        $value = new XML_RPC2_Backend_Php_Value_Struct(array('faultCode' => (int) $code, 'faultString' => (string) $message));
        ob_start();
        print('<?xml version="1.0"?>');
?>
<methodResponse><fault><value><?= $value->encode() ?></value></fault></methodResponse><?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
    /* }}} */
    /* decode {{{ */
    /**
     * Parse a response and either return the native PHP result.
     *
     * This method receives an XML-RPC response document, in SimpleXML format, decodes it and returns the payload value.
     *
     * @param  SimpleXmlElement The Transport XML
     * @return mixed The response payload
     *
     * @see http://www.xmlrpc.com/spec
     * @throws XML_RPC2_FaultException Signals the decoded response was an XML-RPC fault
     * @throws XML_RPC2_DecodeException Signals an ill formed payload response section
     */
    public static function decode(SimpleXMLElement $xml) 
    {
        $faultNode = $xml->xpath('/methodResponse/fault');
        if (count($faultNode) == 1) {
            throw XML_RPC2_FaultException::createFromDecode($faultNode[0]);
        }
        $paramValueNode = $xml->xpath('/methodResponse/params/param/value');
        if (count($paramValueNode) == 1) {
            return XML_RPC2_Backend_Php_Value::createFromDecode($paramValueNode[0])->getNativeValue();
        }
        throw new XML_RPC2_DecodeException('Unable to decode xml-rpc response. No fault nor params/param elements found');
    }
    /* }}} */
}
?>
