<?php

class EvtCatDict {
    public $evt_cat_dict;

    # load in roles dictionary from a json file
    function __construct($json_file){
        $myfile = fopen($json_file, "r") or die("Unable to open $json_file in EvtCatDict->__construct!");
        $this->evt_cat_dict = fread($myfile,filesize($json_file)) or die("Unable to read $json_file in EvtCatDict->__construct!");
        $this->evt_cat_dict = json_decode($this->evt_cat_dict) or die("Error generating json in EvtCatDict->__construct");
        fclose($myfile);
    }

    function get_entry($event_category){
        return $this->evt_cat_dict[$event_category];
    }

    /*
    Given a list of event_categories, we often need to find all of the subcategories that
    have todos. This function expands event_categories into all subcategories that have todos.

    This function returns a new expanded array (with no duplicates) and doesn't modify the array given as input
    */ 
    function expand_event_categories($event_categories){
        $unexpanded = [];
        $expanded = [];
        
        # copy $event_categories into $unexpanded and $expanded
        # so we don't have to modify the old array
        foreach ($event_categories as $event_category){
            if (count($this->evt_cat_dict[$event_category]["event_categories"]) === 0){
                array_push($expanded, $event_category);
            } else {
                array_push($unexpanded, $event_category);
            }
        }

        # expand $unexpanded into $expanded
        # expansion is done by moving each subcategory of an event_category back into $unexpanded 
        # a subcategory in $unexpanded is moved out when it contains a todo list
        while(count($unexpanded) > 0){

            $event_category = array_pop($unexpanded);

            # The event_category is a subcategory if it has a todo list
            if (count($this->evt_cat_dict[$event_category]["todos"]) > 0) {

                # append self to $expanded if not already there (to prevent duplicates)
                if (!in_array($event_category, $expanded)) {
                    array_push($expanded, $event_category);
                }
            }

            # expand subcategories of event_category back into $unexpanded
            foreach ($this->evt_cat_dict[$event_category]["event_categories"] as $sub_event_category) {
                array_push($unexpanded, $sub_event_category);
            }
        }

        return $expanded;
    }
}

?>