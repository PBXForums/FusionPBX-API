Start making pull requests :)


# OK so this is a starter for ten...

This is the result of a lazy Sunday hacking session,
there will almost certainly be bugs, and the code will need checking for 
security, so

**DO NOT DEPLOY THESE APPS ON A PRODUCTION SERVER!**

To make an extensible REST API, my view was to keep the .php code simple and 
drive the API from a SQL table (v_restapi).

To do this we have two apps:<br>
**/app/api**  This is where the API runs, and this location also has the re-write 
rules set up in the default Nginx config.  Everything after the /app/api gets 
rewritten to rewrite_uri.

**/app/rest_api**  This is an application to edit the v_restapi table.
Advanced->Upgade  App Defaults / Schema etc. may be required unless undertake 
these actions manually.

The app_defaults.php contains the SQL to insert the five test APIs into the 
table.

The test scenarios all use the contacts data:

  * List Contacts
  * List Contact Numbrs
  * Add Contact Number
  * Update Contact Number
  * Delete Contact Number

The Rest API uses the usual request methods: GET, POST, PUT, DELETE, and uses 
JSON for the data exchange to and from the client.

Sample test urls are listed below, the GET transactions are easyenough to
execute in your web browser butyou may need to use a tool shuch as Postman 
([https://www.getpostman.com/apps](URL)) to execute the POST, PUT and DELETE options.
The /api-key{} can appear anywhere in the URI, it is stripped out once the 
API Key (uuid) has been extracted.<br>

**List all contacts**<br>
URL: https://&lt;your domain&gt;/app/api/contacts/api-key{&lt;uuid&gt;}<br>
Request Method: GET<br>


**List all numbers for a contact**<br>
URL: https://&lt;your domain>/app/api/contact{&lt;contact uuid&gt;}/numbers/api-key{&lt;uuid&gt;}<br>
Request Method: GET<br>


**Add a contact number**<br>
URL: https://&lt;your domain&gt;/app/api/contact{&lt;contact uuid&gt;}/number/api-key{&lt;uuid&gt;}<br>
Request Method: POST<br>
Body raw data:<br>
{<br>
	"phone_type_voice":"1",<br>
	"phone_type_fax":null,<br>
	"phone_type_video":null,<br>
	"phone_type_text":null,<br>
	"phone_label":"Home",<br>
	"phone_primary":"0",<br>
	"phone_number":"01636600660",<br>
	"phone_extension":"",<br>
	"phone_speed_dial":"",<br>
	"phone_description":"Data Centre"<br>
}<br>


**Update a contact number**<br>
URL: https://&lt;your domain&gt;/app/api/contact{&lt;contact uuid&gt;}/number{&lt;number uuid&gt;}/api-key{&lt;uuid&gt;}<br>
Request Method: PUT<br>
Body raw data:<br>
{<br>
	"phone_type_voice":"1",<br>
	"phone_type_fax":null,<br>
	"phone_type_video":null,<br>
	"phone_type_text":null,<br>
	"phone_label":"Work",<br>
	"phone_primary":"0",<br>
	"phone_number":"01636600550",<br>
	"phone_extension":"",<br>
	"phone_speed_dial":"",<br>
	"phone_description":"Main Office"<br>
}<br>


**Delete a contact number**<br>
URL: https://&lt;your domain&gt;/app/api/contact{&lt;contact uuid&gt;}/number{&lt;number uuid&gt;}/api-key{&lt;uuid&gt;}<br>
Request Method: DELETE<br>
<br>
A note on the rest_api app.  There is an option to make the API global, this is achieved by 
setting the domain_uuid field to null.  Maybe I'm missing something or there is a limitation in 
the database class, but if the domain_uuid is null the normal delete functions do not work.  in 
order to delete a record you must first meake it non global and then delete it.

I think, that's about it.<br>
Adrian Fretwell.


