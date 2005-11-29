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
/* }}} */
/**
 * A CallHandler is responsible for actually calling the server-exported methods from the exported class.
 *
 * This class is abstract and not meant to be used directly by XML_RPC2 users.
 *
 * XML_RPC2_Server_CallHandler provides the basic code for a call handler class. An XML_RPC2 Call Handler 
 * operates in tandem with an XML_RPC2 server to export a classe's methods. While XML_RPC2 Server 
 * is responsible for request decoding and response encoding, the Call Handler is responsible for 
 * delegating the actual method call to the intended target. 
 * 
 * Different server behaviours can be obtained by plugging different Call Handlers into the XML_RPC2_Server. 
 * Namely, there are two call handlers available:
 *  - XML_RPC2_Server_Callhandler_Class: Which exports a classe's public static methods
 *  - XML_RPC2_Server_Callhandler_Instance: Which exports an object's pubilc methods
 *
 * @see XML_RPC2_Server_Callhandler_Class
 * @see XML_RPC2_Server_Callhandler_Instance
 * 
 * @package XML_RPC2
 * @author Sérgio Carvalho <sergio.carvalho@portugalmail.com>
 */ 
abstract class XML_RPC2_Server_CallHandler
{
    /* methods Field {{{ */
    /** Holds server methods */
    protected $_methods = array();
    /** 
     * methods setter 
     *
     * @param array Array of XML_RPC2_Server_Method instances
     */
    protected function setMethods($methods) 
    {
        $this->_methods = $methods;
    }
    /** 
     * methods getter 
     *
     * @return array Array of XML_RPC2_Server_Method instances
     */
    public function getMethods()
    {
        return $this->_methods;
    }
    /** 
     * method appender 
     *
     * @param XML_RPC2_Server_Method Method to append to _methods
     */
    protected function addMethod(XML_RPC2_Server_Method $method) 
    {
        $this->_methods[$method->getName()] = $method;
    }
    /** 
     * method getter
     *
     * @param string Name of method to return
     * @param XML_RPC2_Server_Method Method named $name
     */
    public function getMethod($name)
    {
        return $this->_methods[$name];
    }
    /* }}} */
    /* system_listMethods {{{ */
    /**
     * Introspect server, returning method list.
     *
     * This method may be used to
     * enumerate the methods implemented by the XML-RPC server. The
     * system.listMethods method requires no parameters. It returns an array
     * of strings, each of which is the name of a method implemented by the
     * server.
     *
     * @returns array method names
     *
     */
    protected static final function system_listMethods()
    {
        return array_keys($this->getMethods());
    }
    /* }}} */
    /* system_methodHelp {{{ */
    /**
     * Introspect server, returning method help text.
     *
     * If the method does
     * not exist, returns false. If the method exists, but help text is
     * not defined, returns an empty string.
     *
     * @param   string Method name whose help to return
     * @returns string Method help
     *
     */
    protected static final function system_methodHelp($methodName)
    {
        $methods = $this->getMethods();
        if (!array_key_exists($methodName, $methods)) {
            return false;
        }
        return $methods[$methodName]->methodHelp();
    }
    /* }}} */
    /* system_methodSignature {{{ */
    /**
     * Introspect server, returning a method signature.
     *
     * This method takes one
     * parameter, the name of a method implemented by the XML-RPC server.
     * It returns an array of possible signatures for this method. A signature
     * is an array of types. The first of these types is the return type of
     * the method, the rest are parameters.
     * Even if the XML-RPC spec supports function overloading, PHP does not.
     * On this server, this function will either return a one-element array or
     * false (if the method does not exist).
     *
     * @param   string Method name whose signature to return
     * @returns string Method help
     *
     * @xmlrpc.prefix system.
     *
     */
    protected static final function system_methodSignature($methodName)
    {
        $methods = $this->getMethods();
        if (!array_key_exists($methodName, $methods)) {
            return false;
        }
        return array($methods[$methodName]->methodSignature());
    }
    /* }}} */
}
?>
