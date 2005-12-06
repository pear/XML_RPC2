<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// LICENSE AGREEMENT. If folded, press za here to unfold and read license {{{ 

/**
* +-----------------------------------------------------------------------------+
* | Copyright (c) 2004 Sérgio Gonçalves Carvalho                                |
* +-----------------------------------------------------------------------------+
* | This file is part of XML_RPC2.                                              |
* |                                                                             |
* | XML_RPC2 is free software; you can redistribute it and/or modify            |
* | it under the terms of the GNU Lesser General Public License as published by |
* | the Free Software Foundation; either version 2.1 of the License, or         |
* | (at your option) any later version.                                         |
* |                                                                             |
* | XML_RPC2 is distributed in the hope that it will be useful,                 |
* | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
* | GNU Lesser General Public License for more details.                         |
* |                                                                             |
* | You should have received a copy of the GNU Lesser General Public License    |
* | along with XML_RPC2; if not, write to the Free Software                     |
* | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA                    |
* | 02111-1307 USA                                                              |
* +-----------------------------------------------------------------------------+
* | Author: Sérgio Carvalho <sergio.carvalho@portugalmail.com>                  |
* +-----------------------------------------------------------------------------+
*
* @category   XML
* @package    XML_RPC2
* @author     Sérgio Carvalho <sergio.carvalho@portugalmail.com>  
* @copyright  2004-2005 Sérgio Carvalho
* @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version    CVS: $Id$
* @link       http://pear.php.net/package/XML_RPC2
*/

// }}}

// dependencies {{{
require_once 'XML/RPC2/Exception.php';
require_once 'XML/RPC2/Backend.php';
// }}}


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
 * @category   XML
 * @package    XML_RPC2
 * @author     Sérgio Carvalho <sergio.carvalho@portugalmail.com>  
 * @copyright  2004-2005 Sérgio Carvalho
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/XML_RPC2
 */
abstract class XML_RPC2_Server 
{

    // {{{ properties
    
    /**
     * callHandler field
     *
     * The call handler is responsible for executing the server exported methods
     *
     * @var mixed
     */
    protected $callHandler = null;
    
    /**
     * aliases field
     * 
     * Holds method aliases
     *
     * @var mixed
     */
    protected $aliases = null;
    
    // }}}
    // {{{ setCallHandler()
    
    /** 
     * callHandler setter 
     * 
     * @param object $callHandler object that will receive calls for remote methods
     */
    protected function setCallHandler($callHandler) 
    {
        $this->callHandler = $callHandler;
    }
    
    // }}}
    // {{{ getCallHandler()
    
    /** 
     * callHandler getter 
     * 
     * @return object Object that will receive calls for remote methods
     */
    protected function getCallHandler()
    {
        return $this->callHandler;
    }
    
    // }}}
    // {{{ setAliases()

    /**
     * aliases setter
     * 
     * @param mixed $aliases
     */
    protected function setAliases($aliases) 
    {
        $this->aliases = $aliases;
    }
    
    // }}}
    // {{{ addAlias()
    
    /**
     * aliases appender
     * 
     * @param mixed $alias
     */
    protected function addAlias($alias) 
    {
        $this->aliases[] = $alias;
    }
    
    // }}}
    // {{{ getAliases()
    
    /**
     * aliases getter
     * 
     * @return mixed aliases
     */
    protected function getAliases()
    {
        return $this->aliases;
    }
    
    // }}}
    // {{{ constructor
    
    /**
     * Create a new XML-RPC Server. 
     *
     * @param object $callHandler the call handler will receive a method call for each remote call received. 
     */
    protected function __construct($callHandler)
    {
        $this->callHandler = $callHandler;
    }
    
    // }}}
    // {{{ create()
    
    /**
     * Factory method to select a backend and return a new XML_RPC2_Server based on the backend
     *
     * @param mixed $callTarget either a class name or an object instance. 
     * @param string $prefix this method prefix will be prepended to exported method names. (Defaults to '')
     * @param object $callHandler defaults to selecting a CallHandler suited to the received call target
     * @return object a server class instance
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
    
    // }}}
    // {{{ handleCall()
    
    /**
     * Receive the XML-RPC request, decode the HTTP payload, delegate execution to the call handler, and output the encoded call handler response.
     *
     */
    public abstract function handleCall();
    
    // }}}
    // {{{ errorToException()
    
    /**
     * Transform an error into an exception
     *
     * @param int $errno error number
     * @param string $errstr error string
     * @param string $errfile error file
     * @param int $errline error line
     */
    public static function errorToException($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_WARNING:
            case E_NOTICE:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
                // Silence warnings
                // TODO Logging should occur here
                break;
            default:
                throw new Exception('Classic error reported "' . $errstr . '" on ' . $errfile . ':' . $errline);
        }
    }
    
    // }}}

}

?>
