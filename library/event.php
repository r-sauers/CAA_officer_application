<?php

require_once("library/evtcatdict.php");
require_once("library/rolesdict.php");
require_once("library/officerdict.php");
require_once("library/todolist.php");
require_once("library/todo.php");

class Event {

    public $name, $timestamp, $location, $event_categories_include, 
    $event_categories_exclude, $todolist, $todos;

    function __construct($name, $timestamp, $location, $todoset_endpoint, $event_categories_include, $event_categories_exclude, $evt_cat_dict, $roles_dict, $officers_dict){

        $this->name = $name;
        $this->timestamp = $timestamp;
        $this->location = $location;
        $this->event_categories_include = $event_categories_include;
        $this->event_categories_exclude = $event_categories_exclude;

        # generate all event categories with todos that apply to this event into $expanded_categories
        # so we can add the todos later
        $expanded_include = $evt_cat_dict->expand_event_categories($this->event_categories_include);
        $expanded_exclude = $evt_cat_dict->expand_event_categories($this->event_categories_exclude);
        $expanded_categories = [];
        for ($i = 0; $i < count($expanded_include); $i += 1){
            for ($j = 0; $j < count($expanded_exclude); $j += 1){
                if ($expanded_include[$i] == $expanded_exclude[$j]) {
                    array_push($expanded_categories, $expanded_include[i]);
                }
            }
        }


        # create todolist and add todos
        $this->todolist = TodoList::create_list($name.date(" m/d/Y", $timestamp), null, $todoset_endpoint);
        foreach ($expanded_categories as $event_category){
            $dict_entry = $evt_cat_dict->get_entry($event_category);
            $due_end = $this->timestamp + mktime(0, 0, 0, 0, $dict_entry->due_offset);
            $due_start = $due_end + mktime(0, 0, 0, 0, $dict_entry->due_duration);
            $assignee_ids = $this->get_assignee_ids($event_category, $roles_dict, $officers_dict);
            $todo = new Todo($dict_entry->title, $dict_entry->description_file, $due_end, $assignee_ids, $due_start, true, []);
            array_push($this->todos, $todo);
            $this->todolist->add_todo($todo);
        }
    }

    function get_assignee_ids($event_category, $roles_dict, $officers_dict){
        $assignee_ids = [];
        $roles = $roles_dict->get_roles_for_event($event_category);
        foreach ($roles as $role){
            $officer = $officers_dict->get_by_role(role);
            if ($officer != null){
                if (!in_array($officer->id, $assignee_ids)){
                    array_push($assignee_ids, $officer->id);
                }
            };
        }
        return $assignee_ids;
    }
}

?>