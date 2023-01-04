<?php
/*
 * The following code is adapted from: 
 * https://www.phpclasses.org/package/7700-PHP-Authorize-and-access-APIs-using-OAuth.html
 * 
 */

	// grabs libraries
	require('./http/http.php');
	require('./oauth/oauth_client.php');

	// grabs variables
	require("./oauth_id.php");
	require("./oauth_secret.php");
	
	// configure oauth
	$client = new oauth_client_class;
	$client->server = '37Signals';
	$client->debug = true;
	$client->debug_http = true;
	$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
		dirname(strtok($_SERVER['REQUEST_URI'],'?')).'caaofficerapp.php';

	$client->client_id = $CAA_APP_OAUTH_ID;
	$application_line = __LINE__;
	$client->client_secret = $CAA_APP_OAUTH_SECRET;
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
	
	# This function makes a curl request for reading api data
	function api_curl_get ($uri) {
		global $ACCESS_TOKEN;
		$ch = curl_init($uri);
		$headers = array(
			"Authorization: Bearer ".$ACCESS_TOKEN
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT, "CAA Officer App (sauer319@umn.edu)");
		$response = json_decode(htmlspecialchars_decode(curl_exec($ch)));
		curl_close($ch);
		return $response;
	}

	function api_curl_post_json ($uri) {
		global $ACCESS_TOKEN;
		$ch = curl_init($uri);
		$headers = array(
			"Authorization: Bearer ".$ACCESS_TOKEN,
			"Content-Type: json"
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT, "CAA Officer App (sauer319@umn.edu)");
		$response = json_decode(htmlspecialchars_decode(curl_exec($ch)));
		curl_close($ch);
		return $response;
	}

	function api_curl_post_attachment ($uri) {
		global $ACCESS_TOKEN;
		$ch = curl_init($uri);
		$headers = array(
			"Authorization: Bearer ".$ACCESS_TOKEN
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT, "CAA Officer App (sauer319@umn.edu)");
		$response = json_decode(htmlspecialchars_decode(curl_exec($ch)));
		curl_close($ch);
		return $response;
	}

	function pretty_print($data){
		echo "<pre>".print_r($data, 1)."</pre>";
	}

	// get Access
	$ACCESS_TOKEN = $client->access_token;

	# Welcome User
	echo '<h1>', HtmlSpecialChars($user->identity->first_name),
		' you have successfully logged into CAA\'s basecamp with 37Signals!</h1>';


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