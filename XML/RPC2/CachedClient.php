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
* @author     Fabien MARTY <fab@php.net>  
* @copyright  2005-2006 Fabien MARTY
* @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version    CVS: $Id$
* @link       http://pear.php.net/package/XML_RPC2
*/

// }}}

// dependencies {{{
require_once('Cache/Lite.php');
// }}}

/**
 * XML_RPC "cached client" class.
 *
 * @category   XML
 * @package    XML_RPC2
 * @author     Fabien MARTY <fab@php.net> 
 * @copyright  2005-2006 Fabien MARTY
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link       http://pear.php.net/package/XML_RPC2 
 */
class XML_RPC2_CachedClient {

    // {{{ properties
    
    /**
     * uri Field (holds the uri for the XML_RPC server)
     *
     * @var array
     */
    private $_uri;
    
    /**
     * Holds the prefix to prepend to method names
     *
     * @var string
     */
    private $_prefix; 

    /**
     * proxy Field (holds the proxy server data)
     *
     * @var array
     */
    private $_proxy;
    
    /** 
     * Holds the debug flag 
     *
     * @var boolean
     */
    private $_debug = false;
    
    /**
     * Cache_Lite options array
     *
     * @var array
     */
    private $_cacheOptions = array();
    
    /**
     * Cached methods array (usefull only if cache is off by default)
     *
     * example1 : array('method1ToCache', 'method2ToCache', ...)
     * example2 (with specific cache lifetime) : 
     * array('method1ToCache' => 3600, 'method2ToCache' => 60, ...)
     * NB : a lifetime value of -1 means "no cache for this method"
     *
     * @var array
     */
    private $_cachedMethods = array();
    
    /**
     * Non-Cached methods array (usefull only if cache is on by default)
     *
     * example : array('method1ToCache', 'method2ToCache', ...)
     *
     * @var array
     */
    private $_notCachedMethods = array();
    
    /**
     * cache by default 
     *
     * @var boolean
     */
    private $_cacheByDefault = true;
    
    /**
     * Cache_Lite object
     *
     * @var object 
     */
    private $_cacheObject = null;
    
    /**
     * XML_RPC2_Client object (if needed, dynamically built)
     *
     * @var object
     */
    private $_clientObject = null;
    
    /**
     * Default cache group for XML_RPC client caching
     *
     * @var string
     */
    private $_defaultCacheGroup = 'xml_rpc2_client';
    
    // }}}
    // {{{ setCacheOptions()
    
    /**
     * Set options for the caching process
     *
     * See Cache_Lite constructor for options
     * Specific options are 'cachedMethods', 'notCachedMethods', 'cacheByDefault', 'defaultCacheGroup'
     * See corresponding properties for more informations
     *
     * @param array $array
     */
    public function setCacheOptions($array) 
    {
        if (isset($array['defaultCacheGroup'])) {
            $this->_defaultCacheGroup = $array['defaultCacheGroup'];
            unset($array['defaultCacheGroup']); // this is a "non standard" option for Cache_Lite
        }
        if (isset($array['cachedMethods'])) {
            $this->_cachedMethods = $array['cachedMethods'];
            unset($array['cachedMethods']); // this is a "non standard" option for Cache_Lite
        }  
        if (isset($array['notCachedMethods'])) {
            $this->_notCachedMethods = $array['notCachedMethods'];
            unset($array['notCachedMethods']); // this is a "non standard" option for Cache_Lite
        } 
        if (isset($array['cacheByDefault'])) {
            $this->_cacheByDefault = $array['cacheByDefault'];
            unset($array['CacheByDefault']); // this is a "non standard" option for Cache_Lite
        }     
        $array['automaticSerialization'] = false; // datas are already serialized in this class
        if (!isset($array['lifetime'])) {
            $array['lifetime'] = 3600; // we need a default lifetime
        }
        $this->_cacheOptions = $array;
        $this->_cacheObject = new Cache_Lite($this->_cacheOptions);
    }
    
    // }}}
    // {{{ setDebug()
    
    /**
     * debug flag setter
     * 
     * @param boolean $debug
     */
    public function setDebug($debug) 
    {
        $this->_debug = $debug;
        if (isset($this->_clientObject)) {
            // If the XML_RPC2_Client object is already available, let's
            // really change the debug status
            $this->_clientObject->setDebug($this->_debug);
        }
    }
    
    // }}}
    // {{{ getDebug()
    
    /** 
     * debug getter 
     * 
     * @return boolean
     */
    public function getDebug()
    {
        return $this->_debug;
    }  
    
    // }}}
    // {{{ constructor
    
    /**
     * Constructor
     *
     * @param string URI for the XML-RPC server
     * @param string (optional)  Prefix to prepend on all called functions (defaults to '')
     * @param string (optional)  Proxy server URI (defaults to no proxy)
     */
    protected function __construct($uri, $prefix = '', $proxy = null) 
    {
        $this->_uri = $uri;
        $this->_prefix = $prefix;
        $this->_proxy = $proxy;    
    }
    
