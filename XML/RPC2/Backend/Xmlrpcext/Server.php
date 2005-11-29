<?php
/* LICENSE AGREEMENT. If folded, press za here to unfold and read license {{{ 
   vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4 foldmethod=marker: 
   +-----------------------------------------------------------------------------+
   | Copyright (c) 2004 Sérgio Gonçalves Carvalho                                |
   +-----------------------------------------------------------------------------+
   | This file is part of XML_RPC.                                               |
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
require_once 'XML/RPC2/Backend/Php/Request.php';
require_once 'XML/RPC2/Backend/Php/Response.php';
require_once 'XML/RPC2/Exception.php';
/* }}} */
/**
 * XML_RPC server class XMLRPCext extension-based backend
 * 
 * The XML_RPC2_Server does the work of decoding and encoding xml-rpc request and response. The actual
 * method execution is delegated to the call handler instance.
 *
 * The XML_RPC server is responsible for decoding the request and calling the appropriate method in the
 * call handler class. It then encodes the result into an XML-RPC response and returns it to the client.
 * 
 * @author Sérgio Carvalho
 * @package XML_RPC2
 */
class XML_RPC2_Backend_Xmlrpcext_Server extends XML_RPC2_Server
{
    /* _xmlrpcextServer Field {{{ */
    private $_xmlrpcextServer;
    /** _xmlrpcextServer getter 
     * 
     * @return resource xmlrpcext server
     */
    private function getXmlrpcextServer() 
    {
        return $this->_xmlrpcextServer;
    }
    /** _xmlrpcextServer setter
     * 
     * @param resource xmlrpcext server
     */
    private function setXmlrpcextServer($server) 
    {
        $this->_xmlrpcextServer = $server;
    }
    /* }}} */
    /* Constructor {{{ */
    /**
     * Create a new XML-RPC Server. 
     *
     * The constructor receives only one parameter: the Call Handler. The call handler executes the actual
     * method call. XML_RPC2 server acts as a protocol decoder/encoder between the call handler and the client
     */
    function __construct($callHandler)
    {
        parent::__construct($callHandler);
        $this->setXmlrpcextServer(xmlrpc_server_create());
        foreach ($callHandler->getMethods() as $method) {
            if (xmlrpc_server_register_method($this->getXmlrpcextServer(), 
                                              $method->getName(), 
                                              array($this, 'epiFunctionHandlerAdapter')) !== true) {
                throw new XML_RPC2_Exception('Unable to setup XMLRPCext server. xmlrpc_server_register_method returned non-true.');
            }
        }
    }
    /* }}} */
    /* epiFunctionHandlerAdapter {{{ */
    /**
     * This is an adapter between XML_RPC2_CallHandler::__call and xmlrpc_server_register_method callback interface
     * 
     * @param string Method name
     * @param array Parameters
     * @param array Application data (ignored)
     */
    protected function epiFunctionHandlerAdapter($method_name, $params, $app_data) {
        return @call_user_func_array(array($this->getCallHandler(), $method_name), $params);
    }
    /* }}} */
    /* errorToException {{{ */
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
    /* }}} */
    /* handleCall {{{ */
    /**
     * Respond to the XML-RPC request.
     *
     * handleCall reads the XML-RPC request from the raw HTTP body and decodes it. It then calls the 
     * corresponding method in the call handler class, returning the encoded result to the client.
     */
    public function handleCall()
    {
        try {
            $oldErrorHandler = set_error_handler(array('XML_RPC2_Backend_Xmlrpcext_Server', 'errorToException'));
            $response = @xmlrpc_server_call_method($this->getXmlrpcextServer(), 
                                                  $GLOBALS['HTTP_RAW_POST_DATA'],
                                                  null,
                                                  array('output_type' => 'xml'));
            header('Content-type: text/xml');
            header('Content-length: '.strlen($response));
            print $response;
            if ($oldErrorHandler !== FALSE) set_error_handler($oldErrorHandler);
        } catch (XML_RPC2_FaultException $e) {
            print(XML_RPC2_Backend_Php_Response::encodeFault($e->getFaultCode(), $e->getMessage()));
        } catch (Exception $e) {
            print(XML_RPC2_Backend_Php_Response::encodeFault(1, 'Unhandled ' . get_class($e) . ' exception:' . $e->getMessage()));
        }
    }
    /* }}} */
}
?>
