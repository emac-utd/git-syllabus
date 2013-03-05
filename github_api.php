<?php
    add_action( 'publish_post', $github);
    class github_api {

        const API_URL = 'https://api.github.com/';


        function __construct($oauth_token) { 
            $this->oauth_token = $oauth_token;
            $this->url = API_URL . $oauth_token . '/';

            //take in API key and generate repo name and create repo.
            $params = array(
                'client_id' => $client_id,
                'redirect_uri' => $redirect_uri,
                'state' => $state, 
                );
            
        }

        function get_user_data() {
            $response = wp_remote_get( $url . '/user');


            if ( is_wp_error( $response ) ) {
                echo 'get_user_data effed up';
            }
            else {
                $this->$owner = $response['body']['login'];
                $this->$type = $response['body']['type'];

                if ($this->$type != 'User') {
                    $this->is_org = true;
                }
            }  

        }

        function create_repo($name){
            //TODO: build in a verification that the repo doesn't exist yet

            //create
            $this->$repo_name = $name;

            //add conditional for creating for org? (different pathway)
            $args = array('name' => $name );

            $response = wp_remote_post( $url . 'user/repos', $args );

            if (is_wp_error( $response )) {
                echo 'repo creation effed up';
            }
            else {
                $has_repo = true;
                //right now just returning the url of the newly created repo.
                return $response['body']['url'];
            }

        }

        function access_existing_repo($name) {

        }

        function commit($owner, $message, $author, $parents, $tree) {

            $args = array(
                'message' => $message,
                'author' => $author,
                'parents' => $parents,
                'tree' => $tree, 
            );
                        
            $response = wp_remote_post( $url . 'repos/' . $this->$owner . '/' . $this->$repo_name . '/git/commits' , $args );

            if ( is_wp_error( $response ) ) {
                echo 'commit effed up';
            }
            else {
                //right now just returning the url of the newly created repo.
                return $response['body']['url'];
            }    
        }

        function get_repos()
        {
            $args = array('headers' => array('Accept' => 'application/json'));
            $response = wp_remote_get(github_api::API_URL . 'user/repos?access_token=' . $oauth_token, $args);

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'Get repos failed';
                return "";
            }

            else {
                return json_decode($response['body']);
            }

        }

        function pull () {

        }

        function get_repo_status() {
            //Need to figure out how deep to make this. Eventual thought is sync.
        }
    }

?>