    // }}}
    // {{{ create()
    
    /**
     * "Emulated Factory" method to get the same API than XML_RPC2_Client class
     *
     * Here, simply returns a new instance of XML_RPC2_CachedClient class
     *
     * @param string URI for the XML-RPC server
     * @param string (optional)  Prefix to prepend on all called functions (defaults to '')
     * @param string (optional)  Proxy server URI (defaults to no proxy)
     *
     */
    public static function create($uri, $prefix = '', $proxy = null) 
    {
        return new XML_RPC2_CachedClient($uri, $prefix = '', $proxy = null);
    }
         
    // }}}
    // {{{ __call()
    
    /** 
     * __call Catchall
     *
     * Encapsulate all the class logic :
     * - determine if the cache has to be used (or not) for the called method
     * - see if a cache is available for this call
     * - if no cache available, really do the call and store the result for next time
     *
     * @param   string      Method name
     * @param   array       Parameters
     * @return  mixed       The call result, already decoded into native types
     */
    public function __call($methodName, $parameters)
    {
        $cacheId = md5($methodName . serialize($parameters) . serialize($this->_uri) . serialize($this->_prefix) . serialize($this->_proxy) . serialize($this->_debug)); 
        if (!isset($this->_cacheObject)) {
            $this->_cacheObject = new Cache_Lite($this->_cacheOptions);
        }
        if (in_array($methodName, $this->_notCachedMethods)) {
            // if the called method is listed in _notCachedMethods => no cache
            return $this->_workWithoutCache($methodName, $parameters);
        }
        if (!($this->_cacheByDefault)) {
            if ((!(isset($this->_cachedMethods[$methodName]))) and (!(in_array($methodName, $this->_cachedMethods)))) {
                // if cache is not on by default and if the called method is not described in _cachedMethods array
                // => no cache
                return $this->_workWithoutCache($methodName, $parameters);
            }
        }
        if (isset($this->_cachedMethods[$methodName])) {
            if ($this->_cachedMethods[$methodName] == -1) {
                // if a method is described with a lifetime value of -1 => no cache
                return $this->_workWithoutCache($methodName, $parameters);
            } else {
                // if a method is described with a specific (and <> -1) lifetime
                // => we fix this new lifetime
                $this->_cacheObject->setLifetime($this->_cachedMethods[$methodName]);
            }
        } else {
            // there is no specific lifetime, let's use the default one
            $this->_cacheObject->setLifetime($this->_cacheOptions['lifetime']);
        }
        $data = $this->_cacheObject->get($cacheId, $this->_defaultCacheGroup);
        if (is_string($data)) {
            // cache is hit !
            return unserialize($data);
        }
        // the cache is not hit, let's call the "real" XML_RPC client
        $result = $this->_workWithoutCache($methodName, $parameters);
        $this->_cacheObject->save(serialize($result)); // save in cache for next time...
        return $result;
    }
    
    // }}}
    // {{{ _workWithoutCache()
    
    /**
     * Do the real call if no cache available
     *
     * @param   string      Method name
     * @param   array       Parameters
     * @return  mixed       The call result, already decoded into native types
     */
    private function _workWithoutCache($methodName, $parameters) 
    {
        if (!(isset($this->_clientObject))) {
            // If the XML_RPC2_Client object is not available, let's build it
            require_once('XML/RPC2/Client.php');
            $this->_clientObject = XML_RPC2_Client::create($this->_uri, $this->_prefix, $this->_proxy);
            $this->_clientObject->setDebug($this->_debug);
        }               
        // the real function call...
        return call_user_func_array(array($this->_clientObject, $methodName), $parameters);
    }
    
    // }}}
    // {{{ _makeCacheId()
    
    /** 
     * make a cache id depending on method called (and corresponding parameters) but depending on "environnement" setting too
     *
     * @param string $methodName called method
     * @param array $parameters parameters of the called method
     * @return string cache id
     */
    private function _makeCacheId($methodName, $parameters) 
    {
        return md5($methodName . serialize($parameters) . serialize($this->_uri) . serialize($this->_prefix) . serialize($this->_proxy) . serialize($this->_debug)); 
    }
    
    // }}}
    // {{{ dropCacheFile()
    
    /** 
     * Drop the cache file corresponding to the given method call
     *
     * @param string $methodName called method
     * @param array $parameters parameters of the called method
     */
    public function dropCacheFile($methodName, $parameters) 
    {
        $id = $this->_makeCacheId($methodName, $parameters);
        $this->_clientObject->remove($id, $this->_defaultCacheGroup);
    }
    
    // }}}
    // {{{ clean()
    
    /** 
     * Clean all the cache
     */
    public function clean() 
    {
        $this->_cacheObject->clean($this->_defaultCacheGroup, 'ingroup');
    }

}

?>
