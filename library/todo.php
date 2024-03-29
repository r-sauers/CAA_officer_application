<?php

require_once("library/curl_funcs.php");
require_once("library/misc.php");

/*
This class stores todo information used in the basaecamp api:
    - $api_url:             a link to the api endpoint of the todo
    - $content:             the name of the todo
    - $description_file:    relative address to a file with the description
    - $assignee_ids:        an array of id's of people assigned the task
    - $notify:              boolean, notify assignees?
    - $due_on:              when is the todo due
    - $starts_on:           when should the todo be started

The class can construct itself from an api endpoint, and it can generate
json to create a todo using the api

NOTE: creating a todo in basecamp is done in TodoList
*/
class Todo {
    private $content;
    private $description_file;
    private $assignee_ids;
    private $completion_subscriber_ids;
    private $notify;
    private $due_on;
    private $starts_on;
    private $api_url;

    function __construct($name, $description_file, $due_on, $assignee_ids=[], $starts_on=null, $notify=true, $completion_subscriber_ids=[]){
        if ($starts_on == null){
            $starts_on = date("Y-m-d");
        }
        $this->content = $name;
        $this->due_on = $due_on;
        $this->assignee_ids = $assignee_ids;
        $this->starts_on = $starts_on;
        $this->notify = $notify;
        $this->completion_subscriber_ids = $completion_subscriber_ids;
        $this->description_file = $description_file;
    }

    static function load_from_api($api_url) {

        $response = api_curl_get($api_url);
        if ($response["headers"]["status"] == "200"){
            return self::load_from_api_response($response["body"]);
        } else {
            die("Could not get $api_url when loading todo from api in Todo::load_from_api, status: ".$response["headers"]["status"]);
        }
        
    }

    static function load_from_api_response($response) {

        # create a file to store description in (make sure not to overwrite any files)
        $description_file = generate_description_file($response->content, $response->description);

        $assignee_ids = [];
        foreach ($response["assignees"] as $assignee) {
            array_push($assignee_ids, $assignee->id);
        }
        $completion_subscriber_ids = [];
        foreach ($response["completion_subscribers"] as $completion_subscriber) {
            array_push($completion_subscriber_ids, $completion_subscriber["id"]);
        }

        # create it
        $instance = new self(
            $response["content"], $description_file,
            $response["due_on"], $assignee_ids, $response["starts_on"], 
            null, $completion_subscriber_ids);

        $instance->set_api_url($response["url"]);

        return $instance;

    }

    /*
    Sets the api_url, a link to the corresponding task in the basecamp api
    */
    function set_api_url($api_url) {
        $this->api_url = $api_url;
    }

    /*
    Returns a json body for bascamp api requests to make a todo
    */
    function generate_basecamp_json(){

        $myfile = fopen($this->description_file, "r") or die("Unable to open $this->description_file in Todo->generate_basecamp_json!");
        $description = fread($myfile,filesize($this->description_file)) or die("Unable to read $this->description_file in Todo->generate_basecamp_json!");
        fclose($myfile);

        return json_encode([
            "content" => $this->content,
            "description" => $description,
            "assignee_ids" => $this->assignee_ids,
            "completion_subscriber_ids" => $this->completion_subscriber_ids,
            "notify" => $this->notify,
            "due_on" => $this->due_on,
            "starts_on" => $this->starts_on
        ]);
    }
}
?>