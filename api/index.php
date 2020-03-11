<?php
/*
	PBXForums FusionPBX-API
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	This is an unofficial FusionPBX application, its purpose is to provide an
	educational resource for anyone wishing to better understand the building
	of REST API transactions.

	The code presented here is inspired by the original code at FusionPBX and
	credit must be given to: Mark J Crane <markjcrane@fusionpbx.com>
	for that original work.

	Contributor(s):
	Adrian Fretwell <adrian@a2es.co.uk>
*/

//includes
	include "root.php";
	require_once "resources/require.php";
	require_once "resources/functions/restapi_functions.php";

// https://pbxtest-blue.a2es.uk/app/api/contacts{121548741}/address
// string(27) "contacts{121548741}/address" 

	if(isset($_REQUEST["rewrite_uri"])){
		$rewrite_uri = rtrim($_REQUEST["rewrite_uri"], '/');
	} else {
		send_access_denied();
	}

	$request_method = $_SERVER["REQUEST_METHOD"];
	$segments = explode('/', $rewrite_uri);

	$endpoints = array();
	foreach($segments as $segment) {
		$ids = array();
		preg_match('/(.*){(.*)}/' , $segment , $ids);
		if(count($ids) == 3) {
			$endpoints[$ids[1]] = $ids[2];
		} else {
			$endpoints[$segment] = "";
		}
	}

	if (!array_key_exists('api-key', $endpoints)) {
		send_access_denied();
	}

// set request key value ready for call to check_auth
	$_REQUEST['key'] = $endpoints['api-key'];
	require_once "resources/check_auth.php";

	switch($request_method) {
		case "POST":
			if (!permission_exists('restapi_c')) {send_access_denied(); }
			break;
		case "GET":
			if (!permission_exists('restapi_r')) {send_access_denied(); }
			break;
		case "PUT":
			if (!permission_exists('restapi_u')) {send_access_denied(); }
			break;
		case "DELETE":
			if (!permission_exists('restapi_d')) {send_access_denied(); }
			break;
		default:
			send_access_denied();
}


// remove record Ids but keep placeholders
	$rewrite_uri = preg_replace('/{[^\/]*}/', '{}', $rewrite_uri);
// remove any refernce to the api key from uri that we will compare against the DB
	$rewrite_uri = preg_replace(array('/\/api-key{?}?/', '/^api-key{?}?\//'), '', $rewrite_uri);

	$sql = "select * from v_restapi where api_method = :api_method and api_uri = :api_uri and api_enabled = 'true' and (domain_uuid = :domain_uuid or domain_uuid is null) order by domain_uuid asc";

	$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	$parameters['api_method'] = $request_method;
	$parameters['api_uri'] = $rewrite_uri;

	$database = new database;

	$rows = $database->select($sql, $parameters, 'all');
	if (is_array($rows) && @sizeof($rows) != 0) {
		$api_sql = $rows[0]['api_sql'];
	} else {
		send_api_message(404, "API not found.");
	}

	unset ($parameters, $sql);

	if ($request_method == 'GET') {
		if (strpos($api_sql, ':domain_uuid') > 0){
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		}
		foreach($endpoints as $key => $value){
			if ($key == 'api-key') continue;
			if (strlen($value) > 0) {
				$parameters[$key] = $value;
			}
		}

		//var_dump($parameters);
		//echo "<br>\n";
		//exit;

		$rows = $database->select($api_sql, $parameters, 'all');
		if (is_array($rows) && @sizeof($rows) != 0) {
			send_data($rows);
		} else {
			send_api_message(200, "Empty result set.");
		}
	exit;
	}

	if ($request_method == 'POST') {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		if (!permission_exists('restapi_domain_in_data')) {
			if (strpos($api_sql, ':domain_uuid') > 0){
				$data['domain_uuid'] = $_SESSION['domain_uuid'];
			}
		}
		if (!permission_exists('restapi_new_uuid_in_data')) {
			$data['new_uuid'] = uuid();
		}

		foreach($endpoints as $key => $value){
			if ($key == 'api-key') continue;
			if (strlen($value) > 0) {
				$data[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $data, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		//echo $database->message['error']['message']."\n";
		exit;
	}

	if ($request_method == 'PUT') {
		$data = json_decode(file_get_contents("php://input"), TRUE);
		if (!permission_exists('restapi_domain_in_data')) {
			if (strpos($api_sql, ':domain_uuid') > 0){
				$data['domain_uuid'] = $_SESSION['domain_uuid'];
			}
		}

		foreach($endpoints as $key => $value){
			if ($key == 'api-key') continue;
			if (strlen($value) > 0) {
				$data[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $data, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		//echo $database->message['error']['message']."\n";
		exit;
	}

	if ($request_method == 'DELETE') {

		if (strpos($api_sql, ':domain_uuid') > 0){
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		}
		foreach($endpoints as $key => $value){
			if ($key == 'api-key') continue;
			if (strlen($value) > 0) {
				$parameters[$key] = $value;
			}
		}

		//var_dump($data);
		//echo "<br>\n".$api_sql."<br>\n";
		//exit;

		$database->execute($api_sql, $parameters, 'all');
		send_api_message($database->message['code'], $database->message['message']);
		//echo $database->message['error']['message']."\n";
		exit;
	}

exit;
?>

