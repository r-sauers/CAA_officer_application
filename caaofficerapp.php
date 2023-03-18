<?php
require("./library/verify_login.php");
verify_login();
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

	$Ryan_id = 37984311;
	$Testing_TodoList = "https://3.basecampapi.com/4752465/buckets/18094687/todolists/5700800823.json";

	//pretty_print($evt_cat_dict);

	$video_outreach = new Event("Video Outreach", mktime(0, 0, 0, 1, 30, 2023), "idk", "https://3.basecamp.com/4752465/buckets/18094687/todosets/2877724206", ["video_outreach"], [], $evt_cat_dict, $roles_dict, $officer_dict);
	

	
	
?>
</body>
</html>