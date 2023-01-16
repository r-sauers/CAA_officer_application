<?php

class RolesDict {
    public $roles_dict;

    # load in roles dictionary from a json file
    function __construct($json_file){
        $myfile = fopen($json_file, "r") or die("Unable to open $json_file in RolesDict->__construct!");
        $this->roles_dict = fread($myfile,filesize($json_file)) or die("Unable to read $json_file in RolesDict->__construct!");
        $this->roles_dict = json_decode($this->roles_dict, true) or die("Error generating json in RolesDict->__construct");
        fclose($myfile);
    }

    # return the roles responsible for completing an event category
    function get_roles_for_event($event_category){
        $roles = [];
        foreach (array_keys($this->roles_dict) as $role){
            if (in_array($event_category, $this->roles_dict[$role]["event_categories"])){
                array_push($roles, $role);
            }
        }
        return $roles;
    }
}

?>