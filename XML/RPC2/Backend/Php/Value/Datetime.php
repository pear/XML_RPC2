<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// LICENSE AGREEMENT. If folded, press za here to unfold and read license {{{ 

/**
* +-----------------------------------------------------------------------------+
* | Copyright (c) 2004 S�rgio Gon�alves Carvalho                                |
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
* | Author: S�rgio Carvalho <sergio.carvalho@portugalmail.com>                  |
* +-----------------------------------------------------------------------------+
*
* @category   XML
* @package    XML_RPC2
* @author     S�rgio Carvalho <sergio.carvalho@portugalmail.com>  
* @copyright  2004-2005 S�rgio Carvalho
* @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version    CVS: $Id$
* @link       http://pear.php.net/package/XML_RPC2
*/

// }}}

// dependencies {{{
require_once 'XML/RPC2/Exception.php';
require_once 'XML/RPC2/Backend/Php/Value/Scalar.php';
// }}}

/**
 * XML_RPC datetime value class. Instances of this class represent datetime scalars in XML_RPC
 * 
 * @category   XML
 * @package    XML_RPC2
 * @author     S�rgio Carvalho <sergio.carvalho@portugalmail.com>  
 * @copyright  2004-2005 S�rgio Carvalho
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link       http://pear.php.net/package/XML_RPC2
 */
class XML_RPC2_Backend_Php_Value_Datetime extends XML_RPC2_Backend_Php_Value_Scalar
{

    // {{{ constructor
    
    /**
     * Constructor. Will build a new XML_RPC2_Backend_Php_Value_Datetime with the given value
     * 
     * The provided value can be an int, which will be interpreted as a Unix timestamp, or 
     * a string in iso8601 format
     *
     * @param mixed value
     * @see http://www.w3.org/TR/NOTE-datetime
     */
    public function __construct($nativeValue) 
    {
        $this->setScalarType('dateTime.iso8601');
        if (gettype($nativeValue) == 'string') {
            if (!preg_match('/([0-9]{4})(-?([0-9]{2})(-?([0-9]{2})(T([0-9]{2}):([0-9]{2})(:([0-9]{2})(\.([0-9]+))?)?(Z|(([-+])([0-9]{2}):([0-9]{2})))?)?)?)?/',
                            $nativeValue,
                            $matches)) {
                throw new XML_RPC2_InvalidDateFormatException(sprintf('Provided date \'%s\' is not ISO-8601.', $nativeValue));
            }
            $year           = $matches[1];
            $month          = array_key_exists(3, $matches) ? $matches[3] : 1;
            $day            = array_key_exists(5, $matches) ? $matches[5] : 1;
            $hour           = array_key_exists(7, $matches) ? $matches[7] : 0;
            $minutes        = array_key_exists(8, $matches) ? $matches[8] : 0;
            $seconds        = array_key_exists(10, $matches) ? $matches[10] : 0;
            $milliseconds   = array_key_exists(12, $matches) ? ((double) ('0.' . $matches[12])) : 0;
            $tzSeconds      = array_key_exists(13, $matches) ? 
                                  ($matches[13] == 'Z' ? 0 : ($matches[15] == '-' ? -1 : 1) * (((int) $matches[16]) * 3600 + ((int) $matches[17]) * 60))
                                  : 0;
            $this->setNativeValue(
                ((double) @gmmktime($hour, $minutes, $seconds, $month, $day, $year, 0)) +
                ((double) $milliseconds) - 
                ((double) $tzSeconds));
        } else {
            $this->setNativeValue($nativeValue);
        }
    }
    
    // }}}
    // {{{ decode()
    
    /**
     * Decode transport XML and set the instance value accordingly
     *
     * @param mixed The encoded XML-RPC value,
     */
    public static function decode($xml) 
    {
        // TODO Remove reparsing of XML fragment, when SimpleXML proves more solid. Currently it segfaults when
        // xpath is used both in an element and in one of its children
        $xml = simplexml_load_string($xml->asXML());
        $value = $xml->xpath('/value/dateTime.iso8601/text()');
        if (!array_key_exists(0, $value)) {
            $value = $xml->xpath('/value/text()');
        }
        return (string) $value[0];
    }
    
    // }}}
    
}

?>
