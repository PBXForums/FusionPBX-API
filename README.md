Start making pull requests :)

OK so this is a starter for ten...

This is the result of a lazy Sunday hacking session,
there will almost certainly be bugs, and the code will need checking for 
sercurity, so
DO NOT DEPLOY THESE APPS ON A PRODUCTION SERVER!

To make an extensible REST API, my view was to keep the .php code simple and 
drive the API from a SQL table (v_restapi).

To do this we have two apps: 
/app/api  This is where the API runs, and this location also has the re-write 
rules set up in the default Nginx config.  Everything after the /app/api gets 
rewritten to rewrite_uri.

/app/rest_api  This is an application to edit the v_restapi table.
Advanced->Upgade  App Defaults / Schema etc. may be required unless undertake 
these actions manually.

The app_defaults.php contains the SQL to insert the five test APIs into the 
table.

The test scenarios all use the contacts data:

List Contacts
List Contact Numbrs
Add Contact Number
Update Contact Number
Delete Contact Number

The Rest API uses the usual request methods: GET, POST, PUT, DELETE, and uses 
JSON for the data exchange to and from the client.

Sample test urls are listed below, the GET transactions are easyenough to
execute in your web browser butyou may need to use a tool shuch as Postman 
(https://www.getpostman.com/apps) to execute the POST, PUT and DELETE options.
The /api-key{} can appear anywhere in the URI, it is stripped out once the 
API Key (uuid) has been extracted.

List all contacts

URL: https://<your domain>/app/api/contacts/api-key{<uuid>}

Request Method: GET



List all numbers for a contact

URL: https://<your domain>/app/api/contact{<contact uuid>}/numbers/api-key{<uuid>}

Request Method: GET



Add a contact number

URL: https://<your domain>/app/api/contact{<contact uuid>}/number/api-key{<uuid>}

Request Method: POST

Body raw data:
{
	"phone_type_voice":"1",
	"phone_type_fax":null,
	"phone_type_video":null,
	"phone_type_text":null,
	"phone_label":"Home",
	"phone_primary":"0",
	"phone_number":"01636600660",
	"phone_extension":"",
	"phone_speed_dial":"",
	"phone_description":"Data Centre"
}



Update a contact number

URL: https://<your domain>/app/api/contact{<contact uuid>}/number{<number uuid>}/api-key{<uuid>}

Request Method: PUT

Body raw data:
{
	"phone_type_voice":"1",
	"phone_type_fax":null,
	"phone_type_video":null,
	"phone_type_text":null,
	"phone_label":"Work",
	"phone_primary":"0",
	"phone_number":"01636600550",
	"phone_extension":"",
	"phone_speed_dial":"",
	"phone_description":"Main Office"
}



Delete a contact number

URL: https://<your domain>/app/api/contact{<contact uuid>}/number{<number uuid>}/api-key{<uuid>}

Request Method: DELETE



A note on the rest_api app.  There is an option to make the API global, this is achieved by 
setting the domain_uuid field to null.  Maybe I'm missing something or there is a limitation in 
the database class, but if the domain_uuid is null the normal delete functions do not work.  in 
order to delete a record you must first meake it non global and then delete it.

I think, that's about it.

    Adrian Fretwell.


