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
require_once 'XML/RPC2/Util/HTTPRequest.php';
require_once 'XML/RPC2/Value.php';
require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Backend/Php/Request.php';
require_once 'XML/RPC2/Backend/Php/Response.php';
/* }}} /*
/**
 * XML_RPC client backend class. This is the default, all-php XML_RPC client backend.
 *
 * This backend does not require the xmlrpc extension to be compiled in. It implements
 * XML_RPC based on the always present DOM and SimpleXML PHP5 extensions.
 * 
 * @author Sérgio Carvalho
 * @package XML_RPC2
 */
class XML_RPC2_Backend_Php_Client extends XML_RPC2_Client
{
    /* constructor {{{ */
    /**
     * Construct a new XML_RPC2_Client PHP Backend.
     *
     * To create a new XML_RPC2_Client, a URI must be provided (e.g. http://xmlrpc.example.com/1.0/).
     * Optionally, a prefix may be set, wich will be prepended to method names, before calling.
     * Prefixes are extremely useful namely when method names contain a period '.' turning them invalid
     * under PHP syntax.
     *
     * @param string URI for the XML-RPC server
     * @param string (optional)  Prefix to prepend on all called functions
     * @param string (optional)  Proxy server URI
     *
     */
    function __construct($uri, $prefix = '', $proxy = null)
    {
        parent::__construct($uri, $prefix, $proxy);
    }
    /* }}} */
    /* remoteCall {{{ */
    /**
     * remoteCall executes the XML-RPC call, and returns the result
     *
     * @param   string      Method name
     * @param   array       Parameters
     */
    public function remoteCall($methodName, $parameters)
    {
        $request = new XML_RPC2_Backend_Php_Request($this->getPrefix() . $methodName);
        $request->setParameters($parameters);
        $request = $request->encode();
        $uri = $this->getUri();
        $httpRequest = new XML_RPC2_Util_HTTPRequest($uri);
        $httpRequest->setPostData($request);
        $httpRequest->sendRequest();
        return XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($httpRequest->getBody()));
    }
    /* }}} */
}
?>
