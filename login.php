<?php
session_start();


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
    dirname(strtok($_SERVER['REQUEST_URI'],'?')).'login.php';

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

    $caa_access = false;
    for ($i = 0; $i < count($user->accounts); $i ++){
        if ($user->accounts[$i]->id == 4752465){
            $caa_access = true;
        }
    }

    if ($caa_access) {
        $_SESSION["ACCESS_TOKEN"] = $client->access_token;
        sscanf($user->expires_at, "%d-%d-%dT%d:%d:%dZ", $year, $month, $day, $hour, $minute, $second);
        $_SESSION["EXPIRATION"] = mktime($hour, $minute, $second, $month, $day, $year);
        $_SESSION["CREATED"] = time();
        $_SESSION["USERNAME"] = $user->identity->first_name . " " . $user->identity->last_name;
        

        echo "<p>Hello " . $_SESSION["USERNAME"] . "! You have successfully logged in!</p>";
        if ( isset($_SESSION["REFERER"]) ){
            echo '<a href="'. $_SESSION["REFERER"] . '">go back</a><br>';
        }
        echo '<a href="' . 'http://' . $_SERVER["HTTP_HOST"] . '">go home</a>';
    } else {
        ?>
        <!DOCTYPE html>
        `<html>
        <head>
        <title>Missing Permissions Error</title>
        </head>
        <body>
        <h1>Missing Permissions Error</h1>
        <pre>Error: You don't have access to caa's basecamp. If you think this is a mistake, please contact a student group officer.</pre>
        </body>
        </html>
        <?php
    }
    unset($_SESSION["REFERER"]);
} else {
    ?>
    <!DOCTYPE html>
    `<html>
    <head>
    <title>OAuth client error</title>
    </head>
    <body>
    <h1>OAuth client error</h1>
    <pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
    </body>
    </html>
    <?php

    unset($_SESSION["REFERER"]);
}

?>