<?php
require("./library/verify_login.php");
verify_login();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Event UI Creation Test</title>
        <link rel="stylesheet" href="css/event_category_ui.css">
    </head>
    <body>
        <form id="event-creation" method="post" action="./createevent.php">
            <div id="side-panel">
                <ul>
                </ul>
            </div>
            <div id="main-panel">
                <h2>Create Event</h2>
                <label>Name: </label><input name="event-name" type="text"><br>
                <label>Location: </label><input name="event-location" type="text"><br>
                <label>Date: </label><input name="event-date" type="date"><br><br>
                <input id="submit" type="submit" value="Create Event">
            </div>
        </form>
    </body>
    <script src="js/event_category_ui.js"></script>
</html>

<?php

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

    $res = Event::from_form_data($_POST, "https://3.basecamp.com/4752465/buckets/18094687/todosets/2877724206", $evt_cat_dict, $roles_dict, $officer_dict);
    if (!$res["success"]) {
        echo $res["error"];
    } else {
        echo "success!";
    }

    // $video_outreach = new Event("Video Outreach", mktime(0, 0, 0, 1, 30, 2023), "idk", "https://3.basecamp.com/4752465/buckets/18094687/todosets/2877724206", ["video_outreach"], [], $evt_cat_dict, $roles_dict, $officer_dict);

} else {
    echo "What the hell are you doing?";
}


?>