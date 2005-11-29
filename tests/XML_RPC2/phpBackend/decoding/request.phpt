--TEST--
Request XML-RPC decoding (Php Backend)
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once('XML/RPC2/Backend/Php/Request.php');
$request = XML_RPC2_Backend_Php_Request::createFromDecode(simplexml_load_string('<?xml version="1.0"?><methodCall><methodName>foo.bar</methodName><params><param><value><string>a string</string></value></param><param><value><int>125</int></value></param><param><value><double>125.2</double></value></param><param><value><dateTime.iso8601>19970716192030</dateTime.iso8601></value></param><param><value><boolean>1</boolean></value></param><param><value><boolean>0</boolean></value></param></params></methodCall>'));
var_dump($request->getMethodName());
var_dump($request->getParameters());
?>
--EXPECT--
string(7) "foo.bar"
array(6) {
  [0]=>
  string(8) "a string"
  [1]=>
  int(125)
  [2]=>
  float(125.2)
  [3]=>
  float(869011200)
  [4]=>
  bool(true)
  [5]=>
  bool(false)
}
