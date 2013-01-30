<?php
    add_action( 'publish_post', $github);
    class github_api {

        const OAUTH_URL = 'https://github.com/login/oauth/';
        $has_repo = false;

        function __construct($request, $client_id, $state) { 
            $this->$request = $request;
            $this->$client_id = $client_id;
            $this->$state = $state;

            //take in API key and generate repo name and create repo.
            $params = array(
                'client_id' => $client_id,
                'redirect_uri' => $redirect_uri,
                'state' => $state, 
                );
 
        }

        function create_repo($name){
            //create

            //add conditional for creating for org? (different pathway)
            $args = array('name' => $name );
            wp_remote_post( $OAUTH . '/user/repos', $args );
        }

        function commit() {
            
        }

        function pull () {

        }

        function get_repo_status() {

        }
    }

?>