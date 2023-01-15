<?php

class OfficersDict {

    public $officers;

    # load in officers dictionary from json file
    function __construct($json_file){
        $myfile = fopen($json_file, "r") or die("Unable to open $json_file in OfficersDict->__construct!");
        $this->officers = fread($myfile,filesize($json_file)) or die("Unable to read $json_file in OfficersDict->__construct!");
        $this->officers = json_decode($this->officers) or die("Error generating json in OfficersDict->__construct");
        fclose($myfile);
    }

    # return officer with given role
    function get_by_role($role){
        foreach ($this->officers as $officer){
            if ($officer->role == $role){
                return $officer;
            }
        }
    }
}

?>