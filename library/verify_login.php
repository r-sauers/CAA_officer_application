<?php

/* 
Call verify_login at the start of every page that handles secure information to verify the client
is logged in. This function will stop execution of the php script if the user is not logged in and
prompt them to log in.
*/
function verify_login(){

    session_start();

    /* $_SESSION["REFERER"] is used to keep track of the page to direct back to after logging in*/
    $_SESSION["REFERER"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

    if (!isset($_SESSION["EXPIRATION"])) {
        echo "<p>Please login again</p>";
        echo '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] . '/login.php">Press here to login</a>';
        exit();
    }
    /* if the session is an hour old */
    else if (time() > $_SESSION["EXPIRATION"]) {
        session_unset();
        session_destroy();
        echo "<p>Session has expired</p>";
        echo '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] . '/login.php">Press here to login</a>';
        exit();
    }
}
?>