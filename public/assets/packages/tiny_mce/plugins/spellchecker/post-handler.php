<?php

// Set RPC response headers
header('Content-Type: text/plain');
header('Content-Encoding: UTF-8');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$raw = $GLOBALS["HTTP_RAW_POST_DATA"];

$url = parse_url('http://speller.yandex.net/services/tinyspell');

// Setup request
$req = "POST " . $url["path"] . " HTTP/1.0\r\n";
$req .= "Connection: close\r\n";
$req .= "Host: " . $url['host'] . "\r\n";
$req .= "Content-type: application/json\r\n";
$req .= "Content-Length: " . strlen($raw) . "\r\n";
$req .= "\r\n" . $raw;

if (!isset($url['port']) || !$url['port']) $url['port'] = 80;

$errno = $errstr = "";

$socket = fsockopen($url['host'], intval($url['port']), $errno, $errstr, 30);

if ($socket) {

	// Send request headers
	fputs($socket, $req);

	// Read response headers and data
	$resp = "";

	while (!feof($socket)) $resp .= fgets($socket, 4096);

	fclose($socket);

	// Split response header/data
	$resp = explode("\r\n\r\n", $resp);
	
	echo $resp[1]; // Output body

}

die();

?>