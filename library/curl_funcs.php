<?php

require_once("library/misc.php");

/*
    parses response string into headers and body
*/
function parse_response_str($response_str){

    /* parse response string into headers and body */
    #   - tokenizes string using "\n" as delimiter
    $response = [
        "headers" => [],
        "body" => ""
    ];

    # get status header
    $token = strtok($response_str, "\n");
    $response["headers"]["status"] = sscanf($token, "%s %s")[1];
    
    # loop until end of headers (or end of response)
    $token = strtok("\n");
    while ($token !== "\r" && $token !== false){ 
        $header_name = "";
        $header_value = "";
        sscanf($token, "%[^:]: %s", $header_name, $header_value);
        $response["headers"][$header_name] = $header_value;
        $token = strtok("\n");
    }

    # get body if it exists
    $response["body"] = json_decode(htmlspecialchars_decode(strtok("\n")),true);

    return $response;
}

# This function makes a curl request for reading api data
function api_curl_get ($uri) {

    global $ACCESS_TOKEN;

    # get curl response string from given uri
    $ch = curl_init($uri);
    $headers = array(
        "Authorization: Bearer ".$ACCESS_TOKEN
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); # return string, don't print it
    curl_setopt($ch, CURLOPT_HEADER, true); # include headers in output
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, "CAA Officer App (sauer319@umn.edu)");
    $response_str = htmlSpecialChars(curl_exec($ch));

    $response = parse_response_str($response_str);

    curl_close($ch);
    return $response;
}

function api_curl_post_json ($uri, $data) {

    global $ACCESS_TOKEN;

    if ($GLOBALS["suppress_post_requests"] == false) {

        # post data to uri, and get a response string
        $ch = curl_init($uri);
        $headers = array(
            "Authorization: Bearer " . $ACCESS_TOKEN,
            "Content-Type: application/json"
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); # return string, don't print it
        curl_setopt($ch, CURLOPT_HEADER, true); # include headers in output
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "CAA Officer App (sauer319@umn.edu)");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response_str = curl_exec($ch);

        $response = parse_response_str($response_str);

        curl_close($ch);
        return $response;
    } else {
        $request = [
            "method-type" => "Post",
            "request-url" => $uri,
            "headers" => [
                "Authorization" => "Bearer" . $ACCESS_TOKEN,
                "Content-Type" => "application/json",
                "User-Agent" => "CAA Officer App (sauer319@umn.edu)"

            ],
            "body" => htmlspecialchars($data)
        ];
        pretty_print($request);
        $fake_response = [
            "headers" => [
                "status" => 201,
            ],
            "body" => []
        ];
        return $fake_response;
    }
}

# TODO:
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
?>