<?php

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

    function __construct($name, $description_file, $due_on, $assignee_ids=[], $starts_on=date("Y-m-d"), $notify=true, $completion_subscriber_ids=[]){
        $this->content = $name;
        $this->due_on = $due_on;
        $this->assignee_ids = $assignee_ids;
        $this->starts_on = $starts_on;
        $this->notify = $notify;
        $this->completion_subscriber_ids = $completion_subscriber_ids;
        $this->description_file = $description_file;
    }

    static function loadFromApi($api_url) {
        $response = api_curl_get($api_url);

        # create a file to store description in (make sure not to overwrite any files)
        $dir = "descriptions/";
        $filename = $response->content.".rtf";
        $version = 0;
        $filename_used = true;
        while ($filename_used) {
            $filename_used = false;
            if ($dh = opendir($dir)){
                while (($file = readdir($dh)) !== false){
                    if ($file == $filename){
                        $filename_used = true;
                        $version += 1;
                        $filename = $response->content . " (" . $version . ").rtf";
                        break;
                    }
                }
            }
        }
        $description_file = $dir . $filename;
        $new_file = fopen($description_file, "w") or die("Unable to create file!");
        fwrite($new_file, $response->description);
        fclose($new_file);
        
        # create it
        $instance = new self(
            $response->content, $description_file, 
            $response->due_on, $response->starts_on, 
            $response->notify, $response->completion_subscriber_ids);

        $instance->setApiUrl($api_url);

        return $instance;
    }

    static function loadFromDatabase() {
        die("NOT IMPLEMENTED");
        exit;
    }

    /*
    Sets the api_url, a link to the corresponding task in the basecamp api
    */
    function setApiUrl($api_url) {
        $this->api_url = $api_url;
    }

    /*
    Returns a json body for bascamp api requests to make a todo
    */
    function makeBasecampJson(){
        return json_encode([
            "content" => $this->content,
            "description" => read_file($this->description_file),
            "assignee_ids" => $this->assignee_ids,
            "completion_subscriber_ids" => $this->completion_subscriber_ids,
            "notify" => $this->notify,
            "due_on" => $this->due_on,
            "starts_on" => $this->starts_on
        ]);
    }
}
?>