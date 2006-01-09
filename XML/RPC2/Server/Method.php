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
// }}}

/**
 * Class representing an XML-RPC exported method. 
 *
 * This class is used internally by XML_RPC2_Server. External users of the 
 * package should not need to ever instantiate XML_RPC2_Server_Method
 *
 * @category   XML
 * @package    XML_RPC2
 * @author     Sérgio Carvalho <sergio.carvalho@portugalmail.com>  
 * @copyright  2004-2005 Sérgio Carvalho
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link       http://pear.php.net/package/XML_RPC2
 */
class XML_RPC2_Server_Method
{
    // {{{ properties
    
    /** 
     * Method signature parameters 
     *
     * @var array
     */
    public $parameters;
    
    /**
     * Method signature return type 
     *
     * @var string
     */
    public $returns ;
    
    /** 
     * Method help, for introspection 
     * 
     * @var string
     */
    public $help;
    
    /**
     * internalMethod field : method name in PHP-land
     *
     * @var string
     */
    private $_internalMethod;
    
    /**
     * hidden field : true if the method is hidden 
     *
     * @var boolean
     */
    private $_hidden;
    
    /**
     * name Field : external method name
     *
     * @var string 
     */
    private $_name;
    
    // }}}
    // {{{ getInternalMethod()
    
    /** 
     * internalMethod getter 
     * 
     * @return string internalMethod
     */
    public function getInternalMethod() 
    {
        return $this->_internalMethod;
    }
        
    // }}}
    // {{{ isHidden()
    
    /** 
     * hidden getter
     * 
     * @return boolean hidden value
     */
    public function isHidden() 
    {
        return $this->_hidden;
    }
        
    // }}}
    // {{{ getName()
    
    /**
     * name getter
     *
     * @return string name
     */
    public function getName() 
    {
        return $this->_name;
    }
        
    // }}}
    // {{{ constructor
    
    /**
     * Create a new XML-RPC method by introspecting a PHP method
     *
     * @param ReflectionMethod The PHP method to introspect
     * @param string default prefix
     */
    public function __construct(ReflectionMethod $method, $defaultPrefix)
    {
        $hidden = false;
        $docs = $method->getDocComment();
        if (!$docs) {
            $hidden = true;
        }
        $docs = explode("\n", $docs);

        $parameters = array();
        $methodname = null;
        $returns = 'mixed';
        $shortdesc = '';
        $paramcount = -1;
        $prefix = $defaultPrefix;

        // Extract info from Docblock
        $paramDocs = array();
        foreach ($docs as $i => $doc) {
            $doc = trim($doc, " \r\t/*");
            if (strlen($doc) && strpos($doc, '@') !== 0) {
                if ($shortdesc) {
                    $shortdesc .= "\n";
                }
                $shortdesc .= $doc;
                continue;
            }
            if (strpos($doc, '@xmlrpc.hidden') === 0) {
                $hidden = true;
            }
            if ((strpos($doc, '@xmlrpc.prefix') === 0) && preg_match('/@xmlrpc.prefix( )*(.*)/', $doc, $matches)) {
                $prefix = $matches[2];
            }
            if ((strpos($doc, '@xmlrpc.methodname') === 0) && preg_match('/@xmlrpc.prefix( )*(.*)/', $doc, $matches)) {
                $methodname = $matches[2];
            }
            if (strpos($doc, '@param') === 0) { // Save doctag for usage later when filling parameters
                $paramDocs[] = $doc;
            }

            if (strpos($doc, '@return') === 0) {
                $param = preg_split("/\s+/", $doc);
                if (isset($param[1])) {
                    $param = $param[1];
                    $returns = $param;
                }
            }
        }

        // Fill in info for each method parameter
        foreach ($method->getParameters() as $parameterIndex => $parameter) {
            // Parameter defaults
            $newParameter = array('optional' => false, 'type' => 'mixed');

            // Attempt to extract type and doc from docblock
            if (array_key_exists($parameterIndex, $paramDocs) &&
                preg_match('/@param\s+(\S+)(\s+(.+))/', $paramDocs[$parameterIndex], $matches)) {
                if (strpos($matches[1], '|')) {
                    $newParameter['type'] = explode('|', $matches[1]);
                } else {
                    $newParameter['type'] = $matches[1];
                }
                $newParameter['doc'] = $matches[2];
            }

            // Attempt to extract optional status from Reflection API
            if (method_exists($method, 'isOptional')) {
                $newParameter['optional'] = $parameter->isOptional();
            }

            // Attempt to extract type from Reflection API
            if ($parameter->getClass()) {
                $newParameter['type'] = $parameter->getClass();
            }

            $parameters[$parameter->getName()] = $newParameter;
        }

        if (is_null($methodname)) {
            $methodname = $prefix . $method->getName();
        }

        $this->_internalMethod = $method->getName();
        $this->parameters = $parameters;
        $this->returns  = $returns;
        $this->help = $shortdesc;
        $this->_name = $methodname;
        $this->_hidden = $hidden;
    }
    
    // }}}
    // {{{ matchesSignature()
    
    /** 
     * Check if method matches provided call signature 
     * 
     * Compare the provided call signature with this methods' signature and
     * return true iff they match.
     *
     * @param  string Signature to compare method name
     * @param  array  Array of parameter values for method call.
     * @return boolean True if call matches signature, false otherwise
     */
    public function matchesSignature($methodName, $callParams)
    {
        if ($methodName != $this->_name) return false;
        $paramIndex = 0;
        foreach($this->parameters as $param) {
            if (!($param['optional'] || array_key_exists($paramIndex, $callParams))) { // Missing non-optional param
                return false;
            }
            if ((array_key_exists($paramIndex, $callParams)) &&
                (!($param['type'] == 'mixed' || $param['type'] == gettype($callParams[$paramIndex])))) {
                return false;
            }
        }
        return true;
    }
    
    // }}}
    // {{{ autoDocument()
    
    /**
     * Return HTML snippet documenting method, for XML-RPC server introspection.
     *
     * @return string HTML snippet documenting method
     */
    public function autoDocument()
    {
        $result = '<dl><dt>Method description</dt><dd>' . $this->help . '</dd>';
        $result .= '<dt>Method parameters</dt><dd><dl>';
        foreach ($this->parameters as $paramName => $param) {
            $result .= '<dt><i>' . $param['type'] . "</i>$paramName</dt><dd>";
            if ($param['optional']) $result .= '[optional]';
            $result .= $param['doc'];
            $result .= '</dd>';
        }
        $result .= '</dl></dd>';
        $result .= '<dt>Returns</dt><dd><i>' . $this->returns . '</i></dd>';
        $result .= '</dl>';

        return $result;
    }
    
    // }}}
    // {{{ 
    public function getHTMLSignature() 
    {
        $name = $this->_name;
        $returnType = $this->returns;
        $result  = "<i>($returnType)</i> ";
        $result .= "<b>$name(</b>";
        $first = true;
        while (list($name, $parameter) = each($this->parameters)) {
            if ($first) {
                $first = false;
            } else {
                $result .= ', ';
            }
            $type = $parameter['type'];
            $result .= "<i>($type) </i>";
            $result .= "<b>$name</b>";
        }
        reset($this->parameters);
        $result .= "<b>)</b>";
        return $result;
    }
    
}

?>
