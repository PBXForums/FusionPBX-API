<?php


function send_api_message($code, $text)
{
	header("Content-Type: application/json; charset=UTF-8");
	http_response_code($code);
	$data = array();
	$data['message'] = $text;
	echo json_encode($data);
	exit;
}

function send_data(&$data)
{
	header("Content-Type: application/json; charset=UTF-8");
	http_response_code(200);
	echo json_encode($data);
	exit;
}

function send_access_denied()
{
	send_api_message(403, "Access denied.");
}

?>