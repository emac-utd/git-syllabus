<?php
    add_action( 'publish_post', $github);
    class github_api {

        function __construct($key, $secret) { 
            $this->$key = $key;
            $this->$secret = $secret;

            //take in API key and generate repo name and create repo.
        }

        function create_repo(){
            //curl -i -H 'Authorization: token TOKENHERE' -d '{"name":":NAME"}' https://api.github.com/user/repos
        }

        function commit() {

        }

        function pull () {

        }

        function get_repo_status() {

        }
    }

?>