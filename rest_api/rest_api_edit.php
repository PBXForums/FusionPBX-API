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
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (!permission_exists('restapi_add') && !permission_exists('restapi_edit')) {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//action add or update
	if (is_uuid($_REQUEST["id"])) {
		$action = "update";
		$api_uuid = $_REQUEST["id"];
		$id = $_REQUEST["id"];
	}
	else {
		$action = "add";
	}

//get http post variables and set them to php variables
	if (is_array($_POST)) {
		$api_uuid = $_POST["api_uuid"];
		$api_global = $_POST["api_global"];
		$api_name = $_POST["api_name"];
		$api_category = $_POST["api_category"];
		$api_method = $_POST["api_method"];
		$api_uri = $_POST["api_uri"];
		$api_sql = $_POST["api_sql"];
		$api_enabled = $_POST["api_enabled"];
		$api_description = $_POST["api_description"];
	}

//process the user data and save it to the database
	if (count($_POST) > 0 && strlen($_POST["persistformvar"]) == 0) {

		//delete the restapi
			if (permission_exists('restapi_delete')) {
				if ($_POST['action'] == 'delete' && is_uuid($api_uuid)) {
					//prepare
						$array[0]['checked'] = 'true';
						$array[0]['uuid'] = $api_uuid;
					//delete
						$obj = new rest_api;
						$obj->delete($array);
					//redirect
						header('Location: rest_api.php');
						exit;
				}
			}

		//get the uuid from the POST
			if ($action == "update") {
				$api_uuid = $_POST["api_uuid"];
			}

		//validate the token
			$token = new token;
			if (!$token->validate($_SERVER['PHP_SELF'])) {
				message::add($text['message-invalid_token'],'negative');
				header('Location: rest_api.php');
				exit;
			}

		//check for all required data
			$msg = '';
			if (strlen($api_name) == 0) { $msg .= $text['message-required']." ".$text['label-api_name']."<br>\n"; }
			if (strlen($api_category) == 0) { $msg .= $text['message-required']." ".$text['label-api_category']."<br>\n"; }
			if (strlen($api_method) == 0) { $msg .= $text['message-required']." ".$text['label-api_method']."<br>\n"; }
			if (strlen($api_uri) == 0) { $msg .= $text['message-required']." ".$text['label-api_uri']."<br>\n"; }
			if (strlen($api_sql) == 0) { $msg .= $text['message-required']." ".$text['label-api_sql']."<br>\n"; }
			if (strlen($api_enabled) == 0) { $msg .= $text['message-required']." ".$text['label-api_enabled']."<br>\n"; }
			if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
				require_once "resources/header.php";
				require_once "resources/persist_form_var.php";
				echo "<div align='center'>\n";
				echo "<table><tr><td>\n";
				echo $msg."<br />";
				echo "</td></tr></table>\n";
				persistformvar($_POST);
				echo "</div>\n";
				require_once "resources/footer.php";
				return;
			}

		//add the api_uuid
			if (strlen($api_uuid) == 0) {
				$api_uuid = uuid();
			}

		//prepare the array
			$array['restapi'][0]['restapi_uuid'] = $api_uuid;
			if ($api_global == 'true') {
				$array['restapi'][0]['domain_uuid'] = NULL;
			} else {
				$array['restapi'][0]['domain_uuid'] = $_SESSION["domain_uuid"];
			}
			$array['restapi'][0]['api_name'] = $api_name;
			$array['restapi'][0]['api_category'] = $api_category;
			$array['restapi'][0]['api_method'] = $api_method;
			$array['restapi'][0]['api_uri'] = $api_uri;
			$array['restapi'][0]['api_sql'] = $api_sql;
			$array['restapi'][0]['api_enabled'] = $api_enabled;
			$array['restapi'][0]['api_description'] = $api_description;

		//save to the data
			$database = new database;
			$database->app_name = 'RestAPI';
			$database->app_uuid = '41669f92-ed54-4851-8b98-e244fa71f38c';
			$database->save($array);
			$message = $database->message;

		//redirect the user
			if (isset($action)) {
				if ($action == "add") {
					$_SESSION["message"] = $text['message-add'];
				}
				if ($action == "update") {
					$_SESSION["message"] = $text['message-update'];
				}
				header('Location: rest_api.php');
				return;
			}
	}

