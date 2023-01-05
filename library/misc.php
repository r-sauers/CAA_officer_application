<?php
function pretty_print($data)
{
    echo "<pre>" . print_r($data, 1) . "</pre>";
}

function generate_description_file($name, $description){

    $dir = "./descriptions/";
    $filename = $name.".rtf";
    $filename = str_replace("/", "-", $filename);
    $version = 0;
    $filename_used = true;
    while ($filename_used) {
        $filename_used = false;
        if ($dh = opendir($dir)){
            while (($file = readdir($dh)) !== false){
                if ($file == $filename){
                    $filename_used = true;
                    $version += 1;
                    $filename = $name . " (" . $version . ").rtf";
                    break;
                }
            }
        }
    }
    $description_file = $dir . $filename;
    $new_file = fopen($description_file, "w");
    if ($new_file === false) {
        die("Unable to create file with '$description_file'!");
    }
    fwrite($new_file, $description);
    fclose($new_file);

    return $description_file;
}

function app_url_to_api_url($app_url){
    $api_url =  str_replace("basecamp", "basecampapi", $app_url).".json";
    return $api_url;
}
?>