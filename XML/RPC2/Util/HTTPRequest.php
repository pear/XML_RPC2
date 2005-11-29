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
/* }}} /*
/**
 * XML_RPC utility HTTP request class. This class mimics a subset of PEAR's HTTP_Request
 * and is to be refactored out of the package once HTTP_Request releases an E_STRICT version.
 * 
 * @package XML_RPC2
 * @author Sérgio Carvalho
 */
class XML_RPC2_Util_HTTPRequest
{
    /* body Field {{{ */
    private $_body;
    /**
     * body field getter
     *
     * @return string body value
     */
    public function getBody() 
    {
        return $this->_body;
    }
    /**
     * body field setter
     *
     * @param string body value
     */
    public function setBody($value) 
    {
        $this->_body = $value;
    }
    /* }}} */
    /* postData Field {{{ */
    private $_postData;
    /**
     * postData field getter
     *
     * @return string postData value
     */
    public function getPostData() 
    {
        return $this->_postData;
    }
    /**
     * postData field setter
     *
     * @param string postData value
     */
    public function setPostData($value) 
    {
        $this->_postData = $value;
    }
    /* }}} */
    /* method Field {{{ */
    private $_method = 'POST';
    /**
     * method field getter
     *
     * @return array method value
     */
    public function getMethod() 
    {
        return $this->_method;
    }
    /**
     * method field setter (ignored!)
     * 
     * This setter is ignored, and method set to POST always. It is here for API compatibility reasons alone.
     *
     * @param array method value
     */
    public function setMethod($value) 
    {
        $this->_method = 'POST';
    }
    /* }}} */
    /* params Field {{{ */
    private $_params;
    /**
     * params field getter
     *
     * @return array params value
     */
    public function getParams() 
    {
        return $this->_params;
    }
    /**
     * params field setter
     *
     * @param array params value
     */
    public function setParams($value) 
    {
        $this->_params = $value;
    }
    /* }}} */
    /* proxy Field {{{ */
    private $_proxy = null;
    /**
     * proxy field getter
     *
     * @return string proxy value
     */
    public function getProxy() 
    {
        return $this->_proxy;
    }
    /**
     * proxy field setter
     *
     * @param string proxy value
     */
    public function setProxy($value) 
    {
        $this->_proxy = $value;
    }
    /* }}} */
    /* proxyAuth Field {{{ */
    private $_proxyAuth = null;
    /**
     * proxyAuth field getter
     *
     * @return string proxyAuth value
     */
    public function getProxyAuth() 
    {
        return $this->_proxyAuth;
    }
    /**
     * proxyAuth field setter
     *
     * @param string proxyAuth value
     */
    public function setProxyAuth($value) 
    {
        $this->_proxyAuth = $value;
    }
    /* }}} */
    /* uri Field {{{ */
    private $_uri;
    /**
     * uri field getter
     *
     * @return string uri value
     */
    public function getURI() 
    {
        return $this->_uri;
    }
    /**
     * uri field setter
     *
     * @param string uri value
     */
    public function setURI($value) 
    {
        $this->_uri = $value;
    }
    /* }}} */
    /* constructor {{{ */
    /**
    * Constructor
    *
    * Sets up the object
    * @param    string  The uri to fetch/access
    * @param    array   Associative array of parameters which can have the following keys:
    * <ul>
    *   <li>user           - Basic Auth username (string)</li>
    *   <li>pass           - Basic Auth password (string)</li>
    *   <li>proxy_host     - Proxy server host (string)</li>
    *   <li>proxy_port     - Proxy server port (integer)</li>
    *   <li>proxy_user     - Proxy auth username (string)</li>
    *   <li>proxy_pass     - Proxy auth password (string)</li>
    * </ul>
    * @access public
    */
    public function __construct($uri = '', $params = array())
    {
        $this->setUri($uri);
        if (array_key_exists('user', $params) && array_key_exists('pass', $params)) {
            if (!preg_match('/(https?:\/\/)(.*)/', $uri, $matches)) throw new XML_RPC2_Exception('Unable to parse URI');
            $uri = $matches[1] . uriencode($params['user']) . ':' . uriencode($params['pass']) . '@' . $matches[2];
        }
        if (array_key_exists('proxy_host', $params)) {
            if (!array_key_exists('proxy_port', $params)) {
                $params['proxy_port'] = 3128;
            }
            $this->setProxy("http://{$params['proxy_host']}:{$params['proxy_port']}");
        }
        if (array_key_exists('proxy_user', $params) && array_key_exists('proxy_pass', $params)) {
            $this->setProxyAuth("{$params['proxy_user']}:{$params['proxy_pass']}");
        }
    }
    /* }}} */
    /* sendRequest {{{ */
    /**
    * Sends the request
    *
    * @access public
    * @return mixed  PEAR error on error, true otherwise
    */
    public function sendRequest()
    {
        if (!function_exists('curl_init') &&
            !( // TODO Use PEAR::loadExtension once PEAR passes PHP5 unit tests (E_STRICT compliance, namely)
              @dl('php_curl' . PHP_SHLIB_SUFFIX)    || @dl('curl' . PHP_SHLIB_SUFFIX)
             )) {
            throw new XML_RPC2_CurlException('cURI extension is not present and load failed');
        }
        if ($ch = curl_init()) {
            if (
                (is_null($this->getProxy())     || curl_setopt($ch, CURLOPT_PROXY, $this->getProxy())) &&
                (is_null($this->getProxyAuth()) || curl_setopt($ch, CURLOPT_PROXYAUTH, $this->getProxyAuth())) &&
                curl_setopt($ch, CURLOPT_URL, $this->getUri()) &&
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE) &&
                curl_setopt($ch, CURLOPT_POST, 1) &&
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml')) &&
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getPostData())
            ) {
                $result = curl_exec($ch);
                if (($errno = curl_errno($ch)) != 0) {
                    throw new XML_RPC2_CurlException("Curl returned non-null errno $errno:" . curl_error($ch));
                }
                $info = curl_getinfo($ch);
                if ($info['http_code'] != 200) {
                    throw new XML_RPC2_ReceivedInvalidStatusCodeException('Curl returned non 200 HTTP code: ' . $info['http_code'] . '. Response body:' . $result);
                }
            } else {
                throw new XML_RPC2_CurlException('Unable to setup curl');
            }
        } else {
            throw new XML_RPC2_CurlException('Unable to init curl');
        }
        $this->setBody($result);
        
        return true;
    }
    /* }}} */
}
?>
