<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2019
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Adrian Fretwell <adrian@a2es.co.uk>
*/

//process this only one time
if ($domains_processed == 1) {

	//test to see if we already have any entries in v_restapi
	$sql = "select count(*) as \"restapi_count\" from v_restapi";
	$database = new database;
	$rows = $database->select($sql, null, 'all');
	if (is_array($rows) && @sizeof($rows) != 0) {
		$restapi_count = $rows[0]['restapi_count'];
	}
	unset($sql);


	if ($restapi_count < 1) {
		$sql = "insert into v_restapi values('44acd6ad-c7bc-435a-8a2d-9d047ac4ef43', 
NULL, 
'List Contacts', 
'Contacts', 
'GET', 
'contacts', 
'select * from v_contacts 
where domain_uuid = :domain_uuid', 
'true', 
'Lists all contacts')";

	$database->execute($sql, null, 'all');
	unset($sql);

		$sql = "insert into v_restapi values('2f76129e-e7f3-40a2-ac49-b576e875239a', 
NULL, 
'List Contact Numbers', 
'Contacts', 
'GET', 
'contact{}/numbers', 
'select * from v_contact_phones
where domain_uuid = :domain_uuid
and contact_uuid = :contact', 
'true', 
'Lists numbers for a contact')";

	$database->execute($sql, null, 'all');
	unset($sql);
		$sql = "insert into v_restapi values('cc742cc5-e42a-4a06-8def-5d30a3d99673', 
NULL, 
'Add Contact Number', 
'Contacts', 
'POST', 
'contact{}/number', 
'insert into v_contact_phones values (
:new_uuid,
:domain_uuid,
:contact,
:phone_type_voice,
:phone_type_fax,
:phone_type_video,
:phone_type_text,
:phone_label,
:phone_primary,
:phone_number,
:phone_extension,
:phone_speed_dial,
:phone_description
)', 
'true', 
'Adds a new contact number')";

	$database->execute($sql, null, 'all');
	unset($sql);
		$sql = "insert into v_restapi values('95553635-4c35-427a-9fc6-c1496c149279', 
NULL, 
'Update Contact Number', 
'Contacts', 
'PUT', 
'contact{}/number{}', 
'update v_contact_phones set
phone_type_voice = :phone_type_voice,
phone_type_fax = :phone_type_fax,
phone_type_video = :phone_type_video,
phone_type_text = :phone_type_text,
phone_label = :phone_label,
phone_primary = :phone_primary,
phone_number = :phone_number,
phone_extension = :phone_extension,
phone_speed_dial = :phone_speed_dial,
phone_description = :phone_description
where
contact_phone_uuid = :number
and domain_uuid = :domain_uuid
and contact_uuid = :contact', 
'true', 
'Updates a contact number')";

	$database->execute($sql, null, 'all');
	unset($sql);
		$sql = "insert into v_restapi values('64be0dd0-c630-454c-ae8e-ffa0dfc2bbc5', 
NULL, 
'Delete Contact Number', 
'Contacts', 
'DELETE', 
'contact{}/number{}', 
'delete from v_contact_phones
where
contact_phone_uuid = :number
and domain_uuid = :domain_uuid
and contact_uuid = :contact', 
'true', 
'Deletes a contact number')";

	$database->execute($sql, null, 'all');
	unset($sql);

	}

}

?>