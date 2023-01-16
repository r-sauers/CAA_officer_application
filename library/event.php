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
        foreach ($expanded_include as $event_category) {
            if (!in_array($event_category, $expanded_exclude)) {
                array_push($expanded_categories, $event_category);
            }
        }

        # create todolist and add todos
        $this->todolist = TodoList::create_list($name.date(" m/d/Y", $timestamp), $todoset_endpoint);
        foreach ($expanded_categories as $event_category){
            $dict_entry = $evt_cat_dict->get_entry($event_category);
            $assignee_ids = $this->get_assignee_ids($event_category, $roles_dict, $officers_dict);
            foreach ($dict_entry["todos"] as $todo_template) {
                $due_end = $this->timestamp - $todo_template["due_offset"]*24*60*60;
                $due_start = $due_end - $todo_template["due_duration"]*24*60*60;
                # TODO: make it so dates are neither before current day nor past event date
                $todo = new Todo($todo_template["title"], $todo_template["description_file"], date("Y-m-d", $due_end), $assignee_ids, date("Y-m-d", $due_start), true, []);
                array_push($this->todos, $todo);
                $this->todolist->add_todo($todo);
            }
        }
    }

    function get_assignee_ids($event_category, $roles_dict, $officers_dict){
        $assignee_ids = [];
        $roles = $roles_dict->get_roles_for_event($event_category);
        foreach ($roles as $role){
            $officer = $officers_dict->get_by_role($role);
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