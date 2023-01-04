<?php
function pretty_print($data)
{
    echo "<pre>" . print_r($data, 1) . "</pre>";
}

function generate_description_file($name, $description){
    $dir = "descriptions/";
    $filename = $name.".rtf";
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
    $new_file = fopen($description_file, "w") or die("Unable to create file!");
    fwrite($new_file, $description);
    fclose($new_file);

    return description_file;
}
?>