--TEST--
PHP Backend XML-RPC client against python server returning normal response
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Backend.php';
XML_RPC2_Backend::setBackend('php');
$client = XML_RPC2_Client::create('http://python.xmlrpc2test.sergiocarvalho.com:8765', '', null);
var_dump($client->echo('World'));
?>
--EXPECT--
string(5) "World"
