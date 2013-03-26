<?php
    class github_api {

        const API_URL = 'https://api.github.com/';


        function __construct($oauth_token) { 
            $this->oauth_token = $oauth_token;
            $this->get_user_data();
            $this->repo_name = 'wordpress';
            
        }

        function get_user_data() {  

            $args = array(
                'headers' => array( 
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . $this->oauth_token
                )
            );

            $response = wp_remote_get( github_api::API_URL . 'user', $args);


            if ( is_wp_error( $response ) ) {
                error_log('get_user_data effed up', 0);
            }
            else {
                $body = json_decode($response['body']);
                $this->owner = $body->login;
                $this->type = $body->type;

                if ($this->type != 'User') {
                    $this->is_org = true;
                }
            }  

        }

        function create_repo($name){
            //TODO: build in a verification that the repo doesn't exist yet

            //create
            $this->repo_name = $name;

            //add conditional for creating for org? (different pathway)
            $args = array('name' => $name );

            $response = wp_remote_post( $url . 'user/repos', $args );

            if (is_wp_error( $response )) {
                error_log('repo creation effed up', 0);
            }
            else {
                $this->has_repo = true;
                //right now just returning the url of the newly created repo.
                $body = json_decode($response['body']);
                return $body->url;
            }

        }

        function access_existing_repo($name) {

        }

        function commit($post) {
            $this->get_user_data();
            $content = $post->post_content;
            $file_name = $post->post_title;
            $git_url = github_api::API_URL . 'repos/' . $this->owner . '/' . $this->repo_name . '/git/';

            $args = array(
                'headers' => array( 
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . $this->oauth_token
                )
            );

            $response = wp_remote_get( $git_url . 'refs/heads/master', $args);


            //get sha from latest commit

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                error_log('getting latest sha failed: '.print_r($response, true), 0);
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_latest_commit = $body->object->sha;
            }

            $response = wp_remote_get( $git_url . 'commits/' . $sha_latest_commit, $args);

            //get $sha_base_tree

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                error_log('getting sha tree failed: '.print_r($response, true), 0);
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_base_tree = $body->tree->sha;
                error_log(print_r($response['body'], true));
            }

           $args = array(
               'body' => json_encode(array(
                   'base_tree' => $sha_base_tree,
                   'tree' => array(array(
                        'path' => $file_name,
                        'content' => $content,
                        'type' => 'tree',
                        'mode' => '100644'
                    ))

               )),
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . $this->oauth_token,
                    'Content-type' => 'application/json'
                )
            );   


            $response = wp_remote_post( $git_url . 'trees', $args  );


            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                error_log('creating tree failed: '.print_r($response, true), 0);
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_new_tree = $body->sha;
            }

            $args = array(
                'body' => json_encode(array(
                    'base_tree' => $sha_base_tree,
                    'parents' => array( $sha_latest_commit ),
                    'tree' => $sha_new_tree,
                    'message' => $post->post_title . ' posted from Wordpress'
                )),
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . $this->oauth_token,
                    'Content-type' => 'application/json'
                )
            );     

            $response = wp_remote_post( $git_url . 'commits', $args );

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                error_log('creating commit failed: '.print_r($response, true), 0);
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_new_commit = $body->sha;
            }

            $args = array(
                'body' => json_encode(array(
                    'sha' => $sha_new_commit,
                    'force' => true
                )),
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . $this->oauth_token,
                    'Content-type' => 'application/json'
                ),
                'method' => 'PATCH'
            );   


            $response = wp_remote_request( $git_url . 'refs/heads/master', $args );

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                error_log("creating reference failed: ".print_r($response, true), 0);
                return "";
            }

            else {
                $body = json_decode($response['body']);
            }

        }

        function get_repos()
        {
            $args = array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . $this->oauth_token
                )
            );
            $response = wp_remote_get(github_api::API_URL . 'user/repos', $args);

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

        function check_auth()
        {
            $args = array(
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'token ' . $this->oauth_token
                )
            );
            $response = wp_remote_get(github_api::API_URL . 'user', $args);
            if ( is_wp_error( $response ) || ($response['response']['code'] >= 400 &&  $response['response']['code'] < 500)) {
                return false;
            }
            return true;
        }
    }

?>