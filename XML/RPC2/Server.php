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
require_once 'XML/RPC2/Backend.php';
/* }}} */
/**
 * XML_RPC2_Server is the frontend class for exposing PHP functions via XML-RPC. 
 *
 * Exporting a programatic interface via XML-RPC using XML_RPC2 is exceedingly easy:
 *
 * The first step is to assemble all methods you wish to export into a class. You may either
 * create a (abstract) class with exportable methods as static, or use an existing instance
 * of an object.
 *
 * You'll then need to document the methods using PHPDocumentor tags. XML_RPC2 will use the
 * documentation for server introspection. You'll get something like this:
 *
 * <code>
 * class ExampleServer {
 *     /**
 *      * hello says hello
 *      *
 *      * @param string  Name
 *      * @return string Greetings
 *      {@*}
 *     public static function hello($name) 
    {
 *         return "Hello $name";
 *     }
 * }
 * </code>
 *
 * Now, instantiate the server, using the Factory method to select a backend and a call handler for you:
 * <code>
 * require_once 'XML/RPC2/Server.php';
 * $server = XML_RPC2_Server::create('ExampleServer');
 * $server->handleCall();
 * </code>
 *
 * This will create a server exporting all of the 'ExampleServer' class' methods. If you wish to export
 * instance methods as well, pass an object instance to the factory instead:
 * <code>
 * require_once 'XML/RPC2/Server.php';
 * $server = XML_RPC2_Server::create(new ExampleServer());
 * $server->handleCall();
 * </code>
 *
 */
abstract class XML_RPC2_Server 
{
    /* callHandler Field {{{ */
    /** The call handler is responsible for executing the server exported methods */
    protected $_callHandler = null;
    /** 
     * callHandler setter 
     * 
     * @param object Object that will receive calls for remote methods
     */
    protected function setCallHandler($callHandler) 
    {
        $this->_callHandler = $callHandler;
    }
    /** 
     * callHandler getter 
     * 
     * @return object Object that will receive calls for remote methods
     */
    protected function getCallHandler()
    {
        return $this->_callHandler;
    }
    /* }}} */
    /* aliases Field {{{ */
    /** Holds method aliases */
    protected $_aliases = array();
    /** aliases setter */
    protected function setAliases($aliases) 
    {
        $this->_aliases = $aliases;
    }
    /** aliases appender */
    protected function addAlias($alias) 
    {
        $this->_aliases[] = $alias;
    }
    /** aliases getter */
    protected function getAliases()
    {
        return $this->_aliases;
    }
    /* }}} */
    
    /* Constructor {{{ */
    /**
     * Create a new XML-RPC Server. 
     *
     * @param object Call handler. The call handler will receive a method call for each remote call received. 
     */
    protected function __construct($callHandler)
    {
        $this->_callHandler = $callHandler;
    }
    /* }}} */
    /* create {{{ */
    /**
     * Factory method to select a backend and return a new XML_RPC2_Server based on the backend
     *
     * @param mixed Call target. Either a class name or an object instance. 
     * @param string Method prefix. This prefix will be prepended to exported method names. (Defaults to '')
     * @param object Call handler object. Defaults to selecting a CallHandler suited to the received call target
     *
     * @return XML_RPC2_Server A server class instance
     */
    public static function create($callTarget, $prefix = '', $callHandler = null)
    {
        $backend = XML_RPC2_Backend::getServerClassname();

        // Find callHandler class
        if (is_null($callHandler)) {
            if (is_object($callTarget)) { // Delegate calls to instance methods
                require_once 'XML/RPC2/Server/CallHandler/Instance.php';
                $callHandler = new XML_RPC2_Server_CallHandler_Instance($callTarget, $prefix);
            } else { // Delegate calls to static class methods
                require_once 'XML/RPC2/Server/CallHandler/Class.php';
                $callHandler = new XML_RPC2_Server_CallHandler_Class($callTarget, $prefix);
            }
        }
        return new $backend($callHandler);
    }
    /* }}} */
    /* handleCall(abstract) {{{ */
    /**
     * Receive the XML-RPC request, decode the HTTP payload, delegate execution to the call handler, and output the encoded call handler response.
     *
     */
    public abstract function handleCall();
    /* }}} */
}
?>
