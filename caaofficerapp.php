<?php
/*
 * The following code is adapted from: 
 * https://www.phpclasses.org/package/7700-PHP-Authorize-and-access-APIs-using-OAuth.html
 * 
 */
	
	// grabs libraries
	require('./http/http.php');
	require('./oauth/oauth_client.php');
	
	// configure oauth
	$client = new oauth_client_class;
	$client->server = '37Signals';
	$client->debug = true;
	$client->debug_http = true;
	$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
		dirname(strtok($_SERVER['REQUEST_URI'],'?')).'caaofficerapp.php';

	$client->client_id = getenv("CAA_APP_BASECAMP_OAUTH_ID");
	error_log("\n\n" . $client->client_id . "\n\n");
	$application_line = __LINE__;
	$client->client_secret = getenv("CAA_APP_BASECAMP_OAUTH_SECRET");
	$client->scope = ''; // no api permissions needed

	// request token
	if(($success = $client->Initialize()))
	{
		if(($success = $client->Process()))
		{
			if(strlen($client->authorization_error))
			{
				$client->error = $client->authorization_error;
				$success = false;
			}
			elseif(strlen($client->access_token))
			{
				// gets authorization api
				$success = $client->CallAPI(
					'https://launchpad.37signals.com/authorization.json',
					'GET', array(), array('FailOnAccessError'=>true), $user);
			}
		}
		$success = $client->Finalize($success);
	}
	if($client->exit)
		exit;
	if($success)
	{
?>




<!DOCTYPE html>
<html>
<head>
<title>37Signals OAuth client results</title>
</head>
<body>
<?php

	// When we are testing, it is important to print the json requests instead of sending them
	// This way we can make sure the requests are correct.
	$GLOBALS["suppress_post_requests"] = true;

	require_once("library/misc.php");
	require_once("library/event.php");

	require_once("library/officerdict.php");
	require_once("library/rolesdict.php");
	require_once("library/evtcatdict.php");

	$officer_dict = new OfficersDict("officers.json");
	$roles_dict = new RolesDict("roles.json");
	$evt_cat_dict = new EvtCatDict("event_categories.json");

	// get Access
	apache_setenv('ACCESS_TOKEN', $client->access_token);

	# Welcome User
	echo '<h1>', HtmlSpecialChars($user->identity->first_name),
		' you have successfully logged into CAA\'s basecamp with 37Signals!</h1>';

	$Ryan_id = 37984311;
	$Testing_TodoList = "https://3.basecampapi.com/4752465/buckets/18094687/todolists/5700800823.json";

	//pretty_print($evt_cat_dict);

	$video_outreach = new Event("Video Outreach", mktime(0, 0, 0, 1, 30, 2023), "idk", "https://3.basecamp.com/4752465/buckets/18094687/todosets/2877724206", ["video_outreach"], [], $evt_cat_dict, $roles_dict, $officer_dict);
	

	
	
?>
</body>
</html>




<?php
	}
	else
	{
?>
<!DOCTYPE html>
<html>
<head>
<title>OAuth client error</title>
</head>
<body>
<h1>OAuth client error</h1>
<pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
</body>
</html>
<?php
	}

?>
</body>

</html>