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
/* }}} /*
/**
 * XML_RPC client class. Use this class to access remote methods.
 * 
 * To use this class, construct it providing the server URL and method prefix. 
 * Then, call remote methods on the new instance as if they were local.
 * 
 * Example:
 * <code>
 *  require_once 'XML_RPC2/Client.php';
 * 
 *  $client = XML_RPC2_Client('http://xmlrpc.example.com/1.0/', 'example.');
 *  $result = $client->hello('Sérgio');
 *  print($result);
 * </code>
 * 
 * The above example will call the example.hello method on the xmlrpc.example.com
 * server, under the /1.0/ URI. 
 * 
 * @package XML_RPC2
 * @author Sérgio Carvalho
 */
abstract class XML_RPC2_Client 
{
    /* uri Field {{{ */
    /** Holds the uri for the XML_RPC server */
    protected $_uri = null;
    /** uri setter */
    protected function setUri($uri) 
    {
        if (!$uriParse = parse_url($uri)) {
            throw new XML_RPC2_InvalidUriException(sprintf('Client URI \'%s\' is not valid', $uri));
        }
        $this->_uri = $uriParse;
        foreach (array_keys($this->_uri) as $key) {
            $this->_uri[$key] = urldecode($this->_uri[$key]);
        }
        $this->_uri['uri'] = $uri;
    }
    /** uri getter */
    protected function getUri()
    {
        return $this->_uri['uri'];
    }
    /* }}} */
    /* proxy Field {{{ */
    /** Holds the proxy server data */
    protected $_proxy = null;
    /** proxy setter */
    protected function setProxy($proxy) 
    {
        if (is_null($proxy)) {
            $this->_proxy = null;
            return;
        }
        if (!$proxyParse = parse_url($proxy)) throw new XML_RPC2_InvalidProxyException(sprintf('Proxy URI \'%s\' is not valid', $proxy));
        $this->_proxy = $proxyParse;
        foreach (array_keys($this->_proxy) as $key) {
            $this->_proxy[$key] = urldecode($this->_proxy[$key]);
        }
        $this->_proxy['uri'] = $proxy;
    }
    /** proxy getter */
    protected function getProxy()
    {
        return $this->_proxy['uri'];
    }
    /* }}} */
    /* prefix Field {{{ */
    /** Holds the prefix to prepend to method names */
    protected $_prefix = null;
    /** prefix setter */
    protected function setPrefix($prefix) 
    {
        $this->_prefix = $prefix;
    }
    /** prefix getter */
    protected function getPrefix()
    {
        return $this->_prefix;
    }
    /* }}} */
    /* debug Field {{{ */
    /** Holds the debug flag */
    protected $_debug = false;
    /** debug setter */
    public function setDebug($debug) 
    {
        $this->_debug = $debug;
    }
    /** debug getter */
    public function getDebug()
    {
        return $this->_debug;
    }
    /* }}} */
    
    /* remoteCall(abstract) {{{ */
    /**
     * remoteCall executes the XML-RPC call, and returns the result
     *
     * @param   string      Method name
     * @param   array       Parameters
     */
    public abstract function remoteCall($methodName, $parameters);
    /* }}} */
    /* constructor {{{ */
    /**
     * Construct a new XML_RPC2_Client.
     *
     * To create a new XML_RPC2_Client, a URI must be provided (e.g. http://xmlrpc.example.com/1.0/). 
     * Optionally, a prefix may be set, wich will be prepended to method names, before calling. 
     * Prefixes are extremely useful namely when method names contain a period '.' turning them invalid
     * under PHP syntax.
     * Optionally, an encoding may be set. If set, it will be indicated in the request XML document, and 
     * the response will be converted from its encoding to the designated encoding.
     * The fifth parameter, optional, allows for debugging to be turned on, by passing a boolean true value. 
     *
     * @param string URI for the XML-RPC server
     * @param string (optional)  Prefix to prepend on all called functions (defaults to '')
     * @param string (optional)  Proxy server URI (defaults to no proxy)
     *
     */
    protected function __construct($uri, $prefix = '', $proxy = null)
    {
        $this->setUri($uri);
        $this->setProxy($proxy);
        $this->setPrefix($prefix);
    }
    /* }}} */
    /* create {{{ */
    /**
     * Factory method to select, create and return a XML_RPC2_Client backend
     *
     * To create a new XML_RPC2_Client, a URI must be provided (e.g. http://xmlrpc.example.com/1.0/). 
     * 
     * Optionally, a prefix may be set, wich will be prepended to method names, before calling. 
     * Prefixes are extremely useful namely when method names contain a period '.' turning them invalid
     * under PHP syntax.
     *
     * You may also set a proxy server, through where the requests will be sent. 
     *
     * @param string URI for the XML-RPC server
     * @param string (optional)  Prefix to prepend on all called functions (defaults to '')
     * @param string (optional)  Proxy server URI (defaults to no proxy)
     *
     */
    public static function create($uri, $prefix = '', $proxy = null)
    {
        $backend = XML_RPC2_Backend::getClientClassname();
        return new $backend($uri, $prefix, $proxy);
    }
    /* }}} */
    /* __call {{{ */
    /**
     * __call Catchall. This method catches remote method calls and provides for remote forwarding.
     *
     * If the parameters are native types, this method will use XML_RPC_Value::createFromNative to 
     * convert it into an XML-RPC type. Whenever a parameter is already an instance of XML_RPC_Value
     * it will be used as provided. It follows that, in situations when XML_RPC_Value::createFromNative
     * proves inacurate -- as when encoding DateTime values -- you should present an instance of 
     * XML_RPC_Value in lieu of the native parameter.
     *
     * @param   string      Method name
     * @param   array       Parameters
     * @return  mixed       The call result, already decoded into native types
     */
    public function __call($methodName, $parameters)
    {
        $args = array($methodName, $parameters);
        return @call_user_func_array(
                              array($this, 'remoteCall'),
                              $args
                             );
    }
    /* }}} */
}
?>
