<?php 
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');
//header("Access-Control-Allow-Methods: *");

require_once("MyAPI.php");
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
	$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}
try {
	$API = new MyAPI($_SERVER['SCRIPT_URI'], $_SERVER['QUERY_STRING']);
	echo $API->processAPI();
} catch (Exception $e) {
	echo json_encode(array("success" => false, 'msg' => $e->getMessage()));
}

