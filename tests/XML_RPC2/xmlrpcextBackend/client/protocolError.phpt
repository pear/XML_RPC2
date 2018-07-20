--TEST--
XMLRPCext Backend XML-RPC client with transport error
--SKIPIF--
<?php
if (!function_exists('xmlrpc_server_create')) {
    print "Skip XMLRPC extension unavailable";
}
if (!function_exists('curl_init')) {
    print "Skip CURL extension unavailable";
}
?>
--FILE--
<?php
set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Backend.php';
XML_RPC2_Backend::setBackend('xmlrpcext');
$client = XML_RPC2_Client::create('http://rpc.example.com:1000/', '', null);
try {
    $client->invalidMethod('World');
} catch (XML_RPC2_CurlException $e) {
    echo $e->getMessage();
}
?>
--EXPECTF--
Unable to connect to tcp://rpc.example.com:1000. Error: php_network_getaddresses: getaddrinfo failed: Name or service not known