//pre-populate the form
	if (is_array($_GET) && $_POST["persistformvar"] != "true") {
		$api_uuid = $_GET["id"];
		$sql = "select * from v_restapi ";
		$sql .= "where restapi_uuid = :api_uuid ";
		$parameters['api_uuid'] = $api_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		if (is_array($row) && sizeof($row) != 0) {
			$api_domain_uuid = $row["domain_uuid"];
			$api_name = $row["api_name"];
			$api_category = $row["api_category"];
			$api_method = $row["api_method"];
			$api_uri = $row["api_uri"];
			$api_sql = $row["api_sql"];
			$api_enabled = $row["api_enabled"];
			$api_description = $row["api_description"];
		} else {
			$api_method = "GET";
			$api_domain_uuid = "";
		}
		unset($sql, $parameters, $row);
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//show the header
	$document['title'] = $text['title-restapi'];
	require_once "resources/header.php";

//show the content
	echo "<form name='frm' id='frm' method='post' action=''>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>".$text['title-restapi']."</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'style'=>'margin-right: 15px;','link'=>'rest_api.php']);
	if ($action == 'update' && permission_exists('restapi_delete')) {
		echo button::create(['type'=>'submit','label'=>$text['button-delete'],'icon'=>$_SESSION['theme']['button_icon_delete'],'name'=>'action','value'=>'delete','onclick'=>"if (confirm('".$text['confirm-delete']."')) { document.getElementById('frm').submit(); } else { this.blur(); return false; }",'style'=>'margin-right: 15px;']);
	}
	echo button::create(['type'=>'submit','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'name'=>'action','value'=>'save']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";

	echo "<tr>\n";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_name']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_name' maxlength='255' value='".escape($api_name)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_name']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_global']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='api_global'>\n";
	if (strlen($api_domain_uuid) < 1) {
		echo "		<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "		<option value='true'>".$text['label-true']."</option>\n";
	}
	if (strlen($api_domain_uuid) > 0) {
		echo "		<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "		<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-restapi_global']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "<tr>\n";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_category']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_category' maxlength='255' value='".escape($api_category)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_category']."\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_method']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='api_method'>\n";
	echo "		<option value='GET' selected='selected'>GET</option>\n";
	echo "		<option value='POST' selected='selected'>POST</option>\n";
	echo "		<option value='PUT' selected='selected'>PUT</option>\n";
	echo "		<option value='DELETE' selected='selected'>DELETE</option>\n";
	echo "		<option value='".escape($api_method)."' selected='selected'>".escape($api_method)."</option>\n";
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-restapi_method']."\n";
	echo "</td>\n";
	echo "</tr>\n";



	echo "<tr>\n";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_uri']."\n";
	echo "</td>\n";
	echo "<td width='70%' class='vtable' style='position: relative;' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_uri' maxlength='255' size='70' value='".escape($api_uri)."'>\n";
	echo "<br />\n";
	echo $text['description-restapi_uri']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "	".$text['label-restapi_sql']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<textarea type='text' name='api_sql' class='formfld' style='width: 65%; height: 100px;'>".$api_sql."</textarea>\n";
	echo "	<br />\n";
	echo "	".$text['description-restapi_sql']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_enabled']."\n";
	echo "</td>\n";
	echo "<td class='vtable' style='position: relative;' align='left'>\n";
	echo "	<select class='formfld' name='api_enabled'>\n";
	if ($api_enabled == "true") {
		echo "		<option value='true' selected='selected'>".$text['label-true']."</option>\n";
	}
	else {
		echo "		<option value='true'>".$text['label-true']."</option>\n";
	}
	if ($api_enabled == "false") {
		echo "		<option value='false' selected='selected'>".$text['label-false']."</option>\n";
	}
	else {
		echo "		<option value='false'>".$text['label-false']."</option>\n";
	}
	echo "	</select>\n";
	echo "<br />\n";
	echo $text['description-restapi_enabled']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	".$text['label-restapi_description']."\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='api_description' maxlength='255' size='70' value=\"".escape($api_description)."\">\n";
	echo "<br />\n";
	echo $text['description-restapi_description']."\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "</table>";
	echo "<br /><br />";
	echo "<input type='hidden' name='api_uuid' value='".escape($api_uuid)."'>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>";

//include the footer
	require_once "resources/footer.php";

?>