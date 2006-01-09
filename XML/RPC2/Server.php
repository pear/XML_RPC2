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
    
    /**
     * prefix field
     *
     * @var string
     */
    protected $prefix = '';
    
    /**
     * encoding field
     *
     * TODO : work on encoding for this backend
     *
     * @var string
     */
    protected $encoding = 'iso-8859-1';
    
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
     * @param array associative array of options
     */
    protected function __construct($callHandler, $options = array())
    {
        $this->callHandler = $callHandler;
        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }
        if (isset($options['encoding'])) {
            // TODO : control & exception
            $this->encoding = $options['encoding'];
        }
    }
    
    // }}}
    // {{{ create()
    
    /**
     * Factory method to select a backend and return a new XML_RPC2_Server based on the backend
     *
     * @param mixed $callTarget either a class name or an object instance. 
     * @param array associative array of options
     * @return object a server class instance
     */
    public static function create($callTarget, $options = array())
    {        
        if (isset($options['backend'])) {
            XML_RPC2_Backend::setBackend($options['backend']);
        }
        if (isset($options['prefix'])) {
            $prefix = $options['prefix'];
        } else {
            $prefix = '';
        }
        $backend = XML_RPC2_Backend::getServerClassname();
        // Find callHandler class
        if (!isset($options['callHandler'])) {
            if (is_object($callTarget)) { // Delegate calls to instance methods
                require_once 'XML/RPC2/Server/CallHandler/Instance.php';
                $callHandler = new XML_RPC2_Server_CallHandler_Instance($callTarget, $prefix);
            } else { // Delegate calls to static class methods
                require_once 'XML/RPC2/Server/CallHandler/Class.php';
                $callHandler = new XML_RPC2_Server_CallHandler_Class($callTarget, $prefix);
            }
        } else {
            $callHandler = $options['callHandler'];
        }
        return new $backend($callHandler, $options);
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
    // {{{ autoDocument()
    
    
    
    public function autoDocument()
    {
        print "<html><head><title>Available XMLRPC methods for this server</title></head>";
        print "<h1>Available XMLRPC methods for this server</h1>";
        print "<a name=\"index\"><h2>Index</h2></a>";
        print "<ul>";
        foreach ($this->callHandler->getMethods() as $method) {
            print "<li>";
            $name = $method->getName();
            $id = md5($name);
            $signature = $method->getHTMLSignature();
            print "<a href=\"#$id\">$signature</a>";
            print "</li>";
        }
        print "</ul>";
        print "</table>";
        print "<h2>Details</h2>";
        foreach ($this->callHandler->getMethods() as $method) {
            $name = $method->getName();
            $signature = $method->getHTMLSignature();
            $id = md5($name);
            $help = nl2br(htmlentities($method->help));
            print "<a name=\"$id\"><h3>$signature</h3></a>";
            print "(return to <a href=\"#index\">index</a>)";
            print "<p>Description :</p>";
            print "<ul>";
            print "<li>$help</li>";
            print "</ul>";
            if (count($method->parameters)>0) {
                print "<p>Parameters : </p>";
                print "<ul>";
                while (list($name, $parameter) = each($method->parameters)) {
                    $type = $parameter['type'];
                    $doc = htmlentities($parameter['doc']);
                    print "<li>($type) <b>$name</b> : $doc</li>";
                }
                reset($method->parameters);
                print "</ul>";
            }
        }
    }

}

?>
