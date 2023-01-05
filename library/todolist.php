<?php

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

        # add existing todos
        $response = api_curl_get($instance->todos_url);
        if ($response["headers"]["status"] != 200){
            die("Error: Unsuccessful curl get request to $instance->todos_url in TodoList::load_from_api, status: " 
            . $response["headers"]["status"]);
        }
        foreach ($response["body"] as $todo_json){
            $todo = Todo::load_from_api_response($todo_json);
            array_push($instance->todos, $todo);
        }

        return $instance;
    }

    function generate_basecamp_json(){
        return json_encode([
            "name" => $this->name,
            "description" => readfile($this->description_file)
        ]);
    }

    function add_todo($todo) {

        # make api request to add todo
        $data = $todo.generate_basecamp_json();
        $response = api_curl_post_json($this->todos_url, $data);
        if ($response["headers"]["status"] != "201"){
            die("Error: Unsuccessful curl post request to ".$this->todos_url." in TodoList::add_todo, status: " 
            . $response["headers"]["status"]);
        }
        $todo.set_api_url($response["body"]->url);

        # add todo into TodoList
        array_push($this->todos, $todo);
    }
}
?>