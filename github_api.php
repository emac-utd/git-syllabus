<?php
    add_action( 'publish_post', $github);
    class github_api {

        $has_repo = false;

        function __construct($client_id, $state) { 
            $this->$client_id = $client_id;
            $this->$state = $state;

            //take in API key and generate repo name and create repo.
            $params = array(
                'client_id' => $client_id,
                'redirect_uri' => $redirect_uri,
                'state' => $state, 
                );
 
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