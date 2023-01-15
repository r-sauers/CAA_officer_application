<?php

require_once("library/curl_funcs.php");
require_once("library/todo.php");
require_once("library/misc.php");

class TodoList
{
    private $name;
    private $description_file;
    private $api_url;
    private $todos_url;
    private $todos;

    function __construct($name, $description_file){
        $this->name = $name;
        $this->description_file = $description_file;
        $this->todos = [];
    }

    static function create_list($name, $description_file, $todoset_endpoint){
        $instance = new self($name, $description_file);

        $data = $instance->generate_basecamp_json();
        $response = api_curl_post_json($todoset_endpoint, $data);

        if ($response["headers"]["status"] == "201"){
            $instance->api_url = $response["body"]->url;
            $instance->todos_url = $response["body"]->todos_url;
        } else {
            die("Error: Unsuccessful curl post request to $todoset_endpoint in TodoList::create_list, status: ".$response["headers"]["status"]);
        }

        return $instance;
    }
    

    static function load_from_api($api_url){

        # get api data for todolist
        $response = api_curl_get($api_url);
        
        if($response["headers"]["status"] != "200") {
            die("Error: Unsuccessful curl get request to $api_url in TodoList::load_from_api, status: " 
            . $response["headers"]["status"]);
        }

        # create description file from api response
        $description_file = generate_description_file($response["body"]->name, $response["body"]->description);

        # create instance of TodoList and return it
        $instance = new self($response["body"]->name, $description_file);
        $instance->api_url = $api_url;
        $instance->todos_url = $response["body"]->todos_url;

        # add existing todos with pagination
        $link = $instance->todos_url;
        while (true) {

            // add todos from response
            $response = api_curl_get($link);
            
            if ($response["headers"]["status"] != 200){
                die("Error: Unsuccessful curl get request to $link TodoList::load_from_api, status: " 
                . $response["headers"]["status"]);
            }
            foreach ($response["body"] as $todo_json){
                $todo = Todo::load_from_api_response($todo_json);
                array_push($instance->todos, $todo);
            }

            // get next page link
            $raw_link = htmlspecialchars_decode($response["headers"]["link"]);
            $matches = [];
            if (!preg_match("/(?<=<).([^>]*)/", $raw_link, $matches)) {
                break;
            }
            $link = $matches[0];
        }
        
        
        

        return $instance;
    }

    function generate_basecamp_json(){

        $data = [
            "name" => $this->name,
        ];

        $myfile = fopen($this->description_file, "r") or die("Unable to open $this->description_file in TodoList->generate_basecamp_json!");
        $description = fread($myfile,filesize($this->description_file)) or die("Unable to read $this->description_file in TodoList->generate_basecamp_json!");
        fclose($myfile);
        $data["description"] = $description;

        return json_encode($data);
    }

    function add_todo($todo) {

        # make api request to add todo
        $data = $todo->generate_basecamp_json();
        $response = api_curl_post_json($this->todos_url, $data);
        if ($response["headers"]["status"] != "201"){
            die("Error: Unsuccessful curl post request to ".$this->todos_url." in TodoList::add_todo, status: " 
            . $response["headers"]["status"]);
        }
        $todo->set_api_url($response["body"]->url);

        # add todo into TodoList
        array_push($this->todos, $todo);
    }
}
?>