<?php
    add_action( 'publish_post', $github);
    class github_api {

        const API_URL = 'https://api.github.com/';
        $has_repo = false;

        function __construct($request, $client_id, $state) { 
            $this->$request = $requxest;
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
            $response = wp_remote_post( API_URL . 'user/repos', $args );
            if (is_wp_error( $response )) {
                echo 'repo creation effed up';
            }
            else {
                //right now just returning the url of the newly created repo.
                return $response['body']['url'];
            }

        }

        function commit($owner, $repo, $message, $author, $parents, $tree) {

            $args = array(
                'message' => $message,
                'author' => $author,
                'parents' => $parents,
                'tree' => $tree, 
            );
                        
            $response = wp_remote_post( API_URL . 'repos/' . $owner . '/' . $repo . '/git/commits' , $args = array )

            if (is_wp_error( $response )) {
                echo 'commit effed up';
            }
            else {
                //right now just returning the url of the newly created repo.
                return $response['body']['url'];
            }    
        }

        function pull () {

        }

        function get_repo_status() {
            //Need to figure out how deep to make this. Eventual thought is sync.
        }
    }

?>