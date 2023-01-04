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
?>