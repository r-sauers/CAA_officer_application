<?php

require_once("library/evtcatdict.php");
require_once("library/rolesdict.php");
require_once("library/officerdict.php");
require_once("library/todolist.php");
require_once("library/todo.php");

class Event {

    public $name, $timestamp, $location, $event_categories_include, 
    $event_categories_exclude, $todolist, $todos;

    /** @param formdata the json data retrieved from the create event form. This should be an
     * associative array in the form:
     * array(
     *  "event-name" => "name",
     *  "event-location" => "location",
     *  "event-date" => "YYYY-MM-DD",
     *  "video_outreach" => "on" (event category)
     *  ... (more event categories)
     * )
     *  @return event returns associative array:
     * array(
     *  "success" => true
     *  "event" => Event
     * )
     * or
     * array(
     *  "success" => false
     *  "error" => string
     * )
     */
    static function from_form_data($formdata, $todoset_endpoint, $evt_cat_dict, $roles_dict, $officers_dict){
        
        # check if necessary form data is set
        if (!isset($formdata["event-name"])) {
            return array("success" => false, "error" => "event-name" . ' not set');
        }
        if (!isset($formdata["event-location"])) {
            return array("success" => false, "error" => "event-location" . ' not set');
        }
        if (!isset($formdata["event-date"])) {
            return array("success" => false, "error" => "event-date" . ' not set');
        }

        # create timestamp from date
        if (!sscanf($formdata["event-date"], "%d-%d-%d", $year, $month, $date)){
            return array("success" => false, "error" => "incorrect date format, should be YYYY-MM-DD");
        } else {
            $timestamp = mktime(0, 0, 0, $month, $date, $year);
        }

        # grab event categories
        $evt_cat_inc = [];
        foreach ($formdata as $evtcat => $v){
            if ($v == "on" && $evt_cat_dict->has_category($evtcat)) {
                array_push($evt_cat_inc, $evtcat);
            }
        }
        
        return array(
            "success" => true, 
            "event" => new Event($formdata["event-name"], $timestamp, $formdata["event-location"], $todoset_endpoint, $evt_cat_inc, [], $evt_cat_dict, $roles_dict, $officers_dict)
        );

    }

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

        # create todolist and add todos from the given event categories
        $this->todolist = TodoList::create_list($name.date(" m/d/Y", $timestamp), $todoset_endpoint);
        foreach ($expanded_categories as $event_category){
            
            $dict_entry = $evt_cat_dict->get_entry($event_category);
            $assignee_ids = $this->get_assignee_ids($event_category, $roles_dict, $officers_dict);
            
            foreach ($dict_entry["todos"] as $todo_template) {

                $seconds_offset = $todo_template["due_offset"]*24*60*60; // convert days to seconds
                $seconds_duration = $todo_template["due_duration"]*24*60*60;
                $due_start = max($this->timestamp - $seconds_offset - $seconds_duration, time()); // ensure start date is later than the time of making
                $due_end = min($due_start + $seconds_duration, $this->timestamp); // ensure end date is before event date

                $todo = new Todo($todo_template["title"], $todo_template["description_file"], date("Y-m-d", $due_end), $assignee_ids, date("Y-m-d", $due_start), true, []);
                
                array_push($this->todos, [
                    "event_category" => $event_category, // keep track of event_category so we can update todo when event_category is edited
                    "todo" => $todo
                ]);
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