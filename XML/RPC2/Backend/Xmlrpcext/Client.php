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
require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Util/HTTPRequest.php';
//}}}

/**
 * XML_RPC client backend class. This backend class uses the XMLRPCext extension to execute the call.
 *
 * @category   XML
 * @package    XML_RPC2
 * @author     Sérgio Carvalho <sergio.carvalho@portugalmail.com>  
 * @copyright  2004-2005 Sérgio Carvalho
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link       http://pear.php.net/package/XML_RPC2 
 */
class XML_RPC2_Backend_Xmlrpcext_Client extends XML_RPC2_Client
{
    
    // {{{ constructor
    
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
    
    // }}}
    // {{{ remoteCall()
    
    /**
     * remoteCall executes the XML-RPC call, and returns the result
     *
     * @param   string      Method name
     * @param   array       Parameters
     */
    public function remoteCall($methodName, $parameters)
    {
        $request = xmlrpc_encode_request($this->getPrefix() . $methodName, $parameters);
        $uri = $this->getUri();
        $httpRequest = new XML_RPC2_Util_HTTPRequest($uri);
        $httpRequest->setPostData($request);
        $httpRequest->sendRequest();
        $result = xmlrpc_decode($httpRequest->getBody());
        if ($result === false) {
            throw new XML_RPC2_Exception('Unable to decode response');
        }
        if (xmlrpc_is_fault($result)) {
            throw new XML_RPC2_FaultException($result['faultString'], $result['faultCode']);
        } 
        return $result;
    }
    
    // }}}
    
}

?>
